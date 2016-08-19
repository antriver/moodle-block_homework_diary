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
 * List of all homework for the current user.
 *
 * @package    block_homework_diary
 * @copyright  Anthony Kuske <www.anthonykuske.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('include/header.php');
echo $OUTPUT->header();
echo $hwblock->display->tabs('history');

switch ($hwblock->get_mode()) {

    case 'pastoral-student':
    case 'student':
    case 'parent':

        // Get all the user's group (class) IDs.
        $groupids = $hwblock->groups->get_all_users_group_ids($hwblock->get_user_id());

        echo $hwblock->display->sign('th-list', 'Full List', 'Showing homework for all of your classes.');

        echo '<h3><i class="fa fa-bell"></i> Current Homework</h3>';
        $currenthomework = $hwblock->repository->get_current_homework($groupids);
        echo $hwblock->display->homework_list($currenthomework);

        $privatehomework = $hwblock->repository->get_private_homework($hwblock->get_user_id());
        echo '<h3><i class="fa fa-eye-slash"></i> Your Private Homework</h3>';
        echo $hwblock->display->homework_list($privatehomework);

        echo '<h3><i class="fa fa-calendar"></i> Previous Homework</h3>';
        $previous = $hwblock->repository->get_previous_homework($groupids);
        echo $hwblock->display->homework_list($previous);

        break;

    case 'teacher':

        // Get all the user's group (class) IDs.
        $groupids = $hwblock->groups->get_all_users_group_ids($hwblock->get_user_id());

        echo $hwblock->display->sign('th-list', 'Full List', 'Showing homework for all of your classes.');

        echo '<h3><i class="fa fa-bell"></i> Current Homework</h3>';
        $currenthomework = $hwblock->repository->get_current_homework($groupids);
        echo $hwblock->display->homework_list($currenthomework);

        $scheduled = $hwblock->repository->get_upcoming_homework($groupids);
        echo '<h3><i class="fa fa-pause"></i> Upcoming Homework</h3>';
        echo $hwblock->display->homework_list($scheduled);

        echo '<h3><i class="fa fa-calendar"></i> Previous Homework</h3>';
        $previous = $hwblock->repository->get_previous_homework($groupids);
        echo $hwblock->display->homework_list($previous);

        break;
}

echo $OUTPUT->footer();

