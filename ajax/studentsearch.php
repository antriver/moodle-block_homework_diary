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
 * Search for a user.
 *
 * @package    block_homework_diary
 * @copyright  Anthony Kuske <www.anthonykuske.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__DIR__))) . '/config.php');

require_login();

// Include the goodies for this block.
$hwblock = new \block_homework_diary\local\block();

$q = required_param('q', PARAM_RAW);

$studentcohortid = (int)get_config('block_homework_diary', 'student_cohort');
$sql = "SELECT u.id, u.idnumber, u.firstname, u.lastname
FROM {user} u
JOIN {cohort_members} cm ON cm.userid = u.id
WHERE
	cm.cohortid = ?
	AND (
		u.id = ?
		OR u.idnumber = ?
		OR LOWER(u.department) = ?
		OR REPLACE(CONCAT(LOWER(u.firstname), LOWER(u.lastname)),  ' ', '') LIKE ?
		OR REPLACE(CONCAT(LOWER(u.lastname),  LOWER(u.firstname)), ' ', '') LIKE ?
		OR LOWER(u.lastname) LIKE ?
	)
	AND u.deleted = 0
GROUP BY u.id
ORDER BY u.firstname, u.lastname ASC";

$words = explode(' ', $q);
$wildq = strtolower('%' . implode('%', $words) . '%');

$values = array(
    $studentcohortid, // Cohortid.
    intval($q), // UserID.
    intval($q), // Idnumber.
    strtolower($q), // Department.
    $wildq,
    $wildq,
    $wildq,
    $wildq,
);
$records = $DB->get_records_sql($sql, $values);

header('Content-type: application/json');
echo json_encode(array('users' => $records));
