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
 * Class for calculating stats about a set of courseids
 *
 * @package    block_homework
 * @copyright  Anthony Kuske <www.anthonykuske.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_homework;

class HomeworkStats
{
	private $courseIDs;
	private $groupIDs;
	private $startDate;
	private $endDate;
	private $hwblock;

	function __construct($hwblock)
	{
		$this->hwblock = $hwblock;
		$this->startDate = new \DateTime('monday this week');
		$this->endDate = new \DateTime('sunday this week');
	}

	function setCourseIDs($courseIDs)
	{
		$this->courseIDs = $courseIDs;
	}

	function getCourseIDs()
	{
		return $this->courseIDs;
	}

	function setGroupIDs($groupIDs)
	{
		$this->groupIDs = $groupIDs;
	}

	function getGroupIDs()
	{
		return $this->groupIDs;
	}

	/**
	 * Set the date range to calculate stats for
	 * @param string $startDate Y-m-d format
	 * @param string $endDate   Y-m-d format
	 */
	function setDates(\DateTime $startDate, \DateTime $endDate)
	{
		$this->startDate = $startDate;
		$this->endDate = $endDate;
	}

	function getStartDate()
	{
		return $this->startDate;
	}

	function getEndDate()
	{
		return $this->endDate;
	}

	function getHomework()
	{
		$assignedRangeStart = $this->startDate->format('Y-m-d');
		$assignedRangeEnd = $this->endDate->format('Y-m-d');
		$distinct = false;
		$homework = $this->hwblock->getHomework(
			$this->groupIDs, //groupIDs
			$this->courseIDs, //courseIDs
			null, //assignedFor
			true, //approved
			false, //distinct
			null, //past
			null, //dueDate
			null, //order
			$assignedRangeStart,
			$assignedRangeEnd,
			false //exclude private
		);
		return $homework;
	}
}
