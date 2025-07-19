<?php
/**
 * Random functions
 *
 * @since 1.0
 * @package medical-questionnaire
 * 
 */

// Exit if accessed directly
if (!defined('ABSPATH'))
    exit;

// Main class
class MQ_functions
{

    /**
     * Run main constructor of the class
     *
     * @since 1.0
     * @package medical-questionnaire
     */
    public function __construct()
    {
        add_action('wp_footer', array($this, 'mq_add_loader_and_toaster'));
    }

    /**
     * Add toaster and loader to admin
     *
     * @since 1.0
     * @package medical-questionnaire
     */
    public function mq_add_loader_and_toaster()
    {
        echo ' 
 
 <!-- Toaster notification-->
 <div id="toaster-notification" class="toaster-notification">
 <span id="toaster-message"></span>
 <button class="close-btn">×</button>
 </div> 
 
      <div class="loader-holder" style="display:none;"><span class="loader"></span></div>
 
 ';
    }

    /**
     * Fetch sub options
     *
     * @since 1.0
     * @package medical-questionnaire
     */
    public static function mq_render_question_option($option, $name_prefix, $level = 0)
    {
        $label = esc_html($option['label']);
        $value = strtolower(str_replace(' ', '_', $label));
        $input_id = $name_prefix . '_' . $value;
        $has_input = !empty($option['has_text_input']);
        $points = isset($option['points']) ? $option['points'] : '';
        
        $is_multiselect = get_post_meta(get_the_ID(), '_is_multiselect', true);

        $multiselect_class = $is_multiselect == 'Yes'?'multi-select-input':'';

        // Indentation for visual levels
        $margin = 20 * $level;

        echo '<div class="mq-option-levels '.($level > 1 ?'suboption-level-2':'').' '.($level == 1 ?'suboption-level-1':'').'" style="margin-left:' . esc_attr($margin) . 'px;">';

        echo '<input attr-points-for-this="'.($points?$points:0).'" attr-has-text-input="' . ($has_input ? 'yes' : 'no') . '" 
                type="radio" 
                id="' . esc_attr($input_id) . '" 
                name="' . esc_attr($name_prefix) . '" 
                value="' . esc_attr($value) . '" 
                class="radio-input-mq '.$multiselect_class.'">';

        echo '<label for="' . esc_attr($input_id) . '" class="option-card">';
        echo '<div class="title">' . $label;

        echo '</div></label>';

        if ($has_input) {
            echo '<input style="display:none;" type="text" name="' . esc_attr($input_id . '_text_input') . '" class="text-input-mq" placeholder="Inserisci il valore" />';
        }

        // Recursively render sub_options if exist
        if (!empty($option['sub_options']) && is_array($option['sub_options'])) {
            foreach ($option['sub_options'] as $sub_option) {
                self::mq_render_question_option($sub_option, $name_prefix . '_' . $value, $level + 1);
            }
        }

        echo '</div>';
    }


