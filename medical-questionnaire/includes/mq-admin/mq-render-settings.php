<?php

if (!current_user_can('manage_options')) {
    wp_die('You do not have permission to view this page.');
}

$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'general';

if (isset($_POST['submit']) && !empty($_POST)) {
    unset($_POST['submit']);

    $existing_options = get_option('mq_options', []);
    $mq_options = array_merge($existing_options, $_POST);

    // === Save Termination Outcome Repeater ===
    if (!empty($_POST['heading']) && is_array($_POST['heading'])) {
        $repeater_rows = [];
        foreach ($_POST['heading'] as $i => $heading) {
            $repeater_rows[] = [
                'heading' => sanitize_text_field($heading),
                'content' => sanitize_textarea_field($_POST['content'][$i] ?? '')
            ];
        }
        $mq_options['repeater_rows'] = $repeater_rows;
    }

    // === Save Doctor Response Repeater ===
    if (!empty($_POST['doctor_heading']) && is_array($_POST['doctor_heading'])) {
        $doctor_response_rows = [];
        foreach ($_POST['doctor_heading'] as $i => $heading) {
            $doctor_response_rows[] = [
                'heading' => sanitize_text_field($heading),
                'content' => wp_kses_post($_POST['doctor_content'][$i] ?? '')
            ];
        }
        $mq_options['doctor_response_rows'] = $doctor_response_rows;
    }

    // Unset raw fields to avoid duplication
    unset($_POST['heading'], $_POST['content'], $_POST['doctor_heading'], $_POST['doctor_content']);

    $saved = update_option('mq_options', $mq_options);
    wp_cache_flush();
}



$mq_options_return = get_option('mq_options', []);
$minimum_age_for_treatment = $mq_options_return['minimum-age-for-treatment'] ?? '';
$website_logo_url = $mq_options_return['website-logo-url'] ?? '';
$repeater_rows = $mq_options_return['repeater_rows'] ?? [];
$doctor_responses = $mq_options_return['doctor_response_rows'] ?? [];


?>
<div class="wrap">
    <h1><?php _e('Medical questionnaire settings', 'price-compare-tool'); ?></h1>

    <h2 class="nav-tab-wrapper">
        <a href="?page=medical-questionnaire&tab=general"
            class="nav-tab <?php echo ($active_tab == 'general') ? 'nav-tab-active' : ''; ?>">General settings</a>

        <a href="?page=medical-questionnaire&tab=termination-outcomes"
            class="nav-tab <?php echo ($active_tab == 'termination-outcomes') ? 'nav-tab-active' : ''; ?>">Termination
            outcomes</a>
        <a href="?page=medical-questionnaire&tab=doctor-response"
            class="nav-tab <?php echo ($active_tab == 'doctor-response') ? 'nav-tab-active' : ''; ?>">Doctor response</a>

    </h2>

    <?php if (isset($saved) && $saved) {
        echo '<div id="message" class="notice updated"><p>Settings saved</p></div>';
    } ?>

    <form action="?page=medical-questionnaire&tab=<?php echo esc_attr($active_tab); ?>" method="post">
        <?php if ($active_tab == 'general'): ?>
            <table class="form-table">
                <tbody>
                    <tr>
                        <th scope="row"><?php _e('Minimum age for treatment', 'price-compare-tool'); ?></th>
                        <td><input type="text" class="regular-text" value="<?php echo $minimum_age_for_treatment; ?>"
                                name="minimum-age-for-treatment">
                            <p class="description" id="new-admin-email-description">
                                Enter minimum age for treatment.
                            </p>
                        </td>

                    </tr>

                    <tr>
                        <th scope="row">Website logo url</th>
                        <td><input type="text" class="regular-text" value="<?php echo $website_logo_url; ?>"
                                name="website-logo-url">

                        </td>

                    </tr>


                </tbody>
            </table>
        <?php elseif ($active_tab == 'termination-outcomes'): ?>

            <table id="repeaterTable" border="1" cellpadding="10" style="border-collapse: collapse; width: 100%;"
                class="form-table">
                <thead>
                    <tr>
                        <th style="width: 30%;">Heading</th>
                        <th style="width: 50%;">Content</th>
                        <th style="width: 20%;">Action</th>
                    </tr>
                </thead>
                <tbody id="repeaterBody">
                    <?php
                    
                    if (!empty($repeater_rows)): ?>
                    <?php foreach ($repeater_rows as $row): ?>
                    <tr>
                        <td>
                            <input type="text" name="heading[]" style="width: 100%;" placeholder="Enter Heading" value="<?php echo esc_html($row['heading'] ?? ''); ?>"/>
                            
                        </td>
                        <td>
                            <textarea name="content[]" rows="3" style="width: 100%;" placeholder="Enter Content"><?php echo nl2br(esc_html($row['content'] ?? '')); ?></textarea>
                        </td>
                        <td>
                            <button type="button" onclick="removeRepeaterRow(this)" class="rm-termination-row">Remove</button>
                        </td>
                    </tr>
                          <?php endforeach; ?>

                    <?php else: ?>
  <p>No content found.</p>
