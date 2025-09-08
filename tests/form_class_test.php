<?php
namespace local_tagmanager;
defined('MOODLE_INTERNAL') || die();

final class form_class_test extends \basic_testcase {
    public function test_upload_tags_form_is_moodleform(): void {
        global $CFG;
        require_once($CFG->dirroot . '/local/tagmanager/classes/form/upload_tags_form.php');
        $this->assertTrue(class_exists('\\local_tagmanager\\form\\upload_tags_form'));
        $this->assertTrue(is_subclass_of('\\local_tagmanager\\form\\upload_tags_form', '\\moodleform'));
    }
}
