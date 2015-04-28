<?php

/**
 * @package    block_homework
 * @copyright  Anthony Kuske <www.anthonykuske.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once dirname(dirname(dirname(__DIR__))) . '/config.php';

if (!defined('PUBLIC')) {
	require_login();
}

$PAGE->set_context(context_system::instance());
if (!empty($_SERVER['SCRIPT_NAME'])) {
    $PAGE->set_url($_SERVER['SCRIPT_NAME']);
}

// Include the goodies for this block
$hwblock = new block_homework\Block();

$PAGE->requires->css('/blocks/homework/assets/bootstrap/css/bootstrap.css');
$PAGE->requires->css('/blocks/homework/assets/css/homework.css?v=5');

$PAGE->requires->jquery();
$PAGE->requires->jquery_plugin('ui');

$PAGE->requires->js('/blocks/homework/assets/js/jquery.scrollTo.min.js');
$PAGE->requires->js('/blocks/homework/assets/js/jquery.localScroll.min.js');
$PAGE->requires->js('/blocks/homework/assets/js/jquery.autosize.min.js');
$PAGE->requires->js('/blocks/homework/assets/js/date.js');
$PAGE->requires->js('/blocks/homework/assets/js/homework.js?v=6');

$PAGE->set_title(get_string('pagetitle', 'block_homework'));
$PAGE->set_heading(get_string('pagetitle', 'block_homework'));
