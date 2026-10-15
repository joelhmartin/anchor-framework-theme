/**
 * Anchor Framework — Navigation (anchor-dental)
 * Header scroll state, scroll-direction reveal, mobile menu, dropdowns.
 * Extends the DEKA export navigation.js with body.anchor-scrolled +
 * body.anchor-nav-hidden drivers for the super header and call bar.
 */
(function () {
    'use strict';

    const nav = document.getElementById('anchor-nav');
    const hamburger = document.getElementById('anchor-hamburger');
    const overlay = document.getElementById('anchor-mobile-overlay');
    const callBar = document.getElementById('anchor-call-bar');

    if (!nav) return;

    const body = document.body;

    // Keep the call bar's aria-hidden in sync with its visibility (it is only
    // revealed + interactive while body.anchor-nav-hidden is set).
    function syncCallBar() {
        if (callBar) {
            callBar.setAttribute('aria-hidden', body.classList.contains('anchor-nav-hidden') ? 'false' : 'true');
        }
    }
    const SCROLL_AT = 80;   // solidify nav / hide super header
    const HIDE_AT = 300;    // past here + scrolling down => hide nav, show call bar
    const DELTA = 4;        // ignore micro jitters

    let scrolled = false;
    let mobileOpen = false;
    let lastY = window.scrollY;
    let ticking = false;
    let scrollLockY = 0;   // saved scroll position while the mobile overlay locks the body

    function applyScrollState() {
        const y = window.scrollY;
        const isScrolled = y > SCROLL_AT;

        if (isScrolled !== scrolled) {
            scrolled = isScrolled;
            nav.classList.toggle('anchor-nav--scrolled', scrolled);
            body.classList.toggle('anchor-scrolled', scrolled);

            // Logo swap (white over hero, dark when scrolled)
            const logoLight = nav.querySelector('.anchor-nav__logo-light');
            const logoDark = nav.querySelector('.anchor-nav__logo-dark');
            if (logoLight && logoDark) {
                logoLight.style.display = mobileOpen ? 'block' : (scrolled ? 'none' : '');
                logoDark.style.display = mobileOpen ? 'none' : (scrolled ? '' : 'none');
            }

            // Hamburger bar colors
            const bars = hamburger ? hamburger.querySelectorAll('.anchor-hamburger__bar') : [];
            bars.forEach(function (bar) {
                bar.style.background = mobileOpen ? 'white' : (scrolled ? 'var(--anchor-navy)' : 'white');
            });
        }

        // Direction-driven hide/reveal. Never hide while the mobile menu is open.
        if (!mobileOpen) {
            const goingDown = y > lastY + DELTA;
            const goingUp = y < lastY - DELTA;
            if (y > HIDE_AT && goingDown) {
                body.classList.add('anchor-nav-hidden');
            } else if (goingUp || y <= HIDE_AT) {
                body.classList.remove('anchor-nav-hidden');
            }
        }

        syncCallBar();
        lastY = y;
        ticking = false;
    }

    function onScroll() {
        if (!ticking) {
            window.requestAnimationFrame(applyScrollState);
            ticking = true;
        }
    }

    window.addEventListener('scroll', onScroll, { passive: true });
    applyScrollState();

    // --- Mobile menu toggle ---
    if (hamburger && overlay) {
        hamburger.addEventListener('click', function () {
            mobileOpen = !mobileOpen;

            hamburger.classList.toggle('is-open', mobileOpen);
            overlay.classList.toggle('is-open', mobileOpen);
            nav.classList.toggle('anchor-nav--open', mobileOpen);

            // Lock body scroll while the overlay is open. body{overflow:hidden} alone
            // does not hold on mobile (touch scrolling, iOS), so pin the body with
            // position:fixed and restore the scroll position on close.
            if (mobileOpen) {
                scrollLockY = window.scrollY;
                body.style.overflow = 'hidden';
                body.style.position = 'fixed';
                body.style.top = (-scrollLockY) + 'px';
                body.style.left = '0';
                body.style.right = '0';
                body.style.width = '100%';
            } else {
                body.style.overflow = '';
                body.style.position = '';
                body.style.top = '';
                body.style.left = '';
                body.style.right = '';
                body.style.width = '';
                window.scrollTo(0, scrollLockY);
                // Reset any open drill-down panes so the menu reopens at the root.
                if (overlay && overlay._closeAllPanes) overlay._closeAllPanes();
            }

            // Opening the menu must reveal the nav (clear the hidden driver).
            if (mobileOpen) body.classList.remove('anchor-nav-hidden');
            syncCallBar();

            const bars = hamburger.querySelectorAll('.anchor-hamburger__bar');
            bars.forEach(function (bar) {
                bar.style.background = mobileOpen ? 'white' : (scrolled ? 'var(--anchor-navy)' : 'white');
            });

            const logoLight = nav.querySelector('.anchor-nav__logo-light');
            const logoDark = nav.querySelector('.anchor-nav__logo-dark');
            if (logoLight && logoDark) {
                // Force-show the white logo while the menu is open. Using 'block'
                // (not '') beats the .anchor-nav--scrolled display:none rule that
                // otherwise hides BOTH logos when the menu is opened after scrolling.
                logoLight.style.display = mobileOpen ? 'block' : (scrolled ? 'none' : '');
                logoDark.style.display = mobileOpen ? 'none' : (scrolled ? '' : 'none');
            }
        });

        // Close on link click
        overlay.querySelectorAll('a').forEach(function (link) {
            link.addEventListener('click', function () {
                if (mobileOpen) hamburger.click();
            });
        });
    }

    // --- Mobile drill-down panes ---
    // Each .anchor-mobile-overlay__sub is a full-screen pane that slides + fades in
    // from the right when its .anchor-mobile-overlay__toggle is tapped. A back /
    // breadcrumb header (injected here, derived from the trigger label) returns to
    // the previous level. Supports arbitrary nesting via a pane stack. Generic to
    // the framework — any header using the __toggle/__sub pattern gets panes free.
    if (overlay) {
        const paneStack = [];

        function openPane(pane) {
            if (!pane || pane.classList.contains('is-open')) return;
            pane.classList.add('is-open');
            pane.scrollTop = 0;
            paneStack.push(pane);
            overlay.classList.add('has-pane-open');
        }

        function closePane(pane) {
            if (!pane) return;
            pane.classList.remove('is-open');
            const i = paneStack.indexOf(pane);
            if (i > -1) paneStack.splice(i, 1);
            if (!paneStack.length) overlay.classList.remove('has-pane-open');
        }

        // Reset every open pane (used when the whole menu closes).
        overlay._closeAllPanes = function () {
            paneStack.slice().forEach(closePane);
        };

        overlay.querySelectorAll('.anchor-mobile-overlay__sub').forEach(function (pane) {
            const trigger = overlay.querySelector('.anchor-mobile-overlay__toggle[data-target="' + pane.id + '"]');
            const title = trigger ? trigger.textContent.trim().replace(/\s+/g, ' ') : 'Back';

            if (!pane.querySelector('.anchor-mobile-overlay__pane-head')) {
                const head = document.createElement('button');
                head.type = 'button';
                head.className = 'anchor-mobile-overlay__pane-head';
                head.setAttribute('aria-label', 'Back');
                const arrow = document.createElement('span');
                arrow.className = 'anchor-mobile-overlay__pane-back';
                arrow.setAttribute('aria-hidden', 'true');
                const label = document.createElement('span');
                label.className = 'anchor-mobile-overlay__pane-title';
                label.textContent = title;
                head.appendChild(arrow);
                head.appendChild(label);
                head.addEventListener('click', function () { closePane(pane); });
                pane.insertBefore(head, pane.firstChild);
            }
        });

        overlay.querySelectorAll('.anchor-mobile-overlay__toggle').forEach(function (btn) {
            btn.addEventListener('click', function () {
                openPane(document.getElementById(this.dataset.target));
            });
        });
    }

    // --- Service cards (ac-card): mobile single-open animated accordion ---
    // Desktop (>=992px) shows all panels via CSS. On mobile, tapping a card's
    // summary opens it (animating max-height to its scrollHeight) and closes any
    // other open card — only one open at a time.
    (function acCardAccordion() {
        const cards = Array.prototype.slice.call(document.querySelectorAll('.ac-card'));
        if (!cards.length) return;
        const mq = window.matchMedia('(max-width: 991px)');
        const panelOf = function (card) { return card.querySelector('.ac-card__panel'); };

        function closeCard(card) {
            const p = panelOf(card);
            if (!p) return;
            if (card.classList.contains('is-open')) p.style.maxHeight = p.scrollHeight + 'px';
            window.requestAnimationFrame(function () { p.style.maxHeight = '0px'; });
            card.classList.remove('is-open');
        }
        function openCard(card) {
            cards.forEach(function (c) { if (c !== card) closeCard(c); });
            const p = panelOf(card);
            if (!p) return;
            card.classList.add('is-open');
            p.style.maxHeight = p.scrollHeight + 'px';
        }

        cards.forEach(function (card) {
            const summary = card.querySelector('.ac-card__summary');
            if (!summary) return;
            summary.addEventListener('click', function (e) {
                if (!mq.matches) return;   // desktop: panels always shown
                e.preventDefault();        // summary is a <button>; no default to worry about
                if (card.classList.contains('is-open')) closeCard(card);
                else openCard(card);
            });
        });

        function syncViewport() {
            cards.forEach(function (card) {
                const p = panelOf(card);
                if (!p) return;
                if (mq.matches) {
                    if (!card.classList.contains('is-open')) p.style.maxHeight = '0px';
                } else {
                    p.style.maxHeight = '';
                    card.classList.remove('is-open');
                }
            });
        }
        if (mq.addEventListener) mq.addEventListener('change', syncViewport);
        else if (mq.addListener) mq.addListener(syncViewport);
        syncViewport();
    })();

    // --- Desktop dropdown hover ---
    // Centered mega panels can be wider than the gap between their trigger and
    // the viewport edge. CSS centers them (--dd-x: -50%); this nudges --dd-x just
    // enough to keep the panel on-screen when centering would clip it.
    function clampMega(dropdown) {
        if (!dropdown.classList.contains('anchor-nav__dropdown--mega')) return;
        const inner = dropdown.querySelector('.anchor-nav__dropdown-inner');
        if (!inner) return;
        dropdown.style.setProperty('--dd-x', '-50%'); // reset to centered baseline
        const rect = inner.getBoundingClientRect();
        const margin = 12;
        const vw = document.documentElement.clientWidth;
        let shift = 0;
        if (rect.left < margin) {
            shift = margin - rect.left;
        } else if (rect.right > vw - margin) {
            shift = (vw - margin) - rect.right;
        }
        if (shift !== 0) {
            dropdown.style.setProperty('--dd-x', 'calc(-50% + ' + Math.round(shift) + 'px)');
        }
    }

    document.querySelectorAll('.anchor-nav__item-wrap').forEach(function (wrap) {
        let timeout;
        const dropdown = wrap.querySelector('.anchor-nav__dropdown');
        const btn = wrap.querySelector('.anchor-nav__link');
        if (!dropdown) return;
        wrap.addEventListener('mouseenter', function () {
            clearTimeout(timeout);
            clampMega(dropdown);
            dropdown.classList.add('is-open');
            if (btn) btn.setAttribute('aria-expanded', 'true');
        });
        wrap.addEventListener('mouseleave', function () {
            timeout = setTimeout(function () {
                dropdown.classList.remove('is-open');
                if (btn) btn.setAttribute('aria-expanded', 'false');
            }, 150);
        });
    });

})();
