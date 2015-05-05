<?php

/**
 * Display all homework due in a course
 *
 * @package    block_homework
 * @copyright  Anthony Kuske <www.anthonykuske.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require 'include/header.php';

$courseid = required_param('courseid', PARAM_INT);
$userid = optional_param('userid', '', PARAM_INT); //or groupid instead?
$action = optional_param('action', 'view', PARAM_RAW); //or groupid instead?

$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);

require_once $CFG->dirroot . '/course/lib.php';

echo $OUTPUT->header();
echo $hwblock->display->tabs();

$mode = $hwblock->getMode();

echo '<h2 style="float:right; margin:17px 10px 0;"><i class="fa fa-group"></i> Showing All Classes In Course</h2>';
echo '<h2>' . $course->fullname . '</h2>';

if ($mode == 'teacher' || $mode == 'pastoral') {
	$pendingHomework = $hwblock->getHomework(false, array($course->id), false, false);
	echo '<h3><i class="fa fa-list"></i> Pending Homework For This Course</h3>';
	echo $hwblock->display->homeworkList($pendingHomework, false, false, false, true);
}

echo '<h3><i class="fa fa-bell"></i> Homework Due For This Course</h3>';
$homework = $hwblock->getHomework(false, array($course->id), false, true);
echo $hwblock->display->homeworkList($homework, false, false, false, true);

echo '<h3><i class="fa fa-calendar"></i> Previous Homework For This Course</h3>';
$homework = $hwblock->getHomework(false, array($course->id), false, true, true, true);
echo $hwblock->display->homeworkList($homework, false, false, false, true);

echo $OUTPUT->footer();
