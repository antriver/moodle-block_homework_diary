<?php

/**
 * @package    block_homework
 * @copyright  Anthony Kuske <www.anthonykuske.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$capabilities = array(

	// Ability to add a homework block to a page
	'block/homework:addinstance' => array(
		'captype' => 'write',
		'contextlevel' => CONTEXT_BLOCK,
		'archetypes' => array(
			'editingteacher' => CAP_ALLOW,
			'manager' => CAP_ALLOW
		),
		'clonepermissionsfrom' => 'moodle/site:manageblocks'
	),

	// Ability to approve homework
	'block/homework:approvehomework' => array(
		'captype' => 'write',
		'contextlevel' => CONTEXT_COURSE,
		'archetypes' => array(
			'editingteacher' => CAP_ALLOW,
			'manager' => CAP_ALLOW,
			'teacher' => CAP_ALLOW,
		),
		'clonepermissionsfrom' => 'moodle/course:manageactivities'
	),

	// Ability to add homework
	'block/homework:addhomework' => array(
		'captype' => 'write',
		'contextlevel' => CONTEXT_COURSE,
		'archetypes' => array(
			'editingteacher' => CAP_ALLOW,
			'teacher' => CAP_ALLOW,
			'manager' => CAP_ALLOW,
			'student' => CAP_ALLOW,
		),
	),
);
