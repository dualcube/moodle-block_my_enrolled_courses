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
 * external library
 *
 * @package    block_my_enrolled_courses
 * @copyright  DualCube (https://dualcube.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
require_once("$CFG->libdir/externallib.php");
/**
 * Returns JASON
 * @return external_function_parameters
 */
class moodle_my_enrolled_courses_shorting_external extends external_api {
       /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function my_enrolled_courses_shorting_parameters() {
        return new external_function_parameters(
            array(
                'courseids' => new external_value(PARAM_RAW, 'course ids')
            )
        );
    }


    public static function my_enrolled_courses_shorting($courseids) {
        global $DB, $USER;
        $courseids = self::validate_parameters(self::my_enrolled_courses_shorting_parameters(),
            array(
                'courseids' => $courseids,
            )
        );
        $order = $DB->get_record('block_myenrolledcoursesorder', array('userid' => $USER->id));
        $neworder = new stdClass();
        $neworder->courseorder = $courseids['courseids'];
        if (empty($order)) {
            $neworder->userid = $USER->id;
            $DB->insert_record('block_myenrolledcoursesorder', $neworder);
        } else {
            $neworder->id = $order->id;
            $DB->update_record('block_myenrolledcoursesorder', $neworder);
        }
        return json_encode($neworder);
        die;
    }

    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function my_enrolled_courses_shorting_returns() {
        return new external_value(PARAM_RAW, 'The updated JSON output');
    }

}