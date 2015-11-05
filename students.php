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
 * @package    block_homework
 * @copyright  Anthony Kuske <www.anthonykuske.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('include/header.php');
echo $OUTPUT->header();

echo $hwblock->display->tabs('students');

switch ($hwblock->get_mode()) {

    case 'pastoral':

        echo '<h2><i class="fa fa-user"></i> Student Lookup</h2>';

        // FIXME: SSIS language.
        echo $hwblock->display->sign(
            'search',
            'Find A Student',
            'This section allows you to see what a student sees.
                Search for a student by name or PowerSchool ID below and click on one of the results.');

        echo $hwblock->display->student_list();

        break;
}

echo $OUTPUT->footer();
