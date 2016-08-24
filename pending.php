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
 * List of pending homework for all classes.
 *
 *
 * @package    block_homework_diary
 * @copyright  Anthony Kuske <www.anthonykuske.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('include/header.php');
echo $OUTPUT->header();
echo $hwblock->display->tabs('pending');

if ($tophtml = get_config('block_homework_diary', 'additional_html_top')) {
    echo $tophtml;
}

switch ($hwblock->get_mode()) {

    // Pending homework approval page.
    case 'teacher':
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
}

if ($bottomhtml = get_config('block_homework_diary', 'additional_html_bottom')) {
    echo $bottomhtml;
}

echo $OUTPUT->footer();
