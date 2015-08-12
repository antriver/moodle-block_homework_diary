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
 * Display a list of the groups the user is enrolled in
 *
 * @package    block_homework
 * @copyright  Anthony Kuske <www.anthonykuske.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require 'include/header.php';
echo $OUTPUT->header();

echo $hwblock->display->tabs('classes');

switch ($hwblock->getMode()) {

	case 'pastoral-student':
	case 'student':
	case 'parent':
	case 'teacher':

		/**
		 * Show the timetable view of the student's homework due in the next 2 weeks
		 */
		echo $hwblock->display->sign('group', 'View by Class', 'Find a summary of homework information (including upcoming and due) by class only.');
		$groups = $hwblock->groups->getAllUsersGroups($hwblock->getUserId());
		echo $hwblock->display->classList($groups);

		break;

	case 'pastoral':

		/**
		 * Show all classes in the school
		 */
		echo '<h2><i class="fa fa-group"></i> All Classes</h2>';
		$groups = $hwblock->groups->getAllGroups();
		echo $hwblock->display->classList($groups);

		break;
}

echo $OUTPUT->footer();
