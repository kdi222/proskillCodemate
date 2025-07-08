<?php
require('../../config.php');
require_once('upload_form.php');

require_login();
$context = context_system::instance();
require_capability('block/downloadable_items:managefiles', $context);

$PAGE->set_url('/blocks/downloadable_items/manage.php');
$PAGE->set_context($context);
$PAGE->set_title('Manage Downloadable Items');
$PAGE->set_heading('Manage Downloadable Items');

echo $OUTPUT->header();

$mform = new upload_form();

if ($mform->is_cancelled()) {
    redirect(new moodle_url('/my'));
} else if ($data = $mform->get_data()) {
    global $DB, $USER;

    $fs = get_file_storage();
    file_save_draft_area_files($data->file, $context->id, 'block_downloadable_items', 'document', 0);

    $files = $fs->get_area_files($context->id, 'block_downloadable_items', 'document', 0, '', false);
    foreach ($files as $file) {
        $record = new stdClass();
        $record->title = $data->title;
        $record->sharewith = $data->sharewith;
        $record->useremail = ($data->sharewith == 'SPECIFIC') ? $data->useremail : '';
        $record->filename = $file->get_filename();
        $record->filepath = $file->get_filepath();
        $record->timecreated = time();
        $DB->insert_record('block_download_items', $record);
    }

    echo $OUTPUT->notification("File uploaded successfully!", 'notifysuccess');
}

// Show upload form
$mform->display();

// Show uploaded list
echo "<h3>Uploaded Documents</h3>";
$docs = $DB->get_records('block_download_items', null, 'timecreated DESC');
foreach ($docs as $doc) {
    echo "<div><strong>{$doc->title}</strong> ({$doc->sharewith}) - {$doc->useremail} - {$doc->filename}</div>";
}

echo $OUTPUT->footer();
