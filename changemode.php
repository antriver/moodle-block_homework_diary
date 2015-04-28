<?php

/**
 * @package    block_homework
 * @copyright  Anthony Kuske <www.anthonykuske.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require 'include/header.php';

$mode = required_param('mode', PARAM_RAW);
if ($hwblock->setMode($mode)) {
	redirect('/blocks/homework');
} else {
	die('Invalid mode.');
}
