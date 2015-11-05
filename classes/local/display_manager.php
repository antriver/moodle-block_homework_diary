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
 * Methods for generating HTML to display.
 *
 * @package    block_homework
 * @copyright  Anthony Kuske <www.anthonykuske.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_homework\local;

use block_homework\local\homework_item;
use block_homework\local\homework_stats;
use DateTime;

/**
 * Methods for generating HTML to display.
 *
 * @package    block_homework
 * @copyright  Anthony Kuske <www.anthonykuske.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class display_manager {

    /**
     * @var block
     */
    private $hwblock;

    /**
     * Tabs to be displayed dpending on the mode.
     *
     * @var array
     */
    private $possibletabs = array( // Array of which tabs are shown in different modes.
                                   'student'          => array(
                                       'index'    => array('index.php', '<i class="fa fa-calendar"></i> To Do'),
                                       'history'  => array('history.php', '<i class="fa fa-th-list"></i> Full List / History'),
                                       'classes'  => array('classes.php', '<i class="fa fa-group"></i> View by Class'),
                                       'add'      => array('add.php', '<i class="fa fa-plus-circle"></i> Add Homework'),
                                       'icalfeed' => array('icalfeed.php', '<i class="fa fa-rss"></i> iCal'),
                                   ),
                                   'pastoral-student' => array( // When a pastoral user clicks on a student (same as parent mode).
                                                                'index'    => array(
                                                                    'index.php',
                                                                    '<i class="fa fa-calendar"></i> To Do'
                                                                ),
                                                                'history'  => array(
                                                                    'history.php',
                                                                    '<i class="fa fa-th-list"></i> Full List / History'
                                                                ),
                                                                'classes'  => array(
                                                                    'classes.php',
                                                                    '<i class="fa fa-group"></i> View by Class'
                                                                ),
                                                                'icalfeed' => array(
                                                                    'icalfeed.php',
                                                                    '<i class="fa fa-rss"></i> iCal'
                                                                ),
                                   ),
                                   'teacher'          => array(
                                       'index'   => array('index.php', '<i class="fa fa-check"></i> Manage Submissions'),
                                       'history' => array('history.php', '<i class="fa fa-th-list"></i> Full List / History'),
                                       'classes' => array('classes.php', '<i class="fa fa-group"></i> View by Class'),
                                       'add'     => array('add.php', '<i class="fa fa-plus-circle"></i> Add Homework'),
                                   ),
                                   'parent'           => array(
                                       'index'    => array('index.php', '<i class="fa fa-calendar"></i> To Do'),
                                       'history'  => array('history.php', '<i class="fa fa-th-list"></i> Full List / History'),
                                       'classes'  => array('classes.php', '<i class="fa fa-group"></i> View by Class'),
                                       'icalfeed' => array('icalfeed.php', '<i class="fa fa-rss"></i> iCal'),
                                   ),
                                   'pastoral'         => array(
                                       'index'    => array('index.php', '<i class="fa fa-home"></i> Overview'),
                                       'classes'  => array('classes.php', '<i class="fa fa-group"></i> Classes'),
                                       'students' => array('students.php', '<i class="fa fa-user"></i> Student Lookup'),
                                   ),
    );

    /**
     * Constructor.
     *
     * @param block $hwblock
     */
    public function __construct(block $hwblock) {
        $this->hwblock = $hwblock;
    }

    /**
     * Output the tabs to swith mode.
     *
     * @return string
     */
    private function mode_tabs() {
        $currentmode = $this->hwblock->get_mode();
        $possiblemodes = $this->hwblock->get_possible_modes();

        $modelabels = array(
            'student'  => '<i class="fa fa-user"></i> Student Mode',
            'parent'   => '<i class="fa fa-male"></i> Parent Mode',
            'teacher'  => '<i class="fa fa-magic"></i> Teacher Mode',
            'pastoral' => '<i class="fa fa-heartbeat"></i> Pastoral Mode',
        );

        if ($currentmode == 'pastoral-student') {
            global $DB, $SESSION;
            $possiblemodes[] = 'pastoral-student';
            $student = $DB->get_record('user', array('id' => $SESSION->homeworkblockuser));
            $modelabels['pastoral-student'] = 'Student Mode: ' . $student->firstname . ' ' . $student->lastname;
        }

        if (count($possiblemodes) < 2) {
            return false;
        }

        $t = '<div class="tabs text-center">';
        $t .= '<div class="btn-group">';
        foreach ($possiblemodes as $mode) {
            $t .= '<a class="btn btn-sm btn-small'
                . ($mode == $currentmode ? ' active' : '')
                . '" href="changemode.php?mode='
                . $mode
                . '">'
                . $modelabels[$mode]
                . '</a>';
        }
        $t .= '</div>';
        $t .= '</div>';

        return $t;
    }

    /**
     * Output a tab for each of a user's children and allows them to switch between them
     *
     * @return string
     */
    private function parent_tabs() {
        global $USER;

        $currentuser = $this->hwblock->get_user_id();

        $children = $this->hwblock->get_users_children($USER->id);

        if (!isset($children) || !is_array($children)) {
            return false;
        }

        $t = '<div class="tabs text-center">';
        $t .= '<div class="btn-group">';
        foreach ($children as $child) {
            $t .= '<a class="btn btn-sm btn-small '
                . ($child->userid == $currentuser ? ' active' : '')
                . '" href="changeuser.php?userid=' . $child->userid . '">'
                . $child->firstname
                . ' '
                . $child->lastname
                . '</a>';
        }
        $t .= '</div>';
        return $t;
    }

    /**
     * Output the tabs at the top of all homework pages.
     * Including the subtabs and mode / children tabs.
     *
     * @param bool  $current
     * @param array $subtabs
     * @param bool  $currentsubtab
     * @param bool  $groupid
     *
     * @return string
     */
    public function tabs($current = false, $subtabs = array(), $currentsubtab = false, $groupid = false) {
        global $USER;
        if (!$USER->id) {
            return '';
        }

        $tabs = $this->possibletabs[$this->hwblock->get_mode()];

        $t = '';

        // If in parent mode, show the list of children at the top.
        if ($this->hwblock->get_mode() == 'parent') {
            $t .= $this->parent_tabs();
        }

        // Show tabs for switching modes (if possible).
        $t .= $this->mode_tabs();

        $t .= '<div class="tabs text-center">';

        $t .= '<div class="btn-group">';
        foreach ($tabs as $name => $tab) {
            if ($groupid && $name == 'add') {
                $tab[0] .= '?groupid=' . $groupid;
            }
            $t .= '<a class="btn' . ($name == $current ? ' active' : '') . '" href="' . $tab[0] . '">' . $tab[1] . '</a>';
        }
        $t .= '</div>';

        if ($subtabs) {
            $t .= '<div class="btn-group">';
            foreach ($subtabs as $name => $tab) {
                $t .= '<a class="btn btn-sm btn-small'
                    . ($name == $currentsubtab ? ' active' : '')
                    . '" href="' . $tab[0]
                    . '">'
                    . $tab[1]
                    . '</a>';
            }
            $t .= '</div>';
        }
        $t .= '</div>';

        return $t;
    }

    /**
     * Index page for students.
     *
     * @param homework_item[] $homework
     * @param bool           $hashlinks
     *
     * @return string
     */
    public function overview(array $homework, $hashlinks = false) {
        $today = $this->hwblock->today;

        // Build an array of dates for the next fortnight.
        $dates = array();

        $date = new DateTime('monday this week');

        for ($i = 0; $i < 14; $i++) {

            if ($date->format('l') != 'Saturday' && $date->format('l') != 'Sunday') {
                $dates[$date->format('Y-m-d')] = array();
            }

            $date->modify('+1 day');
        }

        // Sort the homework into the days it's assigned for.
        foreach ($homework as $hw) {
            if (isset($dates[$hw->assigneddate])) {
                $dates[$hw->assigneddate][] = $hw;
            }
        }

        $r = '<ul class="weekOverview row">';

        $i = 0;
        foreach ($dates as $date => $hw) {
            ++$i;
            $past = $date < $today;
            $r .= '<li class="col-md-2 ' . ($past ? 'past' : '') . '">
			<a class="day" href="' . ($hashlinks ? '#' . $date : 'day.php?date=' . $date) . '">';
            $r .= '<h4>' . date('l M jS', strtotime($date)) . '</h4>';
            foreach ($hw as $item) {
                if ($item->courseid) {
                    $icon = '';
                    $text = $item->coursename;
                } else {
                    $icon = 'thumb-tack';
                    $text = $this->truncate($item->description, 30);
                }

                $r .= '<p>' . ($icon ? '<i class="fa fa-' . $icon . '"></i> ' : '') . $text . '</p>';
            }
            $r .= '</a>
			</li>';
            if ($i == 5) {
                $r .= '</ul><div class="clear"></div><ul class="weekOverview row">';
            }
        }

        $r .= '</ul>';
        $r .= '<div class="clear"></div>';
        return $r;
    }

    /**
     * Output an array of classes (groups) as buttons, with a filter box
     *
     * @param object[] $classes
     *
     * @return string
     */
    public function class_list($classes) {
        global $PAGE;
        $PAGE->requires->js('/blocks/homework/assets/js/bindWithDelay.js');
        $PAGE->requires->js('/blocks/homework/assets/js/filter.js');

        $r = '<div class="courseList">';
        $r .= '<input type="text" class="filter" placeholder="Type here to filter by name or teacher..." />';
        $r .= '<div class="row courses">';

        foreach ($classes as $groupid => $group) {
            $r .= '<div class="col-sm-3"><a href="/blocks/homework/class.php?groupid=' . $group->id . '" class="btn">';
            $r .= $group->coursefullname;
            $r .= '<span>' . $group->name . '</span>';
            $r .= '</a></div>';
        }

        $r .= '</div>';
        $r .= '<div class="clear"></div>';
        $r .= '</div>';
        return $r;
    }

    /**
     * Show an array of courses as buttons, with a filter box.
     *
     * @param object[] $courses
     *
     * @return string
     */
    public function course_list($courses) {
        global $PAGE;
        $PAGE->requires->js('/blocks/homework/assets/js/bindWithDelay.js');
        $PAGE->requires->js('/blocks/homework/assets/js/filter.js');

        $r = '<div class="courseList">';
        $r .= '<input type="text" class="filter" placeholder="Type here to filter by name..." />';

        $r .= '<div class="row courses">';

        foreach ($courses as $courseid => $course) {
            $r .= '<div class="col-sm-3"><a href="/blocks/homework/course.php?courseid=' . $courseid . '" class="btn">';
            $r .= $course->fullname;
            $r .= '</a></div>';
        }

        $r .= '</div>';
        $r .= '<div class="clear"></div>';
        $r .= '</div>';

        return $r;
    }

    /**
     * Output a placeholder for a list of students, with a search box.
     *
     * @return string
     */
    public function student_list() {
        global $PAGE;
        $PAGE->requires->js('/blocks/homework/assets/js/bindWithDelay.js');
        $PAGE->requires->js('/blocks/homework/assets/js/filter.js');

        $r = '<div class="courseList userList">';
        $r .= '<input
            type="text"
            class="filter"
            placeholder="Type part of a student\'s name or their whole PowerSchool ID to search..." />';
        $r .= '<div class="row courses"></div>';
        $r .= '<div class="clear"></div>';
        $r .= '</div>';
        return $r;
    }

    /**
     * Output a bootstrap alert
     *
     * @param string $icon
     * @param string $bigtext
     * @param string $littletext
     *
     * @return string
     */
    public function sign($icon, $bigtext, $littletext) {
        return '<div class="alert alert-info">
	    		<i class="fa-3x fa fa-' . $icon . ' pull-left"></i>
	    		<h4>' . $bigtext . '</h4>
	    		<p>' . $littletext . '</p>
	    	</div>';
    }

    /**
     * Output a list of homework to do ooptionally organised with headings for a certain field.
     *
     * @param homework_item[] $homework
     * @param string|null    $headingsforfield                  Watch this field in each homework item, if the contents of this
     *                                                          field is not the same as the last one, a new header will be shown.
     *                                                          This should be a field containing a date that can be parsed by
     *                                                          strtotime()
     * @param string|null    $headingprefix                     Show this text before each heading
     * @param string         $headingdateformat                 The format to show the date from the headingsForField field
     * @param bool           $showclassname                     true or false to show the class (group) name each item is assigned
     *                                                          in
     *
     * @param bool           $showassigneddates
     *
     * @return string
     */
    public function homework_list(
        array $homework,
        $headingsforfield = null,
        $headingprefix = null,
        $headingdateformat = 'l M jS Y',
        $showclassname = false,
        $showassigneddates = true
    ) {
        if (count($homework) < 1) {
            return '<div class="nothing">
				<i class="fa fa-smile-o"></i> Nothing to show here.
			</div>';
        }

        $r = '<div class="homeworkListContainer">';

        $lastheadingfieldvalue = null;

        if ($headingsforfield) {
            $inlist = false;
        } else {
            // If the headingsForField headings are NOT used, start the list here.
            $r .= '<ul class="homeworkList">';
            $inlist = true;
        }

        foreach ($homework as $hw) {

            // If the $headingsforfield headers are used, check if it's time for a new heading and start a new list.
            if ($headingsforfield && $hw->{$headingsforfield} != $lastheadingfieldvalue) {

                if ($inlist) {
                    $r .= '</ul>';
                }

                $r .= '<h3 id="' . $hw->{$headingsforfield} . '">';
                $r .= $headingprefix;
                $r .= date($headingdateformat, strtotime($hw->{$headingsforfield}));
                $r .= '</h3>';

                $r .= '<ul class="homeworkList">';
                $inlist = true;
                $lastheadingfieldvalue = $hw->{$headingsforfield};
            }

            $r .= $this->homework_item($hw, $showclassname, $showassigneddates);
        }

        $r .= '</ul>';
        $r .= '</div>';

        return $r;
    }

    /**
     * Output a single homework item.
     *
     * @param homework_item $hw
     * @param bool         $showclassname
     * @param bool         $showassigneddates
     *
     * @return string
     */
    private function homework_item(homework_item $hw, $showclassname = false, $showassigneddates = false) {
        // Is this item only visible to students in the future?
        $future = $hw->startdate > $this->hwblock->today;

        // Should we show the edit / delete buttons?
        // true if the item is a user's private item,
        // or it's not private and the user is a techer for the course.
        $canedit = $this->hwblock->can_edit_homework_item($hw);

        // Is this due in the past?
        $past = $hw->duedate < $this->hwblock->today;

        $classes = array(
            'homework',
            ($hw->approved ? 'approved' : 'unapproved'),
            ($canedit ? ' canedit' : ''),
            ($past ? ' past' : ''),
            ($future ? ' future' : ''),
            ($hw->private ? ' private' : '')
        );

        $r = '<li class="' . implode(' ', $classes) . '" data-id="' . $hw->id . '" data-duedate="' . $hw->duedate . '">';

        if (!$hw->private && !$hw->approved) {
            $r .= '<h5>
                <i class="fa fa-pause"></i> This must be approved by a teacher before it is visible to the whole class.
            </h5>';
        }

        if (!$hw->private && $hw->approved && $this->hwblock->get_mode() == 'teacher') {
            $r .= '<h5><i class="fa fa-check"></i> Approved and visible to the whole class.</h5>';
        }

        if ($past) {
            $r .= '<h5><i class="fa fa-clock-o"></i> The due date for this has passed.</h5>';
        }

        if ($hw->private) {
            $r .= '<h5><i class="fa fa-eye-slash"></i> Only ' . $this->get_username($hw->userid) . ' can see this.</h5>';
        }

        // Button for teachers to approve pending homework.
        if (!$hw->approved && !$hw->private) {
            // Only teachers should be seeing this.
            $r .= '<span class="buttons approvalButtons">';
            $r .= '<span>
                <i class="fa fa-user"></i> Submitted by ' . $hw->userfirstname . ' ' . $hw->userlastname . '
                 &nbsp;&nbsp; <i class="fa fa-exclamation-triange"></i> Not visible to students until approved</span> &nbsp;';
            if ($canedit) {
                $r .= '<a class="approveHomeworkButton btn-mini btn btn-success" href="#"><i class="fa fa-check"></i> Approve</a>';
            }
            $r .= '</span>';
        }

        if ($future) {
            $r .= '<span class="buttons approvalButtons">';
            $r .= '<span><i class="fa fa-pause"></i> Will not appear to students until ' . date(
                    'l M jS Y',
                    strtotime($hw->startdate)) . '</span>';
            $r .= '</span>';
        }

        // Edit buttons.
        if ($canedit) {
            $r .= '<span class="buttons editButtons">';
            $r .= '<a
                class="btn-mini btn btn-info"
                href="add.php?action=edit&editid=' . $hw->id . '"
                title="Edit">
                <i class="fa fa-pencil"></i> Edit
                </a>';
            $r .= '<a class="deleteHomeworkButton btn-mini btn btn-danger" href="#" title="Delete">
                <i class="fa fa-trash"></i> Delete
            </a>';
            $r .= '</span>';
        }

        $icon = '';
        $r .= '<h5 class="dates">';

        // List of assigned dates.
        if ($showassigneddates && $assignedstr = $this->homework_assigned_dates($hw->get_assigned_dates())) {
            $r .= $assignedstr;
            $r .= ' &nbsp; <i class="fa fa-arrow-right"></i> &nbsp; ';
        }

        // Due date.
        $r .= '<i class="fa fa-bell"></i> <strong>Due on</strong> ' . date('D M jS Y', strtotime($hw->duedate));

        $r .= '</h5>';

        // Course name with link.
        $r .= '<h4><a href="/blocks/homework/class.php?groupid=' . $hw->groupid . '">'
            . ($icon ? '<i class="fa fa-' . $icon . '"></i> ' : '')
            . $hw->coursename
            . '</a></h4>';

        // Class (group) name.
        if ($this->hwblock->get_mode() || $showclassname) {
            $r .= '<h4>' . $hw->get_group_name() . '</h4>';
        }

        // Description.
        $r .= '<p>';

        if ($hw->title) {
            $r .= '<strong>' . $hw->title . '</strong><br/>';
        }

        $r .= $this->filter_text($hw->description);

        // Duration.
        $r .= '<span class="duration"><i class="fa fa-clock-o"></i> This should take ' . $this->homework_duration(
                $hw->duration) . ' in total.</span>';

        $r .= '</p>';

        // Notes.
        if ($notes = $hw->get_notes($this->hwblock->get_user_id())) {
            $notes = $this->filter_text($notes);
        } else {
            $notes = '';
        }

        $r .= '<p class="notes" ' . ($notes ? '' : 'style="display:none;"') . '>' . $notes . '</p>';

        if ($this->hwblock->get_mode() == 'teacher' || $this->hwblock->get_mode() == 'student') {
            // Edit notes button.
            $r .= '<span class="buttons noteButtons">';
            $r .= '<a class="btn-mini btn btn-primary editNotes" href="#">
                <i class="fa fa-comment"></i> Add Notes
                </a>';
            $r .= '<a class="btn-mini btn btn-danger cancelNotes" href="#" style="display:none;">
                <i class="fa fa-times"></i> Cancel
                </a>';
            $r .= '<a class="btn-mini btn btn-success saveNotes" href="#" style="display:none;">
                <i class="fa fa-save"></i> Save Notes
                </a>';
            $r .= '</span>';
        }

        $r .= '<div class="clear"></div>';

        $r .= '</li>';
        return $r;
    }

    /**
     * Output the dates a piece of homework is assigned for.
     *
     * @param string[] $dates
     *
     * @return string
     */
    private function homework_assigned_dates($dates) {
        // Remove empty dates from array.
        foreach ($dates as $i => $date) {
            if (!$date) {
                unset($dates[$i]);
            }
        }

        if (empty($dates)) {
            return '';
        }

        $output = '<strong>To Do On</strong> &nbsp;';
        $count = count($dates);

        $joiner = ' &nbsp;<strong class="ampersand-icon">&amp;</strong>&nbsp; ';

        // Show the first two dates
        // or three if there are only three
        // but only two if there are more than three.
        $i = 0;
        foreach ($dates as $i => $date) {

            if ($i > 0) {
                $output .= $joiner;
            }

            $output .= date('D M jS', strtotime($date));

            // If there are more than 3 dates and this is the 2nd one, stop.
            if ($count > 3 && $i >= 1) {
                break;
            }
        }

        // Show a 'more' link if there are more than 3 dates.
        if ($count > 3) {
            $overflow = $count - $i - 1;
            $output .= $joiner . '<a href="#" class="showAllHomeworkDates">' . $overflow . ' more days...</a>';
            // This is lame but how else to get the json to the page?
            $output .= '<span class="assignedDatesJSON">' . json_encode($dates) . '</span>';
        }

        return $output;
    }

    /**
     * Output the length of time a homework item should take.
     *
     * @param string $duration Duration data from DB.
     * @param bool   $short
     *
     * @return string
     */
    private function homework_duration($duration, $short = false) {
        $r = '';

        if (stripos('-', $duration)) {
            list($min, $max) = explode('-', $duration);
        } else {
            $min = $duration;
            $max = null;
        }

        if ($min == 0 && $max) {

            $r .= 'up to ' . $this->minutes($max, $short);
        } else if ($max) {

            $r .= $short ? '' : 'between ';

            $r .= $this->minutes($min, $short);

            $r .= $short ? ' to ' : ' and ';

            $r .= $this->minutes($max, $short);
        } else {

            $r .= 'up to ' . $this->minutes($min, $short);
        }

        return $r;
    }

    /**
     * Format a number of minutes.
     *
     * @param int  $mins
     * @param bool $short
     *
     * @return string
     */
    private function minutes($mins, $short = false) {
        $hours = floor($mins / 60);
        $mins = $mins % 60;

        $str = '';

        if ($hours) {
            $str .= $hours;
            $str .= $short ? 'hr' : ' hour';
            $str .= $this->s($hours);
        }

        if ($mins) {
            if ($hours) {
                $str .= ' ';
            }
            $str .= $mins;
            $str .= $short ? 'min' : ' minute';
            $str .= $this->s($mins);
        }

        return $str;
    }

    /**
     * Weekly stats view for pastoral staff
     *
     * @param homework_stats $stats
     *
     * @return string
     */
    public function week_stats(homework_stats $stats) {

        // Build an array of dates for the next fortnight.
        /** @var homework_item[][] $dates */
        $dates = array();

        $date = clone $stats->get_start_date();

        $dateinterval = $date->diff($stats->get_end_date());

        for ($i = 0; $i < $dateinterval->d; $i++) {

            if ($date->format('l') != 'Saturday' && $date->format('l') != 'Sunday') {
                $dates[$date->format('Y-m-d')] = array();
            }

            $date->modify('+1 day');
        }

        $homework = $stats->get_homework();

        // Sort the homework into the days it's assigned for.
        foreach ($homework as $hw) {
            if (isset($dates[$hw->assigneddate])) {
                $dates[$hw->assigneddate][] = $hw;
            }
        }

        $r = '<ul class="weekOverview pastoralWeekOverview row">';

        foreach ($dates as $date => $hw) {
            ++$i;
            $past = $date < $this->hwblock->today;
            $r .= '<li class="col-md-2 ' . ($past ? 'past' : '') . '">
				<span class="day row">

				<h4>' . date('l M jS', strtotime($date)) . '</h4>';

            foreach ($hw as $item) {
                if ($item->courseid) {
                    $icon = '';
                    $text = $item->coursename;
                } else {
                    $icon = 'thumb-tack';
                    $text = $this->truncate($item->description, 30);
                }

                $assigneddates = $item->get_assigned_dates();
                if ($assigneddates) {
                    $averageduration = $item->duration / count($assigneddates);
                } else {
                    $averageduration = $item->duration;
                }

                $r .= '<p class="col-md-4"><a href="/blocks/homework/class.php?groupid=' . $item->groupid . '">';
                $r .= ($icon ? '<i class="fa fa-' . $icon . '"></i> ' : '') . $text;
                $r .= '<br/><strong>' . $this->minutes($item->duration, true) . ' total (' . $this->minutes(
                        $averageduration,
                        true) . ' today)</strong>';
                $r .= '</a></p>';
            }

            $r .= '</span>
			</li>';
        }

        $r .= '</ul>';
        $r .= '<div class="clear"></div>';
        return $r;
    }

    /**
     * Output a box with the current user's iCal feed URL.
     *
     * @param bool $button Add a help button
     *
     * @return string
     */
    public function ical_feed_box($button = true) {
        $r = '<div class="feedLink">';
        $text = 'You can see due dates for your homework in iCal by adding this URL.';
        if ($button) {
            $text .= ' <a class="btn btn-mini btn-primary" href="/blocks/homework/icalfeed.php">Click here to learn how</a>';
        }
        $text .= '<input type="text" readonly="readonly" value="' . $this->hwblock->feeds->generate_feed_url() . '"/>';
        $r .= $this->sign('calendar', 'iCal Feed', $text);
        $r .= '</dv>';
        return $r;
    }

    /**
     * Returns an s to pluralize a word depending on if the given number is greater than 1
     *
     * @param int $num
     *
     * @return string
     */
    private function s($num) {
        return $num == 1 ? '' : 's';
    }

    /**
     * Escape a string.
     *
     * @param string $text
     *
     * @return string
     */
    public function filter_text($text) {
        $text = htmlentities($text, ENT_COMPAT, 'UTF-8', false);
        $text = nl2br($text);
        $text = $this->parse_urls($text);
        return $text;
    }

    /**
     * Truncate a string
     *
     * @param string $text
     * @param int    $limit
     * @param string $append
     *
     * @return string
     */
    public function truncate($text, $limit, $append = '...') {
        if ($limit < 1) {
            return '';
        }
        if (strlen($text) > $limit) {
            return substr($text, 0, $limit - 3) . $append;
        } else {
            return $text;
        }
    }

    /**
     * Shows beginning and end of URL. Cuts out the middle.
     *
     * @param string $text
     * @param int    $limit
     *
     * @return string
     */
    public function truncate_url($text, $limit) {
        if ($limit < 1) {
            return '';
        }

        $offset1 = ceil(0.65 * $limit) - 2;
        $offset2 = ceil(0.30 * $limit) - 1;

        if (strlen($text) > $limit) {
            return substr($text, 0, $offset1) . '...' . substr($text, -$offset2);
        } else {
            return $text;
        }
    }

    /**
     * Turn URLs into anchor tags, and images into images
     *
     * @param string $text
     * @param int    $maxurllen
     * @param string $target
     * @param bool   $embedimages
     *
     * @return mixed
     */
    public function parse_urls($text, $maxurllen = 50, $target = "_blank", $embedimages = true) {
        if (preg_match_all('/((ht|f)tps?:\/\/([\w\.]+\.)?[\w-]+(\.[a-zA-Z]{2,4})?[^\s\r\n"\'<>]+)/si', $text, $urls)) {

            foreach (array_unique($urls[1]) as $url) {

                $urltext = $this->truncate_url($url, $maxurllen);

                // Images.
                if ($embedimages && $this->is_url_image($url)) {
                    $text = str_replace(
                        $url,
                        '<a href="' . $url . '" target="' . $target . '" class="embeddedPhoto"><img src="' . $url . '"  /></a>',
                        $text);
                    continue;
                }

                $text = str_replace(
                    $url,
                    '<a href="' . $url . '" target="' . $target . '" title="' . $url . '" rel="nofollow">' . $urltext . '</a>',
                    $text);
            }
        }

        return $text;
    }

    /**
     * Check if a URL is for an image file (based on the extension)
     *
     * @param string $url
     *
     * @return bool
     */
    private function is_url_image($url) {
        $ext = strtolower(substr($url, strrpos($url, '.')));
        return $ext == '.jpg' || $ext == '.png' || $ext == '.gif' || $ext == '.bmp';
    }

    /**
     * Returns the username for a user ID.
     *
     * @param int $userid
     *
     * @return string|bool
     */
    private function get_username($userid) {
        global $DB;
        return $DB->get_field('user', 'username', array('id' => $userid));
    }
}
