<?php

/**
 * Homework block admin settings
 *
 * @package    block_homework
 * @copyright  Anthony Kuske <www.anthonykuske.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Smooth scrolling
 */
$settings->add(
    new admin_setting_configcheckbox(
        'block_homework/smooth_scroll',
        get_string('settings_smooth_scroll_name', 'block_homework'),
        get_string('settings_smooth_scroll_desc', 'block_homework'),
        1
    )
);

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
$cohortList = array(
    0 => '[Not Set]'
);
foreach ($cohorts['cohorts'] as $cohort) {
    $cohortList[$cohort->id] = $cohort->name;
    if ($cohort->idnumber) {
        $cohortList[$cohort->id] .= ' ['.s($cohort->idnumber).']';
    }
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

/**
 * Additonal HTML
 */
$settings->add(new admin_setting_heading(
    'block_homework_additional_html_heading',
    get_string('settings_additional_html_heading_name', 'block_homework'),
    get_string('settings_additional_html_heading_desc', 'block_homework')
));

$settings->add(
    new admin_setting_confightmleditor(
        'block_homework/additional_html_top',
        get_string('settings_additional_html_top_name', 'block_homework'),
        get_string('settings_additional_html_top_desc', 'block_homework'),
        '',
        PARAM_RAW
    )
);

$settings->add(
    new admin_setting_confightmleditor(
        'block_homework/additional_html_bottom',
        get_string('settings_additional_html_bottom_name', 'block_homework'),
        get_string('settings_additional_html_bottom_desc', 'block_homework'),
        '',
        PARAM_RAW
    )
);
