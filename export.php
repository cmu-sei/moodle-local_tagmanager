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
require('../../config.php');
require_login();
require_sesskey();

$context = context_system::instance();
require_capability('local/tagmanager:use', $context);

global $DB;

// Require collection id.
$collectionid = required_param('tc', PARAM_INT);

$tagids = optional_param('tagids', '', PARAM_SEQUENCE);

// Validate collection exists.
if (!$DB->record_exists('tag_coll', ['id' => $collectionid])) {
    print_error('invalidcollection', 'local_tagmanager');
}

$collection = $DB->get_record('tag_coll', ['id' => $collectionid], 'id,name,isdefault', MUST_EXIST);

$collectionname = trim((string)($collection->name ?? ''));
if ($collectionname === '' && (int)$collection->isdefault === 1) {
    $collectionname = get_string('defaultcollectionname', 'local_tagmanager');
}
if ($collectionname === '') {
    $collectionname = 'collection_' . $collectionid;
}

$formatname = trim($collectionname);
$formatname = preg_replace('/[^A-Za-z0-9_-]+/', '_', $formatname);
$formatname = trim($formatname, '_');

if ($formatname === '') {
    $formatname = 'collection_' . $collectionid;
}

$filename = "{$formatname}_export.csv";

$tags = $DB->get_records('tag', ['tagcollid' => $collectionid], 'rawname ASC');

header('Content-Type: text/csv; charset=utf-8');

$disposition = 'attachment; filename="' . $filename . '"';
$disposition .= "; filename*=UTF-8''" . rawurlencode($filename);
header('Content-Disposition: ' . $disposition);

$out = fopen('php://output', 'w');
fputcsv($out, ['tagname', 'description']);
if ($tagids !== '') {
    // Export only selected tags
    $ids = array_filter(array_map('intval', explode(',', $tagids)));

    if (!empty($ids)) {
        list($insql, $params) = $DB->get_in_or_equal($ids, SQL_PARAMS_NAMED);
        $params['collid'] = $collectionid;

        $sql = "SELECT id, rawname, description
                  FROM {tag}
                 WHERE tagcollid = :collid
                   AND id $insql
              ORDER BY rawname ASC";

        $tags = $DB->get_records_sql($sql, $params);

        foreach ($tags as $t) {
            fputcsv($out, [$t->rawname, $t->description ?? '']);
        }
    }
} else {
    // Export all tags in collection
    $tags = $DB->get_records('tag', ['tagcollid' => $collectionid], 'rawname ASC', 'id,rawname,description');
    foreach ($tags as $t) {
        fputcsv($out, [$t->rawname, $t->description ?? '']);
    }
}
fclose($out);
exit;
