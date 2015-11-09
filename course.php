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
 * Display all homework due in a course
 *
 * @package    block_homework
 * @copyright  Anthony Kuske <www.anthonykuske.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('include/header.php');

$courseid = required_param('courseid', PARAM_INT);
$userid = optional_param('userid', '', PARAM_INT); // Or groupid instead?
$action = optional_param('action', 'view', PARAM_RAW); // Or groupid instead?

$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);

require_once($CFG->dirroot . '/course/lib.php');

echo $OUTPUT->header();
echo $hwblock->display->tabs();

$mode = $hwblock->get_mode();

echo '<h2 style="float:right; margin:17px 10px 0;"><i class="fa fa-group"></i> Showing All Classes In Course</h2>';
echo '<h2>' . $course->fullname . '</h2>';

// Get all groupids in the course.
$groups = $DB->get_records('groups', array('courseid' => $course->id));
$groupids = array_map(function ($group) {
    return $group->id;
}, $groups);

if ($mode == 'teacher' || $mode == 'pastoral') {
    // Display homework submitted by students that's not yet approved.
    $pendinghomework = $hwblock->repository->get_pending_homework($groupids);
    echo '<h3><i class="fa fa-exclamation-circle"></i> Pending Homework Submissions For This Course</h3>';
    echo $hwblock->display->homework_list($pendinghomework);
}

echo '<h3><i class="fa fa-bell"></i> Current Homework For This Course</h3>';
$currenthomework = $hwblock->repository->get_current_homework($groupids);
echo $hwblock->display->homework_list($currenthomework);

if ($mode == 'teacher' || $mode == 'pastoral') {
    // Display homework scheduled to appear in the future.
    $scheduled = $hwblock->repository->get_upcoming_homework($groupids);
    echo '<h3><i class="fa fa-pause"></i> Upcoming Homework For This Course</h3>';
    echo $hwblock->display->homework_list($scheduled);
}

if ($mode == 'student' || $mode === 'pastoral-student') {
    // Display private homework for this class to students.
    $privatehomework = $hwblock->repository->get_private_homework($hwblock->get_user_id(), $groupids);
    echo '<h3><i class="fa fa-eye-slash"></i> Your Private Homework For This Course</h3>';
    echo $hwblock->display->homework_list($privatehomework);

}

echo '<h3><i class="fa fa-calendar"></i> Previous Homework For This Course</h3>';
$previous = $hwblock->repository->get_previous_homework($groupids);
echo $hwblock->display->homework_list($previous);

echo $OUTPUT->footer();
