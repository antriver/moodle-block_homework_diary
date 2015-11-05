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

if ($mode == 'teacher' || $mode == 'pastoral') {
    $pendinghomework = $hwblock->get_homework(false, array($course->id), false, false);
    echo '<h3><i class="fa fa-list"></i> Pending Homework For This Course</h3>';
    echo $hwblock->display->homework_list($pendinghomework, false, false, false, true);
}

echo '<h3><i class="fa fa-bell"></i> Homework Due For This Course</h3>';
$homework = $hwblock->get_homework(false, array($course->id), false, true);
echo $hwblock->display->homework_list($homework, false, false, false, true);

echo '<h3><i class="fa fa-calendar"></i> Previous Homework For This Course</h3>';
$homework = $hwblock->get_homework(false, array($course->id), false, true, true, true);
echo $hwblock->display->homework_list($homework, false, false, false, true);

echo $OUTPUT->footer();
