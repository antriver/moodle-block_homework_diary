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

require 'include/header.php';
echo $OUTPUT->header();
echo $hwblock->display->tabs('icalfeed');

switch ($hwblock->getMode()) {

	case 'pastoral-student':
	case 'student':
	case 'parent':

		echo $hwblock->display->icalFeedBox(false);

		echo '<h2><i class="fa fa-group"></i> How To Add Your Homework Feed To iCal</h2>';

		?>
		<ol id="feedhowto">
			<li>Open iCal and choose <strong>File</strong> > <strong>New Calendar Subscription...</strong>
			<img src="assets/img/feed-1.png" />
			</li>

			<li>Paste the link from above in the <strong>Calendar URL</strong> box.
			<img src="assets/img/feed-2.png" />
			</li>

			<li>Change the <strong>Name</strong> to &quot;Homework&quot; and the <strong>Auto-Refresh</strong> option to Every 15 minutes. Then click <strong>OK</strong> and your homework from DragonNet will automatically appear in iCal.
			<img src="assets/img/feed-3.png" />
			</li>

		</ol>
		<?

	break;
}

echo $OUTPUT->footer();
