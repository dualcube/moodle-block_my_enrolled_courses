<?php
require_once('../../config.php');
require_once('functions.php');

global $DB, $CFG, $USER, $PAGE;

$SITE = $DB->get_record('course', array('id'=>optional_param('courseid', SITEID, PARAM_INT)), '*', MUST_EXIST);
$contextid = required_param('contextid', PARAM_INT);
$url = new moodle_url($CFG->wwwroot . '/blocks/my_courses/showhide.php', array('contextid' => $contextid));
list($context, $course, $cm) = get_context_info_array($contextid);

require_login($SITE, false, $cm);

$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');
$PAGE->navbar->add(get_string('block_name', 'block_my_courses'), $url);
$PAGE->set_title($SITE->shortname . ': ' . get_string('block_name', 'block_my_courses') . ': ' . get_string('showhide_page_title', 'block_my_courses'));
$PAGE->set_heading($SITE->fullname . ': ' . get_string('block_name', 'block_my_courses'));

$PAGE->requires->js('/blocks/my_courses/js/jquery-1.10.2.js');
$PAGE->requires->js('/blocks/my_courses/js/button-disable.js');
$PAGE->requires->css('/blocks/my_courses/style.css');

$data = data_submitted();

// Show selected courses
if (optional_param('show', false, PARAM_BOOL)) {
  if(isset($data->hidden))
    show_courses($data->hidden);
}

// Hide selected courses
if (optional_param('hide', false, PARAM_BOOL)) {
  if(isset($data->visible))
    hide_courses($data->visible);
}

echo $OUTPUT->header();
// Print heading
echo $OUTPUT->heading(get_string('showhide_page_title', 'block_my_courses'));

$html = '';
$html .= html_writer::start_tag('div', array('id' => 'showhide_section'));
$html .= html_writer::start_tag('form', array('id' => 'showhide_form', 'method' => 'post', 'action' => $url));
$html .= html_writer::start_tag('table', array('id' => 'showhidecourses', 'class' => 'generaltable'));

$html .= html_writer::start_tag('tr');

$html .= html_writer::start_tag('td', array('id' => 'visiblecourses'));
$html .= html_writer::start_tag('div');
$html .= html_writer::start_tag('lable', array('for' => 'visible'));
$html .= html_writer::start_tag('b');
$html .= get_string('visible_lable', 'block_my_courses');
$html .= html_writer::end_tag('b');
$html .= html_writer::end_tag('lable');
$html .= html_writer::end_tag('div');
$html .= html_writer::start_tag('div');
$html .= html_writer::start_tag('select', array('name' => 'visible[]', 'id' => 'visible', 'multiple' => 'multiple', 'size' => 20));
$html .= get_visible_courses();
$html .= html_writer::end_tag('select');
$html .= html_writer::end_tag('div');
$html .= html_writer::end_tag('td');

$html .= html_writer::start_tag('td', array('id' => 'showorhide'));
$html .= html_writer::start_tag('div', array('id' => 'showbtn'));
$html .= html_writer::empty_tag('input', array('type' => 'submit', 'name' => 'show', 'id' => 'show', 'value' => $OUTPUT->larrow().get_string('showcourse', 'block_my_courses')));
$html .= html_writer::end_tag('div');
$html .= html_writer::start_tag('div', array('id' => 'hidebtn'));
$html .= html_writer::empty_tag('input', array('type' => 'submit', 'name' => 'hide', 'id' => 'hide', 'value' => $OUTPUT->rarrow().get_string('hidecourse', 'block_my_courses')));
$html .= html_writer::end_tag('div');
$html .= html_writer::end_tag('td');

$html .= html_writer::start_tag('td', array('id' => 'hiddencourses'));
$html .= html_writer::start_tag('div');
$html .= html_writer::start_tag('lable', array('for' => 'hidden'));
$html .= html_writer::start_tag('b');
$html .= get_string('hidden_lable', 'block_my_courses');
$html .= html_writer::end_tag('b');
$html .= html_writer::end_tag('lable');
$html .= html_writer::end_tag('div');
$html .= html_writer::start_tag('div');
$html .= html_writer::start_tag('select', array('name' => 'hidden[]', 'id' => 'hidden', 'multiple' => 'multiple',  'size' => 20));
$html .= get_hidden_courses();
$html .= html_writer::end_tag('select');
$html .= html_writer::end_tag('div');
$html .= html_writer::end_tag('td');

$html .= html_writer::end_tag('tr');

$html .= html_writer::end_tag('table');
$html .= html_writer::end_tag('form');
$html .= html_writer::end_tag('div');
echo $html;

unset($_POST);
echo $OUTPUT->footer();