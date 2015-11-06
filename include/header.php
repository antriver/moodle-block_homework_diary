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
 * Initialises objects for the homework diary, includes styles and scripts.
 *
 * @package    block_homework
 * @copyright  Anthony Kuske <www.anthonykuske.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__DIR__))) . '/config.php');

if (!defined('PUBLIC_ACCESS')) {
    require_login();
}

$PAGE->set_context(context_system::instance());
if (!empty($_SERVER['SCRIPT_NAME'])) {
    $PAGE->set_url($_SERVER['SCRIPT_NAME']);
}

// Include the goodies for this block.
$hwblock = new \block_homework\local\block();

$PAGE->requires->css('/blocks/homework/assets/bootstrap/css/bootstrap.css');
$PAGE->requires->css('/blocks/homework/assets/css/homework.css?v=2015110600');

$PAGE->requires->jquery();
$PAGE->requires->jquery_plugin('ui');
$PAGE->requires->jquery_plugin('ui-css');

$PAGE->requires->js('/blocks/homework/assets/js/lib/jquery.scrollTo.min.js');
$PAGE->requires->js('/blocks/homework/assets/js/lib/jquery.localScroll.min.js');
$PAGE->requires->js('/blocks/homework/assets/js/lib/jquery.autosize.min.js');
$PAGE->requires->js('/blocks/homework/assets/js/lib/date.js');
$PAGE->requires->js('/blocks/homework/assets/js/homework.js?v=2015110600');
if (get_config('block_homework', 'smooth_scroll')) {
    $PAGE->requires->js('/blocks/homework/assets/js/lib/localscroll.js');
}

$PAGE->set_title(get_string('pagetitle', 'block_homework'));
$PAGE->set_heading(get_string('pagetitle', 'block_homework'));
