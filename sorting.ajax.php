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
global $CFG, $DB, $USER;
$courseids = $_POST['courseids'];
$order = $DB->get_record('block_myenrolledcoursesorder', array('userid' => $USER->id));
$neworder = new stdClass();
$neworder->courseorder = json_encode($courseids);
if (empty($order)) {
    $neworder->userid = $USER->id;
    $DB->insert_record('block_myenrolledcoursesorder', $neworder);
} else {
    $neworder->id = $order->id;
    $DB->update_record('block_myenrolledcoursesorder', $neworder);
}
echo true;
die;