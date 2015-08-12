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

namespace block_homework;

class GroupManager
{
    private $hwblock;

    public function __construct(Block $hwblock)
    {
        $this->hwblock = $hwblock;
    }


    /**
     * Returns every group from courses that are in the chosen
     * category for the homework diary
     */
    public function getAllGroups()
    {
        global $DB;
        $values = array();
        $sql = 'SELECT
            g.*,
            crs.fullname AS coursefullname
        FROM {course} crs
        LEFT JOIN {context} ct ON ct.instanceid = crs.id AND ct.contextlevel = 50';
        // context level 50 = a course

        $sql .= '
        JOIN {groups} g ON g.courseid = crs.id
        ';

        if ($categoryCtx = $this->hwblock->getCategoryContext()) {
            $path = $categoryCtx->path . '/%';
            $sql .= " WHERE ct.path LIKE ?";
            $values[] = $path;
        }

        $sql .= ' ORDER BY crs.fullname, g.name';

        return $DB->get_records_sql($sql, $values);
    }

    /**
     * Get an array of class group IDs a user is in
     */
    public function getAllUsersGroupIDs($userId)
    {
        $groups = $this->getAllUsersGroups($userId);
        $groupIds = array();
        foreach ($groups as $group) {
            $groupIds[] = $group->id;
        }
        return $groupIds;
    }

    /**
     * Tweak of the Moodle groups_get_user_groups function,
     * but returns every group a user is in, not just from a certain course
     *
     * @category group
     * @param int $userId $USER if not specified
     * @return array Array[groupingid][groupid] including grouping id 0 which means all groups
     */
    function getAllUsersGroups($userId = 0)
    {
        global $USER, $DB;

        if (empty($userId)) {
            $userId = $USER->id;
        }

        $categoryContextPath = $this->hwblock->getCategoryContextPath();

        $params = array($userId);

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

        if ($categoryContextPath) {
            $sql .= ' LEFT JOIN {context} ctx ON ctx.contextlevel = 50 AND ctx.instanceid = c.id';
        }

        $sql .= ' WHERE gm.userid = ?';
        if ($categoryContextPath) {
            $sql .= ' AND ctx.path LIKE ?';
            $params[] = $categoryContextPath . '/%';
        }

        $sql .= ' ORDER BY coursefullname';

        $rs = $DB->get_recordset_sql($sql, $params);

        if (!$rs->valid()) {
            $rs->close(); // Not going to iterate (but exit), close rs
            return array();
        }

        $groups = array();
        foreach ($rs as $group) {
            $groups[$group->id] = $group;
        }
        return $groups;
    }


}
