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
 * Handles interacting with Moodle courses.
 *
 * @package    block_homework
 * @copyright  Anthony Kuske <www.anthonykuske.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_homework;

/**
 * Handles interacting with Moodle courses.
 *
 * @package    block_homework
 * @copyright  Anthony Kuske <www.anthonykuske.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class CourseManager {

    /**
     * @var Block
     */
    private $hwblock;

    /**
     * Constructor.
     *
     * @param Block $hwblock
     */
    public function __construct(Block $hwblock) {
        $this->hwblock = $hwblock;
    }

    /**
     * Returns every course within the category the homework diary is configured to work with.
     *
     * @return object[]
     */
    public function get_all_courses() {

        global $DB;
        $values = array();
        $sql = 'SELECT
            crs.id,
            crs.fullname
        FROM {course} crs
        LEFT JOIN {context} ct ON ct.instanceid = crs.id AND ct.contextlevel = 50';
        // Context level 50 = a course.

        if ($categorycontext = $this->hwblock->get_category_context()) {
            $path = $categorycontext->path . '/%';
            $sql .= " WHERE ct.path LIKE ?";
            $values[] = $path;
        }

        $sql .= ' ORDER BY crs.fullname';

        return $DB->get_records_sql($sql, $values);
    }

    /**
     * Returns every course within the scope of the homework diary that the current user is enroled in.
     *
     * @param int $userid
     * @param int $roleid
     *
     * @return object[]
     */
    public function get_users_courses($userid, $roleid = null) {
        global $DB;

        $values = array(
            $userid
        );

        $sql = 'SELECT
            crs.id,
            crs.fullname
        FROM {role_assignments} ra
        JOIN {context} ct ON ct.id = ra.contextid
        JOIN {course} crs ON crs.id = ct.instanceid
        WHERE ra.userid = ?';

        if ($categorycontext = $this->hwblock->get_category_context()) {
            $path = $categorycontext->path . '/%';
            $sql .= " AND ct.path LIKE ?";
            $values[] = $path;
        }

        if (!is_null($roleid)) {
            $sql .= ' AND ra.roleid = ? ';
            $values[] = $roleid;
        }

        $sql .= 'ORDER BY crs.fullname';

        return $DB->get_records_sql($sql, $values);
    }

    /**
     * Returns an array of courseIDs from an array of coure objects.
     *
     * @param object[] $courses
     *
     * @return int[]
     */
    public function extract_course_ids($courses) {
        $ids = array();
        foreach ($courses as $course) {
            $ids[] = intval($course->id);
        }
        return $ids;
    }

    /**
     * Returns all course IDs that the user is enrolled in.
     *
     * @param int $userid
     * @param int $roleid
     *
     * @return array
     */
    public function get_users_course_ids($userid, $roleid = null) {
        $courses = $this->get_users_courses($userid, $roleid);
        return $this->extract_course_ids($courses);
    }
}