    /**
     * Show question main form
     *
     * @since 1.0
     * @package medical-questionnaire
     */
    public static function render_questionnaire_form()
    {
        $mq_options_return = get_option('mq_options', []);
        $website_logo_url = $mq_options_return['website-logo-url'] ?? '';

        echo '<div class="container main-container-mq">';
        echo '<div class="text-start logo">';
        echo '<a href="' . home_url() . '"><img style="" src="' . esc_url($website_logo_url) . '" alt=" Hims Logo"></a>';
        echo '</div><form id="multiStepForm">';

        $args = [
            'post_type' => 'questions',
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'orderby' => 'menu_order',
            'order' => 'ASC'
        ];

        $query = new WP_Query($args);
        $question_loop_counter = 0;
        $question_number_in_part = [1 => 0, 2 => 0, 3 => 0];

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $question_options = get_post_meta(get_the_ID(), '_mq_question_data', true);
                $question_part_name = get_post_meta(get_the_ID(), '_question_part_name', true);
                $question_type = get_post_meta(get_the_ID(), '_question_type', true) ?: 'Normal radios';
                

                $part_number = strpos($question_part_name, 'Parte 1') !== false ? 1 : (strpos($question_part_name, 'Parte 2') !== false ? 2 : 3);
                $question_number_in_part[$part_number]++;
                $number_in_part = $question_number_in_part[$part_number];
                $global_number = $question_loop_counter + 1;

                $container_class = $question_loop_counter != 0 ? 'hidden-q-container' : '';
                $container_attrs = "data-current-question-part='$part_number' data-question-number-in-part='$number_in_part' data-question-global-number='$global_number'";

                
                $title = esc_html(get_the_title());

                switch ($question_type) {
                    case 'Normal radios':
                        echo "<div class='form-container-mq $container_class' $container_attrs>";
                        echo "<div class='form-part'><h3>" . esc_html($question_part_name) . "</h3></div>";
                        if (!empty($question_options)) {
                            echo "<div class='step-content'><h4 class='mb-3'>$title</h4>";
                            foreach ($question_options as $i => $option) {
                                $label = esc_html($option['label']);
                                $input_id = "question_{$question_loop_counter}_option_{$i}";
                                $input_val = strtolower(str_replace(' ', '_', $label));
                                $has_text_input = !empty($option['has_text_input']);
                                echo "<input attr-has-text-input='" . ($has_text_input ? 'yes' : 'no') . "' type='radio' id='" . esc_attr($input_id) . "' name='question_$question_loop_counter' value='" . esc_attr($input_val) . "' class='radio-input-mq'>";

                                 if ($has_text_input) { echo '<div class="inputBX">';}
                                echo "<label for='" . esc_attr($input_id) . "' class=' " . ($title != '4. Indica la tua altezza e il tuo peso' ? 'option-card' : 'option-card-height-weight') . "'>
                                <div class='title'>$label</div></label>";


                                if ($has_text_input) {
                                    echo "<input type='text' name='{$input_id}_text_input' class='text-input-mq' placeholder='Inserisci il valore' />";
                                }
                                 if ($has_text_input) { echo '</div>';}
                            }
                            echo "<button ".($has_text_input?'disabled="true"':"")."  type='button' class='btn btn-custom next-btn mt-3' $container_attrs>Continua</button></div>";
                        }
                        echo "</div>";
                        break;

                    case 'User data':
                    case 'Login':
                    case 'Register':
                        $form_class = $question_type == 'User data' ? 'option-card-user-data' : ($question_type == 'Login' ? 'option-card-login-mail' : 'option-card-mail');
                        $input_class = $question_type == 'Login' ? 'login-field' : 'user-register-field';
                        echo "<div class='form-container-mq $container_class " . strtolower($question_type) . "' $container_attrs>";
                        echo "<div class='form-part'><h3>" . esc_html($question_part_name) . "</h3></div>";
                        if (!empty($question_options)) {
                            echo "<div class='step-content'><h4 class='mb-3'>$title</h4>";
                            foreach ($question_options as $i => $option) {
                                $label = esc_html($option['label']);
                                $input_id = "question_{$question_loop_counter}_option_{$i}";
                                $input_type = 'text';
                                $placeholder = 'Inserisci il valore';
                                $field_class = '';

                                if ($question_type == 'Login') {
                                    $field_class = ($label == 'Email') ? 'login-email' : 'login-pass';
                                    $placeholder = ($label == 'Email') ? 'Per favore inserisci la tua email' : 'Inserisci la tua password';
                                    $input_type = ($label == 'Email') ? 'text' : 'password';
                                } elseif ($question_type == 'Register') {
                                    switch ($label) {
                                        case 'Nome':
                                            $field_class = 'register-name';
                                            $placeholder = 'Per favore inserisci il tuo nome';
                                            break;
                                        case 'Email':
                                            $field_class = 'register-mail';
                                            $placeholder = 'Per favore inserisci la tua email';
                                            break;
                                        case 'Numero di cellulare':
                                            $field_class = 'register-mobile-number';
                                            $placeholder = 'Inserisci il tuo numero di cellulare';
                                            break;
                                        default:
                                            $field_class = 'register-password';
                                            $input_type = 'password';
                                            $placeholder = 'Inserisci la password';
                                            break;
                                    }
                                } else {
                                    $field_class = 'user-mail-data';
                                    $placeholder = 'Per favore inserisci la tua email';
                                    $input_type = 'email';
                                }

                                echo "<label for='$input_id' class='$form_class'><div class='title'>$label</div></label>";
                                echo "<input type='$input_type' name='{$input_id}_user_input' class='text-input-mq $input_class-$field_class' placeholder='$placeholder' />";
                            }
                            $btn_class = ($question_type == 'Register') ? 'login-register mq-register-form-submit' : (($question_type == 'Login') ? 'login-patient' : '');
                            echo "<button disabled='true' type='button' class='btn btn-custom mt-3 $btn_class' $container_attrs>Continua</button></div>";
                        }
                        echo "</div>";
                        break;

                    case 'Points':
                        echo "<div class='form-container-points form-container-mq $container_class' $container_attrs>";
                        echo "<div class='form-part'><h3>" . esc_html($question_part_name) . "</h3></div>";
                        if (!empty($question_options)) {
                            echo "<div class='step-content'><h4 class='mb-3'>$title</h4>";
                            foreach ($question_options as $i => $option) {
                                $input_id = "question_{$question_loop_counter}_option_{$i}";
                                self::mq_render_question_option($option, $input_id);
                            }
                            echo "<button type='button' class='btn btn-custom next-btn mt-3' $container_attrs>Continua</button></div>";
                          
                        }
                        echo "</div>";
                        break;
                }

                $question_loop_counter++;
            }
            wp_reset_postdata();
        } else {
            echo '<p>No questions found.</p>';
        }

        echo '</form></div>';
    }


}

new MQ_functions();



