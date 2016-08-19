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
 * Methods for retrieving homework items.
 *
 * @package    block_homework_diary
 * @copyright  Anthony Kuske <www.anthonykuske.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_homework_diary\local;

use DateTime;

/**
 * Methods for retrieving homework items.
 *
 * @package    block_homework_diary
 * @copyright  Anthony Kuske <www.anthonykuske.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class homework_repository {
    /**
     * Reference to the main homework block.
     *
     * @var block
     */
    private $hwblock;

    /**
     * Today's date
     *
     * @var string
     */
    public $today;

    /**
     * Constructor.
     *
     * @param block $hwblock
     */
    public function __construct(block $hwblock) {
        $this->hwblock = $hwblock;
        $this->today = date('Y-m-d');
    }

    /**
     * Returns all homework that is visble to students and isn't past its due date.
     *
     * @param int[] $groupids
     *
     * @return homework_item[]
     */
    public function get_current_homework(array $groupids) {
        if (empty($groupids)) {
            return array();
        }

        $params = array();

        $where = 'hw.approved = 1';

        $where .= ' AND hw.private = 0';

        $where .= ' AND hw.startdate <= ?';
        $params[] = $this->today;

        $where .= ' AND hw.duedate >= ?';
        $params[] = $this->today;

        $where .= ' AND hw.groupid IN (' . implode(',', $groupids) . ')';

        return $this->get_homework_by_sql($where, $params);
    }

    /**
     * Returns all homework that is visible to students but is past its due date.
     *
     * @param int[] $groupids
     *
     * @return homework_item[]
     */
    public function get_previous_homework(array $groupids) {
        if (empty($groupids)) {
            return array();
        }

        $params = array();

        $where = 'hw.approved = 1';

        $where .= ' AND hw.private = 0';

        $where .= ' AND hw.startdate <= ?';
        $params[] = $this->today;

        $where .= ' AND hw.duedate < ?';
        $params[] = $this->today;

        $where .= ' AND hw.groupid IN (' . implode(',', $groupids) . ')';

        $orderby = "hw.duedate DESC";

        return $this->get_homework_by_sql($where, $params, $orderby);
    }

    /**
     * Returns all homework that is approved but isn't visible to students yet.
     *
     * @param int[] $groupids
     *
     * @return homework_item[]
     */
    public function get_upcoming_homework(array $groupids) {
        if (empty($groupids)) {
            return array();
        }

        $params = array();
        $where = 'hw.approved = 1';

        $where .= ' AND hw.private = 0';

        $where .= ' AND hw.startdate > ?';
        $params[] = $this->today;

        $where .= ' AND hw.groupid IN (' . implode(',', $groupids) . ')';

        return $this->get_homework_by_sql($where, $params);
    }

    /**
     * Returns all homework submitted by students that is currently pending a teacher's approval.
     *
     * @param int[] $groupids
     *
     * @return homework_item[]
     */
    public function get_pending_homework(array $groupids) {
        if (empty($groupids)) {
            return array();
        }

        $params = array();
        $where = 'hw.approved = 0';
        $where .= ' AND hw.private = 0';
        $where .= ' AND hw.groupid IN (' . implode(',', $groupids) . ')';

        return $this->get_homework_by_sql($where, $params);
    }

    /**
     * Return homework a user has entered for their self.
     *
     * @param int        $userid
     * @param int[] $groupids Limit to just these group IDs. Use group ID 0 to get homework not specific to any group.
     *                             null to not do any groupid filtering.
     *
     * @return homework_item[]
     */
    public function get_private_homework($userid, array $groupids = null) {
        if (empty($userid) || (is_array($groupids) && empty($groupids))) {
            return array();
        }

        $params = array();
        $where = 'hw.private = 1 AND hw.userid = ?';
        $params[] = $userid;

        if (is_array($groupids)) {
            $where .= ' AND hw.groupid IN (' . implode(',', $groupids) . ')';
        }

        return $this->get_homework_by_sql($where, $params);
    }

    /**
     * Returns an array of homework_items matching the given SQL snippet.
     *
     * @param string      $where
     * @param array       $params
     * @param string|null $orderby
     *
     * @return array
     */
    private function get_homework_by_sql($where = '', array $params = array(), $orderby = null) {
        global $DB;

        $sql = "
        SELECT
            DISTINCT hw.*,
            crs.fullname AS coursename
        FROM {block_homework_diary} hw
        LEFT JOIN {course} crs ON crs.id = hw.courseid";

        if ($where) {
            $sql .= " WHERE {$where}";
        }

        if (is_null($orderby)) {
            $orderby = "hw.duedate ASC";
        }

        $sql .= " ORDER BY {$orderby}";

        $records = $DB->get_records_sql($sql, $params);

        $homework = array();
        foreach ($records as $record) {
            $homework[] = new homework_item($record);
        }
        return $homework;
    }

    /**
     * Returns the homework to display on a student's "To Do" / "Week Overview" page.
     * Includes private homework.
     *
     * Returns a multidimensional array with homework items organised by assigned day. The same
     * item will appear multiple times in different days if it is assigned for multiple days.
     * Returned items have an additional assigneddate property.
     *
     * @param int $userid
     * @param int[] $groupids
     *
     *
     * @return homework_item[][]
     */
    public function get_homework_for_student_overview($userid, array $groupids) {
        global $DB;

        // The CONCAT is so the first column is unique, to keep Moodle happy.
        $sql = "
        SELECT
            CONCAT(hw.id, '-', hwdays.id) AS key,
            hw.*,
            hwdays.date AS assigneddate,
            (CASE WHEN hwdays.date = '' OR hwdays.date IS NULL THEN duedate ELSE hwdays.date END) AS sortdate,
            crs.fullname AS coursename
        FROM {block_homework_diary} hw
        LEFT JOIN {course} crs ON crs.id = hw.courseid
        LEFT JOIN {block_homework_diary_dates} hwdays ON hwdays.homeworkid = hw.id
        WHERE (
                (hw.private = 1 AND hw.userid = ?)
                OR
                (approved = 1 AND hw.groupid IN (" . implode(',', $groupids) . "))
            )
            AND hw.startdate <= ?
            AND hw.duedate >= ?
        ORDER BY assigneddate";

        $params = array(
            $userid,
            $this->today,
            $this->today
        );

        $records = $DB->get_records_sql($sql, $params);

        // Organise by assigned date.
        $dates = array();
        foreach ($records as $record) {
            $date = $record->sortdate;
            if (!isset($dates[$date])) {
                $dates[$date] = array();
            }
            $dates[$date][] = new homework_item($record);
        }
        ksort($dates);
        return $dates;
    }

    /**
     * Returns the homework to display on an admin's "Whole school overview" page.
     * Everything that is approved, not private, and has assigned dates between the dates given.
     *
     * Returns a multidimensional array with homework items organised by assigned day. The same
     * item will appear multiple times in different days if it is assigned for multiple days.
     * Returned items have an additional assigneddate property.
     *
     * @param DateTime   $assignedrangestart Only get items assigned for this date and later
     * @param DateTime   $assignedrangeend   Only get items assigned for this date and earlier.
     *
     * @return homework_item[][]
     */
    public function get_homework_for_school_overview(
        DateTime $assignedrangestart,
        DateTime $assignedrangeend
    ) {
        global $DB;

        // The CONCAT is so the first column is unique, to keep Moodle happy.
        $sql = "
        SELECT
            CONCAT(hw.id, '-', hwdays.id) AS key,
            hw.*,
            hwdays.date AS assigneddate,
            crs.fullname AS coursename
        FROM {block_homework_diary} hw
        LEFT JOIN {course} crs ON crs.id = hw.courseid
        LEFT JOIN {block_homework_diary_dates} hwdays ON hwdays.homeworkid = hw.id
        WHERE
            hw.approved = 1
            AND hw.private = 0";

        $params = array();

        $sql .= ' AND hwdays.date >= ?';
        $params[] = $assignedrangestart->format('Y-m-d');

        $sql .= ' AND hwdays.date <= ?';
        $params[] = $assignedrangeend->format('Y-m-d');

        $sql .= " ORDER BY assigneddate";

        $records = $DB->get_records_sql($sql, $params);

        // Organise by assigned date.
        $dates = array();
        foreach ($records as $record) {
            $date = $record->assigneddate;
            if (!isset($dates[$date])) {
                $dates[$date] = array();
            }
            $dates[$date][] = new homework_item($record);
        }
        return $dates;
    }
}
