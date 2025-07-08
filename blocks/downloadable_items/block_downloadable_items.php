<?php
class block_downloadable_items extends block_base {

    public function init() {
        $this->title = get_string('pluginname', 'block_downloadable_items');
    }

    public function has_config() {
        return false;
    }

    public function applicable_formats() {
        return ['all' => true];
    }

    public function instance_allow_multiple() {
        return true;
    }

    public function get_content() {
        global $USER, $DB, $OUTPUT, $PAGE, $CFG;

        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new stdClass();
        $this->content->text = '';
        $this->content->footer = '';

        $docs = $DB->get_records_sql("
            SELECT * FROM {block_download_items}
            WHERE sharewith = 'ALL' OR (sharewith = 'SPECIFIC' AND useremail = ?)
            ORDER BY timecreated DESC
            LIMIT 3
        ", [$USER->email]);

        if ($docs) {
            foreach ($docs as $doc) {
                $fileurl = new moodle_url('/pluginfile.php', [
                    'contextid' => context_system::instance()->id,
                    'component' => 'block_downloadable_items',
                    'filearea'  => 'document',
                    'itemid'    => 0,
                    'filepath'  => $doc->filepath,
                    'filename'  => $doc->filename
                ]);

                // Determine icon
                $pluginpixurl = $CFG->wwwroot . '/blocks/downloadable_items/pix/';
                $icon = '';

                if (stripos($doc->filename, '.pdf') !== false) {
                    $icon = '<img src="' . $pluginpixurl . 'pdf.png" alt="PDF">';
                } elseif (stripos($doc->title, 'video') !== false) {
                    $icon = '<img src="' . $pluginpixurl . 'video.png" alt="Video">';
                } elseif (stripos($doc->title, 'meeting') !== false) {
                    $icon = '<img src="' . $pluginpixurl . 'meeting.png" alt="Meeting">';
                } else {
                    $icon = '<img src="' . $pluginpixurl . 'word.png" alt="Document">';
                }

                // Build HTML block
                $this->content->text .= "
                <div class='resource-item mt-1'>
                    <div class='resource-icon'>{$icon}</div>
                    <div class='resource-info'>
                        <div class='resource-title'>" . s($doc->title) . "</div>
                        <div class='resource-description'>" . s($doc->description ?? 'No description available.') . "</div>
                    </div>
                    <div class='resource-link'>
                        <a href='" . $fileurl . "' target='_blank' title='Open Resource'>&#8599;</a>
                    </div>
                </div>";
            }

            $moreurl = new moodle_url('/blocks/downloadable_items/viewall.php');
            $this->content->footer .= "<div class='view-more-wrapper'>
                <a href='" . $moreurl . "' class='view-more-button'>View More Resources</a>
            </div>";
        } else {
            $this->content->text = '<div class="no-documents">No documents shared with you.</div>';
        }

        // Admin or manager access
        if (has_capability('block/downloadable_items:managefiles', context_system::instance())) {
            $url = new moodle_url('/blocks/downloadable_items/manage.php');
            $this->content->footer .= "<div class='manage-files-link'>" .
                html_writer::link($url, 'Manage Files') . "</div>";
        }

        return $this->content;
    }

    public function instance_config_save($data, $nolongerused = false) {
        global $DB;

        $fs = get_file_storage();
        $context = context_system::instance();

        file_save_draft_area_files($data->config_file, $context->id, 'block_downloadable_items', 'document', 0, ['subdirs' => 0]);

        $files = $fs->get_area_files($context->id, 'block_downloadable_items', 'document', 0, '', false);
        foreach ($files as $file) {
            $record = new stdClass();
            $record->title = $data->config_title;
            $record->description = $data->config_description ?? '';
            $record->sharewith = $data->config_sharewith;
            $record->useremail = ($data->config_sharewith === 'SPECIFIC') ? $data->config_useremail : '';
            $record->filename = $file->get_filename();
            $record->filepath = $file->get_filepath();
            $record->timecreated = time();
            $DB->insert_record('block_download_items', $record);
        }

        parent::instance_config_save($data, $nolongerused);
    }
}
