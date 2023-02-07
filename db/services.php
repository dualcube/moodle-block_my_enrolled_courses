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
 * services
 *
 * @package    block_my_enrolled_courses
 * @copyright  DualCube (https://dualcube.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$services = array(
    'moodle_block_my_enrolled_courses' => array(
        'functions' => array('moodle_my_enrolled_courses_shorting'),
        'requiredcapability' => '',
        'restrictedusers' => 0,
        'enabled' => 1,
    )
);
$functions = array(
    'moodle_my_enrolled_courses_shorting' => array(
        'classname' => 'moodle_my_enrolled_courses_shorting_external',
        'methodname' => 'my_enrolled_courses_shorting',
        'classpath' => 'blocks/my_enrolled_courses/externallib.php',
        'description' => 'Get shorting data',
        'type' => 'read',
        'ajax' => true,
        'loginrequired' => true
    )
);