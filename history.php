<?php

/**
 * List of all homework for the current user
 * TODO: Add pagination
 *
 * @package    block_homework
 * @copyright  Anthony Kuske <www.anthonykuske.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require 'include/header.php';
echo $OUTPUT->header();
echo $hwblock->display->tabs('history');

switch ($hwblock->getMode()) {

	case 'pastoral-student':
	case 'student':
	case 'parent':
	case 'teacher':

		echo $hwblock->display->sign('th-list', 'View History', 'All Homework, Sorted By Due Date (Latest At The Top)');

		// Get the user's group (class) IDs
		$groupIDs = $hwblock->groups->getAllUsersGroupIDs($hwblock->getUserId());

		$approvedStatus = true; // Only show approved homework
		$past = null; // Include future and past
		$order = 'hw.duedate DESC'; // Latest due date at the top

		$homework = $hwblock->getHomework(
			$groupIDs,
			false,
			false,
			$approvedStatus,
			true,
			$past,
			false,
			$order
		);

		echo $hwblock->display->homeworkList($homework);

		break;

	case 'pastoral':

		// What to show here?

		break;

}

echo $OUTPUT->footer();

