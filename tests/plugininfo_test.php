<?php
namespace local_tagmanager;
defined('MOODLE_INTERNAL') || die();

final class plugininfo_test extends \basic_testcase {
    public function test_plugin_is_registered_and_version_is_int(): void {
        $pm = \core_plugin_manager::instance();
        $info = $pm->get_plugin_info('local_tagmanager');
        $this->assertNotNull($info);
        $this->assertIsInt((int)$info->versiondb);
    }
}
