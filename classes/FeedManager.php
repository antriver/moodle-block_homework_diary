<?php

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
