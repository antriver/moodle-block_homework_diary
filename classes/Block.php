<?php

namespace block_homework;

use context_course;
use context_coursecat;

class Block
{
    public $courses;
    public $display;
    public $feeds;
    public $groups;

	public $today;
	public $userId;

	public function __construct()
	{
		$this->today = date('Y-m-d');

        $this->courses = new CourseManager($this);
		$this->display = new DisplayManager($this);
        $this->feeds = new FeedManager($this);
        $this->groups = new GroupManager($this);
	}

	/**
	 * Returns the userID of the current user,
     * or the user that we are looking at if in parent mode
     * or pastoral-student mode
     * @return int
	 */
	public function getUserId()
	{
		if (!empty($this->userId)) {
			return $this->userId;
		}

		global $SESSION, $USER;

		if (!empty($SESSION->homeworkBlockUser)) {
			return $SESSION->homeworkBlockUser;
		}

		$mode = $this->getMode();

		if ($mode == 'parent') {

            // In parent mode, but no child is selected
            // So select the first child
            $children = $this->getUsersChildren($USER->id);

            // Select the first child
			$child = reset($children);
			$SESSION->homeworkBlockUser = $child->userid;
			return $child->userid;

		} else {
			return $USER->id;
		}
	}

	/**
	 * Returns the mode the current user is in
	 * (The default mode for the users role if the mode hasn't been switched)
	 */
	public function getMode()
	{
		global $SESSION, $CFG;
		if (isset($SESSION->homeworkBlockMode)) {
			return $SESSION->homeworkBlockMode;
		}

		$possibleModes = $this->getPossibleModes();
		$SESSION->homeworkBlockMode = $possibleModes[0];
		return $possibleModes[0];
	}

	/**
	 * Which modes can the current user switch to?
	 */
	public function getPossibleModes()
	{
		global $CFG, $USER;

        require_once $CFG->dirroot . '/cohort/lib.php';

        $studentCohortId = (int)get_config('block_homework', 'student_cohort');
        $teacherCohortId = (int)get_config('block_homework', 'teacher_cohort');
        $parentCohortId = (int)get_config('block_homework', 'parent_cohort');
        $secretaryCohortId = (int)get_config('block_homework', 'secretary_cohort');

		if ($teacherCohortId && cohort_is_member($teacherCohortId, $USER->id)) {
			return array('teacher', 'pastoral');
        }

		if ($studentCohortId && cohort_is_member($studentCohortId, $USER->id)) {
			return array('student');
        }

		if ($parentCohortId && cohort_is_member($parentCohortId, $USER->id)) {
			return array('parent');
        }

		if ($secretaryCohortId && cohort_is_member($secretaryCohortId, $USER->id)) {
			return array('pastoral');
        }

		if (\is_siteadmin()) {
			return array('pastoral');
		}

		// Shouldn't get to here, but just in case...
		return array('pastoral');
	}

	public function setMode($newMode)
	{
		global $SESSION;

		$possibleModes = $this->getPossibleModes();
		if (in_array($newMode, $possibleModes)) {
			$SESSION->homeworkBlockMode = $newMode;
			return true;
		}
		return false;
	}

