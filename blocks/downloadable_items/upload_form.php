<?php
require_once("$CFG->libdir/formslib.php");

class upload_form extends moodleform {
    public function definition() {
        $mform = $this->_form;

        $mform->addElement('text', 'title', 'Document Title');
        $mform->setType('title', PARAM_TEXT);
        $mform->addRule('title', null, 'required', null, 'client');

        $mform->addElement('select', 'sharewith', 'Share With', ['ALL' => 'All Users', 'SPECIFIC' => 'Specific User']);
        $mform->addRule('sharewith', null, 'required', null, 'client');

        $mform->addElement('text', 'useremail', 'User Email');
        $mform->setType('useremail', PARAM_EMAIL);
        $mform->addHelpButton('useremail', 'useremail', 'block_downloadable_items');

        $mform->addElement('filepicker', 'file', 'Upload File', null,
            ['maxbytes' => 10485760, 'accepted_types' => '*']);
        $mform->addRule('file', null, 'required', null, 'client');

        $this->add_action_buttons();
    }
}
