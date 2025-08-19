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
class MQ_submit_patient_answers_form
{

    /**
     * Run main constructor of the class
     *
     * @since 1.0
     * @package medical-questionnaire
     */
    public function __construct()
    {
        add_action('wp_ajax_mq_submit_patient_answers_form_ajax_function', array($this, 'mq_submit_patient_answers_form_ajax_function'));

        add_action('wp_ajax_nopriv_mq_submit_patient_answers_form_ajax_function', array($this, 'mq_submit_patient_answers_form_ajax_function'));

    }

    /**
     * Submit ajax function search patient object by email
     *
     * @since 1.0
     * @package medical-questionnaire
     */
    public function mq_submit_patient_answers_form_ajax_function()
    {

        // if (!check_ajax_referer('ajax-nonce', 'mq_nonce', false)) {

        //     wp_send_json_error(['message' => 'Codice di sicurezza (nonce) non valido. Riprova.', 'type' => 'error']);
        //     wp_die();
        // }

        if (!empty($_POST['form_data'])) {

            $form_data = json_decode(stripslashes($_POST['form_data']), true);

            $is_on_last_quetion = $_POST['is_on_last_quetion'] ? $_POST['is_on_last_quetion'] : '';
            $mq_options_return = get_option('mq_options', []);

            $product_outcome_heading = $mq_options_return['product-outcome-heading'] ?? '';

            $total_points = 0;
            $output = "";
            $product_scores = [];

            $outcome_a_terminated = false;
            $outcome_b_terminated = false;
            $outcome_c_terminated = false;
            $outcome_d_terminated = false;

            $continue_to_part_2 = 'yes';
            $continue_to_part_3 = 'no';

            foreach ($form_data as $question) {
                $question_part = $question['question_part'];
                $question_number = $question['question_number_in_part'];
                $global_number = $question['question_global_number'];

                foreach ($question['answers'] as $answer) {
                    $type = $answer['type'];

                    if ($type === 'radio') {

                        if ($question_part == 1 && $question_number == 1 && $answer['value'] == 'Femmina') {

                            $outcome_a_terminated = true;
                            $continue_to_part_2 = 'no';

                        } else if ($question_part == 1 && $question_number == 2 && $answer['value'] == 'No') {

                            $outcome_a_terminated = true;
                            $continue_to_part_2 = 'no';

                        } else if ($question_part == 1 && $question_number == 3 && $answer['value'] == 'Meno di 18 anni') {

                            $outcome_a_terminated = true;
                            $continue_to_part_2 = 'no';

                        } else if ($question_part == 1 && $question_number == 3 && $answer['value'] == 'Più di 65 anni') {

                            $outcome_a_terminated = true;
                            $continue_to_part_2 = 'no';

                        } else if ($question_part == 3 && $question_number == 9 && ($answer['value'] == 'Lupus eritematoso sistemico' || $answer['value'] == 'Altre malattie autoimmuni o reumatiche' || $answer['value'] == 'Problemi alla tiroide')) {

                            $outcome_c_terminated = true;

                        }

                        if (!empty($answer['points']) && $answer['points'] > 0 && $continue_to_part_2 == 'yes') {

                            $total_points += (int) $answer['points'];
                        }

                    }

                    if (!empty($answer['product_related'])) {
                        // Split by comma
                        $entries = explode(',', $answer['product_related']);
                        foreach ($entries as $entry) {
                            $parts = explode('=>', $entry);
                            if (count($parts) === 2) {
                                $product_name = preg_replace('/^[\pZ\pC]+|[\pZ\pC]+$/u', '', $parts[0]);
                                $points_val = (int) filter_var($parts[1], FILTER_SANITIZE_NUMBER_INT);
                                if (!isset($product_scores[$product_name])) {
                                    $product_scores[$product_name] = 0;
                                }
                                $product_scores[$product_name] += $points_val;
                            }
                        }
                    }

                }

            }

            // Apply conditions
            if ($continue_to_part_2 == 'yes') {
                if ($total_points >= 14) {
                    $continue_to_part_3 = "yes";
                } elseif ($total_points >= 9 && $total_points <= 13) {
                    $outcome_b_terminated = true;
                } else if ($total_points < 8) {
                    $outcome_c_terminated = true;
                }
            }

            $mq_options_return = get_option('mq_options', []);
            $repeater_rows = $mq_options_return['repeater_rows'] ?? [];

            arsort($product_scores);
            $top_products = array_slice($product_scores, 0, 3, true);
            foreach ($top_products as $product_name => $score) {

                $product_id = wc_get_product_id_by_sku($product_name);

                if (!$product_id) {

                    $product = get_page_by_title($product_name, OBJECT, 'product');

                    $product_id = $product ? $product->ID : 0;
                }

                if ($product_id && is_int($score) && $score > 0) {
                    $product = wc_get_product($product_id);
                    if ($product) {

                        $image_url = wp_get_attachment_image_url($product->get_image_id(), 'medium') ? wp_get_attachment_image_url($product->get_image_id(), 'medium') : wc_placeholder_img_src('medium');
                        $top_products_data[] = [
                            'id' => $product->get_id(),
                            'name' => $product->get_name(),
                            'link' => get_permalink($product->get_id()),
                            'price' => $product->get_price_html(),
                            'image' => $image_url,
                            'score' => $score
                        ];
                    }
                }
            }

            // Prepare outcome message
            $outcome_return = '';
            if (!empty($repeater_rows)) {

                if ($outcome_a_terminated && isset($repeater_rows[0]) && $continue_to_part_2 == 'no') {

                    $outcome_return = ($repeater_rows[0]['content'] ?? '');

                } elseif ($continue_to_part_2 == 'no' && $outcome_b_terminated && isset($repeater_rows[1])) {

                    $outcome_return = ($repeater_rows[1]['content'] ?? '');

                } elseif ($continue_to_part_3 == 'yes' && $continue_to_part_2 == 'yes' && $outcome_c_terminated && isset($repeater_rows[2])) {

                    $outcome_return = ($repeater_rows[2]['content'] ?? '');

                } elseif ($continue_to_part_2 == 'yes' && $continue_to_part_3 == 'yes' && !$outcome_a_terminated && !$outcome_b_terminated && !$outcome_c_terminated && isset($repeater_rows[3]) && $is_on_last_quetion == 'true' && !$top_products_data) {

                    $outcome_return = ($repeater_rows[3]['content'] ?? '');

                }
            }


            if ($is_on_last_quetion == 'true') {
                $user_info = get_userdata(get_current_user_id());

                if ($user_info) {
                    if ($user_info->display_name) {
                        $user_name = $user_info->display_name;
                    } else {
                        $user_name = '';
                    }
                }

                $user_name = sanitize_text_field($user_name);

                $recent_posts = get_posts([
                    'post_type' => 'patients',
                    'post_status' => 'publish',
                    'posts_per_page' => 1,
                    'orderby' => 'date',
                    'order' => 'DESC',
                    's' => $user_name,
                    'date_query' => [
                        [
                            'after' => '2 minutes ago',
                            'inclusive' => true,
                        ],
                    ],
                ]);

                if (!empty($recent_posts)) {
                    wp_send_json_error([
                        'message' => 'Di recente è stato aggiunto un post di un paziente con questo nome. Attendi prima di aggiungerne un altro.'
                    ]);
                    wp_die();
                }

                $patient_post_id = wp_insert_post([
                    'post_type' => 'patients',
                    'post_status' => 'publish',
                    'post_title' => $user_name . ' - ' . current_time('d-m-Y H:i'),
                ]);

                if (is_wp_error($patient_post_id)) {

                    wp_send_json_error([
                        'message' => 'Errore durante la creazione dei dettagli del paziente.',
                        'type' => 'error'
                    ]);

                } else {

                    $patient_images_urls = self::upload_question_images_and_get_urls($form_data);

                    update_post_meta($patient_post_id, 'patient_answers', $form_data);
                    update_post_meta($patient_post_id, 'patient_id', get_current_user_id());
                    update_post_meta($patient_post_id, 'suggested_products', $top_products_data);
                    update_post_meta($patient_post_id, 'patient_images', $patient_images_urls);

                }
            }

            wp_send_json_success([
                'top_products' => $top_products_data,
                'outcome' => $outcome_return,
                'next_step2_info' => $continue_to_part_2,
                'next_step3_info' => $continue_to_part_3,
                'products_outcome_heading' => $product_outcome_heading,
                'type' => 'success'
            ]);

        } else {
            wp_send_json_error(['message' => __('Qualcosa è andato storto, riprova più tardi.', 'medical-questionnaire'), 'type' => 'error']);
        }

        wp_die();
    }


