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

defined('MOODLE_INTERNAL') || die();

$functions = [
    'block_my_enrolled_courses_shorting' => [
        'classname' => 'block_my_enrolled_courses\external\shorting',
        'methodname' => 'execute',
        'description' => 'Save the display order of the current user\'s enrolled courses.',
        'type' => 'write',
        'ajax' => true,
        'loginrequired' => true,
    ],
];

$services = [
    'block_my_enrolled_courses' => [
        'functions' => ['block_my_enrolled_courses_shorting'],
        'requiredcapability' => '',
        'restrictedusers' => 0,
        'enabled' => 1,
    ],
];
