<?php
/**
 * AI Assistant settings: providers, API keys, models.
 *
 * @package AnchorFramework
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Provider definitions: key => [label, default model].
 *
 * @return array
 */
function ac_ai_providers() {
	return array(
		'anthropic' => array( 'Anthropic (Claude)', 'claude-sonnet-4-6' ),
		'openai'    => array( 'OpenAI (GPT)', 'gpt-4o' ),
		'gemini'    => array( 'Google (Gemini)', 'gemini-2.0-flash' ),
	);
}

/**
 * Register the AI Assistant settings page under the theme menu (top-level if none).
 */
function ac_ai_settings_menu() {
	add_menu_page(
		__( 'AI Assistant', 'anchor-framework' ),
		__( 'AI Assistant', 'anchor-framework' ),
		'manage_options',
		'ac-ai-settings',
		'ac_ai_settings_render',
		'dashicons-superhero',
		59
	);
}
add_action( 'admin_menu', 'ac_ai_settings_menu' );

/**
 * Register the option + sanitizer.
 */
function ac_ai_settings_register() {
	register_setting(
		'ac_ai_settings_group',
		'ac_ai_settings',
		array(
			'type'              => 'array',
			'sanitize_callback' => 'ac_ai_settings_sanitize',
			'default'           => array(),
		)
	);
}
add_action( 'admin_init', 'ac_ai_settings_register' );

/**
 * Sanitize submitted AI settings. Empty key fields preserve the existing key.
 *
 * @param array $input Raw input.
 * @return array
 */
function ac_ai_settings_sanitize( $input ) {
	$existing  = get_option( 'ac_ai_settings', array() );
	$providers = ac_ai_providers();
	$clean     = array();

	$active = isset( $input['active'] ) ? sanitize_text_field( $input['active'] ) : '';
	$clean['active'] = array_key_exists( $active, $providers ) ? $active : '';

	foreach ( $providers as $key => $def ) {
		$model = isset( $input[ $key ]['model'] ) ? sanitize_text_field( $input[ $key ]['model'] ) : $def[1];
		$new_key = isset( $input[ $key ]['api_key'] ) ? trim( (string) $input[ $key ]['api_key'] ) : '';
		// Preserve existing key when the field is left blank.
		if ( '' === $new_key && ! empty( $existing[ $key ]['api_key'] ) ) {
			$new_key = $existing[ $key ]['api_key'];
		}
		$clean[ $key ] = array(
			'api_key' => $new_key,
			'model'   => '' !== $model ? $model : $def[1],
		);
	}
	return $clean;
}

/**
 * Render the settings page.
 */
function ac_ai_settings_render() {
	$saved     = get_option( 'ac_ai_settings', array() );
	$providers = ac_ai_providers();
	$active    = isset( $saved['active'] ) ? $saved['active'] : '';
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'AI Assistant', 'anchor-framework' ); ?></h1>
		<p><?php esc_html_e( 'Configure the AI provider used by the inline page editor. Keys are stored on the server and never sent to the browser.', 'anchor-framework' ); ?></p>
		<form method="post" action="options.php">
			<?php settings_fields( 'ac_ai_settings_group' ); ?>
			<table class="form-table" role="presentation"><tbody>
				<tr>
					<th scope="row"><label for="ac-ai-active"><?php esc_html_e( 'Active provider', 'anchor-framework' ); ?></label></th>
					<td>
						<select id="ac-ai-active" name="ac_ai_settings[active]">
							<option value="" <?php selected( $active, '' ); ?>><?php esc_html_e( '— none —', 'anchor-framework' ); ?></option>
							<?php foreach ( $providers as $key => $def ) : ?>
								<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $active, $key ); ?>><?php echo esc_html( $def[0] ); ?></option>
							<?php endforeach; ?>
						</select>
					</td>
				</tr>
				<?php foreach ( $providers as $key => $def ) :
					$has_key = ! empty( $saved[ $key ]['api_key'] );
					$model   = isset( $saved[ $key ]['model'] ) && '' !== $saved[ $key ]['model'] ? $saved[ $key ]['model'] : $def[1];
					?>
					<tr>
						<th scope="row"><?php echo esc_html( $def[0] ); ?></th>
						<td>
							<p>
								<label><?php esc_html_e( 'API key', 'anchor-framework' ); ?><br>
								<input type="password" class="regular-text" name="ac_ai_settings[<?php echo esc_attr( $key ); ?>][api_key]" value="" autocomplete="off"
									placeholder="<?php echo $has_key ? esc_attr__( '•••••••• (saved — leave blank to keep)', 'anchor-framework' ) : esc_attr__( 'Paste API key', 'anchor-framework' ); ?>" /></label>
							</p>
							<p>
								<label><?php esc_html_e( 'Model', 'anchor-framework' ); ?><br>
								<input type="text" class="regular-text" name="ac_ai_settings[<?php echo esc_attr( $key ); ?>][model]" value="<?php echo esc_attr( $model ); ?>" /></label>
							</p>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody></table>
			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}
