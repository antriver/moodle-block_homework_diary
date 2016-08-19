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
 * Homework block plugin.
 *
 * @package    block_homework_diary
 * @copyright  Anthony Kuske <www.anthonykuske.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Homework block plugin.
 *
 * @package    block_homework_diary
 * @copyright  Anthony Kuske <www.anthonykuske.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_homework_diary extends block_base {

    /**
     * Initialize
     *
     * @throws coding_exception
     */
    public function init() {
        $this->title = get_string('pluginname', 'block_homework_diary');
    }

    /**
     * Content to show when in block form.
     * TODO: Add this feature.
     *
     * @return string
     */
    public function get_content() {
        return '';
    }

    /**
     * Multiple instances are allowed.
     *
     * @return boolean
     */
    public function instance_allow_multiple() {
        return true;
    }

    /**
     * We have a settings.php file
     *
     * @return bool
     */
    public function has_config() {
        return true;
    }
}
