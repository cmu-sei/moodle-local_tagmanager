<?php
require('../../config.php');
require_login();
require_sesskey();

$context = context_system::instance();
require_capability('local/tagmanager:use', $context);

global $DB;
$collectionid = \core_tag_collection::get_default();
$tags = $DB->get_records('tag', ['tagcollid' => $collectionid], 'rawname ASC');

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="tags_export.csv"');

$out = fopen('php://output', 'w');
fputcsv($out, ['tagname', 'description']);
foreach ($tags as $t) {
    fputcsv($out, [$t->rawname, $t->description ?? '']);
}
fclose($out);
exit;