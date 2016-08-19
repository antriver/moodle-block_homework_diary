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
 * Overview page.
 *
 * For students: shows 'to do' view.
 * For admin staff: shows all homework across the school.
 *
 * @package    block_homework_diary
 * @copyright  Anthony Kuske <www.anthonykuske.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('include/header.php');
echo $OUTPUT->header();
echo $hwblock->display->tabs('overview');

if ($tophtml = get_config('block_homework_diary', 'additional_html_top')) {
    echo $tophtml;
}

switch ($hwblock->get_mode()) {

    case 'pastoral-student':
    case 'student':
    case 'parent':

        /**
         * Students get a timetable of homework in their classes.
         */

        echo $hwblock->display->sign('calendar', 'To Do', 'This page presents a two-week overview of your homework.');

        // Get the user's group (class) IDs.
        $groupids = $hwblock->groups->get_all_users_group_ids($hwblock->get_user_id());

        $dateshomework = $hwblock->repository->get_homework_for_student_overview(
            $hwblock->get_user_id(),
            $groupids
        );

        // Display the calendar.
        echo $hwblock->display->overview($dateshomework, true);

        // Display the full list of homework, broken up by date.
        foreach ($dateshomework as $date => $homework) {
            if ($date >= date('Y-m-d')) {
                echo '<h3 id="' . $date . '">' . date('l M jS Y', strtotime($date)) . '</h3>';
                echo $hwblock->display->homework_list($homework);
            }
        }

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

        // Display homework submitted by students that's not yet approved.
        $pendinghomework = $hwblock->repository->get_pending_homework($groupids);
        echo '<h3><i class="fa fa-exclamation-circle"></i> Pending Homework Submissions</h3>';
        echo $hwblock->display->homework_list($pendinghomework);

        break;

    case 'pastoral':

        /**
         * Whole school week overview
         */

        echo $hwblock->display->sign('calendar', 'Overview', 'This page shows all homework assigned this week.');

        $stats = new \block_homework_diary\local\homework_stats($hwblock);
        echo $hwblock->display->week_stats($stats);

        break;
}

if ($bottomhtml = get_config('block_homework_diary', 'additional_html_bottom')) {
    echo $bottomhtml;
}

echo $OUTPUT->footer();
