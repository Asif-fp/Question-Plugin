<?php
/**
 * Register patient user
 *
 * @since 1.0
 * @package medical-questionnaire
 * 
 */

// Exit if accessed directly
if (!defined('ABSPATH'))
    exit;

// Main class
class MQ_register_patient
{

    /**
     * Run main constructor of the class
     *
     * @since 1.0
     * @package medical-questionnaire
     */
    public function __construct()
    {
        add_action('wp_ajax_mq_register_patient_as_user', array($this, 'mq_register_patient_as_user'));

        add_action('wp_ajax_nopriv_mq_register_patient_as_user', array($this, 'mq_register_patient_as_user'));

    }

    /**
     * Submit ajax function search patient object by email
     *
     * @since 1.0
     * @package medical-questionnaire
     */
    public function mq_register_patient_as_user()
    {

        if (!check_ajax_referer('ajax-nonce', 'mq_nonce', false)) {

            wp_send_json_error(['message' => 'Codice di sicurezza (nonce) non valido. Riprova.', 'type' => 'error']);
            wp_die();
        }

        if ($_POST) {

            if (empty($_POST['register_name']) || empty($_POST['register_email']) || empty($_POST['register_phone']) || empty($_POST['register_password'])) {

                wp_send_json_error(['message' => 'Inserisci il valore in tutti i campi', 'type' => 'error']);

            } else if (!is_email($_POST['register_email'])) {
                
                wp_send_json_error(['message' => 'Inserisci un indirizzo email valido', 'type' => 'error']);

            } else if (!preg_match('/^[0-9\+\-\(\)\s]{7,15}$/', $_POST['register_phone'])) {

                wp_send_json_error(['message' => 'Inserisci un numero di cellulare italiano valido come +393331234567 o 3331234567', 'type' => 'error']);

            } else {

                $register_name = isset($_POST['register_name']) ? $_POST['register_name'] : '';
                $register_email = isset($_POST['register_email']) ? $_POST['register_email'] : '';
                $register_phone = isset($_POST['register_phone']) ? $_POST['register_phone'] : '';
                $register_password = isset($_POST['register_password']) ? $_POST['register_password'] : '';

                $username = sanitize_user(current(explode('@', $register_email)));

                $user_id = wp_create_user($username, $register_password, $register_email);

                if (is_wp_error($user_id)) {

                    $translations = [
                        'empty_user_login' => 'Inserisci un nome utente.',
                        'existing_user_login' => 'Questo nome utente è già registrato.',
                        'existing_user_email' => 'Questa email è già registrata.',
                        'invalid_username' => 'Nome utente non valido.',
                        'incorrect_password' => 'La password inserita non è corretta.',
                        'invalid_email' => 'Email non valida.',
                        // fallback
                        'default' => 'Si è verificato un errore. Riprova.'
                    ];

                    $error_code = $user_id->get_error_code();
                    $error_msg = isset($translations[$error_code]) ? $translations[$error_code] : $translations['default'];

                    wp_send_json_error(['message' => $error_msg, 'type' => 'error']);
                }

                wp_update_user([
                    'ID' => $user_id,
                    'display_name' => $register_name,
                    'nickname' => $register_name,
                    'role' => 'customer',
                ]);

                update_user_meta($user_id, 'phone', $register_phone);

                wp_set_current_user($user_id);
                wp_set_auth_cookie($user_id);
                do_action('wp_login', $username, get_user_by('id', $user_id));

                if (!$user_id) {

                    wp_send_json_error(['message' => 'Qualcosa è andato storto, riprova più tardi.', 'type' => 'error']);

                } else {
                    wp_send_json_success(array('message' => 'Registrato con successo', 'type' => 'success'));

                }
            }
        } else {
            wp_send_json_error(['message' => __('Qualcosa è andato storto, riprova più tardi.', 'medical-questionnaire'), 'type' => 'error']);
        }

        wp_die();
    }

}

new MQ_register_patient();
