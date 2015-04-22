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
 * Homework block admin settings
 *
 * @package    block_homework
 * @copyright  Anthony Kuske <anthonykuske@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Category to show courses from
 */

// Load all categories to show in the list
require_once $CFG->dirroot . '/course/externallib.php';
$categories = core_course_external::get_categories(array(), false);
$categoryList = array(
    0 => '[All Cateogries]'
);
foreach ($categories as $category) {
    $categoryList[$category['id']] = $category['name'];
}
asort($categoryList);

$settings->add(
    new admin_setting_configselect(
        'block_homework/course_category',
        get_string('settings_course_category_name', 'block_homework'),
        get_string('settings_course_category_desc', 'block_homework'),
        0,
        $categoryList
    )
);



/**
 * User levels
 */

// Get all system-level cohorts
require_once $CFG->dirroot . '/cohort/lib.php';
$systemCtx = context_system::instance();
$cohorts = cohort_get_cohorts($systemCtx->id, 0, 1000000);
$cohortList = array();
foreach ($cohorts['cohorts'] as $cohort) {
    $cohortList[$cohort->id] = $cohort->name;
}

// Student
$settings->add(
    new admin_setting_configselect(
        'block_homework/student_cohort',
        get_string('settings_student_cohort_name', 'block_homework'),
        get_string('settings_student_cohort_desc', 'block_homework'),
        0,
        $cohortList
    )
);

// Teacher
$settings->add(
    new admin_setting_configselect(
        'block_homework/teacher_cohort',
        get_string('settings_teacher_cohort_name', 'block_homework'),
        get_string('settings_teacher_cohort_desc', 'block_homework'),
        0,
        $cohortList
    )
);

// Parent
$settings->add(
    new admin_setting_configselect(
        'block_homework/parent_cohort',
        get_string('settings_parent_cohort_name', 'block_homework'),
        get_string('settings_parent_cohort_desc', 'block_homework'),
        0,
        $cohortList
    )
);

// Secretary
$settings->add(
    new admin_setting_configselect(
        'block_homework/secretary_cohort',
        get_string('settings_secretary_cohort_name', 'block_homework'),
        get_string('settings_secretary_cohort_desc', 'block_homework'),
        0,
        $cohortList
    )
);
