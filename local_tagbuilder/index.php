<?php
require('../../config.php');
require_login();
require_capability('local/tagbuilder:use', context_system::instance());


// Handle tag export
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['exporttags'])) {
    global $DB;
    $collectionid = \core_tag_collection::get_default();
    $tagrecords = $DB->get_records('tag', ['tagcollid' => $collectionid], 'rawname ASC');
    
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="tags_export.csv"');
    
    $output = fopen('php://output', 'w');
    fputcsv($output, ['tagname', 'description']);
    
    foreach ($tagrecords as $tag) {
        fputcsv($output, [$tag->rawname, $tag->description ?? '']);
    }
    
    fclose($output);
    exit;
}

$PAGE->set_url('/local/tagbuilder/index.php');
$PAGE->set_context(context_system::instance());
$PAGE->set_title('Tag Builder');
$PAGE->set_heading('Tag Builder');

echo $OUTPUT->header();

$uploadresults = [];
$showtags = false;
$tags = [];

// Handle single tag creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tagname'])) {
    global $DB;
    $collectionid = \core_tag_collection::get_default();
    $tagname = trim($_POST['tagname']);
    $description = trim($_POST['tagdesc'] ?? '');
    
    if ($tagname) {
        $existing = \core_tag_tag::get_by_name($collectionid, $tagname);
        if (!$existing) {
            $tags_created = \core_tag_tag::create_if_missing($collectionid, [$tagname]);
            if (!empty($tags_created)) {
                $tag = reset($tags_created);
                if ($description) {
                    $tagrecord = new stdClass();
                    $tagrecord->id = $tag->id;
                    $tagrecord->description = $description;
                    $tagrecord->timemodified = time();
                    $DB->update_record('tag', $tagrecord);
                }
                $uploadresults[] = "<div class='alert alert-success'>Created tag: <strong>$tagname</strong></div>";
            }
        } else {
            $uploadresults[] = "<div class='alert alert-warning'>Tag already exists: <strong>$tagname</strong></div>";
        }
    }
}

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['tagfile'])) {
    global $DB;
    $collectionid = \core_tag_collection::get_default();
    
    $csv = fopen($_FILES['tagfile']['tmp_name'], 'r');
    while (($data = fgetcsv($csv)) !== false) {
        $tagname = trim($data[0] ?? '');
        $description = trim($data[1] ?? '');
    
        if (!$tagname) continue;
    
        $existing = \core_tag_tag::get_by_name($collectionid, $tagname);
        if (!$existing) {
            $tags_created = \core_tag_tag::create_if_missing($collectionid, [$tagname]);
            if (!empty($tags_created)) {
                $tag = reset($tags_created);
                if ($description) {
                    $tagrecord = new stdClass();
                    $tagrecord->id = $tag->id;
                    $tagrecord->description = $description;
                    $tagrecord->timemodified = time();
                    $DB->update_record('tag', $tagrecord);
                }
                $uploadresults[] = "<div>Created tag: <strong>$tagname</strong></div>";
            }
        } else {
            $uploadresults[] = "<div class='small'>Tag already exists: <span class='text-muted'>$tagname</span></div>";
        }
    }
    fclose($csv);
}

// Handle list tags
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['listtags'])) {
    global $DB;
    $collectionid = \core_tag_collection::get_default();
    $tagrecords = $DB->get_records('tag', ['tagcollid' => $collectionid], 'rawname ASC');
    
    foreach ($tagrecords as $tag) {
        $instancecount = $DB->count_records('tag_instance', ['tagid' => $tag->id]);
        $tags[] = [
            'name' => s($tag->rawname),
            'id' => s($tag->id),
            'description' => s($tag->description ?? ''),
            'instancecount' => $instancecount,
            'candel' => $instancecount == 0
        ];
    }
    $showtags = true;
}

// Handle tag deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['deletetag'])) {
    global $DB;
    $tagid = intval($_POST['tagid']);
    
    if ($tagid) {
        // Check if tag has any instances (items tagged with it)
        $instances = $DB->count_records('tag_instance', ['tagid' => $tagid]);
        
        if ($instances == 0) {
            $tagname = $DB->get_field('tag', 'rawname', ['id' => $tagid]);
            $DB->delete_records('tag', ['id' => $tagid]);
            $uploadresults[] = "<div class='alert alert-success'>Deleted unused tag: <strong>$tagname</strong></div>";
        } else {
            $uploadresults[] = "<div class='alert alert-danger'>Cannot delete tag - it has $instances tagged items</div>";
        }
    }
}

echo $OUTPUT->render_from_template('local_tagbuilder/tagbuilder', [
    'uploadresults' => $uploadresults,
    'showtags' => $showtags,
    'tags' => $tags
]);

echo $OUTPUT->footer();
