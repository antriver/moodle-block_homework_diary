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

/**
 * Capabilities for homework block.
 *
 * @package    block_homework_diary
 * @copyright  Anthony Kuske <www.anthonykuske.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Post installation procedure.
 * Migrate from the old block_homework to the new block_homework_diary. But make sure it's our block_homework and
 * not the other one that's now in the directory!
 *
 * @see upgrade_plugins_modules()
 */
function xmldb_block_homework_diary_install() {
    global $DB;

    $oldversionfile = dirname(dirname(dirname(__FILE__))) . '/homework/version.php';
    if (!file_exists($oldversionfile)) {
        return false;
    }

    $plugin = new stdClass();
    include($oldversionfile);
    if (empty($plugin->version)) {
        return false;
    }

    if ($plugin->version > 2015110905) {
        // This isn't our one.
        return false;
    }

    $DB->execute('INSERT INTO {block_homework_diary} SELECT * FROM {block_homework}');
    $maxid = $DB->get_record_sql('SELECT MAX(id) AS id FROM {block_homework_diary}');
    $nextid = $maxid->id + 1;
    $DB->execute('ALTER SEQUENCE {block_homework_diary}_id_seq RESTART WITH '.$nextid);

    $DB->execute('INSERT INTO {block_homework_diary_dates} SELECT * FROM {block_homework_assign_dates}');
    $maxid = $DB->get_record_sql('SELECT MAX(id) AS id FROM {block_homework_diary_dates}');
    $nextid = $maxid->id + 1;
    $DB->execute('ALTER SEQUENCE {block_homework_diary_dates}_id_seq RESTART WITH '.$nextid);

    $DB->execute('INSERT INTO {block_homework_diary_notes} SELECT * FROM {block_homework_notes}');
    $maxid = $DB->get_record_sql('SELECT MAX(id) AS id FROM {block_homework_diary_notes}');
    $nextid = $maxid->id + 1;
    $DB->execute('ALTER SEQUENCE {block_homework_diary_notes}_id_seq RESTART WITH '.$nextid);

    return true;
}
