<?php
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
        }
    }
}
