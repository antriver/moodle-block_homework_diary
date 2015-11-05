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
 * Front page for homework block
 *
 * For students: shows 'to do' view
 * For teachers: shows 'pending submissions'
 *
 * @package    block_homework
 * @copyright  Anthony Kuske <www.anthonykuske.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('include/header.php');
echo $OUTPUT->header();
echo $hwblock->display->tabs('index');

if ($tophtml = get_config('block_homework', 'additional_html_top')) {
    echo $tophtml;
}

switch ($hwblock->get_mode()) {

    case 'pastoral-student':
    case 'student':
    case 'parent':
        /**
         * Show the timetable view of the student's homework due in the next 2 weeks
         */

        echo $hwblock->display->sign('calendar', 'To Do', 'This page presents a two-week overview of your homework.');

        // Get the user's group (class) IDs.
        $groupids = $hwblock->groups->get_all_users_group_ids($hwblock->get_user_id(), true);

        // Get the homework for those groups.
        $approved = true;
        $distinct = false;
        $homework = $hwblock->get_homework(
            $groupids,
            false,
            false,
            $approved,
            $distinct,
            false,
            false,
            'assigneddate',
            null,
            null,
            true
        );

        echo $hwblock->display->overview($homework, true);

        echo '<br/><br/>';

        // Show the list.
        echo $hwblock->display->homework_list($homework, 'assigneddate', 'To Do On ', 'l M jS Y', false, false);

        echo $hwblock->display->ical_feed_box();

        break;

    case 'teacher':
        /**
         * Pending homework approval page
         */

        echo $hwblock->display->sign(
            'check',
            'Manage Submissions',
            'This section shows homework that a students in your classes have submitted.
                Other students will NOT see these until approved by you.');

        // Get the user's group (class) IDs.
        $groupids = $hwblock->groups->get_all_users_group_ids($hwblock->get_user_id());

        // Get the homework for those groups.
        $approved = false;
        $distinct = true;
        $homework = $hwblock->get_homework(
            $groupids,
            false,
            false,
            $approved,
            $distinct);

        // Show the list.
        echo $hwblock->display->homework_list($homework);

        break;

    case 'pastoral':

        echo $hwblock->display->sign('calendar', 'Overview', 'This page shows all homework assigned this week.');

        /**
         * Whole school week overview
         */

        $stats = new \block_homework\local\homework_stats($hwblock);
        echo $hwblock->display->week_stats($stats);

        break;
}

if ($bottomhtml = get_config('block_homework', 'additional_html_bottom')) {
    echo $bottomhtml;
}

echo $OUTPUT->footer();
