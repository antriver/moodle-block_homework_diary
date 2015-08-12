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
 * Show all homework due on a single day
 *
 * @package    block_homework
 * @copyright  Anthony Kuske <www.anthonykuske.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

die('Not used');

/*
include 'include/header.php';

$day = required_param('date', PARAM_RAW);

$date = new \DateTime();
$date->setTimestamp(strtotime($day));

echo $OUTPUT->header();
echo $hwblock->display->tabs('overview', $subtabs);

switch ($hwblock->getMode()) {

	case 'student':

		echo '<h2>Homework Due on ' . $date->format('l F jS') . '</h2>';
		$homework = $hwblock->getHomeworkForUser($USER->id, 1, $date->format('U'));
		echo $hwblock->display->homeworkList($homework);
		break;

}

?>
<div class="course-content">
<div class="single-section">
<div class="section-navigation mdl-bottom">
	<?php

	$date->modify('-1 day');

	$previousLink = $date->format('Y-m-d');
	$previousText = $date->format('l F jS');

	$date->modify('+2 days');

	$nextLink = $date->format('Y-m-d');
	$nextText = $date->format('l F jS');

	?>
	<span class="mdl-left"><a href="day.php?date=<?=$previousLink?>"><span class="larrow"></span> <?=$previousText?></a></span>
	<span class="mdl-right"><a href="day.php?date=<?=$nextLink?>"><?=$nextText?> <span class="rarrow"></span></a></span>

</div>
</div>
</div>
<?php

echo $OUTPUT->footer(); */
