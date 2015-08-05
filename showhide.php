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
 * Version details
 *
 * @package    block
 * @subpackage block_my_enrolled_courses
 * @copyright  Dualcube (http://dualcube.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once('locallib.php');

global $DB, $CFG, $USER, $PAGE;

$courseid = optional_param('courseid', SITEID, PARAM_INT);
$SITE = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
$contextid = required_param('contextid', PARAM_INT);
$url = new moodle_url($CFG->wwwroot . '/blocks/my_enrolled_courses/showhide.php', array('contextid' => $contextid));
list($context, $course, $cm) = get_context_info_array($contextid);

require_login($SITE, false, $cm);

$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');
$blockname = get_string('block_name', 'block_my_enrolled_courses');
$PAGE->navbar->add($blockname, $url);
$title = $SITE->shortname.': '.get_string('block_name', 'block_my_enrolled_courses').': '.
  get_string('showhide_page_title', 'block_my_enrolled_courses');
$PAGE->set_title($title);
$heading = $SITE->fullname.': '.get_string('block_name', 'block_my_enrolled_courses');
$PAGE->set_heading($heading);

$PAGE->requires->js('/blocks/my_enrolled_courses/js/jquery-1.10.2.js');
$PAGE->requires->js('/blocks/my_enrolled_courses/js/button-disable.js');
$PAGE->requires->css('/blocks/my_enrolled_courses/style.css');

$data = data_submitted();

// Show selected courses.
if (optional_param('show', false, PARAM_BOOL) && confirm_sesskey()) {
    if (isset($data->hidden)) {
        block_my_enrolled_courses_show_courses($data->hidden);
    }
}

// Hide selected courses.
if (optional_param('hide', false, PARAM_BOOL) && confirm_sesskey()) {
    if (isset($data->visible)) {
        block_my_enrolled_courses_hide_courses($data->visible);
    }
}

echo $OUTPUT->header();
// Print heading.
$pagetitle = get_string('showhide_page_title', 'block_my_enrolled_courses');
echo $OUTPUT->heading($pagetitle);

$html = '';
$html .= html_writer::start_tag('div', array('id' => 'showhide_section'));
$html .= html_writer::start_tag('form', array('id' => 'showhide_form', 'method' => 'post', 'action' => $url));
$sesskey = sesskey();
$html .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'sesskey', 'value' => $sesskey));
$html .= html_writer::start_tag('table', array('id' => 'showhidecourses', 'class' => 'generaltable block_my_enrolled_courses'));
$html .= html_writer::start_tag('tr');
$html .= html_writer::start_tag('td', array('id' => 'visiblecourses', 'class' => 'block_my_enrolled_courses'));
$html .= html_writer::start_tag('div');
$html .= html_writer::start_tag('lable', array('for' => 'visible'));
$html .= html_writer::start_tag('b');
$html .= get_string('visible_lable', 'block_my_enrolled_courses');
$html .= html_writer::end_tag('b');
$html .= html_writer::end_tag('lable');
$html .= html_writer::end_tag('div');
$html .= html_writer::start_tag('div');
$html .= html_writer::start_tag('select', array('name' => 'visible[]', 'id' => 'visible', 'multiple' => 'multiple', 'size' => 20));
$html .= block_my_enrolled_courses_get_visible_courses();
$html .= html_writer::end_tag('select');
$html .= html_writer::end_tag('div');
$html .= html_writer::end_tag('td');
$html .= html_writer::start_tag('td', array('id' => 'showorhide', 'class' => 'block_my_enrolled_courses'));
$html .= html_writer::start_tag('div', array('id' => 'showbtn', 'class' => 'block_my_enrolled_courses'));
$submittext = $OUTPUT->larrow().get_string('showcourse', 'block_my_enrolled_courses');
$html .= html_writer::empty_tag('input', array('type' => 'submit', 'name' => 'show', 'id' => 'show',
    'value' => $submittext));
$html .= html_writer::end_tag('div');
$html .= html_writer::start_tag('div', array('id' => 'hidebtn', 'class' => 'block_my_enrolled_courses'));
$submittext = $OUTPUT->rarrow().get_string('hidecourse', 'block_my_enrolled_courses');
$html .= html_writer::empty_tag('input', array('type' => 'submit', 'name' => 'hide', 'id' => 'hide',
    'value' => $submittext));
$html .= html_writer::end_tag('div');
$html .= html_writer::end_tag('td');
$html .= html_writer::start_tag('td', array('id' => 'hiddencourses', 'class' => 'block_my_enrolled_courses'));
$html .= html_writer::start_tag('div');
$html .= html_writer::start_tag('lable', array('for' => 'hidden'));
$html .= html_writer::start_tag('b');
$html .= get_string('hidden_lable', 'block_my_enrolled_courses');
$html .= html_writer::end_tag('b');
$html .= html_writer::end_tag('lable');
$html .= html_writer::end_tag('div');
$html .= html_writer::start_tag('div');
$html .= html_writer::start_tag('select', array('name' => 'hidden[]', 'id' => 'hidden', 'multiple' => 'multiple',  'size' => 20));
$html .= block_my_enrolled_courses_get_hidden_courses();
$html .= html_writer::end_tag('select');
$html .= html_writer::end_tag('div');
$html .= html_writer::end_tag('td');
$html .= html_writer::end_tag('tr');
$html .= html_writer::end_tag('table');
$html .= html_writer::end_tag('form');
$html .= html_writer::end_tag('div');
echo $html;

echo $OUTPUT->footer();