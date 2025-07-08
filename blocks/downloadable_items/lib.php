<?php 
function block_downloadable_items_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = array()) {
    global $USER;
    
    if ($context->contextlevel != CONTEXT_SYSTEM) {
        return false;
    }

    $itemid = array_shift($args);
    $filename = array_pop($args);
    $filepath = '/';

    $fs = get_file_storage();
    $file = $fs->get_file($context->id, 'block_downloadable_items', 'document', $itemid, $filepath, $filename);

    if (!$file || $file->is_directory()) {
        return false;
    }

    // Check access rights
    $doc = $DB->get_record('block_download_items', ['id' => $itemid]);
    if ($doc->sharewith == 'ALL' || ($doc->sharewith == 'SPECIFIC' && $doc->useremail == $USER->email)) {
        send_stored_file($file, 0, 0, $forcedownload, $options);
    }

    return false;
}
