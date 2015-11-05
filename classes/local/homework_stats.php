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
 * Class for calculating reports about a set of courseids
 *
 * @package    block_homework
 * @copyright  Anthony Kuske <www.anthonykuske.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_homework\local;

use DateTime;

/**
 * Class for calculating reports about a set of courseids
 *
 * @package    block_homework
 * @copyright  Anthony Kuske <www.anthonykuske.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class homework_stats {

    /**
     * @var array
     */
    private $courseids = array();

    /**
     * @var array
     */
    private $groupids = array();

    /**
     * @var DateTime
     */
    private $startdate;

    /**
     * @var DateTime
     */
    private $enddate;

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
        $this->startdate = new DateTime('monday this week');
        $this->enddate = new DateTime('sunday this week');
    }

    /**
     * Set the course IDs to limit the report to.
     *
     * @param array $courseids
     */
    public function set_course_ids(array $courseids) {
        $this->courseids = $courseids;
    }

    /**
     * Get the course IDs the report is limited to.
     *
     * @return array
     */
    public function get_course_ids() {
        return $this->courseids;
    }

    /**
     * Set the group IDs to limit the report to.
     *
     * @param array $groupids
     */
    public function set_group_ids(array $groupids) {
        $this->groupids = $groupids;
    }

    /**
     * Get the group IDs the report is limited to.
     *
     * @return array
     */
    public function get_group_ids() {
        return $this->groupids;
    }

    /**
     * Set the date range to calculate stats for
     *
     * @param DateTime $startdate
     * @param DateTime $enddate
     */
    public function set_dates(DateTime $startdate, DateTime $enddate) {
        $this->startdate = $startdate;
        $this->enddate = $enddate;
    }

    /**
     * Get the start date of the report.
     *
     * @return DateTime
     */
    public function get_start_date() {
        return $this->startdate;
    }

    /**
     * Get the end date of the report.
     *
     * @return DateTime
     */
    public function get_end_date() {
        return $this->enddate;
    }

    /**
     * Fetch the homework items that apply to the given criteria (date ranges etc.)
     *
     * @return homework_item[]
     */
    public function get_homework() {
        $assignedrangestart = $this->startdate->format('Y-m-d');
        $assignedrangeend = $this->enddate->format('Y-m-d');
        $distinct = false;
        $homework = $this->hwblock->get_homework(
            $this->groupids,
            $this->courseids,
            null,
            true,
            $distinct,
            null,
            null,
            null,
            $assignedrangestart,
            $assignedrangeend,
            false
        );
        return $homework;
    }
}
