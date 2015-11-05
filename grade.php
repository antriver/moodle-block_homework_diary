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

$grade = required_param('grade', PARAM_INT);

echo $OUTPUT->header();

echo $hwblock->display->tabs('grades');

switch ($hwblock->getMode()) {

    case 'pastoral':

        echo $hwblock->display->sign('calendar', "Grade {$grade} Overview", "This page shows homework assigned this week for grade {$grade} classes.");

        // Load classes in this grade

        $groups = $hwblock->getAllGroups($grade);
        $groupIDs = $hwblock->extractGroupIDsFromTimetable($groups);

        $stats = new \block_homework\HomeworkStats($hwblock);
        $stats->setGroupIDs($groupIDs);

        echo $hwblock->display->weekStats($stats);

        // Show classes in this grade

        echo '<hr/>';

        echo '<h2><i class="fa fa-group"></i> Grade ' . $grade . ' Classes</h2>';
        $classes = $hwblock->getAllGroups($grade);
        echo $hwblock->display->classList($classes);

        break;
}

echo $OUTPUT->footer();
