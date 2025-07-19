<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://https://futureprofilez.com/
 * @since             1.0.0
 * @package           Medical_Questionnaire
 *
 * @wordpress-plugin
 * Plugin Name:       Medical Questionnaire
 * Plugin URI:        https://https://futureprofilez.com/
 * Description:       medical questionnaire for hair loss diagnosis and treatment recommendation
 * Version:           1.0.0
 * Author:            futureprofilez team
 * Author URI:        https://https://futureprofilez.com//
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       medical-questionnaire
 * Domain Path:       /languages
 */
// Exit if accessed directly
if (!defined('ABSPATH')) exit;

// Main class
class Medical_questionnaire
{

	/**
	 * Run functions
	 *
	 * @since 1.0
	 * @package medical-questionnaire
	 */
	public function __construct()
	{
		add_action('plugins_loaded', array($this, 'mq_load_textdomain'));
		add_action('plugins_loaded', array($this, 'mq_constants'));
		add_action('plugins_loaded', array($this, 'mq_includes'));
		register_activation_hook(__FILE__, [__CLASS__, 'mq_on_activation']);
		register_deactivation_hook(__FILE__, [__CLASS__, 'mq_on_deactivation']);
	}

	/**
	 * Internationalization (set language)
	 *
	 * @since 1.0
	 * @package medical-questionnaire
	 * 
	 */
	public function mq_load_textdomain()
	{
		load_plugin_textdomain('medical-questionnaire', false, dirname(plugin_basename(__FILE__)) . '/languages/');
	}

	/**
	 * Constants and paths of the plugin
	 *
	 * @since 1.0
	 * @package medical-questionnaire
	 * 
	 */
	public function mq_constants()
	{

		if (!defined('MQ_DIR'))
			define('MQ_DIR', trailingslashit(plugin_dir_path(__FILE__)));

		if (!defined('MQ_URL'))
			define('MQ_URL', trailingslashit(plugin_dir_url(__FILE__)));

		if (!defined('MQ_VERSION'))
			define('MQ_VERSION', '1.0');

		if (!defined('MQ_INCLUDES'))
			define('MQ_INCLUDES', MQ_DIR . trailingslashit('includes'));

		if (!defined('MQ_ASSETS'))
			define('MQ_ASSETS', MQ_URL . trailingslashit('assets'));
	}

	/**
	 * Loads the initial files needed by the plugin.
	 *
	 * @since 1.0
	 * @package medical-questionnaire
	 */
	public function mq_includes()
	{
		require_once(MQ_INCLUDES . 'init-mq.php');
	}

	/**
	 * Register actiavation hook
	 *
	 * @since 1.0
	 * @package medical-questionnaire
	 */
	public static function mq_on_activation()
	{
		flush_rewrite_rules();
		update_option('is_mq_active', true);
	}

	/**
	 * Register deactivation hook
	 *
	 * @since 1.0
	 * @package medical-questionnaire
	 *
	 */
	public static function mq_on_deactivation()
	{
		update_option('is_mq_active', false);
	}
}

// Run the main class
new Medical_questionnaire();
