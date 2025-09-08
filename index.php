<?php
require('../../config.php');
require_login();

$context = context_system::instance();
require_capability('local/tagmanager:use', $context);

// ---------- Early export ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['exporttags'])) {
    global $DB;
    $collectionid = \core_tag_collection::get_default();
    $tags = $DB->get_records('tag', ['tagcollid' => $collectionid], 'rawname ASC');

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="tags_export.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['tagname', 'description']);
    foreach ($tags as $t) {
        fputcsv($out, [$t->rawname, $t->description ?? '']);
    }
    fclose($out);
    exit;
}

// ---------- Page ----------
$PAGE->set_url('/local/tagmanager/index.php');
$PAGE->set_context($context);
$PAGE->set_title('Tag Manager');
$PAGE->set_heading('Tag Manager');

require_once($CFG->dirroot.'/local/tagmanager/classes/form/upload_tags_form.php');
use local_tagmanager\form\upload_tags_form;

$mform = new upload_tags_form();

// Draft area before rendering.
$draftid = file_get_submitted_draft_itemid('tagfile');
file_prepare_draft_area(
    $draftid,
    $context->id,
    'local_tagmanager',
    'tagcsv',
    0,
    ['subdirs' => 0, 'maxfiles' => 1]
);
$mform->set_data(['tagfile' => $draftid]);

echo $OUTPUT->header();

$uploadresults = [];
$showtags = false;
$tags = [];

// ---------- Create single tag (left manual form) ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tagname'])) {
    global $DB;
    $collectionid = \core_tag_collection::get_default();
    $tagname = trim($_POST['tagname']);
    $description = trim($_POST['tagdesc'] ?? '');

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
                $uploadresults[] = "<div class='alert alert-success'>Created tag: <strong>" . s($tagname) . "</strong></div>";
            }
        } else {
            $uploadresults[] = "<div class='alert alert-warning'>Tag already exists: <strong>" . s($tagname) . "</strong></div>";
        }
    }
}

// ---------- Filepicker submit (bulk CSV) ----------
if ($data = $mform->get_data()) {
    $draftid = file_get_submitted_draft_itemid('tagfile');
    file_save_draft_area_files(
        $draftid,
        $context->id,
        'local_tagmanager',
        'tagcsv',
        0,
        ['subdirs' => 0, 'maxfiles' => 1]
    );

    $fs = get_file_storage();
    $files = $fs->get_area_files($context->id, 'local_tagmanager', 'tagcsv', 0, 'id', false);

    if (!empty($files)) {
        $file = reset($files);
        $content = $file->get_content();

        $fh = fopen('php://temp', 'r+');
        fwrite($fh, $content);
        rewind($fh);

        global $DB;
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
                    $uploadresults[] = "<div>Created tag: <strong>" . s($tagname) . "</strong></div>";
                }
            } else {
                $uploadresults[] = "<div class='small'>Tag already exists: <span class='text-muted'>" . s($tagname) . "</span></div>";
            }
        }
        fclose($fh);
    } else {
        $uploadresults[] = $OUTPUT->notification('No file saved to file area.', 'notifyproblem');
    }
}

// ---------- List tags ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['listtags'])) {
    global $DB;
    $collectionid = \core_tag_collection::get_default();
    $tagrecords = $DB->get_records('tag', ['tagcollid' => $collectionid], 'rawname ASC');

    foreach ($tagrecords as $t) {
        $instancecount = $DB->count_records('tag_instance', ['tagid' => $t->id]);
        $tags[] = [
            'name' => s($t->rawname),
            'id' => s($t->id),
            'description' => s($t->description ?? ''),
            'instancecount' => $instancecount,
            'candel' => $instancecount == 0
        ];
    }
    $showtags = true;
}

// ---------- Delete tag ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['deletetag'])) {
    global $DB;
    $tagid = (int)($_POST['tagid'] ?? 0);
    if ($tagid) {
        $instances = $DB->count_records('tag_instance', ['tagid' => $tagid]);
        if ($instances == 0) {
            $tagname = $DB->get_field('tag', 'rawname', ['id' => $tagid]);
            $DB->delete_records('tag', ['id' => $tagid]);
            $uploadresults[] = "<div class='alert alert-success'>Deleted unused tag: <strong>" . s($tagname) . "</strong></div>";
        } else {
            $uploadresults[] = "<div class='alert alert-danger'>Cannot delete tag - it has " . (int)$instances . " tagged items</div>";
        }
    }
}

// ---------- Render into Mustache (templates/tagmanager.mustache) ----------
if (method_exists($mform, 'render')) {
    $formhtml = $mform->render();
} else {
    ob_start();
    $mform->display();
    $formhtml = ob_get_clean();
}

echo $OUTPUT->render_from_template('local_tagmanager/tagmanager', [
    'uploadresults' => $uploadresults,
    'showtags'      => $showtags,
    'tags'          => $tags,
    'formhtml'      => $formhtml,
]);

echo $OUTPUT->footer();
