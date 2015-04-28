<?php

/**
 * @package    block_homework
 * @copyright  Anthony Kuske <www.anthonykuske.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class block_homework extends block_base {

	public function init() {
		$this->title = get_string('pluginname', 'block_homework');
	}

	/**
	 * Stuff to show when in block form.
	 */
	public function get_content() {
		return '';

		/*global $CFG, $OUTPUT;

		if ($this->content !== null) {
			return $this->content;
		}

		if (empty($this->instance)) {
			$this->content = '';
			return $this->content;
		}

		$this->content = new stdClass();
		$this->content->items = array();
		$this->content->icons = array();
		$this->content->footer = '';

		// user/index.php expect course context, so get one if page has module context.
		$currentcontext = $this->page->context->get_course_context(false);

		if (! empty($this->config->text)) {
			$this->content->text = $this->config->text;
		}

		$this->content = '';
		if (empty($currentcontext)) {
			return $this->content;
		}
		if ($this->page->course->id == SITEID) {
			$this->context->text .= "site context";
		}

		if (! empty($this->config->text)) {
			$this->content->text .= $this->config->text;
		}

		return $this->content;*/
	}

	public function instance_allow_multiple() {
		  return true;
	}

	public function has_config() {
		return true;
	}
}