    /**
     * Upload image
     *
     * @since 1.0
     * @package medical-questionnaire
     */
    public static function upload_question_images_and_get_urls($question_data)
    {
        $uploaded_urls = [];

        if (!empty($question_data['answers']) && is_array($question_data['answers'])) {
            foreach ($question_data['answers'] as $answer) {
                if ($answer['type'] === 'image' && !empty($answer['file_name']) && !empty($answer['valid'])) {

                    // 🔹 File ka local path (agar form se aa rha ho to sahi path banani hogi)
                    $source_path = ABSPATH . 'temp_uploads/' . $answer['file_name']; // Example: /wp-content/temp_uploads/

                    if (file_exists($source_path)) {
                        $upload_dir = wp_upload_dir();
                        $filename = wp_unique_filename($upload_dir['path'], $answer['file_name']);

                        // File ko copy/move karo
                        $new_file_path = $upload_dir['path'] . '/' . $filename;
                        copy($source_path, $new_file_path);

                        // WP me insert as attachment
                        $filetype = wp_check_filetype($filename, null);
                        $attachment_id = wp_insert_attachment([
                            'guid' => $upload_dir['url'] . '/' . $filename,
                            'post_mime_type' => $filetype['type'],
                            'post_title' => sanitize_file_name($filename),
                            'post_content' => '',
                            'post_status' => 'inherit'
                        ], $new_file_path);

                        // Metadata generate
                        require_once ABSPATH . 'wp-admin/includes/image.php';
                        $attach_data = wp_generate_attachment_metadata($attachment_id, $new_file_path);
                        wp_update_attachment_metadata($attachment_id, $attach_data);

                        // URL array me add karo
                        $uploaded_urls[] = wp_get_attachment_url($attachment_id);
                    }
                }
            }
        }

        return $uploaded_urls; // Saare image URLs ka array return karega
    }





}

new MQ_submit_patient_answers_form();
