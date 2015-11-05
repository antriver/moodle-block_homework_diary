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

