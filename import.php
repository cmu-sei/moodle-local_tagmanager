<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
//

/*
Local Tag Manager Plugin

Copyright 2026 Carnegie Mellon University.

NO WARRANTY. THIS CARNEGIE MELLON UNIVERSITY AND SOFTWARE ENGINEERING INSTITUTE MATERIAL IS FURNISHED ON AN "AS-IS" BASIS.
CARNEGIE MELLON UNIVERSITY MAKES NO WARRANTIES OF ANY KIND, EITHER EXPRESSED OR IMPLIED, AS TO ANY MATTER INCLUDING, BUT NOT LIMITED TO,
WARRANTY OF FITNESS FOR PURPOSE OR MERCHANTABILITY, EXCLUSIVITY, OR RESULTS OBTAINED FROM USE OF THE MATERIAL. CARNEGIE MELLON UNIVERSITY DOES NOT MAKE ANY WARRANTY OF ANY KIND WITH RESPECT TO FREEDOM FROM PATENT, TRADEMARK, OR COPYRIGHT INFRINGEMENT.

Licensed under a GNU GENERAL PUBLIC LICENSE - Version 3, 29 June 2007-style license, please see license.txt or contact permission@sei.cmu.edu for full terms.
[DISTRIBUTION STATEMENT A] This material has been approved for public release and unlimited distribution. Please see Copyright notice for non-US Government use and distribution.

This Software includes and/or makes use of Third-Party Software each subject to its own license.

DM26-0016
*/

/**
 * Tag Manager Plugin.
 * @package    local_tagmanager
 * @copyright  2026 Carnegie Mellon University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require('../../config.php');

$tc = required_param('tc', PARAM_INT);

require_login();
$context = context_system::instance();
require_capability('local/tagmanager:use', $context);

$PAGE->set_context($context);
$url = new moodle_url('/local/tagmanager/import.php', ['tc' => $tc]);
$PAGE->set_url($url);
$PAGE->set_pagelayout('admin');
$PAGE->set_title(get_string('importtags', 'local_tagmanager'));
$PAGE->set_heading(get_string('importtags', 'local_tagmanager'));

global $DB, $OUTPUT, $CFG;

if (!$DB->record_exists('tag_coll', ['id' => $tc])) {
    print_error('invalidcollection', 'local_tagmanager');
}

require_once($CFG->dirroot.'/local/tagmanager/classes/form/upload_tags_form.php');

$mform = new \local_tagmanager\form\upload_tags_form($url->out(false), [
    'fixedcollid' => $tc,
]);

$draftid = file_get_submitted_draft_itemid('tagfile');
file_prepare_draft_area(
    $draftid,
    $context->id,
    'local_tagmanager',
    'tagcsv',
    $tc,
    ['subdirs' => 0, 'maxfiles' => 1]
);

$mform->set_data([
    'tagfile' => $draftid,
    'tagcollid' => $tc,
]);

if ($mform->is_cancelled()) {
    redirect(new moodle_url('/tag/manage.php', ['tc' => $tc]));
}

$didprocess = false;
$successcount = 0;
$existscount = 0;
$importednames = [];
$existingnames = [];
$maxnames = 25;
$error = null;

if ($data = $mform->get_data()) {
    require_sesskey();

    file_save_draft_area_files(
        $draftid,
        $context->id,
        'local_tagmanager',
        'tagcsv',
        $tc,
        ['subdirs' => 0, 'maxfiles' => 1]
    );

    $fs = get_file_storage();
    $files = $fs->get_area_files($context->id, 'local_tagmanager', 'tagcsv', $tc, 'id', false);

    if (empty($files)) {
        $error = get_string('notif_nofile', 'local_tagmanager');
    } else {
        $file = reset($files);

        $fh = fopen('php://temp', 'r+');
        fwrite($fh, $file->get_content());
        rewind($fh);

        $firstrow = true;

        while (($row = fgetcsv($fh)) !== false) {
            $tagname = trim($row[0] ?? '');
            $desc    = trim($row[1] ?? '');

            // Skip header row
            if ($firstrow) {
                $firstrow = false;

                $h1 = \core_text::strtolower(trim($tagname));
                $h2 = \core_text::strtolower(trim($desc));

                $isheader =
                    ($h1 === 'tagname' || $h1 === 'tag name' || $h1 === 'name') &&
                    ($h2 === 'description' || $h2 === 'tag description' || $h2 === 'desc');

                if ($isheader) {
                    continue;
                }
            }

            if ($tagname === '') {
                continue;
            }

            $existing = \core_tag_tag::get_by_name($tc, $tagname);
            if ($existing) {
                $existscount++;
                if (count($existingnames) < $maxnames) {
                    $existingnames[] = $tagname;
                }
                continue;
            }

            $created = \core_tag_tag::create_if_missing($tc, [$tagname]);
            if (!empty($created)) {
                $tag = reset($created);

                if ($desc !== '') {
                    $DB->update_record('tag', (object)[
                        'id' => $tag->id,
                        'description' => $desc,
                        'timemodified' => time(),
                    ]);
                }

                $successcount++;
                if (count($importednames) < $maxnames) {
                    $importednames[] = $tagname;
                }
            }
        }

        fclose($fh);
        $didprocess = true;

        $format_list = function(array $names, int $total) {
            if (empty($names)) {
                return '';
            }
            $safe = array_map('s', $names);
            $text = implode(', ', $safe);
            $hidden = $total - count($names);
            if ($hidden > 0) {
                $text .= ' (+' . $hidden . ' more)';
            }
            return $text;
        };

        $importedlist = $format_list($importednames, $successcount);
        $existinglist = $format_list($existingnames, $existscount);

        // Build per-type messages (can include HTML).
        $successmsg = '';
        $warnmsg = '';

        if ($successcount) {
            $successmsg = 'Created tag: <strong>' . $importedlist . '</strong>';
        }

        if ($existscount) {
            $warnmsg = 'Tag already exists: <strong>' . $existinglist . '</strong>';
        }

        if (!$successcount && !$existscount) {
            \core\notification::add('No tags imported.', \core\notification::INFO);
        } else {
            if ($successmsg !== '') {
                \core\notification::add($successmsg, \core\notification::SUCCESS);
            }
            if ($warnmsg !== '') {
                \core\notification::add($warnmsg, \core\notification::WARNING); // yellow
            }
        }

        redirect(new moodle_url('/tag/manage.php', ['tc' => $tc]));

    }

}

echo $OUTPUT->header();

if ($error) {
    echo $OUTPUT->notification($error, \core\output\notification::NOTIFY_ERROR);
} else if ($didprocess) {
    echo $OUTPUT->notification("Imported: {$successcount}. Already existed: {$existscount}.", \core\output\notification::NOTIFY_SUCCESS);
}

$mform->display();

echo $OUTPUT->footer();
