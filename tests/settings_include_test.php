<?php
namespace local_tagmanager;
defined('MOODLE_INTERNAL') || die();

final class settings_include_test extends \basic_testcase {
    public function test_including_settings_with_no_siteconfig_is_noop(): void {
        global $CFG, $hassiteconfig, $ADMIN;
        $hassiteconfig = false;
        $ADMIN = null; // avoid accidental tree usage
        require($CFG->dirroot . '/local/tagmanager/settings.php');
        $this->assertTrue(true); // no exception == pass
    }
}
