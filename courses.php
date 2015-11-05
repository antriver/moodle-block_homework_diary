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
 * Display a list of the courses the user is enrolled in
 * It is preferable to use the classes.php page which shows groups instead.
 *
 * @package    block_homework
 * @copyright  Anthony Kuske <www.anthonykuske.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require 'include/header.php';
echo $OUTPUT->header();

echo $hwblock->display->tabs('courses');

switch ($hwblock->getMode()) {

    case 'student':
    case 'parent':

        echo '<h2><i class="fa fa-th-list"></i> My Courses</h2>';
        $courses = $hwblock->getUsersCourses($hwblock->getUserId());
        echo $hwblock->display->courseList($courses);

        break;

    case 'teacher':

        echo '<h2><i class="fa fa-th-list"></i> Courses I Teach</h2>';
        $teacherRoleID = 3;
        $courses = $hwblock->getUsersCourses($hwblock->getUserId(), $teacherRoleID);
        echo $hwblock->display->courseList($courses);

        break;

    case 'pastoral':

        // Show all courses in the school
        echo '<h2><i class="fa fa-th-list"></i> All Courses</h2>';

        $courses = $hwblock->getAllCourses();

        echo $hwblock->display->courseList($courses);

        break;
}

echo $OUTPUT->footer();
