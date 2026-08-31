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
 * Local library
 *
 * @package    block_my_enrolled_courses
 * @copyright  DualCube (https://dualcube.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Unhide the given courses for the current user, keeping the stored course order in sync.
 *
 * @param int[] $courseids Course ids to make visible again.
 * @return void
 */
function block_my_enrolled_courses_show_courses($courseids) {
    global $DB, $USER;

    if (empty($courseids)) {
        return;
    }

    foreach ($courseids as $courseid) {
        $DB->delete_records('block_my_enrolled_courses', ['userid' => $USER->id, 'courseid' => $courseid, 'hide' => 1]);
    }

    $coursesorder = $DB->get_record('block_myenrolledcoursesorder', ['userid' => $USER->id]);
    if (!empty($coursesorder) && is_string($coursesorder->courseorder)) {
        $coursesinorder = json_decode($coursesorder->courseorder, true);
        $coursesdiff = array_diff($courseids, $coursesinorder);
        if (!empty($coursesdiff)) {
            $record = new stdClass();
            $record->id = $coursesorder->id;
            $record->courseorder = json_encode(array_merge($courseids, $coursesinorder));
            $DB->update_record('block_myenrolledcoursesorder', $record);
        }
    } else {
        $record = new stdClass();
        $record->userid = $USER->id;
        $record->courseorder = json_encode($courseids);
        $DB->insert_record('block_myenrolledcoursesorder', $record);
    }
}

/**
 * Hide the given courses for the current user, keeping the stored course order in sync.
 *
 * @param int[] $courseids Course ids to hide.
 * @return void
 */
function block_my_enrolled_courses_hide_courses($courseids) {
    global $DB, $USER;

    if (empty($courseids)) {
        return;
    }

    foreach ($courseids as $courseid) {
        $hiddencourse = $DB->get_record(
            'block_my_enrolled_courses',
            ['userid' => $USER->id, 'courseid' => $courseid, 'hide' => 1]
        );

        if (empty($hiddencourse)) {
            $course = new stdClass();
            $course->userid = $USER->id;
            $course->courseid = $courseid;
            $course->hide = 1;
            $DB->insert_record('block_my_enrolled_courses', $course);
        }
    }

    $coursesorder = $DB->get_record('block_myenrolledcoursesorder', ['userid' => $USER->id]);
    if (!empty($coursesorder) && is_string($coursesorder->courseorder)) {
        $coursesinorder = json_decode($coursesorder->courseorder, true);
        $record = new stdClass();
        $record->id = $coursesorder->id;
        $record->courseorder = json_encode(array_values(array_diff($coursesinorder, $courseids)));
        $DB->update_record('block_myenrolledcoursesorder', $record);
    } else {
        $enroledcourses = enrol_get_my_courses();
        $visiblecourses = [];
        if (!empty($enroledcourses)) {
            foreach ($enroledcourses as $enroledcourse) {
                if (!in_array($enroledcourse->id, $courseids)) {
                    $visiblecourses[] = $enroledcourse->id;
                }
            }
        }
        $record = new stdClass();
        $record->userid = $USER->id;
        $record->courseorder = json_encode($visiblecourses);
        $DB->insert_record('block_myenrolledcoursesorder', $record);
    }
}

/**
 * Build the <optgroup> of courses the current user has not hidden.
 *
 * @return string HTML options for the visible courses select box.
 */
function block_my_enrolled_courses_get_visible_courses() {
    global $DB, $USER;

    $enroledcourses = enrol_get_my_courses();
    $visiblecourses = [];

    if (!empty($enroledcourses)) {
        foreach ($enroledcourses as $id => $course) {
            $hiddencourse = $DB->get_record(
                'block_my_enrolled_courses',
                ['userid' => $USER->id, 'courseid' => $id, 'hide' => 1]
            );
            if (empty($hiddencourse)) {
                $visiblecourses[$id] = $course;
            }
        }
    }

    $label = get_string('none', 'block_my_enrolled_courses');
    if (!empty($visiblecourses)) {
        $label = get_string('visible_lable', 'block_my_enrolled_courses') . '(' . count($visiblecourses) . ')';
    }

    $html = html_writer::start_tag('optgroup', ['label' => $label]);
    foreach ($visiblecourses as $id => $course) {
        $html .= html_writer::start_tag('option', ['value' => $id]);
        $html .= format_string($course->fullname);
        $html .= html_writer::end_tag('option');
    }
    $html .= html_writer::end_tag('optgroup');

    return $html;
}

/**
 * Build the <optgroup> of courses the current user has hidden.
 *
 * @return string HTML options for the hidden courses select box.
 */
function block_my_enrolled_courses_get_hidden_courses() {
    global $DB, $USER;

    $enroledcourses = enrol_get_my_courses();
    $hiddencourses = [];

    if (!empty($enroledcourses)) {
        foreach ($enroledcourses as $id => $course) {
            $hiddencourse = $DB->get_record(
                'block_my_enrolled_courses',
                ['userid' => $USER->id, 'courseid' => $id, 'hide' => 1]
            );
            if (!empty($hiddencourse)) {
                $hiddencourses[$id] = $course;
            }
        }
    }

    $label = get_string('none', 'block_my_enrolled_courses');
    if (!empty($hiddencourses)) {
        $label = get_string('hidden_lable', 'block_my_enrolled_courses') . '(' . count($hiddencourses) . ')';
    }

    $html = html_writer::start_tag('optgroup', ['label' => $label]);
    foreach ($hiddencourses as $id => $course) {
        $html .= html_writer::start_tag('option', ['value' => $id]);
        $html .= format_string($course->fullname);
        $html .= html_writer::end_tag('option');
    }
    $html .= html_writer::end_tag('optgroup');

    return $html;
}

/**
 * Build the HTML for the visible, ordered course list shown inside the block.
 *
 * @return string
 */
function block_my_enrolled_courses_visible_in_block() {
    global $DB, $USER, $OUTPUT, $CFG;

    $enroledcourses = enrol_get_my_courses();
    block_my_enrolled_courses_manage_courses($enroledcourses);

    $coursesorder = $DB->get_record('block_myenrolledcoursesorder', ['userid' => $USER->id]);
    $coursesinorder = [];
    if (!empty($coursesorder) && is_string($coursesorder->courseorder)) {
        $coursesinorder = json_decode($coursesorder->courseorder, true);
    }

    $html = html_writer::start_tag('ul', ['id' => 'course_list_in_block']);

    if (!empty($coursesinorder)) {
        $courses = $DB->get_records_list('course', 'id', $coursesinorder, '', 'id, fullname');
        foreach ($coursesinorder as $id) {
            if (!isset($courses[$id])) {
                continue;
            }
            $url = new moodle_url($CFG->wwwroot . '/course/view.php', ['id' => $id]);
            $content = html_writer::start_tag('div', ['class' => 'li_course', 'data-id' => $id]);
            $anchor = html_writer::link($url, format_string($courses[$id]->fullname));
            $dragable = html_writer::start_tag(
                'span',
                ['role' => 'button', 'aria-haspopup' => 'false', 'data-drag-type' => 'move']
            );
            $dragable .= html_writer::start_tag('i', ['class' => 'fa fa-arrows']);
            $dragable .= html_writer::end_tag('i');
            $dragable .= html_writer::end_tag('span');
            $courseicon = $OUTPUT->pix_icon('i/course', get_string('course'));
            $colapsible = html_writer::start_tag('span', ['class' => 'expandable_icon']);
            $colapsible .= get_string('colapsibleplus', 'block_my_enrolled_courses');
            $colapsible .= html_writer::end_tag('span');
            $content .= "$dragable $courseicon $anchor $colapsible";
            $content .= html_writer::end_tag('div');
            $content .= block_my_enrolled_courses_course_modules($id);
            $html .= html_writer::tag('li', $content, ['class' => 'course_list_item_in_block']);
        }
    }

    $html .= html_writer::end_tag('ul');

    return $html;
}

/**
 * Build the HTML for the collapsible list of activities within a course.
 *
 * @param int $id Course id.
 * @return string
 */
function block_my_enrolled_courses_course_modules($id) {
    $modinfo = get_fast_modinfo($id);
    $content = '';

    if (empty($modinfo)) {
        return $content;
    }

    $content .= html_writer::start_tag('div', ['class' => 'course_modules', 'style' => 'display: none']);
    $content .= html_writer::start_tag('ul', ['class' => 'course_modules_list_in_block']);
    foreach ($modinfo->cms as $mod) {
        if ($mod->visible == 1) {
            $content .= html_writer::start_tag('li');
            $content .= html_writer::link($mod->url, $mod->name);
            $content .= html_writer::end_tag('li');
        }
    }
    $content .= html_writer::end_tag('ul');
    $content .= html_writer::end_tag('div');

    return $content;
}

/**
 * Drop stored hidden/order records for courses the user is no longer enrolled in, and make sure
 * every currently enrolled course has an entry in the stored course order.
 *
 * @param stdClass[] $enroledcourses Courses returned by enrol_get_my_courses() for the current user.
 * @return void
 */
function block_my_enrolled_courses_manage_courses($enroledcourses) {
    global $DB, $USER;

    $enroledcourseids = [];
    if (!empty($enroledcourses)) {
        foreach ($enroledcourses as $enroledcourse) {
            $enroledcourseids[] = $enroledcourse->id;
        }
    }

    $hiddencourses = $DB->get_records('block_my_enrolled_courses', ['userid' => $USER->id, 'hide' => 1]);
    $hiddencourseids = [];
    if (!empty($hiddencourses)) {
        foreach ($hiddencourses as $hiddencourse) {
            if (!in_array($hiddencourse->courseid, $enroledcourseids)) {
                $DB->delete_records('block_my_enrolled_courses', ['id' => $hiddencourse->id]);
            }
            $hiddencourseids[] = $hiddencourse->courseid;
        }
    }

    $courseinorderobj = $DB->get_record('block_myenrolledcoursesorder', ['userid' => $USER->id]);
    if (!empty($courseinorderobj)) {
        $courseinorder = json_decode($courseinorderobj->courseorder, true);
        $removed = array_diff($courseinorder, $enroledcourseids);
        $added = array_diff($enroledcourseids, $courseinorder);
        asort($added);
        if (!empty($removed) || !empty($added)) {
            $result = array_diff($courseinorder, $removed);
            $result = array_merge($result, $added);
            $result = array_diff($result, $hiddencourseids);

            $neworder = new stdClass();
            $neworder->id = $courseinorderobj->id;
            $neworder->courseorder = json_encode(array_values($result));
            $DB->update_record('block_myenrolledcoursesorder', $neworder);
        }
    } else {
        $neworder = new stdClass();
        $neworder->userid = $USER->id;
        $neworder->courseorder = json_encode($enroledcourseids);
        $DB->insert_record('block_myenrolledcoursesorder', $neworder);
    }
}
