<?php
/**
 * Search if patient or user exists by email
 *
 * @since 1.0
 * @package medical-questionnaire
 * 
 */

// Exit if accessed directly
if (!defined('ABSPATH'))
    exit;

// Main class
class MQ_search_patient_object
{

    /**
     * Run main constructor of the class
     *
     * @since 1.0
     * @package medical-questionnaire
     */
    public function __construct()
    {
        add_action('wp_ajax_mq_search_patient_object_by_email', array($this, 'mq_search_patient_object_by_email'));

        add_action('wp_ajax_nopriv_mq_search_patient_object_by_email', array($this, 'mq_search_patient_object_by_email'));

    }

    /**
     * Submit ajax function search patient object by email
     *
     * @since 1.0
     * @package medical-questionnaire
     */
    public function mq_search_patient_object_by_email()
    {   


        if (!check_ajax_referer('ajax-nonce', 'mq_nonce', false)) {

            wp_send_json_error(['message' => 'Codice di sicurezza (nonce) non valido. Riprova.', 'type' => 'error']);
            wp_die();
        }

        if ($_POST) {

            $user_mail = $_POST['user_mail'] ? $_POST['user_mail'] : '';

            if (!is_email($user_mail)) {

                wp_send_json_error(['message' => __('Si prega di inserire un indirizzo email valido', 'medical-questionnaire'), 'type' => 'error']);

            } else {
                $user = get_user_by('email', $user_mail);

                if (!$user) {
                    wp_send_json_success(array('message' => '', 'type' => 'success', 'is_user_exists' => 'false'));

                } else {
                    wp_send_json_success(array('message' => '', 'type' => 'success', 'is_user_exists' => 'true'));

                }
            }

 

        } else {
            wp_send_json_error(['message' => __('Qualcosa è andato storto, riprova più tardi.', 'medical-questionnaire'), 'type' => 'error']);
        }

        wp_die();
    }

}

new MQ_search_patient_object();
