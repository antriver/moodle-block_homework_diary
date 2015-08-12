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
 * @package    block_homework
 * @copyright  Anthony Kuske <www.anthonykuske.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once dirname(dirname(dirname(__DIR__))) . '/config.php';

require_login();

// Include the goodies for this block
$hwblock = new block_homework\Block;

$q = required_param('q', PARAM_RAW);

// FIXME: SSIS
$sql = "SELECT id, idnumber, firstname, lastname
FROM {user}
WHERE
	email LIKE '%@student.ssis-suzhou.net'
	AND (
		id = ?
		OR idnumber = ?
		OR LOWER(department) = ?
		OR REPLACE(CONCAT(LOWER(firstname), LOWER(lastname)),  ' ', '') LIKE ?
		OR REPLACE(CONCAT(LOWER(lastname),  LOWER(firstname)), ' ', '') LIKE ?
		OR LOWER(lastname) LIKE ?
	)
	AND deleted = 0
ORDER BY firstname, lastname ASC";

$words = explode(' ', $q);
$wildq = strtolower('%' . implode('%', $words) . '%');

$values = array(
	intval($q), // userID
	intval($q), // idnumber
	strtolower($q), // department
	$wildq,
	$wildq,
	$wildq,
	$wildq,
);
$records = $DB->get_records_sql($sql, $values);

header('Content-type: application/json');
echo json_encode(array('users' => $records));
