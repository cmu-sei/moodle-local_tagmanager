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
Tag Manager Plugin for Moodle

Copyright 2026 Carnegie Mellon University.

NO WARRANTY. THIS CARNEGIE MELLON UNIVERSITY AND SOFTWARE ENGINEERING INSTITUTE MATERIAL IS FURNISHED ON AN "AS-IS" BASIS.
CARNEGIE MELLON UNIVERSITY MAKES NO WARRANTIES OF ANY KIND, EITHER EXPRESSED OR IMPLIED, AS TO ANY MATTER INCLUDING, BUT NOT LIMITED TO,
WARRANTY OF FITNESS FOR PURPOSE OR MERCHANTABILITY, EXCLUSIVITY, OR RESULTS OBTAINED FROM USE OF THE MATERIAL. CARNEGIE MELLON UNIVERSITY
DOES NOT MAKE ANY WARRANTY OF ANY KIND WITH RESPECT TO FREEDOM FROM PATENT, TRADEMARK, OR COPYRIGHT INFRINGEMENT.

Licensed under a GNU GENERAL PUBLIC LICENSE - Version 3, 29 June 2007-style license, please see license.txt or contact permission@sei.cmu.edu for full terms.

[DISTRIBUTION STATEMENT A] This material has been approved for public release and unlimited distribution. Please see Copyright notice for non-US Government use and distribution.

This Software includes and/or makes use of Third-Party Software each subject to its own license.

DM26-0016
*/

/**
 * Moodle form to upload csv file container tags to be imported by the Tag Manager Plugin.
 * @package    local_tagmanager
 * @copyright  2026 Carnegie Mellon University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_tagmanager\form;

defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir . '/formslib.php');

class upload_tags_form extends \moodleform {
    public function definition() {
        global $CFG;
        $m = $this->_form;

        $collections = $this->_customdata['collections'] ?? [];
        $defaultcoll = $this->_customdata['defaultcoll'] ?? \core_tag_collection::get_default();

        $fixedcollid = $this->_customdata['fixedcollid'] ?? null;

        // Collapsible section.
        $m->addElement('header', 'hdr', get_string('uploadtags','local_tagmanager'));
        $m->setExpanded('hdr', true);

        if ($fixedcollid) {
            $m->addElement('hidden', 'tc', $fixedcollid);
            $m->setType('tc', PARAM_INT);
            $m->addElement('hidden', 'tagcollid', (int)$fixedcollid);
            $m->setType('tagcollid', PARAM_INT);
        } else {
            $m->addElement('select', 'tagcollid', get_string('tagcollection', 'local_tagmanager'), $collections);
            $m->addRule('tagcollid', null, 'required', null, 'client');
            $m->setType('tagcollid', PARAM_INT);
            $m->setDefault('tagcollid', $defaultcoll);
        }

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

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        $fixedcollid = $this->_customdata['fixedcollid'] ?? null;

        if (!$fixedcollid) {
            $collections = $this->_customdata['collections'] ?? [];
            if (empty($data['tagcollid']) || !array_key_exists((int)$data['tagcollid'], $collections)) {
                $errors['tagcollid'] = get_string('invalidcollection', 'local_tagmanager');
            }
        }

        return $errors;
    }
}
