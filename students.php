<?php

/**
 * Show a list of all students in the school so pastoral staff can switch to one
 *
 * @package    block_homework
 * @copyright  Anthony Kuske <www.anthonykuske.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require 'include/header.php';
echo $OUTPUT->header();

echo $hwblock->display->tabs('students');

switch ($hwblock->getMode()) {

	case 'pastoral':

		echo '<h2><i class="fa fa-user"></i> Student Lookup</h2>';

		echo $hwblock->display->sign('search', 'Find A Student', 'This section allows you to see what a student sees. Search for a student by name or PowerSchool ID below and click on one of the results.');

		echo $hwblock->display->studentList();

		break;
}

echo $OUTPUT->footer();
