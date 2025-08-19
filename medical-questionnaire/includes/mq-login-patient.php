<?php
/**
 * Login patient user
 *
 * @since 1.0
 * @package medical-questionnaire
 * 
 */

// Exit if accessed directly
if (!defined('ABSPATH'))
    exit;

// Main class
class MQ_login_patient
{

    /**
     * Run main constructor of the class
     *
     * @since 1.0
     * @package medical-questionnaire
     */
    public function __construct()
    {
        add_action('wp_ajax_mq_login_patient_user', array($this, 'mq_login_patient_user'));
        add_action('wp_ajax_nopriv_mq_login_patient_user', array($this, 'mq_login_patient_user'));

    }

    /**
     * Submit ajax function search patient object by email
     *
     * @since 1.0
     * @package medical-questionnaire
     */
    public function mq_login_patient_user()
    {

        if (is_user_logged_in()) {
            wp_send_json_success([
                'message' => 'Accesso riuscito!',
                'type' => 'success'
            ]);
            wp_die();
        }

        if ($_POST) {

            $user_mail = isset($_POST['login_email']) ? sanitize_email($_POST['login_email']) : '';
            $user_pass = isset($_POST['login_pass']) ? $_POST['login_pass'] : '';

            if (empty($user_mail)) {
                wp_send_json_error(['message' => 'Per favore inserisci la tua email', 'type' => 'error']);
            } elseif (empty($user_pass)) {
                wp_send_json_error(['message' => 'Inserisci la tua password', 'type' => 'error']);
            } elseif (!is_email($user_mail)) {
                wp_send_json_error(['message' => 'Inserisci un indirizzo email valido', 'type' => 'error']);
            }

            $user_obj = get_user_by('email', $user_mail);

            if (!$user_obj) {
                wp_send_json_error([
                    'message' => 'Email non registrata.',
                    'type' => 'error'
                ]);
            }

            $info = [
                'user_login' => $user_obj->user_login,
                'user_password' => $user_pass,
                'remember' => true
            ];

            $user = wp_signon($info, false);

            // ✅ Corrected: Check if login failed
            if (is_wp_error($user)) {
                $translations = [
                    'empty_user_login' => 'Inserisci un nome utente.',
                    'existing_user_login' => 'Questo nome utente è già registrato.',
                    'existing_user_email' => 'Questa email è già registrata.',
                    'invalid_username' => 'Nome utente non valido.',
                    'incorrect_password' => 'La password inserita non è corretta.',
                    'invalid_email' => 'Email non valida.',
                    'default' => 'Si è verificato un errore. Riprova.'
                ];

                $error_code = $user->get_error_code();
                $error_msg = isset($translations[$error_code]) ? $translations[$error_code] : $translations['default'];

                wp_send_json_error(['message' => $error_msg, 'type' => 'error']);
            }

            // ✅ Success login
            wp_set_current_user($user->ID);
            wp_set_auth_cookie($user->ID);
            wp_send_json_success(['message' => 'Accesso riuscito!', 'type' => 'success']);

        } else {
            wp_send_json_error(['message' => __('Qualcosa è andato storto, riprova più tardi.', 'medical-questionnaire'), 'type' => 'error']);
        }

        wp_die();
    }


}

new MQ_login_patient();
