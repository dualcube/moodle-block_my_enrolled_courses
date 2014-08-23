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

// Show selected hidden courses.
function block_my_enrolled_courses_show_courses($courseids) {
    global $DB, $USER;

    if (! empty($courseids)) {
        foreach ($courseids as $courseid) {
            $DB->delete_records('block_my_enrolled_courses', array('userid' => $USER->id, 'courseid' => $courseid, 'hide' => 1));
        }
    }
}

// Hide seleced courses.
function block_my_enrolled_courses_hide_courses($courseids) {
    global $DB, $USER;

    if (! empty($courseids)) {
        foreach ($courseids as $courseid) {
            $hiddencourse = $DB->get_record('block_my_enrolled_courses',
                array('userid' => $USER->id, 'courseid' => $courseid, 'hide' => 1));

            if (empty($hiddencourse)) {
                $course = new stdClass();
                $course->userid = $USER->id;
                $course->courseid = $courseid;
                $course->hide = 1;
                $DB->insert_record('block_my_enrolled_courses', $course);
            }
        }
    }
}

// Return HTML for visible courses list.
function block_my_enrolled_courses_get_visible_courses() {
    global $DB, $USER;

    $enroledcourses = enrol_get_my_courses();
    $visiblecourses = array();

    if (! empty($enroledcourses)) {
        foreach ($enroledcourses as $id => $course) {
            $hiddencourse = $DB->get_record('block_my_enrolled_courses',
                array('userid' => $USER->id, 'courseid' => $id, 'hide' => 1));
            if (empty($hiddencourse)) {
                $visiblecourses[$id] = $course;
            }
        }
    }
    $html = '';
    $lable = get_string('none', 'block_my_enrolled_courses');
    if (! empty($visiblecourses)) {
        $lable = get_string('visible_lable', 'block_my_enrolled_courses'). '(' . count($visiblecourses) . ')';
    }
    $html .= html_writer::start_tag('optgroup', array('label' => $lable));

    foreach ($visiblecourses as $id => $course) {
        $html .= html_writer::start_tag('option', array('value' => $id));
        $html .= $course->fullname;
        $html .= html_writer::end_tag('option');
    }

    $html .= html_writer::end_tag('optgroup');
    return $html;
}

// Return HTML for hidden courses list.
function block_my_enrolled_courses_get_hidden_courses() {
    global $DB, $USER;

    $enroledcourses = enrol_get_my_courses();
    $hiddencourses = array();

    if (! empty($enroledcourses)) {
        foreach ($enroledcourses as $id => $course) {
            $hiddencourse = $DB->get_record('block_my_enrolled_courses',
                array('userid' => $USER->id, 'courseid' => $id, 'hide' => 1));
            if (! empty($hiddencourse)) {
                $hiddencourses[$id] = $course;
            }
        }
    }

    $html = '';
    $lable = get_string('none', 'block_my_enrolled_courses');
    if (! empty($hiddencourses)) {
        $lable = get_string('hidden_lable', 'block_my_enrolled_courses'). '(' . count($hiddencourses) . ')';
    }

    $html .= html_writer::start_tag('optgroup', array('label' => $lable));

    if (! empty($hiddencourses)) {
        foreach ($hiddencourses as $id => $course) {
            $html .= html_writer::start_tag('option', array('value' => $id));
            $html .= $course->fullname;
            $html .= html_writer::end_tag('option');
        }
    }

    $html .= html_writer::end_tag('optgroup');
    return $html;
}

// Return HTML for visible courses list in my_courses block.
function block_my_enrolled_courses_visible_in_block() {
    global $DB, $USER, $OUTPUT, $CFG;

    $enroledcourses = enrol_get_my_courses();
    if (! empty($enroledcourses)) {
        block_my_enrolled_courses_remove_older_courses($enroledcourses);
    }
    $html = '';
    $html .= html_writer::start_tag('ul', array('id' => 'course_list_in_block'));
    if (! empty($enroledcourses)) {
        foreach ($enroledcourses as $id => $course) {
            $hiddencourse = $DB->get_record('block_my_enrolled_courses',
                array('userid' => $USER->id, 'courseid' => $id, 'hide' => 1));
            if (empty($hiddencourse)) {
                $html .= html_writer::start_tag('div', array('class' => 'li_course'));

                $url = new moodle_url($CFG->wwwroot . '/course/view.php', array('id' => $id));
                $anchor = html_writer::link($url, $course->fullname);
                $courseicon = $OUTPUT->pix_icon('i/course', get_string('course'));
                $content = "$courseicon $anchor";
                $html .= html_writer::tag('li', $content, array('class' => 'course_list_item_in_block'));
                $html .= html_writer::end_tag('div');
            }
        }
    }
    $html .= html_writer::end_tag('ul');
    return $html;
}

// Remove older courses records from database.
function block_my_enrolled_courses_remove_older_courses($enroledcourses) {
    global $DB, $USER;

    $hiddencourses = $DB->get_records('block_my_enrolled_courses', array('userid' => $USER->id, 'hide' => 1));
    if (! empty($hiddencourses)) {
        $enroledcourseids = array();
        foreach ($enroledcourses as $enroledcourse) {
            $enroledcourseids[] = $enroledcourse->id;
        }

        foreach ($hiddencourses as $hiddencourse) {
            if (! in_array($hiddencourse->courseid, $enroledcourseids)) {
                $DB->delete_records('block_my_enrolled_courses', array('id' => $hiddencourse->id));
            }
        }
    }
}
