<?php
defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $ADMIN->add('appearance', new admin_externalpage(
        'local_tagbuilder',
        'Manage Tags with Tag Builder',
        new moodle_url('/local/tagbuilder/index.php'),
        'local/tagbuilder:use'
    ));
}
