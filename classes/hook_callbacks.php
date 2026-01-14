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

use core\hook\output\before_footer_html_generation as before_footer;
use moodle_url;

class hook_callbacks {
    public static function before_footer_html_generation(before_footer $hook): void {
        global $PAGE;

        // Only on /tag/manage.php (both with or without ?tc).
        $target = new moodle_url('/tag/manage.php');
        if (!$PAGE->url->compare($target, URL_MATCH_BASE)) {
            return;
        }

        if (!has_capability('local/tagmanager:use', \context_system::instance())) {
            return;
        }

        $tc = optional_param('tc', null, PARAM_INT);

        if ($tc === null) {
            \local_tagmanager\output\tagmanager_ui::inject($hook);
            $PAGE->requires->js_call_amd('local_tagmanager/exportmenu', 'init');
        } else {
            $tc = required_param('tc', PARAM_INT);
            $PAGE->requires->js_call_amd('local_tagmanager/managecollection', 'init', [$tc]);
        }

    }
}
