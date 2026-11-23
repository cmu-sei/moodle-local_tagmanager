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
 * Keeps spreadsheet formulas in exported tags from running when the CSV is opened.
 *
 * A cell that starts with =, +, - or @ (or a tab or carriage return) is treated as a formula by
 * Excel, LibreOffice and Google Sheets. A tag name or description is free text that any user can
 * set, so the export puts a single quote in front of such a cell, which makes spreadsheets show
 * it as plain text. The importer takes the quote off again, so an exported file imports back to
 * the same tags.
 */
class csv {
    /** @var string Matches a cell a spreadsheet would read as a formula, with or without our quote in front. */
    private const FORMULA = "/^'*[=+\\-@\\t\\r]/";

    /**
     * Make a cell safe to put in an exported CSV.
     *
     * @param string $value The cell text.
     * @return string The text, with a leading quote added when a spreadsheet could read it as a formula.
     */
    public static function escape_cell(string $value): string {
        return preg_match(self::FORMULA, $value) ? "'" . $value : $value;
    }

    /**
     * Undo escape_cell() on a cell read from an imported CSV.
     *
     * @param string $value The cell text.
     * @return string The text without the quote that escape_cell() added.
     */
    public static function unescape_cell(string $value): string {
        return ($value !== '' && $value[0] === "'" && preg_match(self::FORMULA, $value)) ? substr($value, 1) : $value;
    }

    /**
     * Write one tag row to an exported CSV, guarding each cell.
     *
     * The escape character is empty so a quote inside a cell is always doubled; with a backslash
     * escape, PHP leaves a quote after a backslash undoubled and a standard CSV reader would split
     * the cell at that point, which could leave a formula at the start of a new cell.
     *
     * @param resource $out Open file handle.
     * @param string $name Tag name.
     * @param string $description Tag description.
     */
    public static function write_row($out, string $name, string $description): void {
        fputcsv($out, [self::escape_cell($name), self::escape_cell($description)], ',', '"', '');
    }
}
