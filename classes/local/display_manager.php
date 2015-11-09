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
    private $possibletabs = array(
        'student'          => array(
            'overview' => array('overview.php', '<i class="fa fa-calendar"></i> To Do'),
            'history'  => array('history.php', '<i class="fa fa-th-list"></i> Full List / History'),
            'classes'  => array('classes.php', '<i class="fa fa-group"></i> View by Class'),
            'add'      => array('add.php', '<i class="fa fa-plus-circle"></i> Add Homework'),
            'icalfeed' => array('icalfeed.php', '<i class="fa fa-rss"></i> iCal'),
        ),
        'pastoral-student' => array(
            'overview' => array('overview.php', '<i class="fa fa-calendar"></i> To Do'),
            'history'  => array('history.php', '<i class="fa fa-th-list"></i> Full List / History'),
            'classes'  => array('classes.php', '<i class="fa fa-group"></i> View by Class'),
            'icalfeed' => array('icalfeed.php', '<i class="fa fa-rss"></i> iCal'),
        ),
        'teacher'          => array(
            'pending' => array('pending.php', '<i class="fa fa-check"></i> Pending Submissions'),
            'history' => array('history.php', '<i class="fa fa-th-list"></i> Full List / History'),
            'classes' => array('classes.php', '<i class="fa fa-group"></i> View by Class'),
            'add'     => array('add.php', '<i class="fa fa-plus-circle"></i> Add Homework'),
        ),
        'parent'           => array(
            'overview' => array('overview.php', '<i class="fa fa-calendar"></i> To Do'),
            'history'  => array('history.php', '<i class="fa fa-th-list"></i> Full List / History'),
            'classes'  => array('classes.php', '<i class="fa fa-group"></i> View by Class'),
            'icalfeed' => array('icalfeed.php', '<i class="fa fa-rss"></i> iCal'),
        ),
        'pastoral'         => array(
            'overview' => array('overview.php', '<i class="fa fa-home"></i> Overview'),
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
     * @param homework_item[] $homework Organised by date from the get_homework_for_student_overview
     *                                  or get_homework_for_school_overview methods.
     * @param bool            $hashlinks Add links to #date?
     *
     * @return string
     */
    public function overview(array $homework, $hashlinks = false) {
        $today = $this->hwblock->today;

        // First build an array of dates for the next fortnight.
        $dates = array();
        $date = new DateTime('monday this week');
        for ($day = 0; $day < 14; $day++) {
            // Skip Sat and Sun in the calendar.
            if ($date->format('l') != 'Saturday' && $date->format('l') != 'Sunday') {
                $dates[$date->format('Y-m-d')] = $date->format('l M jS');
            }
            $date->modify('+1 day');
        }

        $r = '<ul class="weekOverview row">';

        $col = 0;
        foreach ($dates as $date => $displaydate) {
            ++$col;
            $past = $date < $today;
            $r .= '<li class="col-md-2 ' . ($past ? 'past' : '') . '">
            <a class="day" href="' . ($hashlinks ? '#' . $date : 'day.php?date=' . $date) . '">';
            $r .= '<h4>' . $displaydate . '</h4>';

            if (isset($homework[$date])) {
                foreach ($homework[$date] as $item) {
                    if ($item->courseid) {
                        $text = $item->coursename;
                    } else {
                        $text = $this->truncate($item->description, 30);
                    }

                    $r .= '<p>' . $text . '</p>';
                }
            }
            $r .= '</a>
            </li>';
            if ($col == 5) {
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
        $PAGE->requires->js('/blocks/homework/assets/js/lib/bindWithDelay.js');
        $PAGE->requires->js('/blocks/homework/assets/js/filter.js');

        $r = '<div class="courseList">';
        $r .= '<input type="text" class="filter" placeholder="Type here to filter by name or teacher..." />';
        $r .= '<div class="row courses">';

        foreach ($classes as $groupid => $group) {
            $r .= '<div class="col-sm-3"><a href="class.php?groupid=' . $group->id . '" class="btn">';
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
        $PAGE->requires->js('/blocks/homework/assets/js/lib/bindWithDelay.js');
        $PAGE->requires->js('/blocks/homework/assets/js/filter.js');

        $r = '<div class="courseList">';
        $r .= '<input type="text" class="filter" placeholder="Type here to filter by name..." />';

        $r .= '<div class="row courses">';

        foreach ($courses as $courseid => $course) {
            $r .= '<div class="col-sm-3"><a href="course.php?courseid=' . $courseid . '" class="btn">';
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
        $PAGE->requires->js('/blocks/homework/assets/js/lib/bindWithDelay.js');
        $PAGE->requires->js('/blocks/homework/assets/js/filter.js');

        $r = '<div class="courseList userList">';
        $r .= '<input
            type="text"
            class="filter"
            placeholder="Type part of a student\'s name or their whole PowerSchool ID to search..." />';
        $r .= '<div class="row users"></div>';
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
     * Output a list of homework.
     *
     * @param homework_item[] $homework
     *
     * @return string
     */
    public function homework_list(array $homework) {
        if (count($homework) < 1) {
            return '<div class="nothing">
                <i class="fa fa-smile-o"></i> Nothing to show here.
            </div>';
        }

        $r = '<div class="homeworkListContainer">';
        $r .= '<ul class="homeworkList">';
        foreach ($homework as $hw) {
            $r .= $this->homework_item($hw);
        }
        $r .= '</ul>';
        $r .= '</div>';

        return $r;
    }

    /**
     * Output a single homework item.
     *
     * @param homework_item $hw
     *
     * @internal param bool $showclassname
     * @internal param bool $showassigneddates
     *
     * @return string
     */
    public function homework_item(homework_item $hw) {

        $past = $hw->is_past_due();

        // Should we show the edit / delete buttons?
        // true if the item is a user's private item,
        // or it's not private and the user is a techer for the course.
        $canedit = $this->hwblock->can_edit_homework_item($hw);

        // CSS classes to add to the output.
        $classes = array(
            'clearfix',
            'homework',
            ($hw->approved ? 'approved' : 'unapproved'),
            ($canedit ? ' canedit' : ''),
            ($hw->is_past_due() ? ' past' : ''),
            ($hw->is_in_future() ? ' future' : ''),
            ($hw->private ? ' private' : '')
        );

        $r = '<li class="' . implode(' ', $classes) . '" data-id="' . $hw->id . '" data-duedate="' . $hw->duedate . '">';

        // Dates.
        $r .= '<div class="dates">';

        // Full list of assigned dates.
        if ($assignedstr = $this->homework_assigned_dates($hw)) {
            $r .= $assignedstr;
            $r .= ' &nbsp; <i class="fa fa-arrow-right"></i> &nbsp; ';
        }

        // Due date.
        $r .= '<span class="label label-' . ($past ? 'important' : 'info') . '">
                <i class="fa fa-bell"></i> <strong>' . ($past ? 'Was due' : 'Due') . ' on</strong> '
            . date('D M jS Y', strtotime($hw->duedate))
            . '</span>';

        $r .= '</div>';

        $r .= '<h3 class="course-name">';
        // Course name.
        $r .= '<a href="class.php?groupid=' . $hw->groupid . '">' . $hw->coursename . '</a>';

        // Group name.
        $r .= '<span class="group-name">' . $hw->get_group_name() . '</span>';
        $r .= '</h3>';

        // Description.
        $r .= '<div class="description">';

        if ($hw->title) {
            $r .= '<h4 class="title"><a href="hw.php?id=' . $hw->id . '">' . $hw->title . '</a></h4>';
        }

        $r .= '<p>' . $this->filter_text($hw->description) . '</p>';

        // Duration.
        $r .= '<p class="duration">
            <i class="fa fa-clock-o"></i>
            This should take ' . $this->homework_duration($hw->duration) . ' in total.
            </p>';

        $r .= '</div>';

        // Notes.
        if ($notes = $hw->get_notes($this->hwblock->get_user_id())) {
            $notes = $this->filter_text($notes);
        } else {
            $notes = '';
        }
        $r .= '<p class="notes" ' . ($notes ? '' : 'style="display:none;"') . '>' . $notes . '</p>';

        // Buttons.
        $buttons = $this->get_homework_item_buttons($hw);
        if (!empty($buttons)) {
            $r .= '<div class="buttons">' . implode(' ', $buttons) . '</div>';
        }

        // Labels.
        $labels = $this->get_homework_item_labels($hw);
        if (!empty($labels)) {
            $r .= '<div class="labels">' . implode(' ', $labels) . '</div>';
        }

        $r .= '</li>';
        return $r;
    }

    /**
     * Returns the buttons to display at the bottom of a piece of homework.
     *
     * @param homework_item $homeworkitem
     *
     * @return string[]
     */
    private function get_homework_item_buttons(homework_item $homeworkitem) {
        $canedit = $this->hwblock->can_edit_homework_item($homeworkitem);

        $buttons = array();

        if ($this->hwblock->get_mode() == 'teacher' || $this->hwblock->get_mode() == 'student') {

            // Add notes button.
            $buttons[] = '<a class="btn btn-inverse editNotes" href="#">
                <i class="fa fa-comment"></i> Add Notes
                </a>';

            // Canel adding/editing notes button (hidden initially - show by JS).
            $buttons[] = '<a class="btn btn-danger cancelNotes" href="#" style="display:none;">
                <i class="fa fa-times"></i> Cancel Editing Notes
                </a>';

            // Save notes button  (hidden initially - show by JS).
            $buttons[] = '<a class="btn btn-success saveNotes" href="#" style="display:none;">
                <i class="fa fa-save"></i> Save Notes
                </a>';
        }

        if ($canedit) {
            // Edit button.
            $buttons[] = '<a
                class="btn btn-inverse"
                href="add.php?action=edit&editid=' . $homeworkitem->id . '"
                title="Edit">
                <i class="fa fa-pencil"></i> Edit Homework
            </a>';

            // Delete button.
            $buttons[] = '<a class="deleteHomeworkButton btn btn-danger" href="#" title="Delete">
                <i class="fa fa-trash"></i> Delete Homework
            </a>';
        }

        return $buttons;
    }

    /**
     * Returns the bootstrap labels to display at the top of homework.
     *
     * @param homework_item $homeworkitem
     *
     * @return string[]
     */
    private function get_homework_item_labels(homework_item $homeworkitem) {
        global $USER;

        $labels = array();

        $canedit = $this->hwblock->can_edit_homework_item($homeworkitem);

        if ($homeworkitem->private) {
            $labels[] = '<span class="label label-inverse">
                <i class="fa fa-eye-slash"></i>
                Only ' . $this->get_name($homeworkitem->userid) . ' can see this
            </span>';
        } else if ($this->hwblock->get_mode() == 'teacher') {

            if ($homeworkitem->userid != $USER->id) {
                $labels[] = '<span class="label label-info">
                    <i class="fa fa-user"></i>
                    Submitted by ' . $this->get_name($homeworkitem->userid) . '
                </span>';
            }

            if ($homeworkitem->approved && $homeworkitem->is_in_future()) {

                // Approved and pending.
                $visibledate = date('M jS Y', strtotime($homeworkitem->startdate));
                $labels[] = '<span class="label label-warning">
                    <i class="fa fa-pause"></i>
                    Visible to students from ' . $visibledate . '
                </span>';
            } else if ($homeworkitem->approved) {

                // Approved and visible.
                $labels[] = '<span class="label label-success">
                    <i class="fa fa-check"></i>
                    Visible to students
                </span>';
            } else {

                // Not approved yet.
                $unapprovedlabel = '<span class="label label-important">
                    <i class="fa fa-exclamation-circle"></i>
                    Not visible to students until approved';
                if ($canedit) {
                    $unapprovedlabel .= ' <a class="approveHomeworkButton btn-mini btn btn-success" href="#">
                            <i class="fa fa-check"></i> Approve
                        </a>';
                }
                $unapprovedlabel .= '</span>';
                $labels[] = $unapprovedlabel;
            }
        } else if ($this->hwblock->get_mode() == 'pastoral') {

            $labels[] = '<span class="label label-info">
                <i class="fa fa-user"></i>
                Submitted by ' . $this->get_name($homeworkitem->userid) . '
            </span>';
        }

        return $labels;
    }

    /**
     * Output the full list of dates a piece of homework is assigned for.
     *
     * @param homework_item $homeworkitem
     *
     * @return string
     */
    private function homework_assigned_dates(homework_item $homeworkitem) {

        if ($homeworkitem->assigneddate) {
            // If showing on the overview page, just show one assigned date.
            $dates = array($homeworkitem->assigneddate);
        } else {
            $dates = $homeworkitem->get_assigned_dates();
        }

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

            $output .= '<sapn class="label label-info">' . date('D M jS', strtotime($date)) . '</sapn>';

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
        $dates = array();
        $date = clone $stats->get_start_date();
        $dateinterval = $date->diff($stats->get_end_date());
        for ($i = 0; $i < $dateinterval->d; $i++) {
            $dates[$date->format('Y-m-d')] = $date->format('l M jS');
            $date->modify('+1 day');
        }

        /** @var homework_item[][] $homework */
        $homework = $stats->get_homework();

        $r = '<ul class="weekOverview pastoralWeekOverview row">';

        foreach ($dates as $date => $displaydate) {

            $past = $date < $this->hwblock->today;
            $r .= '<li class="col-md-2 ' . ($past ? 'past' : '') . '">
                <span class="day row">

                <h4>' . $displaydate . '</h4>';

            if (isset($homework[$date])) {
                foreach ($homework[$date] as $item) {

                    if ($item->courseid) {
                        $text = $item->coursename;
                    } else {
                        $text = $this->truncate($item->description, 30);
                    }

                    $assigneddates = $item->get_assigned_dates();
                    if ($assigneddates) {
                        $averageduration = $item->duration / count($assigneddates);
                    } else {
                        $averageduration = $item->duration;
                    }

                    $r .= '<p class="col-md-4"><a href="class.php?groupid=' . $item->groupid . '">';
                    $r .= $text;
                    $r .= '<br/><strong>' . $this->minutes($item->duration, true) . ' total (' . $this->minutes(
                            $averageduration,
                            true) . ' today)</strong>';
                    $r .= '</a></p>';
                }
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
            $text .= ' <a class="btn btn-mini btn-primary" href="icalfeed.php">Click here to learn how</a>';
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
     * @param int  $userid
     * @param bool $useyou Returns "you" if theis is the current logged in user (not necessarily the user we're acting as).
     *
     * @return string|bool
     */
    private function get_name($userid, $useyou = true) {
        global $DB, $USER;
        if ($useyou && $userid === $USER->id) {
            return 'you';
        }
        $user = $DB->get_record('user', array('id' => $userid));
        return fullname($user);
    }
}
