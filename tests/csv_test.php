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

NO WARRANTY. THIS CARNEGIE MELLON UNIVERSITY AND SOFTWARE ENGINEERING INSTITUTE MATERIAL IS FURNISHED ON AN "AS-IS"
BASIS. CARNEGIE MELLON UNIVERSITY MAKES NO WARRANTIES OF ANY KIND, EITHER EXPRESSED OR IMPLIED, AS TO ANY MATTER
INCLUDING, BUT NOT LIMITED TO, WARRANTY OF FITNESS FOR PURPOSE OR MERCHANTABILITY, EXCLUSIVITY, OR RESULTS OBTAINED FROM
USE OF THE MATERIAL. CARNEGIE MELLON UNIVERSITY DOES NOT MAKE ANY WARRANTY OF ANY KIND WITH RESPECT TO FREEDOM FROM
PATENT, TRADEMARK, OR COPYRIGHT INFRINGEMENT.

Licensed under a GNU GENERAL PUBLIC LICENSE - Version 3, 29 June 2007-style license, please see license.txt or contact
permission@sei.cmu.edu for full terms.

[DISTRIBUTION STATEMENT A] This material has been approved for public release and unlimited distribution. Please see
Copyright notice for non-US Government use and distribution.

This Software includes and/or makes use of Third-Party Software each subject to its own license.

DM26-0016
*/

/**
 * Tag Manager Plugin.
 * @package    local_tagmanager
 * @copyright  2026 Carnegie Mellon University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_tagmanager;

/**
 * Tests for the CSV formula guard.
 */
#[\PHPUnit\Framework\Attributes\CoversClass(csv::class)]
final class csv_test extends \advanced_testcase {
    /**
     * Cells a spreadsheet would read as a formula, and how they should be exported.
     *
     * @return array
     */
    public static function formula_provider(): array {
        return [
            'equals' => ['=SUM(A1:A9)', "'=SUM(A1:A9)"],
            'plus' => ['+1+1', "'+1+1"],
            'minus' => ['-2+3', "'-2+3"],
            'at' => ['@SUM(1)', "'@SUM(1)"],
            'tab' => ["\t=1", "'\t=1"],
            'carriage return' => ["\r=1", "'\r=1"],
            'already quoted formula' => ["'=1", "''=1"],
            'dde style payload' => ['=cmd|\' /C calc\'!A0', "'=cmd|' /C calc'!A0"],
        ];
    }

    /**
     * A cell a spreadsheet could read as a formula gets a leading quote.
     *
     * @dataProvider formula_provider
     * @param string $value The tag text.
     * @param string $expected What the export should write.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('formula_provider')]
    public function test_escape_cell_quotes_formulas(string $value, string $expected): void {
        $this->assertSame($expected, csv::escape_cell($value));
    }

    /**
     * Importing a cell undoes what the export did to it.
     *
     * @dataProvider formula_provider
     * @param string $value The tag text.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('formula_provider')]
    public function test_unescape_cell_restores_the_original(string $value): void {
        $this->assertSame($value, csv::unescape_cell(csv::escape_cell($value)));
    }

    public function test_ordinary_text_is_left_alone(): void {
        foreach (['Alpha', 'a-b', 'x=y', 'email@example.com', '', "it's", '2024-01', 'Q&A'] as $value) {
            $this->assertSame($value, csv::escape_cell($value));
            $this->assertSame($value, csv::unescape_cell($value));
        }
    }

    public function test_import_reads_back_what_the_export_wrote(): void {
        global $DB;
        $this->resetAfterTest();
        $tc = \core_tag_collection::get_default();

        $names = ['=SUM_A1', '+44 phone', '@mention', '-1 point'];
        $out = fopen('php://temp', 'r+');
        fputcsv($out, ['tagname', 'description'], ',', '"', '\\');
        foreach ($names as $name) {
            fputcsv($out, [csv::escape_cell($name), csv::escape_cell('=1+1')], ',', '"', '\\');
        }
        rewind($out);
        $content = stream_get_contents($out);
        fclose($out);

        $result = importer::import_csv($tc, $content);

        $this->assertSame(4, $result->created);
        foreach ($names as $name) {
            $tag = \core_tag_tag::get_by_name($tc, $name);
            $this->assertNotFalse($tag, "The tag $name should exist exactly as written.");
        }
        $tag = \core_tag_tag::get_by_name($tc, '@mention');
        $this->assertSame('=1+1', $DB->get_field('tag', 'description', ['id' => $tag->id]));
    }
}
