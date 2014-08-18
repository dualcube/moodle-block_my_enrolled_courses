<?php

defined('MOODLE_INTERNAL') || die();
require_once('functions.php');

class block_my_courses extends block_base {

  function init() {
      $this->title = get_string('pluginname', 'block_my_courses');
  }

  function get_content() {
    global $CFG, $PAGE;
    
    $PAGE->requires->js('/blocks/my_courses/js/jquery-1.10.2.js');
    $PAGE->requires->js('/blocks/my_courses/js/jquery-ui.js');
    $PAGE->requires->js('/blocks/my_courses/js/sortable.js');
    $PAGE->requires->css('/blocks/my_courses/style.css');
    
    if ($this->content !== null) {
        return $this->content;
    }

    $this->content = new stdClass();
    
    $html = get_visible_courses_in_block();
    $this->content->text = $html;
    
    $url = new moodle_url($CFG->wwwroot . '/blocks/my_courses/showhide.php', array('contextid' => $this->context->id));
    $link = html_writer::link($url, get_string('showhide', 'block_my_courses'));            
    $this->content->footer = $link;

    return $this->content;
  }
}
