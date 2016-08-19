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
 * Show a list of all students in the school so pastoral staff can switch to one
 *
 * @package    block_homework_diary
 * @copyright  Anthony Kuske <www.anthonykuske.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('include/header.php');
echo $OUTPUT->header();

echo $hwblock->display->tabs('students');

switch ($hwblock->get_mode()) {

    case 'pastoral':

        echo '<h2><i class="fa fa-user"></i> ' . get_string('student_search_title', 'block_homework_diary') . '</h2>';

        echo $hwblock->display->sign(
            'search',
            get_string('student_search_title', 'block_homework_diary'),
            get_string('student_search_desc', 'block_homework_diary')
        );

        echo $hwblock->display->student_list();

        break;
}

echo $OUTPUT->footer();
