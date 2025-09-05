<?php
defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $ADMIN->add('appearance', new admin_externalpage(
        'local_tagmanager',
        'Manage Tags with Tag Manager',
        new moodle_url('/local/tagmanager/index.php'),
        'local/tagmanager:use'
    ));
}
