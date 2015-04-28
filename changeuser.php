<?php

/**
 * @package    block_homework
 * @copyright  Anthony Kuske <www.anthonykuske.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require '../../config.php';
$userid = required_param('userid', PARAM_INT);

//TODO: Permissions check here?

if ($SESSION->homeworkBlockMode == 'pastoral') {
	$SESSION->homeworkBlockMode = 'pastoral-student';
}

$SESSION->homeworkBlockUser = $userid;
header('Location: index.php');
