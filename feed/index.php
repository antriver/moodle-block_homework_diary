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
 * Generate an XML iCal compatible feed of a student's homework.
 *
 * @package    block_homework
 * @copyright  Anthony Kuske <www.anthonykuske.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Allow caching for 30 minutes.
session_cache_expire(30);
session_cache_limiter('public');

require_once(dirname(dirname(dirname(__DIR__))) . '/config.php');

$username = required_param('u', PARAM_RAW);
$key = required_param('k', PARAM_RAW);

// Get the user from their username.
$user = $DB->get_record('user', array('username' => $username), '*', IGNORE_MISSING);
if (!$user) {
    header('User not found', true, 404);
    exit();
}

// Include the homework stuff.
$hwblock = new \block_homework\local\block();

$hwblock->userid = $user->id;

// Check the key.
if ($key != $hwblock->feeds->generate_feed_key($user)) {
    die("Invalid key");
}

// Get the user's group (class) IDs.
$groupids = $hwblock->groups->get_all_users_group_ids($user->id);

$currenthomework = $hwblock->repository->get_current_homework($groupids);
$privatehomework = $hwblock->repository->get_private_homework($user->id);
/** @var block_homework\local\homework_item[] $homework */
$homework = array_merge($currenthomework, $privatehomework);

/**
 * Output the feed...
 */

// The iCal date format. Note the Z on the end indicates a UTC timestamp.
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
    if ($notes = $hw->get_notes($user->id)) {
        $desc .= "\n---Your Notes---\n" . $notes;
    }

    $output .= "BEGIN:VEVENT" . $eol .
        "UID:" . $hw->id . $eol .
        "DTSTART;VALUE=DATE:" . date(DATE_ICAL, $timestamp) . $eol .
        "DTEND;VALUE=DATE:" . date(DATE_ICAL, strtotime("+1 DAY", $timestamp)) . $eol .
        "DTSTAMP:" . date(DATE_ICAL, $timestamp) . 'T000000' . $eol .
        $hwblock->feeds->wordwrap("SUMMARY:" . htmlspecialchars($hw->get_title())) . $eol .
        $hwblock->feeds->wordwrap("DESCRIPTION:" . $hwblock->feeds->format_description($desc)) . $eol .
        "URL;VALUE=URI:" . htmlspecialchars($url) . $eol .
        "END:VEVENT" . $eol;
}

$output .= "END:VCALENDAR";

echo $output;
