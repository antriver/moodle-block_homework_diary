<?php

/**
 * Displays a single homework item
 *
 * @package    block_homework
 * @copyright  Anthony Kuske <www.anthonykuske.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('PUBLIC', true);
require 'include/header.php';

$id = required_param('id', PARAM_INT);

echo $OUTPUT->header();
echo $hwblock->display->tabs();

$hw = \block_homework\HomeworkItem::load($id);

echo $hwblock->display->homeworkList(array($hw));

echo $OUTPUT->footer();
