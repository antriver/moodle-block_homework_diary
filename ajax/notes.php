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
 * Update student notes for a homework item.
 *
 * @package    block_homework
 * @copyright  Anthony Kuske <www.anthonykuske.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__DIR__))) . '/config.php');

require_login();

// Include the goodies for this block.
$hwblock = new \block_homework\local\block();

$action = required_param('action', PARAM_RAW);
$homeworkid = required_param('homeworkid', PARAM_RAW);
$notes = required_param('notes', PARAM_RAW);

// Get the item.
$hw = \block_homework\local\homework_item::load($homeworkid);

switch ($action) {

    case 'save':
        $response = array(
            'success' => $hw->set_notes($hwblock->get_user_id(), $notes),
            'text'    => $hwblock->display->filter_text($notes)
        );
        break;
}

header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Content-type: application/json');
echo json_encode($response);
