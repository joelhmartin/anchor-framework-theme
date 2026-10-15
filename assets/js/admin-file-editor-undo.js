/**
 * Persist undo history across reloads in the WordPress File Editors.
 *
 * Two strategies, picked at runtime:
 *   1. CodeMirror present  -> mirror cm.getHistory()/setHistory() to localStorage.
 *   2. Plain textarea only -> maintain our own snapshot undo/redo stack.
 *
 * Storage is keyed per file (theme|plugin + relative path) so switching files
 * never restores the wrong history. History is only restored when the on-screen
 * content still matches what we last stored — after a save+reload the saved file
 * equals our last snapshot, so undo can walk back through the whole session.
 *
 * Enqueued only on theme-editor.php / plugin-editor.php (see admin-file-editor-undo.php).
 */
( function ( $ ) {
	'use strict';

	var PREFIX = 'acFileEditorUndo:';
	var MAX_BYTES = 1500000; // ~1.5MB guard per entry; skip persisting if larger.

	/**
	 * Build a stable storage key from the form's theme/plugin + file fields.
	 */
	function storageKey() {
		var $form = $( '#template, #newcontent' ).closest( 'form' ).first();
		if ( ! $form.length ) {
			$form = $( 'form[name="template"]' ).first();
		}
		function field( name ) {
			var $el = $form.find( '[name="' + name + '"]' );
			return $el.length ? String( $el.val() || '' ) : '';
		}
		// theme-editor uses `theme`; plugin-editor uses `plugin`. `file` is shared.
		var scope = field( 'theme' ) || field( 'plugin' ) || 'unknown';
		var file = field( 'file' ) || '';
		return PREFIX + encodeURIComponent( scope ) + '|' + encodeURIComponent( file );
	}

	function read( key ) {
		try {
			var raw = window.localStorage.getItem( key );
			return raw ? JSON.parse( raw ) : null;
		} catch ( e ) {
			return null;
		}
	}

	function write( key, obj ) {
		try {
			var str = JSON.stringify( obj );
			if ( str.length > MAX_BYTES ) {
				return; // Too large to safely store; leave prior entry in place.
			}
			window.localStorage.setItem( key, str );
		} catch ( e ) {
			// Quota or privacy mode — best effort, drop our own stale keys and retry once.
			try {
				for ( var i = window.localStorage.length - 1; i >= 0; i-- ) {
					var k = window.localStorage.key( i );
					if ( k && k.indexOf( PREFIX ) === 0 && k !== key ) {
						window.localStorage.removeItem( k );
					}
				}
				window.localStorage.setItem( key, JSON.stringify( obj ) );
			} catch ( e2 ) {
				/* give up silently */
			}
		}
	}

	function debounce( fn, wait ) {
		var t;
		return function () {
			var ctx = this,
				args = arguments;
			clearTimeout( t );
			t = setTimeout( function () {
				fn.apply( ctx, args );
			}, wait );
		};
	}

	/**
	 * Small, non-blocking confirmation so the user knows undo survived the reload.
	 */
	function flash( message ) {
		var $n = $(
			'<div class="ac-undo-flash" role="status" style="' +
				'position:fixed;z-index:100000;right:20px;bottom:20px;' +
				'background:#1e1e1e;color:#fff;padding:10px 16px;border-radius:6px;' +
				'font-size:13px;box-shadow:0 4px 16px rgba(0,0,0,.3);opacity:0;' +
				'transition:opacity .25s ease;">' +
				message +
				'</div>'
		);
		$( 'body' ).append( $n );
		requestAnimationFrame( function () {
			$n.css( 'opacity', '1' );
		} );
		setTimeout( function () {
			$n.css( 'opacity', '0' );
			setTimeout( function () {
				$n.remove();
			}, 300 );
		}, 2600 );
	}

	/* ----------------------------------------------------------------------
	 * Strategy 1: CodeMirror
	 * ------------------------------------------------------------------- */
	function wireCodeMirror( cm, key ) {
		var stored = read( key );
		if ( stored && stored.mode === 'cm' && stored.value === cm.getValue() && stored.history ) {
			try {
				cm.setHistory( stored.history );
				flash( 'Undo history restored — Ctrl/Cmd+Z still works.' );
			} catch ( e ) {
				/* incompatible shape — ignore */
			}
		}

		var persist = function () {
			write( key, {
				mode: 'cm',
				value: cm.getValue(),
				history: cm.getHistory(),
				t: Date.now()
			} );
		};

		cm.on( 'change', debounce( persist, 400 ) );
		// Capture the final state right before the reload/navigation.
		$( window ).on( 'beforeunload', persist );
		persist();
	}

	/* ----------------------------------------------------------------------
	 * Strategy 2: plain <textarea> fallback (custom undo/redo stack)
	 * ------------------------------------------------------------------- */
	function wireTextarea( el, key ) {
		var stack, ptr;
		var stored = read( key );

		function snapshot() {
			return { value: el.value, s: el.selectionStart, e: el.selectionEnd };
		}

		if (
			stored &&
			stored.mode === 'ta' &&
			stored.stack &&
			stored.stack.length &&
			stored.stack[ stored.stack.length - 1 ].value === el.value
		) {
			stack = stored.stack;
			ptr = stack.length - 1;
			flash( 'Undo history restored — Ctrl/Cmd+Z still works.' );
		} else {
			stack = [ snapshot() ];
			ptr = 0;
		}

		var persist = debounce( function () {
			write( key, { mode: 'ta', stack: stack, t: Date.now() } );
		}, 200 );

		function pushIfChanged() {
			if ( el.value === stack[ ptr ].value ) {
				return;
			}
			// Drop any redo branch, then push the new state.
			stack = stack.slice( 0, ptr + 1 );
			stack.push( snapshot() );
			// Cap stack length to keep storage bounded.
			if ( stack.length > 200 ) {
				stack.shift();
			}
			ptr = stack.length - 1;
			persist();
		}

		function apply( state ) {
			el.value = state.value;
			try {
				el.setSelectionRange( state.s, state.e );
			} catch ( e ) {
				/* selection out of range after restore — ignore */
			}
			// Keep WordPress' dirty/lint tracking in sync.
			$( el ).trigger( 'change' ).trigger( 'input' );
			persist();
		}

		var record = debounce( pushIfChanged, 350 );
		$( el ).on( 'input', record );

		$( el ).on( 'keydown', function ( ev ) {
			var mod = ev.ctrlKey || ev.metaKey;
			if ( ! mod ) {
				return;
			}
			var k = ev.key.toLowerCase();
			var isRedo = ( k === 'z' && ev.shiftKey ) || k === 'y';
			var isUndo = k === 'z' && ! ev.shiftKey;
			if ( ! isUndo && ! isRedo ) {
				return;
			}
			// Commit any pending edit before moving the pointer.
			pushIfChanged();
			if ( isUndo && ptr > 0 ) {
				ev.preventDefault();
				ptr--;
				apply( stack[ ptr ] );
			} else if ( isRedo && ptr < stack.length - 1 ) {
				ev.preventDefault();
				ptr++;
				apply( stack[ ptr ] );
			}
		} );

		$( window ).on( 'beforeunload', pushIfChanged );
	}

	/* ----------------------------------------------------------------------
	 * Boot: wait for CodeMirror (init is deferred) then pick a strategy.
	 * ------------------------------------------------------------------- */
	$( function () {
		var key = storageKey();
		var textarea = document.getElementById( 'newcontent' );
		var tries = 0;

		( function waitForCM() {
			var cm =
				window.wp &&
				wp.themePluginEditor &&
				wp.themePluginEditor.instance &&
				wp.themePluginEditor.instance.codemirror;

			if ( cm ) {
				wireCodeMirror( cm, key );
				return;
			}
			if ( tries++ < 40 ) {
				setTimeout( waitForCM, 75 ); // up to ~3s for the deferred init.
				return;
			}
			// No CodeMirror — use the textarea fallback if present.
			if ( textarea ) {
				wireTextarea( textarea, key );
			}
		} )();
	} );
} )( jQuery );
