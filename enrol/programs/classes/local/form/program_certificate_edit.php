<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

namespace enrol_programs\local\form;

/**
 * Edit program certificate settings.
 *
 * @package    enrol_programs
 * @copyright  2022 Open LMS (https://www.openlms.net/)
 * @author     Petr Skoda
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class program_certificate_edit extends \local_openlms\dialog_form {
    protected function definition() {
        global $OUTPUT;

        $mform = $this->_form;
        $data = $this->_customdata['data'];
        $context = $this->_customdata['context'];

        $canmanagetemplates = \tool_certificate\permission::can_manage_anywhere();
        $templates = self::get_templates($context, $data->templateid);

        $templateoptions = ['' => get_string('certificatetemplatechoose', 'enrol_programs')] + $templates;
        $manageurl = new \moodle_url('/admin/tool/certificate/manage_templates.php');

        $elements = [];
        $elements[] = $mform->createElement('select', 'templateid', get_string('certificatetemplate', 'tool_certificate'), $templateoptions);

        if ($canmanagetemplates) {
            $elements[] = $mform->createElement('static', 'managetemplates', '',
                $OUTPUT->action_link($manageurl, get_string('managetemplates', 'tool_certificate')));
        }
        $mform->addGroup($elements, 'template_group', get_string('certificatetemplate', 'tool_certificate'),
            \html_writer::div('', 'w-100'), false);

        $rules = [];
        $rules['templateid'][] = [null, 'required', null, 'client'];
        $mform->addGroupRule('template_group', $rules);

        \tool_certificate\certificate::add_expirydate_to_form($mform);

        $mform->addElement('hidden', 'id', $data->id);
        $mform->setType('id', PARAM_INT);

        $this->add_action_buttons(true, get_string('updateprogram', 'enrol_programs'));

        $this->set_data($data);
    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        return $errors;
    }

    public static function get_templates(\context $context, ?int $templateid): array {
        global $DB;

        $templates = [];
        if (!empty($records = \tool_certificate\permission::get_visible_templates($context))) {
            foreach ($records as $record) {
                $templates[$record->id] = format_string($record->name);
            }
        }
        if ($templateid && !isset($templates[$templateid])) {
            $record = $DB->get_record('tool_certificate_templates', ['id' => $templateid]);
            if ($record) {
                $templates[$record->id] = format_string($record->name);
            }
        }

        asort($templates);
        return $templates;
    }
}
