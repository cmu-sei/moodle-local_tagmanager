<?php
namespace local_tagmanager;
defined('MOODLE_INTERNAL') || die();

final class lang_strings_test extends \basic_testcase {
    public function test_pluginname_string_exists(): void {
        $s = get_string('pluginname', 'local_tagmanager');
        $this->assertIsString($s);
        $this->assertNotSame('[[pluginname]]', $s);
    }
}