    public function getUsersChildren($userId)
    {
        global $DB;
        $usercontexts = $DB->get_records_sql("SELECT c.instanceid, c.instanceid, u.id AS userid, u.firstname, u.lastname
         FROM {role_assignments} ra, {context} c, {user} u
         WHERE ra.userid = ?
              AND ra.contextid = c.id
              AND c.instanceid = u.id
              AND c.contextlevel = " . \CONTEXT_USER, array($userId));
        return $usercontexts;
    }

    /**
     * Return the category ID that the homework block is set to work from
     */
    public function getCategoryId()
    {
        return (int)get_config('block_homework', 'course_category');
    }

    public function getCategoryContext()
    {
        // Limit to a certain category?
        $categoryId = $this->getCategoryId();
        if (!$categoryId) {
            return null;
        }
        return context_coursecat::instance($categoryId);
    }

    public function getCategoryContextPath()
    {
        if ($categoryContext = $this->getCategoryContext()) {
            return $categoryContext->path;
        }
        return null;
    }


	/**
	 * Capability checks
	 */

	public function canEditHomeworkItem($homeworkItem)
	{
		if ($homeworkItem->private) {
			return $homeworkItem->userid == $this->getUserId();
		} else {
			return $this->canApproveHomework($homeworkItem->courseid);
		}
	}

	/**
	 * Is the logged in user allowed to add homework to a course?
	 */
	public function canAddHomework($courseid)
	{
		$mode = $this->getMode();
		if ($mode == 'teacher' || $mode == 'student') {

			$context = context_course::instance($courseid);
			return has_capability('block/homework:addhomework', $context);

			return true;
		}
		return false;
	}

	/**
	 * Is the logged in user allowed to approve (make visible) homework in a course?
	 */
	public function canApproveHomework($courseid)
	{
		$mode = $this->getMode();
		if ($mode == 'teacher') {
			$context = context_course::instance($courseid);
			return has_capability('block/homework:approvehomework', $context);
		}
		return false;
	}


	/**
	 * Getting homework
	 * @param array groupIDs (optional) Limit to homework assigned in the given groupIDs
	 * @param array courseIDs (optional) Limit to homework assigned in the given courseIDs
	 * @param array assignedFor (optional) Limit to homework assigned to do on the given date(s) (Y-m-d format)
	 * @param bool approved	(optional)
	 *                      true for only approved,
	 *                      false for only not approved,
	 *                      null for everything
	 * @param bool distinct (optional)
	 *                      if true, will only return one row for each homework item.
	 *                      if false, will return a row for every day a homework item is set.
	 *                      default is true
	 * @param bool past (optional)
	 *                  true to only get items due in the past,
	 *                  false to only get items due in the future,
	 *                  null to get everything.
	 *                  If distinct is true, "past" means that the item's due date is in the past.
	 *                  If distinct is false, "past" means that the assigned day is in the past.
	 * @param string (Y-m-d)	dueDate	(optional) get only items due on a specific day (Y-m-d format)
	 * @param string (Y-m-d)	assignedRangeStart	(optional) Get items assigned for this date and on
	 * @param string (Y-m-d)	assignedRangeEnd	(optional) Get items assigned for this date or before
	 */
	public function getHomework(
		$groupIDs = false,
		$courseIDs = false,
		$assignedFor = false,
		$approved = true,
		$distinct = true,
		$past = false,
		$dueDate = false,
		$order = null,
		$assignedRangeStart = null,
		$assignedRangeEnd = null,
		$includePrivate = true
	) {
		global $DB;
		$params = array();

		// The purpose of the "key" field is because Moodle makes the first column
		// the key for the array it returns. So it needs to be unique to get all the rows
		// from the join

		$sql = 'SELECT ' . ($distinct ? 'DISTINCT' : 'CONCAT(hw.id, \'-\', days.id) AS key,') . '
			hw.*,
			days.date AS assigneddate,
			crs.id AS courseid,
			crs.fullname AS coursename,
			usr.username AS username,
			usr.firstname AS userfirstname,
			usr.lastname AS userlastname
		FROM {block_homework} hw
		LEFT JOIN {course} crs ON crs.id = hw.courseid
		JOIN {block_homework_assign_dates} days ON days.homeworkid = hw.id
		LEFT JOIN {user} usr ON usr.id = hw.userid';

		$sql .= ' WHERE (';
		$where = true;

		// Begin selecting portion...
		$and = false;

		$privateSelector = "(private = 1 AND userID = " . intval($this->getUserId()) . ')';

		if ($includePrivate) {
			// Include private homework for the current logged in user
			$sql .= ' ' . $privateSelector;
			$and = true;
		} else {
			// Exclude all private homework
			$sql .= ' private = 0';
			$and = true;
		}

		if (is_array($groupIDs)) {

			if (count($groupIDs) < 1) {
				return array();
			}

			// Group IDs
			$sql .= ($and ? ' OR' : ' ');
			$and = true;

			if (count($groupIDs) == 1) {
				$sql .= ' hw.groupid = ?';
				$params[] = $groupIDs[0];
			} elseif (count($groupIDs)) {
				$sql .= ' hw.groupid IN (' . implode(',', $groupIDs) . ')';
			}
		}

		// Course IDs
		if (is_array($courseIDs)) {

			if (count($courseIDs) < 1) {
				return array();
			}

			$sql .= ($and ? ' OR' : ' ');
			$and = true;

			if (count($courseIDs) == 1) {
				$sql .= ' hw.courseid = ?';
				$params[] = $courseIDs[0];
			} elseif (count($courseIDs)) {
				$sql .= ' hw.courseid IN (' . implode(',', $courseIDs) . ')';
			}
		}

		$sql .= ')';
		// End selecting portion...

		// Begin filtering portion...

		// Show only stuff that has a start date (visible date) of today or earlier
		if ($this->getMode() != 'teacher' && $this->getMode() != 'pastoral') {
			$sql .= ($where ? ' AND' : ' WHERE');
			$where = true;

			$sql .= ' (hw.startdate <= ? OR ' . $privateSelector . ')';
			$params[] = $this->today;
		}

		// Assigned dates
		if (is_array($assignedFor)) {

			$sql .= ($where ? ' AND' : ' WHERE');
			$where = true;

			if (count($assignedFor) == 1) {
				$sql .= ' days.date = ?';
				$params[] = $assignedFor[0];
			} elseif (count($assignedFor)) {
				$sql .= ' days.date IN (\'' . implode('\', \'', $assignedFor) . '\')';
			}
		}

		// Approved?
		if (!is_null($approved)) {

			$sql .= ($where ? ' AND' : ' WHERE');
			$where = true;

			// The IS NULL part is so private homework remains included
			if ($approved) {
				$sql .= ' (approved = 1 OR ' . $privateSelector . ')';
			} else {
				$sql .= ' (approved = 0 OR ' . $privateSelector . ')';
			}
		}

		// In the past?
		if (!is_null($past)) {

			$sql .= ($where ? ' AND' : ' WHERE');
			$where = true;

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


		if (!is_null($assignedRangeStart)) {
			$sql .= ($where ? ' AND' : ' WHERE');
			$where = true;
			$sql .= ' days.date >= ?';
			$params[] = $assignedRangeStart;
		}

		if (!is_null($assignedRangeEnd)) {
			$sql .= ($where ? ' AND' : ' WHERE');
			$where = true;
			$sql .= ' days.date <= ?';
			$params[] = $assignedRangeEnd;
		}

		if (is_null($order)) {
			$order = 'days.date ASC, hw.approved ASC, hw.duedate ASC';
		}

		if ($order) {
			$sql .= ' ORDER BY ' . $order;
		}

		#print_object($sql);
		#print_object($params);

		$records = $DB->get_records_sql($sql, $params);
		$return = array();
		foreach ($records as $record) {
			$return[] = new HomeworkItem($record);
		}

		return $return;
	}





/**
 * select distinct(usr.*)
* from ssismdl_groups_members grpmbr
* join ssismdl_user usr on usr.id = grpmbr.userid
* join ssismdl_groups grp on grp.id = grpmbr.groupid
* join ssismdl_context ctx on ctx.instanceid = grp.courseid and ctx.contextlevel = 50
* join ssismdl_role_assignments ra on ra.contextid = ctx.id
* where groupid = 789
 */
	/*public function getTeacherForGroupID($groupID)
	{
		global $DB;



		$teachers = $DB->get_records_sql($sql, $params);
	}*/

	/**
	 * Email alerts
	 */
	/*public function emailTeacherOnNewHomework($homeworkItem, $from)
	{
		$subject = "Homework submitted for " . $homeworkItem->coursename;
		$teachers = $this->getTeacherForGroupID($homeworkItem->groupid);

		$messagehtml = 'Hello!';

		$messagetext = strip_tags($messagehtml);

		foreach ($teachers as $teacher) {

			email_to_user($user, $from, $subject, $messagetext, $messagehtml);

		}
	}*/
}
