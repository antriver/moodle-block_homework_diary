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
 * The beef of the homework block.
 *
 * @package    block_homework_diary
 * @copyright  Anthony Kuske <www.anthonykuske.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_homework_diary\local;

use context_course;
use context_coursecat;

/**
 * The beef of the homework block.
 *
 * @package    block_homework_diary
 * @copyright  Anthony Kuske <www.anthonykuske.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block {

    /**
     * @var display_manager
     */
    public $display;

    /**
     * @var feed_manager
     */
    public $feeds;

    /**
     * @var feed_manager
     */
    public $groups;

    /**
     * @var homework_repository
     */
    public $repository;

    /**
     * Today's date
     *
     * @var string
     */
    public $today;

    /**
     * Current userID
     *
     * @var int
     */
    public $userid;

    /**
     * Constructor
     */
    public function __construct() {
        $this->today = date('Y-m-d');

        $this->display = new display_manager($this);
        $this->feeds = new feed_manager($this);
        $this->groups = new group_manager($this);
        $this->repository = new homework_repository($this);
    }

    /**
     * Write all request into to a log file for super debugging.
     *
     * @return bool
     */
    private function log() {
        // Skip the RSS feed because those requests happen too often.
        if (stripos($_SERVER['REQUEST_URI'], '/blocks/homework/feed') === 0) {
            return false;
        }

        global $USER;
        $filename = dirname(dirname(__DIR__)) . '/log.txt';
        $file = fopen ($filename, 'a+');
        $line = date('Y-m-d H:i:s')
                . "\t" . $_SERVER['REMOTE_ADDR']
                . "\t" . $_SERVER['HTTP_X_FORWARDED_FOR']
                . "\t" . $_SERVER['HTTP_USER_AGENT']
                . "\t" . ($USER ? $USER->id : '')
                . "\t" . $_SERVER['REQUEST_URI']
                . "\t" . var_export($_GET, true)
                . "\t" . var_export($_POST, true) . PHP_EOL;
        fwrite($file, $line);
        fclose($file);
        return true;
    }

    /**
     * Returns the userID of the current user,
     * or the user that we are looking at if in parent mode
     * or pastoral-student mode
     *
     * @return int
     */
    public function get_user_id() {
        if (!empty($this->userid)) {
            return $this->userid;
        }

        global $SESSION, $USER;

        if (!empty($SESSION->homeworkblockuser)) {
            return $SESSION->homeworkblockuser;
        }

        $mode = $this->get_mode();

        if ($mode == 'parent') {

            // In parent mode, but no child is selected so select the first child.
            $children = $this->get_users_children($USER->id);
            $child = reset($children);
            $SESSION->homeworkblockuser = $child->userid;
            return $child->userid;
        } else {
            return $USER->id;
        }
    }

    /**
     * Returns the mode the current user is in
     * (The default mode for the users role if the mode hasn't been switched)
     *
     * @return string
     */
    public function get_mode() {
        global $SESSION;
        if (isset($SESSION->homeworkblockmode)) {
            return $SESSION->homeworkblockmode;
        }

        $possiblemodes = $this->get_possible_modes();
        $SESSION->homeworkblockmode = $possiblemodes[0];
        return $possiblemodes[0];
    }

    /**
     * Which modes can the current user switch to?
     *
     * @return string[]
     */
    public function get_possible_modes() {
        global $CFG, $USER;

        require_once($CFG->dirroot . '/cohort/lib.php');

        $studentcohortid = (int)get_config('block_homework_diary', 'student_cohort');
        $teachercohortid = (int)get_config('block_homework_diary', 'teacher_cohort');
        $parentcohortid = (int)get_config('block_homework_diary', 'parent_cohort');
        $secretarycohortid = (int)get_config('block_homework_diary', 'secretary_cohort');

        if ($teachercohortid && cohort_is_member($teachercohortid, $USER->id)) {
            return array('teacher', 'pastoral');
        }

        if ($studentcohortid && cohort_is_member($studentcohortid, $USER->id)) {
            return array('student');
        }

        if ($parentcohortid && cohort_is_member($parentcohortid, $USER->id)) {
            return array('parent');
        }

        if ($secretarycohortid && cohort_is_member($secretarycohortid, $USER->id)) {
            return array('pastoral');
        }

        if (\is_siteadmin()) {
            return array('pastoral');
        }

        // Shouldn't get to here, but just in case...
        return array('pastoral');
    }

    /**
     * Set the current mode.
     *
     * @param string $mode
     * @param bool   $userid
     *
     * @return bool
     */
    public function set_mode($mode, $userid = false) {
        global $SESSION, $USER;

        $possiblemodes = $this->get_possible_modes();
        if (in_array($mode, $possiblemodes)) {
            $SESSION->homeworkblockmode = $mode;

            if ($userid) {
                $SESSION->homeworkblockuser = $userid;
            } else {
                $SESSION->homeworkblockuser = $USER->id;
            }

            return true;
        }
        return false;
    }

    /**
     * Return all users the current user is a parent of.
     *
     * @param int $userid
     *
     * @return object[]
     */
    public function get_users_children($userid) {
        global $DB;
        $usercontexts = $DB->get_records_sql(
            "SELECT c.instanceid, c.instanceid, u.id AS userid, u.firstname, u.lastname
         FROM {role_assignments} ra, {context} c, {user} u
         WHERE ra.userid = ?
              AND ra.contextid = c.id
              AND c.instanceid = u.id
              AND c.contextlevel = " . \CONTEXT_USER,
            array($userid));
        return $usercontexts;
    }

    /**
     * Return the category ID that the homework block is set to work from
     *
     * @return int
     */
    public function get_category_id() {
        return (int)get_config('block_homework_diary', 'course_category');
    }

    /**
     * Return the context that the homework block is set to work from.
     *
     * @return context_coursecat|null
     */
    public function get_category_context() {
        // Limit to a certain category?
        $categoryid = $this->get_category_id();
        if (!$categoryid) {
            return null;
        }
        return context_coursecat::instance($categoryid);
    }

    /**
     * Return the context path that the homework block is set to work from.
     *
     * @return string|null
     */
    public function get_category_context_path() {
        if ($categorycontext = $this->get_category_context()) {
            return $categorycontext->path;
        }
        return null;
    }

    /**
     * Check user has permission to edit the homework
     *
     * @param homework_item $homeworkitem
     *
     * @return bool
     */
    public function can_edit_homework_item(homework_item $homeworkitem) {
        if ($homeworkitem->private) {
            return $homeworkitem->userid == $this->get_user_id();
        } else {
            return $this->can_approve_homework($homeworkitem->courseid);
        }
    }

    /**
     * Is the logged in user allowed to add homework to a course?
     *
     * @param int $courseid
     *
     * @return bool
     */
    public function can_add_homework($courseid) {
        $mode = $this->get_mode();
        if ($mode == 'teacher' || $mode == 'student') {

            $context = context_course::instance($courseid);
            return has_capability('block/homework:addhomework', $context);
        }
        return false;
    }

    /**
     * Is the logged in user allowed to approve (make visible) homework in a course?
     *
     * @param int $courseid
     *
     * @return bool
     */
    public function can_approve_homework($courseid) {
        $mode = $this->get_mode();
        if ($mode == 'teacher') {
            $context = context_course::instance($courseid);
            return has_capability('block/homework:approvehomework', $context);
        }
        return false;
    }
}
