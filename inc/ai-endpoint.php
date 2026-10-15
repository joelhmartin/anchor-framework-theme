<?php
/**
 * REST endpoint that runs the AI edit loop for a page.
 *
 * @package AnchorFramework
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the route.
 */
function ac_ai_register_route() {
	register_rest_route(
		'anchor-dental/v1',
		'/ai',
		array(
			'methods'             => 'POST',
			'callback'            => 'ac_ai_handle_request',
			'permission_callback' => function () {
				return current_user_can( 'edit_pages' );
			},
			'args'                => array(
				'post_id' => array( 'type' => 'integer', 'required' => false ),
				'prompt'  => array( 'type' => 'string', 'required' => true ),
				'html'    => array( 'type' => 'string', 'required' => false ),
				'css'     => array( 'type' => 'string', 'required' => false ),
				'js'      => array( 'type' => 'string', 'required' => false ),
			),
		)
	);
}
add_action( 'rest_api_init', 'ac_ai_register_route' );

/**
 * Build the system prompt.
 *
 * @return string
 */
function ac_ai_system_prompt() {
	return implode(
		"\n",
		array(
			'You are an expert front-end developer embedded in a WordPress theme called Anchor Framework.',
			'You edit ONE page at a time. The page consists of three parts the user can change:',
			'- HTML: the page body markup, rendered between the theme\'s shared header and footer.',
			'- CSS: custom CSS for this page only (injected in <style> in the head).',
			'- JS: custom JavaScript for this page only (injected in <script> in the footer).',
			'The theme defines CSS variables such as --primary-color, --accent-color, --text-color, --secondary-color. Prefer these over hard-coded colors.',
			'You may call read_theme_file to inspect theme files (e.g. assets/css/custom.css, header.php) before editing. Only read what you need.',
			'When you are finished, call submit_edits with ONLY the fields you changed (html, css, and/or js) plus a short explanation.',
			'Return complete file contents for any field you include (not a diff/patch). Keep changes minimal and consistent with the existing code style.',
		)
	);
}

/**
 * Handle the request: run the tool loop.
 *
 * @param WP_REST_Request $req Request.
 * @return WP_REST_Response
 */
function ac_ai_handle_request( $req ) {
	$provider = ac_ai_make_provider();
	if ( ! $provider ) {
		return new WP_REST_Response( array( 'ok' => false, 'error' => __( 'No AI provider is configured.', 'anchor-framework' ) ), 200 );
	}

	$prompt = (string) $req->get_param( 'prompt' );
	$html   = (string) $req->get_param( 'html' );
	$css    = (string) $req->get_param( 'css' );
	$js     = (string) $req->get_param( 'js' );

	$tools  = ac_ai_tools();
	$system = ac_ai_system_prompt();

	$user_content = "Here is the current page.\n\n=== HTML ===\n" . $html . "\n\n=== CSS ===\n" . $css . "\n\n=== JS ===\n" . $js . "\n\n=== REQUEST ===\n" . $prompt;
	$messages = array( array( 'role' => 'user', 'content' => $user_content ) );

	$trace = array();
	$max_iters = 6;

	for ( $i = 0; $i < $max_iters; $i++ ) {
		$resp = $provider->chat( $system, $messages, $tools );

		if ( 'error' === $resp['type'] ) {
			return new WP_REST_Response( array( 'ok' => false, 'error' => $resp['error'], 'trace' => $trace ), 200 );
		}

		if ( 'text' === $resp['type'] ) {
			// Model answered without calling submit_edits — no structured edits.
			return new WP_REST_Response(
				array( 'ok' => true, 'edits' => new stdClass(), 'explanation' => $resp['text'], 'trace' => $trace ),
				200
			);
		}

		// tool_calls: record assistant turn, then handle each call.
		$messages[] = array( 'role' => 'assistant', 'content' => '', 'tool_calls' => $resp['raw_assistant'], 'raw_assistant' => $resp['raw_assistant'] );

		$submit = null;
		foreach ( $resp['calls'] as $call ) {
			$name = $call['name'];
			$args = $call['args'];
			$trace[] = array( 'tool' => $name, 'args' => ( 'read_theme_file' === $name ? $args : array_keys( $args ) ) );

			if ( 'submit_edits' === $name ) {
				$submit = $args;
				// Provide a tool result so a well-formed transcript exists (not strictly needed since we stop).
				$messages[] = array( 'role' => 'tool', 'tool_call_id' => $call['id'], 'name' => $name, 'content' => 'OK' );
				continue;
			}

			if ( isset( $tools[ $name ]['callback'] ) && is_callable( $tools[ $name ]['callback'] ) ) {
				$result = call_user_func( $tools[ $name ]['callback'], $args );
			} else {
				$result = 'ERROR: unknown tool ' . $name;
			}
			$messages[] = array( 'role' => 'tool', 'tool_call_id' => $call['id'], 'name' => $name, 'content' => (string) $result );
		}

		if ( null !== $submit ) {
			$edits = array();
			foreach ( array( 'html', 'css', 'js' ) as $k ) {
				if ( isset( $submit[ $k ] ) && is_string( $submit[ $k ] ) ) {
					$edits[ $k ] = $submit[ $k ];
				}
			}
			return new WP_REST_Response(
				array(
					'ok'          => true,
					'edits'       => empty( $edits ) ? new stdClass() : $edits,
					'explanation' => isset( $submit['explanation'] ) ? (string) $submit['explanation'] : '',
					'trace'       => $trace,
				),
				200
			);
		}
	}

	return new WP_REST_Response(
		array( 'ok' => false, 'error' => __( 'The AI did not finish within the step limit. Try a simpler request.', 'anchor-framework' ), 'trace' => $trace ),
		200
	);
}
