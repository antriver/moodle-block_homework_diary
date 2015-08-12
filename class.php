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
 * Display all homework due in a class (group)
 *
 * @package    block_homework
 * @copyright  Anthony Kuske <www.anthonykuske.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require 'include/header.php';

$groupid = required_param('groupid', PARAM_INT);

$group = $DB->get_record('groups', array('id' => $groupid), '*', MUST_EXIST);
$course = $DB->get_record('course', array('id' => $group->courseid), '*', MUST_EXIST);

require_once $CFG->dirroot . '/course/lib.php';

echo $OUTPUT->header();
echo $hwblock->display->tabs(false, false, false, $group->id);

$mode = $hwblock->getMode();

// echo '<h2 style="float:right; margin:17px 10px 0;"><i class="fa fa-group"></i> ' . $group->name . '</h2>';
// echo '<h2>' . ($courseIcon ? '<i class="fa fa-' . $courseIcon . '"></i> ' : '') . $course->fullname . '</h2>';

echo $hwblock->display->sign('', 'Now viewing ' . $course->fullname, '<small style="float:right;">Class: ' . $group->name . '</small> See below for upcoming and due homework.');

if ($mode == 'teacher' || $mode == 'pastoral') {
	$pendingHomework = $hwblock->getHomework(array($group->id), false, false, false);
	echo '<h3><i class="fa fa-check"></i> Upcoming Homework For This Class</h3>';
	echo $hwblock->display->homeworkList($pendingHomework);
}

echo '<h3><i class="fa fa-bell"></i> Due Homework For This Class</h3>';
$homework = $hwblock->getHomework(array($group->id), false, false, true);
echo $hwblock->display->homeworkList($homework);

echo '<h3><i class="fa fa-calendar"></i> Previous Homework For This Class</h3>';
$homework = $hwblock->getHomework(array($group->id), false, false, true, true, true);
echo $hwblock->display->homeworkList($homework);

echo $OUTPUT->footer();
