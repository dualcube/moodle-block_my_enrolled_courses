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
 * Show/hide page
 *
 * @package    block_my_enrolled_courses
 * @copyright  DualCube (https://dualcube.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');

use block_my_enrolled_courses\course_list;

$courseid = optional_param('courseid', SITEID, PARAM_INT);
$course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);
$contextid = required_param('contextid', PARAM_INT);
$url = new moodle_url('/blocks/my_enrolled_courses/showhide.php', ['contextid' => $contextid]);
[$context, $unused, $cm] = get_context_info_array($contextid);

require_login($course, false, $cm);

$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');
$blockname = get_string('block_name', 'block_my_enrolled_courses');
$PAGE->navbar->add($blockname, $url);
$title = $course->shortname . ': ' . $blockname . ': ' . get_string('showhide_page_title', 'block_my_enrolled_courses');
$PAGE->set_title($title);
$PAGE->set_heading($course->fullname . ': ' . $blockname);
$PAGE->requires->js_call_amd('block_my_enrolled_courses/myenrolledcourses', 'showhide');
$PAGE->requires->css('/blocks/my_enrolled_courses/style.css');

// Show selected courses.
if (optional_param('show', false, PARAM_BOOL) && confirm_sesskey()) {
    $hidden = optional_param_array('hidden', [], PARAM_INT);
    if (!empty($hidden)) {
        course_list::show_courses($hidden);
    }
}

// Hide selected courses.
if (optional_param('hide', false, PARAM_BOOL) && confirm_sesskey()) {
    $visible = optional_param_array('visible', [], PARAM_INT);
    if (!empty($visible)) {
        course_list::hide_courses($visible);
    }
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('showhide_page_title', 'block_my_enrolled_courses'));

$html = html_writer::start_tag('div', ['id' => 'showhide_section']);
$html .= html_writer::start_tag('form', ['id' => 'showhide_form', 'method' => 'post', 'action' => $url]);
$html .= html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()]);
$html .= html_writer::start_tag('table', ['id' => 'showhidecourses', 'class' => 'generaltable block_my_enrolled_courses']);
$html .= html_writer::start_tag('tr');

$html .= html_writer::start_tag('td', ['id' => 'visiblecourses', 'class' => 'block_my_enrolled_courses']);
$html .= html_writer::start_tag('div');
$html .= html_writer::tag(
    'label',
    html_writer::tag('b', get_string('visible_lable', 'block_my_enrolled_courses')),
    ['for' => 'visible']
);
$html .= html_writer::end_tag('div');
$html .= html_writer::start_tag('div');
$html .= html_writer::start_tag('select', ['name' => 'visible[]', 'id' => 'visible', 'multiple' => 'multiple', 'size' => 20]);
$html .= course_list::visible_options();
$html .= html_writer::end_tag('select');
$html .= html_writer::end_tag('div');
$html .= html_writer::end_tag('td');

$html .= html_writer::start_tag('td', ['id' => 'showorhide', 'class' => 'block_my_enrolled_courses']);
$html .= html_writer::start_tag('div', ['id' => 'showbtn', 'class' => 'block_my_enrolled_courses']);
$submittext = $OUTPUT->larrow() . get_string('showcourse', 'block_my_enrolled_courses');
$html .= html_writer::empty_tag('input', ['type' => 'submit', 'name' => 'show', 'id' => 'show', 'value' => $submittext]);
$html .= html_writer::end_tag('div');
$html .= html_writer::start_tag('div', ['id' => 'hidebtn', 'class' => 'block_my_enrolled_courses']);
$submittext = $OUTPUT->rarrow() . get_string('hidecourse', 'block_my_enrolled_courses');
$html .= html_writer::empty_tag('input', ['type' => 'submit', 'name' => 'hide', 'id' => 'hide', 'value' => $submittext]);
$html .= html_writer::end_tag('div');
$html .= html_writer::end_tag('td');

$html .= html_writer::start_tag('td', ['id' => 'hiddencourses', 'class' => 'block_my_enrolled_courses']);
$html .= html_writer::start_tag('div');
$html .= html_writer::tag(
    'label',
    html_writer::tag('b', get_string('hidden_lable', 'block_my_enrolled_courses')),
    ['for' => 'hidden']
);
$html .= html_writer::end_tag('div');
$html .= html_writer::start_tag('div');
$html .= html_writer::start_tag('select', ['name' => 'hidden[]', 'id' => 'hidden', 'multiple' => 'multiple', 'size' => 20]);
$html .= course_list::hidden_options();
$html .= html_writer::end_tag('select');
$html .= html_writer::end_tag('div');
$html .= html_writer::end_tag('td');

$html .= html_writer::end_tag('tr');
$html .= html_writer::end_tag('table');
$html .= html_writer::end_tag('form');
$html .= html_writer::start_tag('div', ['class' => 'saveandback']);
$html .= html_writer::link(new moodle_url('/'), get_string('submitandback', 'block_my_enrolled_courses'));
$html .= html_writer::end_tag('div');
$html .= html_writer::end_tag('div');
echo $html;

echo $OUTPUT->footer();
