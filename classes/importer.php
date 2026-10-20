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

/**
 * Imports tags from CSV content into a tag collection.
 *
 * The CSV is parsed from memory and never stored, so the plugin keeps no copy of the upload.
 */
class importer {
    /**
     * Import tags from CSV content.
     *
     * Column 1 is the tag name, column 2 an optional description. A header row is skipped.
     *
     * @param int $tc Tag collection id.
     * @param string $content Raw CSV content.
     * @param int $maxnames Maximum number of names to return per list.
     * @return \stdClass created, existed, importednames, existingnames.
     */
    public static function import_csv(int $tc, string $content, int $maxnames = 25): \stdClass {
        global $DB;

        $result = (object)[
            'created' => 0,
            'existed' => 0,
            'importednames' => [],
            'existingnames' => [],
        ];

        $fh = fopen('php://temp', 'r+');
        fwrite($fh, $content);
        rewind($fh);

        $firstrow = true;

        while (($row = fgetcsv($fh, null, ',', '"', '\\')) !== false) {
            $tagname = trim($row[0] ?? '');
            $desc    = trim($row[1] ?? '');

            // Skip header row.
            if ($firstrow) {
                $firstrow = false;

                $h1 = \core_text::strtolower(trim($tagname));
                $h2 = \core_text::strtolower(trim($desc));

                $isheader =
                    ($h1 === 'tagname' || $h1 === 'tag name' || $h1 === 'name') &&
                    ($h2 === 'description' || $h2 === 'tag description' || $h2 === 'desc');

                if ($isheader) {
                    continue;
                }
            }

            if ($tagname === '') {
                continue;
            }

            $existing = \core_tag_tag::get_by_name($tc, $tagname);
            if ($existing) {
                $result->existed++;
                if (count($result->existingnames) < $maxnames) {
                    $result->existingnames[] = $tagname;
                }
                continue;
            }

            $created = \core_tag_tag::create_if_missing($tc, [$tagname]);
            if (!empty($created)) {
                $tag = reset($created);

                if ($desc !== '') {
                    $DB->update_record('tag', (object)[
                        'id' => $tag->id,
                        'description' => $desc,
                        'timemodified' => time(),
                    ]);
                }

                $result->created++;
                if (count($result->importednames) < $maxnames) {
                    $result->importednames[] = $tagname;
                }
            }
        }

        fclose($fh);

        return $result;
    }
}
