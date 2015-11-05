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
 * A single homework assignment.
 *
 * @package    block_homework
 * @copyright  Anthony Kuske <www.anthonykuske.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_homework\local;

/**
 * A single homework assignment.
 *
 * @package    block_homework
 * @copyright  Anthony Kuske <www.anthonykuske.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class homework_item {

    /**
     * Row of data from the database
     * @var object
     */
    private $row;

    /**
     * Row of data from the assigned days table
     * @var object[]
     */
    private $assigneddates = null;

    /**
     * Name of the table containing homework.
     * @var string
     */
    private static $table = 'block_homework';

    /**
     * Name of the table containing which days a task is assigned for.
     * @var string
     */
    private static $assigneddaystable = 'block_homework_assign_dates';

    /**
     * Name of the table containing student notes for homework.
     * @var string
     */
    private static $notestable = 'block_homework_notes';

    /**
     * Constructor
     *
     * @param object $row
     */
    public function __construct($row = null) {
        $this->row = $row ? $row : new \stdClass();
    }

    /**
     * Return a property form the database row.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function __get($key) {
        if (isset($this->row->{$key})) {
            return $this->row->{$key};
        }
        return null;
    }

    /**
     * Set a property in the database row (doesn't write it to the DB)
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return mixed
     */
    public function __set($key, $value) {
        if (isset($this->row->{$key})) {
            $this->row->{$key} = $value;
        }
    }

    /**
     * Returns the underlying database row object.
     *
     * @return object
     */
    public function get_row() {
        return $this->row;
    }

    /**
     * Returns the title of this piece of homework.
     *
     * @return string
     */
    public function get_title() {
        return !empty($this->row->title) ? $this->row->title : $this->row->coursename;
    }

    /**
     * Returns the name of the group this homework belongs to.
     *
     * @return string
     */
    public function get_group_name() {
        global $DB;
        $group = $DB->get_record('groups', array('id' => $this->row->groupid));
        return $group->name;
    }

    /**
     * Get notes made about this item by the given user ID
     *
     * @param int $userid
     *
     * @return string|bool
     */
    public function get_notes($userid) {
        global $DB;
        $notes = $DB->get_field(self::$notestable, 'notes', array('userid' => $userid, 'homeworkid' => $this->row->id));
        return $notes;
    }

    /**
     * Save a user's notes about this item
     *
     * @param int    $userid
     * @param string $notes
     *
     * @return bool|int
     */
    public function set_notes($userid, $notes) {
        global $DB;

        if ($record = $DB->get_record(self::$notestable, array('userid' => $userid, 'homeworkid' => $this->row->id))) {
            $record->notes = $notes;
            return $DB->update_record(self::$notestable, $record);
        } else {
            $record = new \stdClass();
            $record->homeworkid = $this->row->id;
            $record->userid = $userid;
            $record->notes = $notes;
            return $DB->insert_record(self::$notestable, $record);
        }
    }

    /**
     * Save any modifications to this object back to the database
     *
     * @return bool
     */
    public function save() {
        global $DB;
        return $DB->update_record('block_homework', $this->row);
    }

    /**
     * Add a day this item is assigned
     *
     * @param string $date Y-m-d format
     *
     * @return bool
     */
    public function add_assigned_date($date) {
        global $DB;

        $daterow = new \stdClass();
        $daterow->homeworkid = $this->row->id;
        $daterow->date = $date;

        if ($DB->insert_record(self::$assigneddaystable, $daterow)) {
            $this->assigneddates[$date] = $date;
            return true;
        }
        return false;
    }

    /**
     * Remove a day this item is assigned
     *
     * @param string $date Y-m-d format
     *
     * @return bool
     */
    public function remove_assigned_date($date) {
        global $DB;

        if ($DB->delete_records(
            self::$assigneddaystable,
            array(
                'homeworkid' => $this->row->id,
                'date'       => $date
            ))
        ) {
            unset($this->assigneddates[$date]);
            return true;
        }
        return false;
    }

    /**
     * Remove all the assigned days for this item
     *
     * @return bool
     */
    public function clear_assigned_dates() {
        global $DB;

        if ($DB->delete_records(
            self::$assigneddaystable,
            array(
                'homeworkid' => $this->row->id
            ))
        ) {
            $this->assigneddates = array();
            return true;
        }
        return false;
    }

    /**
     * Return all the assigned days for this item
     *
     * @param bool $reload Fetch data form the database again?
     *
     * @return string[]
     */
    public function get_assigned_dates($reload = false) {
        if (!$reload && !is_null($this->assigneddates)) {
            return array_values($this->assigneddates);
        }

        global $DB;
        $assigneddates = array();
        $daterows = $DB->get_records(
            self::$assigneddaystable,
            array(
                'homeworkid' => $this->row->id
            ));
        foreach ($daterows as $row) {
            $assigneddates[$row->date] = $row->date;
        }

        $this->assigneddates = $assigneddates;
        return array_values($assigneddates);
    }

    /**
     * Loads a homework_item instance with info from the database for the given ID
     *
     * @param int  $homeworkid
     * @param bool $simple
     *
     * @throws \Exception
     * @return homework_item
     */
    public static function load($homeworkid, $simple = false) {
        global $DB;
        if ($simple) {
            $row = $DB->get_record(
                self::$table,
                array(
                    'id' => $homeworkid
                ),
                '*',
                MUST_EXIST);
        } else {

            $sql = 'SELECT hw.*,
				crs.id AS courseid,
				crs.fullname AS coursename,
				usr.username AS username,
				usr.firstname AS userfirstname,
				usr.lastname AS userlastname
			FROM {block_homework} hw
			LEFT JOIN {course} crs ON crs.id = hw.courseid
			LEFT JOIN {user} usr ON usr.id = hw.userid
			WHERE hw.id = ?';
            $params = array(
                'id' => $homeworkid
            );

            $rows = $DB->get_records_sql($sql, $params);
            if (count($rows) < 1) {
                throw new \Exception("Couldn't find that piece of homework");
            }
            $row = reset($rows);
        }
        return new homework_item($row);
    }
}
