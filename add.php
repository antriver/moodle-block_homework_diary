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
 * Form for adding/editing homework.
 *
 * @package    block_homework
 * @copyright  Anthony Kuske <www.anthonykuske.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('include/header.php');

// Are we viewing the form or adding stuff?
$action = optional_param('action', 'view', PARAM_RAW);

echo $OUTPUT->header();

echo $hwblock->display->tabs('add');
$mode = $hwblock->get_mode();

switch ($action) {

    /**
     * Display an existing item in the form to make changes
     */
    case 'edit':

        define('FORMACTION', 'edit');
        $editid = required_param('editid', PARAM_INT);
        // Load the existing item.
        $homeworkitem = \block_homework\local\homework_item::load($editid);
        if (!$homeworkitem) {
            die("Unable to find that homework item.");
        }

        if (!$hwblock->can_edit_homework_item($homeworkitem)) {
            die("You don't have permission to edit that piece of homework.");
        }

        break;

    /**
     * Save changes made from edit action
     */
    case 'saveedit':

        $assigneddates = required_param('assigneddates', PARAM_RAW);
        $description = optional_param('description', '', PARAM_RAW);
        $duedate = optional_param('duedate', null, PARAM_RAW);
        $duration = required_param('duration', PARAM_RAW);
        $editid = required_param('editid', PARAM_INT);
        $groupid = required_param('groupid', PARAM_INT);
        $private = optional_param('private', 0, PARAM_INT);
        $startdate = required_param('startdate', PARAM_RAW);
        $title = required_param('title', PARAM_RAW);

        // Load the existing item.
        $homeworkitem = \block_homework\local\homework_item::load($editid);
        if (!$homeworkitem) {
            die("Unable to find that homework item.");
        }

        // Find the course.
        $courseid = $DB->get_field('groups', 'courseid', array('id' => $groupid), MUST_EXIST);

        // Check permissions.
        $context = \context_course::instance($courseid);
        require_capability('block/homework:addhomework', $context);

        if (!$hwblock->can_edit_homework_item($homeworkitem)) {
            throw new unauthorized_access_exception("You don't have permission to edit that piece of homework.");
        }

        $homeworkitem->courseid = $courseid;
        $homeworkitem->description = $description;
        $homeworkitem->duedate = $duedate;
        $homeworkitem->duration = $duration;
        $homeworkitem->groupid = $groupid;
        $homeworkitem->startdate = $startdate;
        $homeworkitem->title = $title;

        // Auto approve when a teacher edits.
        if ($mode == 'teacher' && !$homeworkitem->private) {
            $homeworkitem->approved = 1;
        }

        if ($homeworkitem->save()) {

            //$homeworkitem = \block_homework\local\homework_item::load($editid);

            // Remove all existing assigned dates.
            $homeworkitem->clear_assigned_dates();

            // Now add the assigned dates.
            $assigneddates = explode(',', $assigneddates);
            foreach ($assigneddates as $date) {
                $homeworkitem->add_assigned_date($date);
            }

            echo '<div class="alert alert-success"><i class="fa fa-check"></i> Changes saved.</div>';
        } else {
            echo '<div class="alert alert-error"><i class="fa fa-times"></i> There was an error saving the changes.</div>';
        }

        break;

    case 'save':

        if ($mode != 'student' && $mode != 'teacher') {
            throw new unauthorized_access_exception("You need to be in student or teacher mode to add homework.");
        }

        $assigneddates = required_param('assigneddates', PARAM_RAW);
        $description = optional_param('description', '', PARAM_RAW);
        $duedate = optional_param('duedate', null, PARAM_RAW);
        $duration = required_param('duration', PARAM_RAW);
        $groupid = required_param('groupid', PARAM_INT);
        $private = optional_param('private', 0, PARAM_INT);
        $startdate = required_param('startdate', PARAM_RAW);
        $title = required_param('title', PARAM_RAW);

        // Find the course.
        $courseid = $DB->get_field('groups', 'courseid', array('id' => $groupid), MUST_EXIST);

        // Check permissions.
        $context = \context_course::instance($courseid);
        require_capability('block/homework:addhomework', $context);

        // Create the item.
        $homeworkitem = new stdClass();
        $homeworkitem->added = time();
        $homeworkitem->approved = $hwblock->can_approve_homework($courseid) ? 1 : 0;
        $homeworkitem->courseid = $courseid;
        $homeworkitem->description = $description;
        $homeworkitem->duedate = $duedate;
        $homeworkitem->duration = $duration;
        $homeworkitem->groupid = $groupid;
        $homeworkitem->private = $private;
        $homeworkitem->startdate = $startdate;
        $homeworkitem->title = $title;
        $homeworkitem->userid = $USER->id;

        // Save in the database.
        if ($id = $DB->insert_record('block_homework', $homeworkitem)) {

            // Fetch it from the database so we know the real saved data.
            $homeworkitem = \block_homework\local\homework_item::load($id);

            // Add the assigned dates.
            $assigneddates = explode(',', $assigneddates);
            foreach ($assigneddates as $date) {
                $homeworkitem->add_assigned_date($date);
            }

            if ($homeworkitem->private) {
                // Student submitted private homework.
                echo '<div class="alert alert-success">
                <i class="fa fa-check"></i> The homework has been saved and is visible on <a href="index.php">your overview</a>.
                </div>';

            } else if ($homeworkitem->approved && $homeworkitem->startdate <= $hwblock->today) {
                // Approved homework that is visible today or in the past.
                echo '<div class="alert alert-success">
                <i class="fa fa-check"></i> The homework has been submitted successfully
                and is now visible to students in the class.
                </div>';

            } else if ($homeworkitem->approved && $homeworkitem->startdate > $hwblock->today) {
                // Approved homework that becomes visible in the future.
                echo '<div class="alert alert-success">
                    <i class="fa fa-pause"></i>
                    The homework has been submitted successfully and will become visible to students on '
                    . date('l M jS', strtotime($homeworkitem->startdate))
                    . '</div>';

            } else if (!$homeworkitem->approved) {
                // Unapproved homework.
                echo '<div class="alert alert-success">
                <i class="fa fa-check"></i> The homework has been submitted successfully and will
                become visible to everybody in the class once a teacher approves it.</div>';

            }

            echo '<hr/>';
        } else {
            echo '<div class="alert alert-error"><i class="fa fa-times"></i> There was an error adding the homework.</div>';
        }

        break;

    case 'add':
    default:
        define('FORMACTION', 'add');
        break;
}

if (defined('FORMACTION')) {

    if (FORMACTION === 'edit') {

        echo $hwblock->display->sign(
            'edit-sign',
            'Edit Homework',
            'Click the Submit button at the bottom to save your changes.');
    } else if (FORMACTION === 'add' && $mode == 'student') {

        echo $hwblock->display->sign(
            'plus-sign',
            'Add Homework',
            'You may add homework for either just yourself, or everyone in your class.
            If the latter, your class teacher will have to approve it before it appears on DragonNet.');
    } else if (FORMACTION === 'add' && $mode == 'teacher') {

        echo $hwblock->display->sign(
            'play-sign',
            'Add Homework',
            'Here you may add homework for every student in a class to see.
            It does not need to be approved separately, it will become visible instantly.');
    }

    include('include/add_form.php');
}

echo $OUTPUT->footer();
