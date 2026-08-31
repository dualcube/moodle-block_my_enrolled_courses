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
 * main block page
 *
 * @package    block_my_enrolled_courses
 * @copyright  DualCube (https://dualcube.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
require_once('locallib.php');

/**
 * My enrolled courses block class.
 *
 * @package    block_my_enrolled_courses
 * @copyright  DualCube (https://dualcube.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_my_enrolled_courses extends block_base {
    /**
     * Initialise the block title.
     *
     * @return void
     */
    public function init() {
        $this->title = get_string('pluginname', 'block_my_enrolled_courses');
    }

    /**
     * Build the block content.
     *
     * @return stdClass
     */
    public function get_content() {
        $this->page->requires->js_call_amd('block_my_enrolled_courses/myenrolledcourses', 'sorting');
        $this->page->requires->css('/blocks/my_enrolled_courses/style.css');

        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new stdClass();
        $this->content->text = block_my_enrolled_courses_visible_in_block();

        $url = new moodle_url('/blocks/my_enrolled_courses/showhide.php', ['contextid' => $this->context->id]);
        $this->content->footer = html_writer::link($url, get_string('showhide', 'block_my_enrolled_courses'));

        return $this->content;
    }
}
