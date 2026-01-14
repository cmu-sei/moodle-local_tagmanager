<?php
/*
Tag Manager Plugin for Moodle

Copyright 2026 Carnegie Mellon University.

NO WARRANTY. THIS CARNEGIE MELLON UNIVERSITY AND SOFTWARE ENGINEERING INSTITUTE MATERIAL IS FURNISHED ON AN "AS-IS" BASIS.
CARNEGIE MELLON UNIVERSITY MAKES NO WARRANTIES OF ANY KIND, EITHER EXPRESSED OR IMPLIED, AS TO ANY MATTER INCLUDING, BUT NOT LIMITED TO,
WARRANTY OF FITNESS FOR PURPOSE OR MERCHANTABILITY, EXCLUSIVITY, OR RESULTS OBTAINED FROM USE OF THE MATERIAL. CARNEGIE MELLON UNIVERSITY
DOES NOT MAKE ANY WARRANTY OF ANY KIND WITH RESPECT TO FREEDOM FROM PATENT, TRADEMARK, OR COPYRIGHT INFRINGEMENT.

Licensed under a GNU GENERAL PUBLIC LICENSE - Version 3, 29 June 2007-style license, please see license.txt or contact permission@sei.cmu.edu for full terms.

[DISTRIBUTION STATEMENT A] This material has been approved for public release and unlimited distribution. Please see Copyright notice for non-US Government use and distribution.

This Software includes and/or makes use of Third-Party Software each subject to its own license.

DM26-0016
*/

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
