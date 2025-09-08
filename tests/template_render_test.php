<?php
namespace local_tagmanager;
defined('MOODLE_INTERNAL') || die();

final class template_render_test extends \advanced_testcase {
    protected function setUp(): void {
        $this->resetAfterTest(true);
    }

    public function test_template_renders_without_exceptions(): void {
        global $PAGE, $OUTPUT;

        $PAGE->set_context(\context_system::instance());
        $PAGE->set_url(new \moodle_url('/'));

        $html = $OUTPUT->render_from_template(
            'local_tagmanager/tagmanager',
            ['now' => time()]
        );

        $this->assertIsString($html);
        $this->assertNotSame('', trim($html));
    }
}
