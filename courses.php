<?php

/**
 * Display a list of the courses the user is enrolled in
 * It is preferable to use the classes.php page which shows groups instead.
 *
 * @package    block_homework
 * @copyright  Anthony Kuske <www.anthonykuske.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require 'include/header.php';
echo $OUTPUT->header();

echo $hwblock->display->tabs('courses');

switch ($hwblock->getMode()) {

	case 'student':
	case 'parent':

		echo '<h2><i class="fa fa-th-list"></i> My Courses</h2>';
		$courses = $hwblock->getUsersCourses($hwblock->getUserId());
		echo $hwblock->display->courseList($courses);

		break;

	case 'teacher':

		echo '<h2><i class="fa fa-th-list"></i> Courses I Teach</h2>';
		$teacherRoleID = 3;
		$courses = $hwblock->getUsersCourses($hwblock->getUserId(), $teacherRoleID);
		echo $hwblock->display->courseList($courses);

		break;

	case 'pastoral':

		 // Show all courses in the school
		echo '<h2><i class="fa fa-th-list"></i> All Courses</h2>';

		$courses = $hwblock->getAllCourses();

		echo $hwblock->display->courseList($courses);

		break;
}

echo $OUTPUT->footer();
