<?php
/**
 * @package Revendless
 * @version 0.0.8
 */
/*
Plugin Name: Revendless
Plugin URI: http://www.revendless.com
Description: Used by thousands of websites and blogs, <strong>Revendless is one of the best monetization toolkits</strong> out there. It helps you to generate advertising revenues in an automated fashion, easy and effective! To get started: 1) Click the "Activate" link to the left of this description, 2) <a href="http://www.revendless.com/registration">Sign up for your Revendless account and get your free API key</a>, and 3) Go to the Revendless configuration page and save your API key.
Version: 0.0.8
Author: Revendless
Author URI: http://www.revendless.com
Text Domain: revendless
License: MIT License (MIT)

Copyright (c) 2015 Revendless

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.

*/

if(!class_exists('WP')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit();
}

define('REVENDLESS_PLUGIN_URL', plugin_dir_url(__FILE__));
define('REVENDLESS_PLUGIN_DIR', plugin_dir_path(__FILE__));

/**
 * Load the Revendless plugin for Wordpress based on an access method of admin or public site.
 */
class Revendless_Loader {

	/**
	 * Plugin version
	 *
	 * @var string
	 */
	const VERSION = '0.0.8';

	/**
	 * Plugin update url
	 *
	 * @var string
	 */
	const PLUGIN_UPDATE_URL = 'http://assets.revendless.com/wordpress/plugin.json';

	/**
	 * Settings
	 *
	 * @var array
	 */
	public $settings = array();

	/**
	 * Construct
	 */
	public function __construct()
	{
		$settings = get_option('revendless_settings');

		if (is_array($settings)) {
			$this->settings = $settings;
		}

		$this->initShortCodes();

		if(is_admin()) {

			add_action('admin_head', array(&$this, 'registerSDK'), 1);

			$this->initAdmin();
			$this->initToolkitButton();

		} else {

			add_action('wp_head', array(&$this, 'registerSDK'), 1);

		}
	}

	/**
	 * Register the Revendless JavaScript SDK
	 *
	 * @return void
	 */
	public function registerSDK()
	{
		if(isset($this->settings['api_key']) && strlen($this->settings['api_key']) > 0) {
			require('views/sdk.js.php');
		}
	}

	/**
	 * Initialize admin
	 *
	 * @return void
	 */
	public function initAdmin()
	{
		if (!class_exists('RevendlessSettings')) {
			require_once(REVENDLESS_PLUGIN_DIR.'admin/settings.php' );
		}

		RevendlessSettings::init();
	}

	/**
	 * Initialize toolkit button
	 *
	 * @return void
	 */
	public function initToolkitButton()
	{
		if(isset($this->settings['api_key']) && strlen($this->settings['api_key']) > 0) {
			wp_enqueue_style('revendless-toolkit', plugins_url('static/css/admin/toolkit.css', __FILE__));
			add_action('media_buttons', array(&$this, 'addMediaButtons'), 15);
		}
	}

	/**
	 * Add media buttons
	 *
	 * @return void
	 */
	public function addMediaButtons()
	{
		print '<a href="javascript:rev(\'toolkit\');" id="rev-toolkit-button" class="button"><span></span>Add products</a>';
	}

	/**
	 * Initialize short codes
	 *
	 * @return void
	 */
	public function initShortCodes()
	{
		/* @fix: keep previous shortcodes working */
		add_shortcode('rev-widget', array(&$this, 'addCarouselShortCode'));

		/* shortcodes */
		add_shortcode('rev-carousel', array(&$this, 'addCarouselShortCode'));
		add_shortcode('rev-board', array(&$this, 'addBoardShortCode'));

		/* adds shortcodes to wordpress excerpts and widgets */
		add_filter('the_excerpt', 'do_shortcode');
		add_filter('widget_text', 'do_shortcode');
	}

	/**
	 * Add carousel short code
	 *
	 * @param array $atts
	 * @return void
	 */
	public function addCarouselShortCode($atts)
	{
		$atts = shortcode_atts(array(
			'ids' => null,
		), $atts);

		$ids = (!is_null($atts['ids'])) ? ' data-ids="'.$atts['ids'].'"' : '';

		$output = '<!-- Product integrations powered by Revendless / http://www.revendless.com -->'."\n";
		$output.= '<div class="rev-carousel" data-type="product"'.$ids.'></div>'."\n";

		return $output;
	}

	/**
	 * Add board short code
	 *
	 * @param array $atts
	 * @return void
	 */
	public function addBoardShortCode($atts)
	{
		$atts = shortcode_atts(array(
			'ids' => null,
		), $atts);

		$ids = (!is_null($atts['ids'])) ? ' data-ids="'.$atts['ids'].'"' : '';

		$output = '<!-- Product integrations powered by Revendless / http://www.revendless.com -->'."\n";
		$output.= '<div class="rev-board" data-type="product"'.$ids.'></div>'."\n";

		return $output;
	}

}

/**
 * Bootstrap
 *
 * @return void
 */
function revendless_loader_init() {
	new Revendless_Loader();
}

add_action('init', 'revendless_loader_init', 0 );