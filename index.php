<?php
require('../../config.php');
require_login();
require_capability('local/tagmanager:use', context_system::instance());

$PAGE->set_url('/local/tagmanager/index.php');
$PAGE->set_context(context_system::instance());
$PAGE->set_title('Tag Manager');
$PAGE->set_heading('Tag Manager');

echo $OUTPUT->header();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['tagfile'])) {
    $lines = file($_FILES['tagfile']['tmp_name'], FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    global $DB;
    $collectionid = \core_tag_collection::get_default();
    
    $csv = fopen($_FILES['tagfile']['tmp_name'], 'r');
    while (($data = fgetcsv($csv)) !== false) {
        $tagname = trim($data[0] ?? '');
        $description = trim($data[1] ?? '');
    
        if (!$tagname) continue;
    
        $existing = \core_tag_tag::get_by_name($collectionid, $tagname);
        if (!$existing) {
            $tags = \core_tag_tag::create_if_missing($collectionid, [$tagname]);
            if (!empty($tags)) {
                $tag = reset($tags);
                if ($description) {
                    $tagrecord = new stdClass();
                    $tagrecord->id = $tag->id;
                    $tagrecord->description = $description;
                    $tagrecord->timemodified = time();
                    $DB->update_record('tag', $tagrecord);
                }
                echo html_writer::div("Created tag: <strong>$tagname</strong>");
            }
        } else {
            echo html_writer::div("Tag already exists: <span class='text-muted'>$tagname</span>", 'small');
        }
    }
    fclose($csv);
}

echo '<form method="POST" enctype="multipart/form-data" class="p-3 border rounded mb-4">';
echo '<div class="mb-3">';
echo '<label for="tagfile" class="form-label">Upload Tag File</label>';
echo '<input type="file" class="form-control" id="tagfile" name="tagfile">';
echo '</div>';
echo '<button type="submit" class="btn btn-primary">Upload Tags</button>';
echo '</form>';

echo '<form method="POST" class="p-3 border rounded">';
echo '<input type="hidden" name="listtags" value="1" />';
echo '<button type="submit" class="btn btn-secondary">List All Tags</button>';
echo '</form>';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['listtags'])) {
    global $DB, $OUTPUT;
    $collectionid = \core_tag_collection::get_default();
    $tags = $DB->get_records('tag', ['tagcollid' => $collectionid], 'rawname ASC');

    $table = new html_table();
    $table->head = ['Tag Name', 'Tag ID', 'Description'];
    $table->attributes['class'] = 'table table-striped table-bordered table-sm';

    foreach ($tags as $tag) {
        $table->data[] = [
            s($tag->rawname),
            s($tag->id),
            s($tag->description ?? '')
        ];
    }

    echo '<div class="mt-5">';
    echo $OUTPUT->heading('All Tags in Default Collection', 3);
    echo html_writer::table($table);
    echo '</div>';
}

echo $OUTPUT->footer();
