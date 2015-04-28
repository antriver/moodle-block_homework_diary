<?php

/**
 * @package    block_homework
 * @copyright  Anthony Kuske <www.anthonykuske.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Can't work anymore due to removing course metadata
header('Location: /blocks/homework');
die();

/**
 * Display all the grades (years) in the school
 */

require 'include/header.php';
echo $OUTPUT->header();

echo $hwblock->display->tabs('grades');

switch ($hwblock->getMode()) {

	case 'pastoral':

		echo '<h2><i class="fa fa-group"></i> All Grades</h2>';

		echo '<ul class="buttons">';
		for ($grade = 1; $grade <= 12; $grade++) {

			echo '<li>';
				echo '<a class="btn" href="grade.php?grade=' . $grade . '">Grade ' . $grade . '</a>';
			echo '</li>';

		}
		echo '</ul>';

		break;
}

echo $OUTPUT->footer();
