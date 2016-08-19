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
 * Generates an iCal feed of a student's homework.
 *
 * @package    block_homework_diary
 * @copyright  Anthony Kuske <www.anthonykuske.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_homework_diary\local;

/**
 * Generates an iCal feed of a student's homework.
 *
 * @package    block_homework_diary
 * @copyright  Anthony Kuske <www.anthonykuske.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class feed_manager {
    /**
     * @var block
     */
    private $hwblock;

    /**
     * Constructor
     *
     * @param block $hwblock
     */
    public function __construct(block $hwblock) {
        $this->hwblock = $hwblock;
    }

    /**
     * Generates a key to use in the URL for an iCal feed
     *
     * @param  object $user Moodle user object
     *
     * @return string
     */
    public function generate_feed_key($user) {
        $key = sha1($user->id . $user->username . $user->firstaccess . $user->timecreated);
        return $key;
    }

    /**
     * Generate the absoloute iCal feed URL for a user's homework
     *
     * @param  boolean $userid
     *
     * @return string
     */
    public function generate_feed_url($userid = null) {
        global $DB, $CFG;

        if ($userid === null) {
            $userid = $this->hwblock->get_user_id();
        }

        $user = $DB->get_record('user', array('id' => $userid));

        $key = $this->generate_feed_key($user);

        $url = $CFG->wwwroot . '/blocks/homework_diary/feed/?';
        $url .= http_build_query(
            array(
                'u' => $user->username,
                'k' => $key
            ));

        return $url;
    }

    /**
     * Formats a description string for outputting in the XML
     *
     * @param string $text
     *
     * @return string
     */
    public function format_description($text) {
        $text = str_replace("\r\n", "\\n", $text);
        $text = str_replace("\n", "\\n", $text);
        return $text;
    }

    /**
     * Wraps lines of text to a certain length.
     * Alternative to PHP's wordwrap() because of https://bugs.php.net/bug.php?id=22487
     *
     * @param string $text
     *
     * @return string
     */
    public function wordwrap($text) {
        return join("\r\n ", str_split($text, 75));
    }
}
