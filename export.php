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

namespace local_tagmanager\form;

defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir . '/formslib.php');

class upload_tags_form extends \moodleform {
    public function definition() {
        global $CFG;
        $m = $this->_form;

        // Collapsible section.
        $m->addElement('header', 'hdr', get_string('uploadtags','local_tagmanager'));
        $m->setExpanded('hdr', true);

        // Filepicker.
        $m->addElement('filepicker','tagfile', get_string('csvfile','local_tagmanager'), null, [
            'maxbytes'       => $CFG->maxbytes,
            'accepted_types' => ['.csv'],
            'subdirs'        => 0,
        ]);
        $m->addRule('tagfile', null, 'required', null, 'client');

        $btns = [];
        $btns[] = $m->createElement('submit', 'submitbutton', get_string('uploadtags','local_tagmanager'));
        $btns[] = $m->createElement('cancel', 'cancel', get_string('cancel'));
        $m->addGroup($btns, 'buttonar', '', ' ', false);

    }
}
