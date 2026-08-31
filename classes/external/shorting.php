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

namespace block_my_enrolled_courses\external;

use context_user;
use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_multiple_structure;
use core_external\external_value;
use stdClass;

/**
 * External function to persist the display order of the current user's enrolled courses.
 *
 * @package    block_my_enrolled_courses
 * @copyright  DualCube (https://dualcube.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class shorting extends external_api {
    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'courseids' => new external_multiple_structure(
                new external_value(PARAM_INT, 'Course id'),
                'Ordered list of enrolled course ids'
            ),
        ]);
    }

    /**
     * Store the given course order for the current user.
     *
     * @param int[] $courseids Ordered list of enrolled course ids.
     * @return string JSON encoded record that was saved.
     */
    public static function execute($courseids): string {
        global $DB, $USER;

        $params = self::validate_parameters(self::execute_parameters(), ['courseids' => $courseids]);

        self::validate_context(context_user::instance($USER->id));

        $order = $DB->get_record('block_myenrolledcoursesorder', ['userid' => $USER->id]);

        $neworder = new stdClass();
        $neworder->userid = $USER->id;
        $neworder->courseorder = json_encode($params['courseids']);

        if (empty($order)) {
            $neworder->id = $DB->insert_record('block_myenrolledcoursesorder', $neworder);
        } else {
            $neworder->id = $order->id;
            $DB->update_record('block_myenrolledcoursesorder', $neworder);
        }

        return json_encode($neworder);
    }

    /**
     * Returns description of method result value.
     *
     * @return external_value
     */
    public static function execute_returns(): external_value {
        return new external_value(PARAM_RAW, 'The updated JSON output');
    }
}
