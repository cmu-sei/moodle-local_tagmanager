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

/**
 * Tag Manager Plugin.
 * @package    local_tagmanager
 * @copyright  2026 Carnegie Mellon University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_tagmanager;
defined('MOODLE_INTERNAL') || die();

/**
 * Tests for the CSV importer.
 */
#[\PHPUnit\Framework\Attributes\CoversClass(importer::class)]
final class importer_test extends \advanced_testcase {
    public function test_import_creates_tags_and_skips_existing(): void {
        global $CFG, $DB;
        $this->resetAfterTest();

        $tc = \core_tag_collection::get_default();
        \core_tag_tag::create_if_missing($tc, ['Existing']);

        $content = file_get_contents($CFG->dirroot . '/local/tagmanager/tests/fixtures/tags.csv');
        $result = importer::import_csv($tc, $content);

        $this->assertSame(2, $result->created);
        $this->assertSame(1, $result->existed);
        $this->assertSame(['Alpha', 'Beta'], $result->importednames);
        $this->assertSame(['Existing'], $result->existingnames);
        $this->assertSame('First imported tag', $DB->get_field('tag', 'description', ['tagcollid' => $tc, 'name' => 'alpha']));
    }

    public function test_import_keeps_no_copy_of_the_csv(): void {
        global $CFG, $DB;
        $this->resetAfterTest();

        $tc = \core_tag_collection::get_default();
        $content = file_get_contents($CFG->dirroot . '/local/tagmanager/tests/fixtures/tags.csv');
        importer::import_csv($tc, $content);

        $this->assertSame(0, $DB->count_records('files', ['component' => 'local_tagmanager']));
    }

    public function test_delete_stored_csvs_removes_only_tagcsv_files(): void {
        global $CFG, $DB;
        $this->resetAfterTest();
        require_once($CFG->dirroot . '/local/tagmanager/db/upgradelib.php');

        $fs = get_file_storage();
        $syscontextid = \context_system::instance()->id;
        $record = [
            'contextid' => $syscontextid,
            'component' => 'local_tagmanager',
            'filearea' => 'tagcsv',
            'itemid' => \core_tag_collection::get_default(),
            'filepath' => '/',
            'filename' => 'tags.csv',
        ];
        $fs->create_file_from_string($record, "tagname,description\nAlpha,First\n");
        $other = ['component' => 'local_tagmanager', 'filearea' => 'other'] + $record;
        $fs->create_file_from_string($other, 'keep');

        local_tagmanager_delete_stored_csvs();

        $this->assertSame(0, $DB->count_records('files', ['component' => 'local_tagmanager', 'filearea' => 'tagcsv']));
        $this->assertFalse($fs->is_area_empty($syscontextid, 'local_tagmanager', 'other'));
    }
}
