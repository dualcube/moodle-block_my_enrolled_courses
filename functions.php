<?php

// Show selected hidden courses
function show_courses($courseids) {
  global $DB, $USER;
  
  if(! empty($courseids)) {
    foreach($courseids as $courseid) {
      $DB->delete_records('block_my_enrolled_courses', array('userid' => $USER->id, 'courseid' => $courseid, 'hide' => 1));
    }
  }
}

// Hide seleced courses
function hide_courses($courseids) {
  global $DB, $USER;
  
  if(! empty($courseids)) {
    foreach($courseids as $courseid) {
      $hidden_course = $DB->get_record('block_my_enrolled_courses', array('userid' => $USER->id, 'courseid' => $courseid, 'hide' => 1));
      
      if(empty($hidden_course)) {
        $course = new stdClass();
        $course->userid = $USER->id;
        $course->courseid = $courseid;
        $course->hide = 1;
        $DB->insert_record('block_my_enrolled_courses', $course);
      }
    }
  }
}

// Return HTML for visible courses list
function get_visible_courses() {
  global $DB, $USER;
  
  $enroled_courses = enrol_get_my_courses();
  $visible_courses = array();
  
  if(! empty($enroled_courses)) {
    foreach($enroled_courses as $id => $course) {
      $hidden_course = $DB->get_record('block_my_enrolled_courses', array('userid' => $USER->id, 'courseid' => $id, 'hide' => 1));
      if(empty($hidden_course)) {
        $visible_courses[$id] = $course;
      }
    }
  }
  
  $html = '';
  $lable = get_string('none', 'block_my_enrolled_courses');
  if(! empty($visible_courses))
    $lable = get_string('visible_lable', 'block_my_enrolled_courses'). '(' . count($visible_courses) . ')';
  
  $html .= html_writer::start_tag('optgroup', array('label' => $lable));
  
  foreach($visible_courses as $id => $course) {
    $html .= html_writer::start_tag('option', array('value' => $id));
    $html .= $course->fullname;
    $html .= html_writer::end_tag('option');
  }
  
  $html .= html_writer::end_tag('optgroup');
  return $html;
}

// Return HTML for hidden courses list
function get_hidden_courses() {
  global $DB, $USER;
  
  $enroled_courses = enrol_get_my_courses();
  $hidden_courses = array();
  
  if(! empty($enroled_courses)) {
    foreach($enroled_courses as $id => $course) {
      $hidden_course = $DB->get_record('block_my_enrolled_courses', array('userid' => $USER->id, 'courseid' => $id, 'hide' => 1));
      if(! empty($hidden_course)) {
        $hidden_courses[$id] = $course;
      }
    }
  }
  
  $html = '';
  $lable = get_string('none', 'block_my_enrolled_courses');
  if(! empty($hidden_courses))
    $lable = get_string('hidden_lable', 'block_my_enrolled_courses'). '(' . count($hidden_courses) . ')';
  
  $html .= html_writer::start_tag('optgroup', array('label' => $lable));
  
  if(! empty($hidden_courses)) {
    foreach($hidden_courses as $id => $course) {
      $html .= html_writer::start_tag('option', array('value' => $id));
      $html .= $course->fullname;
      $html .= html_writer::end_tag('option');
    }
  }
  
  $html .= html_writer::end_tag('optgroup');
  return $html;
}

// Return HTML for visible courses list in my_courses block
function get_visible_courses_in_block() {
  global $DB, $USER, $OUTPUT, $CFG;
    
  $enroled_courses = enrol_get_my_courses();
  if(! empty($enroled_courses))
    remove_older_course_records($enroled_courses);
  $html = '';
  $html .= html_writer::start_tag('ul', array('id' => 'course_list_in_block'));
  if(! empty($enroled_courses)) {
    foreach($enroled_courses as $id => $course) {
      $hidden_course = $DB->get_record('block_my_enrolled_courses', array('userid' => $USER->id, 'courseid' => $id, 'hide' => 1));
      if(empty($hidden_course)) {        
        $html .= html_writer::start_tag('div', array('class' => 'li_course'));
        
        $url = new moodle_url($CFG->wwwroot . '/course/view.php', array('id' => $id));
        $anchor = html_writer::link($url, $course->fullname);        
        $course_icon = $OUTPUT->pix_icon('i/course', get_string('course'));
        $content = "$course_icon $anchor";
        
        $html .= html_writer::tag('li', $content, array('class' => 'course_list_item_in_block'));
        
        $html .= html_writer::end_tag('div');
      }
    }
  }
  $html .= html_writer::end_tag('ul');
  return $html;
}

// Remove older courses records from database
function remove_older_course_records($enroled_courses) {
  global $DB, $USER;
  
  $hidden_courses = $DB->get_records('block_my_enrolled_courses', array('userid' => $USER->id, 'hide' => 1));
  if(! empty($hidden_courses)) {
    $enroled_course_ids = array();
    foreach($enroled_courses as $enroled_course)
      $enroled_course_ids[] = $enroled_course->id;
    
    foreach($hidden_courses as $hidden_course) {
      if(! in_array($hidden_course->courseid, $enroled_course_ids)) {
        $DB->delete_records('block_my_enrolled_courses', array('id' => $hidden_course->id));
      }
    }
  }
}