<?php endif; ?>
                </tbody>
            </table>

            <br>
            <button type="button" onclick="addRepeaterRow()" class="add-termination-row">+ Add Row</button>


            <script>
                function addRepeaterRow() {
                    const tbody = document.getElementById('repeaterBody');
                    const newRow = document.createElement('tr');

                    newRow.innerHTML = `
      <td><input type="text" name="heading[]" style="width: 100%;" placeholder="Enter Heading" /></td>
      <td><textarea name="content[]" rows="3" style="width: 100%;" placeholder="Enter Content"></textarea></td>
      <td><button type="button" onclick="removeRepeaterRow(this)" class="rm-termination-row">Remove</button></td>
    `;

                    tbody.appendChild(newRow);
                }

                function removeRepeaterRow(button) {
                    const row = button.closest('tr');
                    if (document.querySelectorAll('#repeaterBody tr').length > 1) {
                        row.remove();
                    } else {
                        alert('At least one row is required.');
                    }
                }
            </script>
        <?php elseif ($active_tab == 'doctor-response'): ?>


<table id="doctorRepeaterTable" border="1" cellpadding="10" style="border-collapse: collapse; width: 100%;" class="form-table">
    <thead>
        <tr>
            <th style="width: 30%;">Heading</th>
            <th style="width: 50%;">Content</th>
            <th style="width: 20%;">Action</th>
        </tr>
    </thead>
    <tbody id="doctorRepeaterBody">
        <?php if (!empty($doctor_responses)) :
            foreach ($doctor_responses as $index => $row) : ?>
                <tr>
                    <td>
                        <input type="text" name="doctor_heading[]" style="width: 100%;" placeholder="Enter Heading"
                               value="<?php echo esc_attr($row['heading'] ?? ''); ?>" />
                    </td>
                    <td>
                        <?php
                        $editor_id = 'doctor_content_' . $index;
                        wp_editor(
                            $row['content'] ?? '',
                            $editor_id,
                            [
                                'textarea_name' => 'doctor_content[]',
                                'textarea_rows' => 5,
                                'media_buttons' => false,
                                'editor_class' => 'wp-editor-area'
                            ]
                        );
                        ?>
                    </td>
                    <td>
                        <button type="button" onclick="removeDoctorRow(this)" class="rm-doctor-row">Remove</button>
                    </td>
                </tr>
            <?php endforeach;
        endif; ?>
    </tbody>
</table>
<br>
<button type="button" onclick="addDoctorRow()" class="add-doctor-row">+ Add Row</button>
<script>
let doctorIndex = <?php echo count($doctor_responses); ?>;

function addDoctorRow() {
    const tbody = document.getElementById('doctorRepeaterBody');
    const newRow = document.createElement('tr');

    newRow.innerHTML = `
        <td>
            <input type="text" name="doctor_heading[]" style="width: 100%;" placeholder="Enter Heading" />
        </td>
        <td>
            <textarea id="doctor_content_${doctorIndex}" name="doctor_content[]" rows="5" style="width:100%;"></textarea>
        </td>
        <td>
            <button type="button" onclick="removeDoctorRow(this)" class="rm-doctor-row">Remove</button>
        </td>
    `;

    tbody.appendChild(newRow);
    wp.editor.initialize(`doctor_content_${doctorIndex}`, {
        tinymce: true,
        quicktags: true,
        mediaButtons: false
    });
    doctorIndex++;
}

function removeDoctorRow(button) {
    const row = button.closest('tr');
    if (document.querySelectorAll('#doctorRepeaterBody tr').length > 1) {
        row.remove();
    } else {
        alert('At least one row is required.');
    }
}
</script>


        <?php endif; ?>
        <p class="submit"><input type="submit" name="submit" class="button button-primary" value="Save options"></p>
    </form>
</div>