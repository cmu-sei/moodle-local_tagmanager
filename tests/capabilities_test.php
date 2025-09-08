<?php
namespace local_tagmanager;
defined('MOODLE_INTERNAL') || die();

final class capabilities_test extends \basic_testcase {
    public function test_access_file_declares_capabilities(): void {
        global $CFG;
        $capabilities = [];
        require($CFG->dirroot . '/local/tagmanager/db/access.php'); // populates $capabilities
        $this->assertIsArray($capabilities);
        $this->assertNotEmpty($capabilities);

        $this->assertArrayHasKey('local/tagmanager:use', $capabilities);
        $c = $capabilities['local/tagmanager:use'];
        $this->assertArrayHasKey('captype', $c);
        $this->assertArrayHasKey('contextlevel', $c);
        $this->assertArrayHasKey('archetypes', $c);
    }
}
