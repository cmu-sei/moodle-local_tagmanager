<?php
namespace local_tagmanager;

use core\hook\output\before_footer_html_generation as before_footer;
use moodle_url;

class hook_callbacks {
    public static function before_footer_html_generation(before_footer $hook): void {
        global $PAGE;

        $target = new moodle_url('/tag/manage.php');
        if (!$PAGE->url->compare($target, URL_MATCH_BASE)) {
            return;
        }

        if (optional_param('tc', null, PARAM_RAW) !== null) {
            return;
        }

        if (!has_capability('local/tagmanager:use', \context_system::instance())) {
            return;
        }

        \local_tagmanager\output\tagmanager_ui::inject($hook);
    }
}
