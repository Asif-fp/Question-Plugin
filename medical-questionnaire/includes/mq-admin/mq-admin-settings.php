<?php
/**
 * Admin settings
 *
 * @since 1.0
 * @package medical-questionnaire
 */

// Exit if accessed directly
if (!defined('ABSPATH')) exit;

// Main class
class MQ_admin_settings {

    /**
     * Constructor
     *
     * @since 1.0
     */
    public function __construct() {
        add_action( 'admin_menu', array( $this, 'mq_add_menu_page' ) );
    }

    /**
     * Add main menu and settings submenu
     *
     * @since 1.0
     * @package medical-questionnaire
     */
    public function mq_add_menu_page() {

        add_menu_page(
            'MQ',        
            'MQ',         
            'manage_options', 
            'medical-questionnaire',         
            array($this,'mq_settings_page'),               
            'dashicons-editor-ol', 
            25                
        );

    }

    /**
     * Settings Page Content
     *
     * @since 1.0
     * @package medical-questionnaire
     */
    public function mq_settings_page() {
 
		$file_path = MQ_INCLUDES . '/mq-admin/mq-render-settings.php';
		if (file_exists($file_path)) {
			require($file_path);
		} 
    }

 
}

// Initialize the class
new MQ_admin_settings();
