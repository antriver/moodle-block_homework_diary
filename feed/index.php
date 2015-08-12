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
 * @package    block_homework
 * @copyright  Anthony Kuske <www.anthonykuske.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Allow caching for 30 minutes
session_cache_expire(30);
session_cache_limiter('public');

/**
 * Return an iCal compatible feed of a user's homework
 */

require_once dirname(dirname(dirname(__DIR__))) . '/config.php';

$username = required_param('u', PARAM_RAW);
$key = required_param('k', PARAM_RAW);

// Get the user from their username
$user = $DB->get_record('user', array('username' => $username), '*', MUST_EXIST);

// Include the homework stuff
$hwblock = new \block_homework\Block;

$hwblock->userID = $user->id;

// Check the key
if ($key != $hwblock->feeds->generateFeedKey($user)) {
	die("Invalid key");
}

// Get the user's group (class) IDs
$groupIDs = $hwblock->groups->getAllUsersGroupIDs($user->id);

$homework = $hwblock->getHomework(
	$groupIDs, //$groupIDs,
	false, //$courseIDs,
	false, //$assignedFor,
	true, //$approved,
	true ,//$distinct,
	false, //$past,
	false, //$dueDate,
	null, //$order,
	null, //$assignedRangeStart,
	null, //$assignedRangeEnd,
	true //$includePrivate
);


function formatDescription($text)
{
	$text = str_replace("\r\n", "\\n", $text);
	$text = str_replace("\n", "\\n", $text);
	#$text = htmlspecialchars($text);
	return $text;
}

function lines($text)
{
	// Using join instead of wordwrap because of https://bugs.php.net/bug.php?id=22487
	return join("\r\n ", str_split($text, 75));
}

/**
 * Output the feed...
 */

// the iCal date format. Note the Z on the end indicates a UTC timestamp.
define('DATE_ICAL', 'Ymd');

$eol = "\r\n";

$output = "BEGIN:VCALENDAR" . $eol .
	"VERSION:2.0" . $eol .
	"PRODID:-//project/author//NONSGML v1.0//EN" . $eol .
	"CALSCALE:GREGORIAN" . $eol;

foreach ($homework as $hw) {

	$url = $CFG->wwwroot . '/blocks/homework/hw.php?id=' . $hw->id;

	$timestamp = strtotime($hw->duedate);

	$desc = $hw->description;
	if ($notes = $hw->getNotes($user->id)) {
		$desc .= "\n---Your Notes---\n" . $notes;
	}

	$output .=
	"BEGIN:VEVENT" . $eol .
	"UID:" . $hw->id . $eol .
	"DTSTART;VALUE=DATE:" . date(DATE_ICAL, $timestamp) . $eol .
	"DTEND;VALUE=DATE:" . date(DATE_ICAL, strtotime("+1 DAY", $timestamp)) . $eol .
	"DTSTAMP:" . date(DATE_ICAL, $timestamp) . 'T000000' . $eol .
	lines("SUMMARY:" . htmlspecialchars($hw->getTitle())) . $eol .
	lines("DESCRIPTION:" . formatDescription($desc)) . $eol .
	"URL;VALUE=URI:" . htmlspecialchars($url) . $eol .
	"END:VEVENT" . $eol;
}

$output .= "END:VCALENDAR";

echo $output;
