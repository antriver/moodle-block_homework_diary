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

namespace block_homework;

class FeedManager
{
    private $hwblock;

    public function __construct(Block $hwblock)
    {
        $this->hwblock = $hwblock;
    }

    /**
     * Generates a key to use in the URL for an iCal feed
     * @param  object $user Moodle user object
     * @return string
     */
    public function generateFeedKey($user)
    {
        global $DB;
        $key = sha1($user->id . $user->username . $user->firstaccess . $user->timecreated);
        return $key;
    }

    /**
     * Generate the absoloute iCal feed URL for a user's homework
     * @param  boolean $userId
     * @return string
     */
    public function generateFeedURL($userId = null)
    {
        global $DB, $CFG;

        if ($userId === null) {
            $userId = $this->hwblock->getUserId();
        }

        $user = $DB->get_record('user', array('id' => $userId));

        $key = $this->generateFeedKey($user);

        $url = $CFG->wwwroot . '/blocks/homework/feed/?';
        $url .= http_build_query(array(
            'u' => $user->username,
            'k' => $key
        ));

        return $url;
    }
}
