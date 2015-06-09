<?php

/**
 * Revendless settings general
 */
class RevendlessSettingsGeneral extends RevendlessSettings
{

	/**
	 * Page slug
	 *
	 * @var string
	 */
	const PAGE_SLUG = 'revendless-general-settings';

	/**
	 * Option name
	 *
	 * @var string
	 */
	const OPTION_NAME = 'revendless_settings';

	/**
	 * Options
	 *
	 * @var string
	 */
	protected $options = array();

	/**
	 * Hook suffix
	 *
	 * @var string
	 */
	protected $hookSuffix = '';

	/**
	 * Url shortener choices
	 *
	 * @var array
	 */
	public static $urlShortenerChoices = array(
		'google' => 'goo.gl'
	);

	/**
	 * Construct
	 *
	 * @param array $options existing options
	 */
	public function __construct(array $options = array())
	{
		if(!empty($options)) {
			$this->options = $options;
		}
	}

	/**
	 * Add menu item
	 *
	 * @return string
	 */
	public static function addMenuItem()
	{
		$generalSettings = new self();

		$hookSuffix = add_utility_page(
			__('Revendless Plugin Settings', 'revendless'), 'Revendless', 'manage_options', self::PAGE_SLUG, array(&$generalSettings, 'loadSettingsPage'), 'none'
		);

		if($hookSuffix) {
			$generalSettings->hookSuffix = $hookSuffix;
			register_setting($hookSuffix, self::OPTION_NAME, array('RevendlessSettingsGeneral', 'sanitizeOptions'));
			add_action('load-'.$hookSuffix, array(&$generalSettings, 'onload'));
		}

		return $hookSuffix;
	}

	/**
	 * Load settings page
	 *
	 * @return void
	 */
	public function loadSettingsPage()
	{
		if(!isset($this->hookSuffix)) {
			return;
		}

		RevendlessSettings::settingsPageTemplate($this->hookSuffix, __('Revendless for WordPress', 'revendless'));
	}

	/**
	 * Onload
	 *
	 * @return void
	 */
	public function onload()
	{
		$options = get_option(self::OPTION_NAME);

		if (is_array($options)) {
			$this->options = $options;
		}

		$this->initSettingsApi();
	}

	/**
	 * Initialize settings api
	 *
	 * @return void
	 */
	private function initSettingsApi()
	{
		if(!isset( $this->hookSuffix)) {
			return;
		}

		$section = 'revendless-general';

		add_settings_section($section, __('General settings', 'revendless'), array(&$this, 'sectionHeader'), $this->hookSuffix);
		add_settings_field('revendless-api-key', _x('<abbr title="Application programming interface">API</abbr> Key', 'Revendless API Key', 'revendless'), array(&$this, 'showApiKeyInput'), $this->hookSuffix, $section, array('label_for' => 'revendless-api-key'));

		$section = 'revendless-general-url';

		add_settings_section($section, __('URL settings', 'revendless'), array(&$this, 'sectionHeaderUrl'), $this->hookSuffix);
		add_settings_field('revendless-url-shortener', _x('URL Shortener', 'Revendless URL Shortener', 'revendless'), array(&$this, 'showUrlShortenerSelect'), $this->hookSuffix, $section, array('label_for' => 'revendless-url-shortener'));
	}

	/**
	 * Section header
	 *
	 * @return void
	 */
	public function sectionHeader()
	{
		if(isset($this->options['api_key']) && !empty($this->options['api_key'])) {
			print '<p><a href="http://www.revendless.com" target="_blank">'.esc_html(__('Manage your account settings on Revendless', 'revendless')).'</a></p>';
		} else {
			print '<p><a href="http://www.revendless.com/registration" target="_blank">'.esc_html(sprintf(__('Not yet registered? Register now and receive your your personal API key for %s.', 'revendless'), get_bloginfo('name'))).'</a></p>';
		}
	}

	/**
	 * Show api key input
	 *
	 * @return void
	 */
	public function showApiKeyInput()
	{
		$id = 'revendless-api-key';
		$option = 'api_key';

		$value = (isset($this->options[$option]) && $this->options[$option]) ? ' value="'.esc_attr($this->options[$option]).'"' : '';

		settings_errors($id);

		print '<input type="text" name="'.self::OPTION_NAME.'['.$option.']"'.$value.' id="'.$id.'" maxlength="40" size="45" autocomplete="off" pattern="[a-zA-Z0-9]+" />';
		print '<p class="description">'.esc_html( __('An API Key associates your product integrations with your personal Revendless account.', 'revendless')).'</p>';
	}

	/**
	 * Section header url
	 *
	 * @return void
	 */
	public function sectionHeaderUrl()
	{
		print '<p>'.esc_html(__('Configuration settings for the Revendless Tracking-URLs.', 'revendless')).'</p>';
	}

	/**
	 * Show url shortener select
	 *
	 * @return void
	 */
	public function showUrlShortenerSelect()
	{
		$id = 'revendless-url-shortener';
		$option = 'url_shortener';

		$selectedValue = (isset($this->options[$option]) && $this->options[$option]) ? esc_attr($this->options[$option]) : '';

		$options = '<option value=""'.selected($selectedValue, '', false) . '>'.esc_html(__('No URL-Shortener', 'revendless')).'</option>';
		foreach(self::$urlShortenerChoices as $value => $label) {
			$options .= '<option value="' .$value. '"'.selected($value, $selectedValue, false).'>'.esc_html(__($label, 'revendless')).'</option>';
		}

		print '<select name="'.self::OPTION_NAME.'['.$option.']"'.'" id="'.$id.'">'.$options.'</select>';
		print '<p class="description">'.esc_html(__('Use of an url shortener when generating the tracking links.', 'revendless')).'</p>';
	}

	/**
	 * Sanitize user input
	 *
	 * @param array $options user options
	 * @return array
	 */
	public static function sanitizeOptions($options)
	{
		$sanitizedOptions = array();

		if(isset($options['api_key']))
		{
			$apiKey = trim($options['api_key']);
			if (!empty($apiKey))
			{
				if(preg_match('/^[a-zA-Z0-9]+$/', $apiKey)) {
					$sanitizedOptions['api_key'] = $apiKey;
				} else if (function_exists('add_settings_error')) {
					add_settings_error('revendless-api-key', 'revendless-api-key-error', __('An API Key can contain only alphanumeric characters.', 'revendless'));
				}
			}
			unset($apiKey);
		}

		if(isset($options['url_shortener']))
		{
			$urlShortener = $options['url_shortener'];
			if (array_key_exists($urlShortener, self::$urlShortenerChoices))
			{
				$sanitizedOptions['url_shortener'] = $urlShortener;
			} else if (function_exists('add_settings_error')) {
				add_settings_error('revendless-url-shortener', 'revendless-url-shortener-error', __('Selected option not available.', 'revendless'));
			}
			unset($urlShortener);
		}

		return $sanitizedOptions;
	}

}