<?php
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
namespace local_tagmanager\output;

use core\hook\output\before_footer_html_generation as before_footer;
use moodle_url;

class tagmanager_ui {

    /**
     * Called from the hook. Renders inline UI on /tag/manage.php.
     */
    public static function inject(before_footer $hook): void {
        global $PAGE, $OUTPUT, $CFG, $DB;

        // Capability check.
        if (!has_capability('local/tagmanager:use', \context_system::instance())) {
            return;
        }

        require_once($CFG->dirroot.'/local/tagmanager/classes/form/upload_tags_form.php');
        $context = \context_system::instance();

        // Prepare form.
        $mform = new \local_tagmanager\form\upload_tags_form();
        $draftid = file_get_submitted_draft_itemid('tagfile');
        file_prepare_draft_area($draftid, $context->id, 'local_tagmanager', 'tagcsv', 0,
            ['subdirs' => 0, 'maxfiles' => 1]);
        $mform->set_data(['tagfile' => $draftid]);

        // State for template.
        $notifications = [];
        $showtags = false;
        $tags = [];

        // ---- Handle single tag create ----
        if (optional_param('createtag', 0, PARAM_BOOL) && confirm_sesskey()) {
            $collectionid = \core_tag_collection::get_default();
            $tagname = trim(required_param('tagname', PARAM_TEXT));
            $description = trim(optional_param('tagdesc', '', PARAM_RAW_TRIMMED));

            if ($tagname !== '') {
                $existing = \core_tag_tag::get_by_name($collectionid, $tagname);
                if (!$existing) {
                    $created = \core_tag_tag::create_if_missing($collectionid, [$tagname]);
                    if (!empty($created)) {
                        $tag = reset($created);
                        if ($description) {
                            $DB->update_record('tag', (object)[
                                'id' => $tag->id,
                                'description' => $description,
                                'timemodified' => time(),
                            ]);
                        }
                        $notifications[] = ['type' => 'success',
                            'text' => get_string('notif_created', 'local_tagmanager', $tagname)];
                    }
                } else {
                    $notifications[] = ['type' => 'warning',
                        'text' => get_string('notif_exists', 'local_tagmanager', $tagname)];
                }
            }
        }

        // ---- Handle CSV upload ----
        if ($data = $mform->get_data()) {
            file_save_draft_area_files($draftid, $context->id, 'local_tagmanager', 'tagcsv', 0,
                ['subdirs' => 0, 'maxfiles' => 1]);

            $fs = get_file_storage();
            $files = $fs->get_area_files($context->id, 'local_tagmanager', 'tagcsv', 0, 'id', false);

            if (!empty($files)) {
                $file = reset($files);
                $fh = fopen('php://temp', 'r+');
                fwrite($fh, $file->get_content());
                rewind($fh);

                $collectionid = \core_tag_collection::get_default();
                while (($row = fgetcsv($fh)) !== false) {
                    $tagname = trim($row[0] ?? '');
                    $desc    = trim($row[1] ?? '');
                    if ($tagname === '') { continue; }

                    $existing = \core_tag_tag::get_by_name($collectionid, $tagname);
                    if (!$existing) {
                        $created = \core_tag_tag::create_if_missing($collectionid, [$tagname]);
                        if (!empty($created)) {
                            $tag = reset($created);
                            if ($desc) {
                                $DB->update_record('tag', (object)[
                                    'id' => $tag->id,
                                    'description' => $desc,
                                    'timemodified' => time(),
                                ]);
                            }
                            $notifications[] = ['type' => 'success',
                                'text' => get_string('notif_created', 'local_tagmanager', $tagname)];
                        }
                    } else {
                        $notifications[] = ['type' => 'info',
                            'text' => get_string('notif_exists_info', 'local_tagmanager', $tagname)];
                    }
                }
                fclose($fh);
            } else {
                $notifications[] = ['type' => 'danger',
                    'text' => get_string('notif_nofile', 'local_tagmanager')];
            }
        }

        // ---- List tags ----
        if (optional_param('listtags', 0, PARAM_BOOL) && confirm_sesskey()) {
            $collectionid = \core_tag_collection::get_default();
            $tagrecords = $DB->get_records('tag', ['tagcollid' => $collectionid], 'rawname ASC');

            foreach ($tagrecords as $t) {
                $instancecount = $DB->count_records('tag_instance', ['tagid' => $t->id]);
                $tags[] = [
                    'id' => (int)$t->id,
                    'name' => s($t->rawname),
                    'description' => s($t->description ?? ''),
                    'instancecount' => $instancecount,
                    'candel' => $instancecount == 0,
                ];
            }
            $showtags = true;
        }

        // ---- Delete tag ----
        if (optional_param('deletetag', 0, PARAM_BOOL) && confirm_sesskey()) {
            $tagid = required_param('tagid', PARAM_INT);
            $instances = $DB->count_records('tag_instance', ['tagid' => $tagid]);
            if ($instances == 0) {
                $tagname = $DB->get_field('tag', 'rawname', ['id' => $tagid]);
                $DB->delete_records('tag', ['id' => $tagid]);
                $notifications[] = ['type' => 'success',
                    'text' => get_string('notif_deleted', 'local_tagmanager', $tagname)];
            } else {
                $notifications[] = ['type' => 'danger',
                    'text' => get_string('notif_cannotdelete', 'local_tagmanager', $instances)];
            }
        }

        // ---- Render form ----
        // ---- Render form ----
        if (method_exists($mform, 'render')) {
            $formhtml = $mform->render();
        } else {
            ob_start();
            $mform->display();
            $formhtml = ob_get_clean();
        }

        // Turn typed notifications into HTML strings the template expects.
        $uploadresults_html = [];
        foreach ($notifications as $n) {
            // keep it simple; whitelist the bootstrap style suffix
            $type = preg_replace('/[^a-z]/', '', $n['type'] ?? 'info');
            $text = $n['text'] ?? '';
            $uploadresults_html[] = "<div class='alert alert-{$type}'>{$text}</div>";
        }

        // ---- Build template data ----
        $data = [
            'formhtml'      => $formhtml,
            'uploadresults' => $uploadresults_html,
            'showtags'      => $showtags,
            'tags'          => $tags,
            'exporturl'     => (new moodle_url('/local/tagmanager/export.php', ['sesskey'=>sesskey()]))->out(false),
            'sesskey'       => sesskey(),
        ];

        // Inject into hook.
        $hook->add_html($OUTPUT->render_from_template('local_tagmanager/tagmanager', $data));
    }
}
