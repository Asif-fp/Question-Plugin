<?php
/**
 * Register post types and meta boxes
 *
 * @since 1.0
 * @package medical-questionnaire
 * 
 */

// Exit if accessed directly
if (!defined('ABSPATH'))
    exit;

// Main class
class MQ_custom_post_types
{
    /**
     * Run main constructor of the class
     *
     * @since 1.0
     * @package medical-questionnaire
     */
    public function __construct()
    {
        add_action('init', array($this, 'mq_regsiter_post_types'));
        add_action('add_meta_boxes', array($this, 'mq_add_company_meta_boxes'));
        add_action('save_post', array($this, 'mq_save_meta_boxes'));
    }

    /**
     * Register custom post types
     *
     * @since 1.0
     * @package medical-questionnaire
     */
    public function mq_regsiter_post_types()
    {

        $labels = array(
            'name' => 'Questions',
            'singular_name' => 'Question',
            'menu_name' => 'Questions',
            'name_admin_bar' => 'Question',
            'add_new' => 'Add New',
            'add_new_item' => 'Add New Question',
            'new_item' => 'New Question',
            'edit_item' => 'Edit Question',
            'view_item' => 'View Question',
            'all_items' => 'All Questions',
            'search_items' => 'Search Questions',
            'not_found' => 'No questions found.',
            'not_found_in_trash' => 'No questions found in Trash.'
        );

        $args = array(
            'labels' => $labels,
            'public' => true,
            'has_archive' => true,
            'show_in_rest' => true, // For Gutenberg/REST support
            'menu_position' => 5,
            'menu_icon' => 'dashicons-editor-help',
            'supports' => array('title'), // only title
            'capability_type' => 'post',
            'publicly_queryable' => false, // if you don’t want front-end access
            'exclude_from_search' => true
        );

        register_post_type('questions', $args);
        
        // Regsiter patient post type
        $labels = array(
            'name' => 'Patients',
            'singular_name' => 'Patient',
            'menu_name' => 'Patients',
            'name_admin_bar' => 'Patient',
            'add_new' => 'Add New',
            'add_new_item' => 'Add New Patient',
            'new_item' => 'New Patient',
            'edit_item' => 'Edit Patient',
            'view_item' => 'View Patient',
            'all_items' => 'All Patients',
            'search_items' => 'Search Patients',
            'not_found' => 'No patients found.',
            'not_found_in_trash' => 'No patients found in Trash.'
        );

        $args = array(
            'labels' => $labels,
            'public' => false, // Not publicly queryable
            'show_ui' => true,  // Show in admin menu
            'show_in_menu' => true,
            'capability_type' => 'post',
            'hierarchical' => false,
            'menu_position' => 20,
            'menu_icon' => 'dashicons-id', // Or any other Dashicon
            'supports' => array('title'), // ✅ Only show title field
            'has_archive' => false,
            'rewrite' => false, // ✅ No permalink support
            'publicly_queryable' => false, // ✅ Can't be queried on front-end
        );

        register_post_type('patients', $args);

    }

    /**
     * Register meta box in the questions
     *
     * @since 1.0
     * @package medical-questionnaire
     */
    public function mq_add_company_meta_boxes()
    {
        add_meta_box(
            'mq_question_details',
            'Question details',
            array($this, 'mq_render_question_details_meta_box'),
            'questions',
            'normal',
            'high'
        );

    }

