<?php

/**
 * Revendless settings
 */
class RevendlessSettings
{

	/**
	 * Initialize settings
	 *
	 * @return void
	 */
	public static function init()
	{
		add_action('admin_menu', array('RevendlessSettings', 'addSettingsMenu'));
		add_action( 'admin_enqueue_scripts', array( 'RevendlessSettings', 'enqueueScripts' ) );
	}

	/**
	 * Add settings menu
	 *
	 * @return void
	 */
	public static function addSettingsMenu()
	{
		if(!class_exists('RevendlessSettingsGeneral')) {
			require_once(dirname(__FILE__).'/settings-general.php' );
		}

		RevendlessSettingsGeneral::addMenuItem();
	}

	/**
	 * Enqueue scripts
	 *
	 * @return void
	 */
	public static function enqueueScripts()
	{
		wp_enqueue_style('revendless-admin-icons', plugins_url('static/css/admin/icons.css', dirname(__FILE__)));
	}

	/**
	 * Settings page template
	 *
	 * @param string $pageSlug page slug
	 * @param string $pageTitle page title
	 * @return void
	 */
	public static function settingsPageTemplate($pageSlug, $pageTitle)
	{
		print '<div class="wrap">';

		/**
		 * Echo content before the page header
		 */
		do_action('revendless_settings_before_header_'.$pageSlug);

		print '<header><h2>'.esc_html($pageTitle).'</h2></header>';

		/**
		 * Echo content after the page header
		 */
		do_action('revendless_settings_after_header_'.$pageSlug);

		settings_errors('general');

		print '<form method="post" action="options.php">';

		settings_fields($pageSlug);
		do_settings_sections($pageSlug);
		submit_button();

		print '</form>';
		print '</div>';

		/**
		 * Echo content at the bottom of the page
		 */
		do_action( 'revendless_settings_footer_'.$pageSlug);
	}

	/**
	 * Clean up custom form field attributes (fieldset, input, select) before use.
	 * Used by widget builders. Could be used by other plugins building on top of plugin
	 *
	 * @param array $attributes attributes that may possibly map to a HTML attribute we would like to use
	 * @param array $default_values fallback values
	 * @return array sanitized values unique to each field
	 */
	public static function parse_form_field_attributes($attributes, $default_values)
	{
		$attributes = wp_parse_args( (array) $attributes, $default_values );

		if ( ! empty( $attributes['id'] ) )
			$attributes['id'] = sanitize_html_class( $attributes['id'] );
		if ( ! empty( $attributes['class'] ) ) {
			$classes = explode( ' ', $attributes['class'] );
			array_walk( $classes, 'sanitize_html_class' );
			$attributes['class'] = implode( ' ', $classes );
		}

		return $attributes;
	}

}