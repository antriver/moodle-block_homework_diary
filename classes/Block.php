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
 * @package    block_homework
 * @copyright  Anthony Kuske <www.anthonykuske.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_homework;

use context_course;
use context_coursecat;

/**
 * The beef of the homework block.
 *
 * @package    block_homework
 * @copyright  Anthony Kuske <www.anthonykuske.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class Block {

    /**
     * @var DisplayManager
     */
    public $display;

    /**
     * @var FeedManager
     */
    public $feeds;

    /**
     * @var FeedManager
     */
    public $groups;

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

        $this->display = new DisplayManager($this);
        $this->feeds = new FeedManager($this);
        $this->groups = new GroupManager($this);
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

        $studentcohortid = (int)get_config('block_homework', 'student_cohort');
        $teachercohortid = (int)get_config('block_homework', 'teacher_cohort');
        $parentcohortid = (int)get_config('block_homework', 'parent_cohort');
        $secretarycohortid = (int)get_config('block_homework', 'secretary_cohort');

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
     *
     * @return bool
     */
    public function set_mode($mode) {
        global $SESSION;

        $possiblemodes = $this->get_possible_modes();
        if (in_array($mode, $possiblemodes)) {
            $SESSION->homeworkblockmode = $mode;
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
        return (int)get_config('block_homework', 'course_category');
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
     * @param HomeworkItem $homeworkitem
     *
     * @return bool
     */
    public function can_edit_homework_item(HomeworkItem $homeworkitem) {
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

    /**
     * Getting homework
     *
     * @param                array $groupids                (optional) Limit to homework assigned in the given groupids
     * @param                array $courseids               (optional) Limit to homework assigned in the given courseids
     * @param                array $assignedfor             (optional) Limit to homework assigned to do on the given date(s) (Y-m-d
     *                                                      format)
     * @param                bool  $approved                (optional)
     *                                                      true for only approved,
     *                                                      false for only not approved,
     *                                                      null for everything
     * @param                bool  $distinct                (optional)
     *                                                      if true, will only return one row for each homework item.
     *                                                      if false, will return a row for every day a homework item is set.
     *                                                      default is true
     * @param                bool  $past                    (optional)
     *                                                      true to only get items due in the past,
     *                                                      false to only get items due in the future,
     *                                                      null to get everything.
     *                                                      If distinct is true, "past" means that the item's due date is in the
     *                                                      past. If distinct is false, "past" means that the assigned day is in
     *                                                      the past.
     * @param string               $duedate                 (optional) get only items due on a specific day (Y-m-d format)
     * @param string               $order
     * @param string               $assignedrangestart      (optional) Get items assigned for this date and on (Y-m-d)
     * @param string               $assignedrangeend        (optional) Get items assigned for this date or before (Y-m-d)
     *
     * @param bool                 $includeprivate
     *
     * @return HomeworkItem[]
     */
    public function get_homework(
        $groupids = null,
        $courseids = null,
        $assignedfor = null,
        $approved = true,
        $distinct = true,
        $past = false,
        $duedate = null,
        $order = null,
        $assignedrangestart = null,
        $assignedrangeend = null,
        $includeprivate = true
    ) {
        global $DB;
        $params = array();

        /**
         * The purpose of the "key" field is because Moodle makes the first column
         * the key for the array it returns. So it needs to be unique to get all the rows
         * from the join
         */
        $sql = 'SELECT ' . ($distinct ? 'DISTINCT' : 'CONCAT(hw.id, \'-\', days.id) AS key,') . '
			hw.*,
			' . ($distinct ? '' : 'days.date AS assigneddate,') . '
			crs.id AS courseid,
			crs.fullname AS coursename,
			usr.username AS username,
			usr.firstname AS userfirstname,
			usr.lastname AS userlastname
		FROM {block_homework} hw
		LEFT JOIN {course} crs ON crs.id = hw.courseid
		' . ($distinct ? '' : 'JOIN {block_homework_assign_dates} days ON days.homeworkid = hw.id') . '
		LEFT JOIN {user} usr ON usr.id = hw.userid
        WHERE ';

        // Begin selecting portion...

        $privateclause = "private = 1 AND userID = " . intval($this->get_user_id()) . '';

        if ($includeprivate) {
            // Include private homework for the current logged in user.
            $sql .= '(' . $privateclause . ') OR (';
        } else {
            $sql .= '(';
        }

        $and = false;

        if (is_array($groupids)) {

            if (count($groupids) < 1) {
                return array();
            }

            // Group IDs.
            $sql .= ($and ? 'OR ' : '');
            $and = true;

            if (count($groupids) == 1) {
                $sql .= 'hw.groupid = ?';
                $params[] = $groupids[0];
            } else if (count($groupids)) {
                $sql .= 'hw.groupid IN (' . implode(',', $groupids) . ')';
            }
        }

        // Course IDs.
        if (is_array($courseids)) {

            if (count($courseids) < 1) {
                return array();
            }

            $sql .= ($and ? ' OR' : ' ');
            $and = true;

            if (count($courseids) == 1) {
                $sql .= ' hw.courseid = ?';
                $params[] = $courseids[0];
            } else if (count($courseids)) {
                $sql .= ' hw.courseid IN (' . implode(',', $courseids) . ')';
            }
        }

        // Show only stuff that has a start date (visible date) of today or earlier.
        if ($this->get_mode() != 'teacher' && $this->get_mode() != 'pastoral') {
            $sql .= ($and ? ' AND' : ' ');
            $and = true;

            $sql .= ' hw.startdate <= ?';
            $params[] = $this->today;
        }

        if ($duedate) {
            $sql .= ($and ? ' AND' : ' ');
            $and = true;
            $sql .= ' hw.duedate = ?';
            $params[] = $duedate;
        }

        // Assigned dates.
        if (is_array($assignedfor)) {

            $sql .= ($and ? ' AND' : ' ');
            $and = true;

            if (count($assignedfor) == 1) {
                $sql .= ' days.date = ?';
                $params[] = $assignedfor[0];
            } else if (count($assignedfor)) {
                $sql .= ' days.date IN (\'' . implode('\', \'', $assignedfor) . '\')';
            }
        }

        // Approved?
        if (!is_null($approved)) {

            $sql .= ($and ? ' AND' : ' ');
            $and = true;

            if ($approved) {
                $sql .= ' approved = 1';
            } else {
                $sql .= ' approved = 0';
            }
        }

        // In the past?
        if (!is_null($past)) {

            $sql .= ($and ? ' AND' : ' ');
            $and = true;

            if ($distinct) {
                if ($past) {
                    $sql .= ' hw.duedate < ?';
                } else {
                    $sql .= ' hw.duedate >= ?';
                }
            } else {
                if ($past) {
                    $sql .= ' days.date < ?';
                } else {
                    $sql .= ' days.date >= ?';
                }
            }
            $params[] = $this->today;
        }

        if (!is_null($assignedrangestart)) {
            $sql .= ($and ? ' AND' : ' ');
            $and = true;
            $sql .= ' days.date >= ?';
            $params[] = $assignedrangestart;
        }

        if (!is_null($assignedrangeend)) {
            $sql .= ($and ? ' AND' : ' ');
            $sql .= ' days.date <= ?';
            $params[] = $assignedrangeend;
        }

        $sql .= ')';

        if (is_null($order)) {
            $order = 'hw.approved ASC, hw.duedate ASC';
        }

        if ($order) {
            $sql .= '
            ORDER BY ' . $order;
        }

        $records = $DB->get_records_sql($sql, $params);
        $return = array();
        foreach ($records as $record) {
            $return[] = new HomeworkItem($record);
        }

        return $return;
    }
}
