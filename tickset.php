<?php
/*
Plugin Name: Tickset
Plugin URI: http://wordpress.org/plugins/tickset/
Description: Sell tickets digitally. Simple.
Version: 2.0
Author: Tickset, khromov
Requires at least: 5.0
Requires PHP: 5.6
Author URI: https://tickset.com/
GitHub Plugin URI: khromov/tickset
License: GPL2
Text Domain: tickset
Domain Path: /languages
*/

define('TICKSET_PLUGIN_VERSION', '1.0.1');
define('TICKSET_ROOT_DIR', plugin_dir_path( __FILE__ ));
define('TICKSET_PLUGIN_URL', plugins_url('/', __FILE__));
define('TICKSET_PREFIX', 'tickset_');
define('TICKSET_PREFIX_NO_UNDERSCORE', 'tickset');
define('TICKSET_API_BASE', 'https://tickset.com/api');
define('TICKSET_SIGNUP_URL', 'https://tickset.com/signup/');
define('TICKSET_PROFILE_URL', 'https://tickset.com/profile/');

require TICKSET_ROOT_DIR . 'includes/admin.php';
require TICKSET_ROOT_DIR . 'includes/rest.php';
require TICKSET_ROOT_DIR . 'includes/tickset-api.php';
require TICKSET_ROOT_DIR . 'includes/shortcodes.php';

//Create-guten-block
require TICKSET_ROOT_DIR . 'src/init.php';

class WP_Tickset {
	function __construct() {
		add_action('plugins_loaded', [$this, 'load_plugin_textdomain']);
	}

	function load_plugin_textdomain() {
		load_plugin_textdomain( 'tickset', FALSE,  basename( dirname( __FILE__ ) ) . '/languages/' );
	}

	/**
	 * Helper for plugin options
	 *
	 * @param $name
	 * @param null $default
	 *
	 * @return mixed|null
	 */
	static function get_option($name, $default = null) {
		$options = array_merge(apply_filters('tickset_default_options', [
			'api_key' => '',
		]), get_option('tickset_settings', []));

		return isset($options[$name]) ? $options[$name] : $default;
	}
}

new WP_Tickset();
new WP_Tickset_Admin();
new WP_Tickset_REST();
new WP_Tickset_API();

/* Uninstallation routine */
register_activation_hook( __FILE__, 'tickset_activation_hook');

function tickset_activation_hook() {
	register_uninstall_hook( __FILE__, 'tickset_uninstall_hook');
}

function tickset_uninstall_hook() {
	global $wpdb;
	$wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE 'tickset_%'");
	wp_cache_flush();
}
