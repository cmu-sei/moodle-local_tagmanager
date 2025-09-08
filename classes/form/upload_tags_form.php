<?php
namespace local_tagmanager\form;

defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir . '/formslib.php');

class upload_tags_form extends \moodleform {
    public function definition() {
        global $CFG;
        $m = $this->_form;

        $m->addElement('header', 'hdr', get_string('uploadtags','local_tagmanager'));
        $m->addElement('filepicker','tagfile',get_string('csvfile','local_tagmanager'),null,[
            'maxbytes' => $CFG->maxbytes,
            'accepted_types' => ['.csv'],
            'subdirs' => 0,
        ]);
        $m->addRule('tagfile', null, 'required', null, 'client');
        $this->add_action_buttons(true, get_string('uploadtags','local_tagmanager'));
    }
}