    /**
     * Render qestion meta box
     *
     * @since 1.0
     * @package medical-questionnaire
     */
    public function mq_render_question_details_meta_box($post)
    {
        $question_data = get_post_meta($post->ID, '_mq_question_data', true);
        $question_part_name = get_post_meta($post->ID, '_question_part_name', true);
        $question_type = get_post_meta($post->ID, '_question_type', true);
        $is_multiselect = get_post_meta($post->ID, '_is_multiselect', true);

        ?>
        <p><label for="question-part"><strong>Question part name</strong> </label>
            <input type="text" id="question-part" name="question-part-name" value="<?php echo $question_part_name;?>"
                style="width: 100%;height: 41px;margin-top: 7px;">
        </p>
        <p><label for="question-part"><strong>Question type</strong> </label>
        <br/>
          <select name="question-type" style="width: 100%;height: 41px;margin-top: 7px;">
            <option <?php echo $question_type == ''?'selected="selected"':''?>>Select</option>
             <option <?php echo $question_type == 'Normal radios'?'selected="selected"':''?>>Normal radios</option>
            <option <?php echo $question_type == 'User data'?'selected="selected"':''?>>User data</option>
            <option <?php echo $question_type == 'Login'?'selected="selected"':''?>>Login</option>
            <option <?php echo $question_type == 'Register'?'selected="selected"':''?>>Register</option>
            <option <?php echo $question_type == 'Points'?'selected="selected"':''?>>Points</option>
            <option <?php echo $question_type == 'Routine'?'selected="selected"':''?>>Routine</option>
          </select>  
        </p>

          <p><label for="is-multiselect"><strong>Multiselect</strong> </label>
        <br/>
          <select name="is-multiselect" style="width: 100%;height: 41px;margin-top: 7px;">
            <option <?php echo $is_multiselect == ''?'selected="selected"':''?>>Select</option>
             <option <?php echo $is_multiselect == 'Yes'?'selected="selected"':''?>>Yes</option>
            <option <?php echo $is_multiselect == 'No'?'selected="selected"':''?>>No</option>
           
          </select>  
        </p>

        <div id="mq-options-wrapper">
            <button type="button" onclick="mqAddOption()" class="button button-primary button-large add-option-button">+ Add
                Option</button>
            <div id="mq-options-container">
                
            </div>
        </div>

        <input type="hidden" name="mq_question_data_json" id="mq_question_data_json">

        <script>
            let options = <?= json_encode($question_data ?: []) ?>;

            function getNestedOption(path) {
                return path.split('.').reduce((acc, key) => acc && acc[key], options);
            }

            function updateOptionValue(path, key, value) {
                let obj = getNestedOption(path);
                if (obj) obj[key] = value;
                document.getElementById('mq_question_data_json').value = JSON.stringify(options);
            }

            function mqAddOption() {
                options.push({
                label: '',
                points: '',
                products: '',
                has_text_input: false,
                termination_outcome: '',
                sub_options: []
                });
                mqRenderOptions();
            }

            function mqAddNestedOption(path) {
                let obj = getNestedOption(path);
            if (!obj.sub_options) obj.sub_options = [];
            obj.sub_options.push({
            label: '',
            points: '',
            products: '',
            has_text_input: false,
            termination_outcome: '',
            sub_options: []
            });mqRenderOptions();
            }

            function renderOptionBlock(opt, path) {
    const i = path.join('_');
    return `
  <div class="mq-option-block">
    <input type="text" placeholder="Label" value="${opt.label || ''}" 
      oninput="updateOptionValue('${path.join('.')}', 'label', this.value)">

    <input type="text" placeholder="Points" value="${opt.points || ''}" 
      oninput="updateOptionValue('${path.join('.')}', 'points', this.value)">

    <input type="text" placeholder="Products" value="${opt.products || ''}" 
      oninput="updateOptionValue('${path.join('.')}', 'products', this.value)">

    <label>
      <input type="checkbox" ${opt.has_text_input ? 'checked' : ''} 
        onchange="updateOptionValue('${path.join('.')}', 'has_text_input', this.checked)">
      Needs text input
    </label>

    <select onchange="updateOptionValue('${path.join('.')}', 'termination_outcome', this.value)">
      <option value="">Termination Outcome</option>
      <option value="A" ${opt.termination_outcome === 'A' ? 'selected' : ''}>A</option>
      <option value="B" ${opt.termination_outcome === 'B' ? 'selected' : ''}>B</option>
      <option value="C" ${opt.termination_outcome === 'C' ? 'selected' : ''}>C</option>
      <option value="D" ${opt.termination_outcome === 'D' ? 'selected' : ''}>D</option>
    </select>

    <button class="button button-primary button-large suboption-btn" type="button" onclick="mqAddNestedOption('${path.join('.')}')">+ Sub Option</button>

    <div class="nested-sub-options">
      ${(opt.sub_options || []).map((sub, j) =>
        renderOptionBlock(sub, [...path, 'sub_options', j])
    ).join('')}
    </div>
  </div>
`;
}


            function mqRenderOptions() {
                const container = document.getElementById('mq-options-container');
                container.innerHTML = options.map((opt, i) =>
                    renderOptionBlock(opt, [i])
                ).join('');
                document.getElementById('mq_question_data_json').value = JSON.stringify(options);
            }

            document.addEventListener('DOMContentLoaded', mqRenderOptions);
        </script>


        <style>
            .mq-option-block {
                border: 1px solid #ccc;
                padding: 10px;
                margin-bottom: 10px;
            }

            .mq-option-block input {
                margin: 3px 0px 10px 4px;

            }

            .nested-sub-options {
                margin-top: 11px;
            }

            div#mq-options-container {
                margin-top: 12px;
            }
        </style>
        <?php
    }

    /**
     * Save meta boxes
     *
     * @since 1.0
     * @package medical-questionnaire
     */

    public function mq_save_meta_boxes($post_id)
    {
        // Avoid autosave/save for revisions
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
            return;
        if (wp_is_post_revision($post_id))
            return;

        // Check post type
        if (get_post_type($post_id) !== 'questions')
            return;

        // Check permission
        if (!current_user_can('edit_post', $post_id))
            return;

        // Save questions part name
        if (isset($_POST['question-part-name'])) {
                update_post_meta($post_id, '_question_part_name',$_POST['question-part-name']);
            
        }
        if (isset($_POST['question-type'])) {
                update_post_meta($post_id, '_question_type',$_POST['question-type']);
            
        }
        if (isset($_POST['is-multiselect'])) {
                update_post_meta($post_id, '_is_multiselect',$_POST['is-multiselect']);
            
        }
        // Save questions options
        if (isset($_POST['mq_question_data_json'])) {
            $json_data = wp_unslash($_POST['mq_question_data_json']); 
            $decoded = json_decode($json_data, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                update_post_meta($post_id, '_mq_question_data', $decoded);
            }
        }
    }

}

// Initialize the class
new MQ_custom_post_types();
