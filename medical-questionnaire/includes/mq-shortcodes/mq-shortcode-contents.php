<?php

/**
 * Return shorcode contents
 *
 * @since 1.0
 * @package medical-questionnaire
 * 
 */

// Exit if accessed directly
if (!defined('ABSPATH'))
	exit;

// Main class
class MQ_shortcode_contents
{

	/**
	 * Run main constructor of the class
	 *
	 * @since 1.0
	 * @package medical-questionnaire
	 */
	public function __construct()
	{
		add_shortcode('mq_questionarrie_form', array($this, 'questionarrie_form_shortcode_function'));
	}

	/**
	 * Questionarrie Form 
	 *
	 * @since 1.0
	 * @package medical-questionnaire
	 */
	public function questionarrie_form_shortcode_function()
	{
		ob_start();
		include MQ_INCLUDES . 'mq-shortcodes/mq-questionarrie-form.php';
		return ob_get_clean();
	}



}

new MQ_shortcode_contents();
