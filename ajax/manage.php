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
 * Interface for editing a homework item.
 *
 * @package    block_homework_diary
 * @copyright  Anthony Kuske <www.anthonykuske.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__DIR__))) . '/config.php');

require_login();

// Include the goodies for this block.
$hwblock = new \block_homework_diary\local\block();

$action = required_param('action', PARAM_RAW);
$homeworkid = required_param('homeworkid', PARAM_RAW);

$hw = \block_homework_diary\local\homework_item::load($homeworkid);

// Check permissions.
if (!$hwblock->can_edit_homework_item($hw)) {
    die("You don't have permission to edit that piece of homework.");
}

$return = array();

switch ($action) {
    case 'approve':
        $hw->approved = 1;
        $return['success'] = $hw->save();
        $return['html'] = $hwblock->display->homework_item($hw);
        break;

    case 'edit':
        // This is no longer used because we return to the add.php page to edit instead
        // of editing inline.
        $hw->description = required_param('description', PARAM_RAW);
        $return['success'] = $hw->save();
        $return['html'] = $hwblock->display->homework_item($hw);
        break;

    case 'delete':
        $return['success'] = $DB->delete_records('block_homework_diary', array('id' => $hw->id));
        break;

    default:
        $return['success'] = false;
        break;
}

header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Content-type: application/json');
echo json_encode($return);
