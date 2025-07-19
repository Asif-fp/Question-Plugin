<?php
/**
 * Enque css and js files in the plugin
 *
 * @since 1.0
 * @package medical-questionnaire
 * 
 */

// Exit if accessed directly
if (!defined('ABSPATH')) exit;

// Main class
class Init_mq
{

	/**
	 * Run main constructor of the class
	 *
	 * @since 1.0
	 * @package medical-questionnaire
	 */
	public function __construct()
	{
		// Enque  assets
		add_action('wp_enqueue_scripts', array($this, 'mq_include_frontend_assets'));
		add_action('admin_enqueue_scripts', array($this, 'mq_include_backend_assets'));
		add_action('admin_enqueue_scripts', [$this, 'mq_enqueue_editor_assets']);


		// Include files
		require(MQ_INCLUDES . '/mq-shortcodes/mq-shortcode-contents.php');
		require(MQ_INCLUDES . '/mq-admin/mq-admin-settings.php');
		require(MQ_INCLUDES . '/mq-admin/mq-custom-post-types.php');
		require(MQ_INCLUDES . '/mq-functions.php');
		require(MQ_INCLUDES . '/mq-search-patient-object.php');
		require(MQ_INCLUDES . '/mq-register-patient.php');
		require(MQ_INCLUDES . '/mq-login-patient.php');
	}

	/**
	 * Enque files on frontend
	 *
	 * @since 1.0
	 * @package medical-questionnaire
	 */
	public function mq_include_frontend_assets()
	{

		// Register files
		wp_register_style('mq-frontend-bootstrap-style', MQ_ASSETS . 'css/bootstrap.min.css', array(), rand());
		wp_register_style('mq-frontend-style', MQ_ASSETS . 'css/mq-frontend-style.css', array(), rand());


		wp_register_script('mq-frontend-jquery-script', MQ_ASSETS . 'js/jquery.min.js', array(), time(), true);
		wp_register_script('mq-frontend-bootstrap-script', MQ_ASSETS . 'js/bootstrap.min.js', array('jquery'), time(), true);
		wp_register_script('mq-frontend-script', MQ_ASSETS . 'js/mq-frontend-script.js', array('jquery'), time(), true);


		wp_localize_script('mq-frontend-script', 'mq_ajax_object_frontend', array('ajaxurl' => admin_url('admin-ajax.php'), 'nonce' => wp_create_nonce('ajax-nonce')));

		// Enque 
		wp_enqueue_style('mq-frontend-bootstrap-style');
		wp_enqueue_style('mq-frontend-style');

		wp_enqueue_script('mq-frontend-jquery-script');
		wp_enqueue_script('mq-frontend-bootstrap-script');
		
		wp_enqueue_script('mq-frontend-script');

	}

	/**
	 * Enque files on backend
	 *
	 * @since 1.0
	 * @package medical-questionnaire
	 */
	public function mq_include_backend_assets()
	{

		   wp_enqueue_style(
        'mq-backend-style',
        MQ_ASSETS. 'css/mq-backend-style.css',
        [],
        time()
    );

	}

	/**
	 * Enque wp editor files
	 *
	 * @since 1.0
	 * @package medical-questionnaire
	 */
	public function mq_enqueue_editor_assets($hook)
	{
		if ($hook === 'toplevel_page_medical-questionnaire') {
			wp_enqueue_editor(); // Loads TinyMCE and QuickTags
		}
	}
	


}

new Init_mq();
