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
 * Handles interacting with Moodle groups.
 *
 * @package    block_homework
 * @copyright  Anthony Kuske <www.anthonykuske.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_homework\local;

use block_homework\local\block;

/**
 * Handles interacting with Moodle groups.
 *
 * @package    block_homework
 * @copyright  Anthony Kuske <www.anthonykuske.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class group_manager {

    /**
     * @var block
     */
    private $hwblock;

    /**
     * Constructor.
     *
     * @param block $hwblock
     */
    public function __construct(block $hwblock) {
        $this->hwblock = $hwblock;
    }

    /**
     * Returns every group from courses that are in the chosen category for the homework diary.
     *
     * @return object[]
     */
    public function get_all_groups() {
        global $DB;
        $values = array();
        // Context level 50 = a course.
        $sql = 'SELECT
            g.*,
            crs.fullname AS coursefullname
        FROM {course} crs
        LEFT JOIN {context} ct ON ct.instanceid = crs.id AND ct.contextlevel = 50
        JOIN {groups} g ON g.courseid = crs.id
        ';

        if ($categorycontext = $this->hwblock->get_category_context()) {
            $path = $categorycontext->path . '/%';
            $sql .= " WHERE ct.path LIKE ?";
            $values[] = $path;
        }

        $sql .= ' ORDER BY crs.fullname, g.name';

        return $DB->get_records_sql($sql, $values);
    }

    /**
     * Get an array of class/group IDs a user is in.
     *
     * @param int $userid
     *
     * @return int[]
     */
    public function get_all_users_group_ids($userid) {
        $groups = $this->get_all_users_groups($userid);
        $groupids = array();
        foreach ($groups as $group) {
            $groupids[] = $group->id;
        }
        return $groupids;
    }

    /**
     * Tweak of the Moodle groups_get_user_groups function;
     * returns every group a user is in, not just from a certain course
     *
     * @param int $userid $USER if not specified
     *
     * @return array Array[groupingid][groupid] including grouping id 0 which means all groups
     */
    public function get_all_users_groups($userid = 0) {
        global $USER, $DB;

        if (empty($userid)) {
            $userid = $USER->id;
        }

        $categorycontextpath = $this->hwblock->get_category_context_path();

        $params = array($userid);

        $sql = "SELECT
                g.*,
                gg.groupingid,
                c.id AS courseid,
                c.fullname AS coursefullname,
                c.shortname AS courseshortname
            FROM {groups} g
            JOIN {groups_members} gm   ON gm.groupid = g.id
            LEFT JOIN {groupings_groups} gg ON gg.groupid = g.id
            LEFT JOIN {course} c ON c.id = g.courseid";

        if ($categorycontextpath) {
            $sql .= ' LEFT JOIN {context} ctx ON ctx.contextlevel = 50 AND ctx.instanceid = c.id';
        }

        $sql .= ' WHERE gm.userid = ?';
        if ($categorycontextpath) {
            $sql .= ' AND ctx.path LIKE ?';
            $params[] = $categorycontextpath . '/%';
        }

        $sql .= ' ORDER BY coursefullname';

        $rs = $DB->get_recordset_sql($sql, $params);

        if (!$rs->valid()) {
            $rs->close();
            return array();
        }

        $groups = array();
        foreach ($rs as $group) {
            $groups[$group->id] = $group;
        }
        return $groups;
    }
}
