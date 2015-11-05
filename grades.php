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
 * @package    block_homework
 * @copyright  Anthony Kuske <www.anthonykuske.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Can't work anymore due to removing course metadata
header('Location: /blocks/homework');
die();

/**
 * Display all the grades (years) in the school
 */

require 'include/header.php';
echo $OUTPUT->header();

echo $hwblock->display->tabs('grades');

switch ($hwblock->getMode()) {

    case 'pastoral':

        echo '<h2><i class="fa fa-group"></i> All Grades</h2>';

        echo '<ul class="buttons">';
        for ($grade = 1; $grade <= 12; $grade++) {

            echo '<li>';
            echo '<a class="btn" href="grade.php?grade=' . $grade . '">Grade ' . $grade . '</a>';
            echo '</li>';

        }
        echo '</ul>';

        break;
}

echo $OUTPUT->footer();
