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

namespace block_my_enrolled_courses\privacy;

use context;
use context_user;
use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\transform;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;

/**
 * Privacy subsystem implementation for block_my_enrolled_courses.
 *
 * @package    block_my_enrolled_courses
 * @copyright  DualCube (https://dualcube.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\core_userlist_provider,
    \core_privacy\local\request\plugin\provider {
    /**
     * Returns information about the user data stored by this plugin.
     *
     * @param collection $collection A collection to add metadata to.
     * @return collection The updated collection.
     */
    public static function get_metadata(collection $collection): collection {
        $collection->add_database_table('block_my_enrolled_courses', [
            'userid' => 'privacy:metadata:block_my_enrolled_courses:userid',
            'courseid' => 'privacy:metadata:block_my_enrolled_courses:courseid',
            'hide' => 'privacy:metadata:block_my_enrolled_courses:hide',
        ], 'privacy:metadata:block_my_enrolled_courses');

        $collection->add_database_table('block_my_enrolled_courses_order', [
            'userid' => 'privacy:metadata:block_my_enrolled_courses_order:userid',
            'courseorder' => 'privacy:metadata:block_my_enrolled_courses_order:courseorder',
        ], 'privacy:metadata:block_my_enrolled_courses_order');

        return $collection;
    }

    /**
     * Get the list of contexts that contain user information for the specified user.
     *
     * @param int $userid The user to search.
     * @return contextlist The contextlist containing the list of contexts used in this plugin.
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        $params = ['userid' => $userid, 'contextuser' => CONTEXT_USER];

        $sql = "SELECT c.id
                  FROM {context} c
                 WHERE c.instanceid = :userid
                   AND c.contextlevel = :contextuser
                   AND (EXISTS (SELECT 1 FROM {block_my_enrolled_courses} h WHERE h.userid = c.instanceid)
                    OR EXISTS (SELECT 1 FROM {block_my_enrolled_courses_order} o WHERE o.userid = c.instanceid))";

        $contextlist = new contextlist();
        $contextlist->add_from_sql($sql, $params);

        return $contextlist;
    }

    /**
     * Get the list of users within a specific context.
     *
     * @param userlist $userlist The userlist containing the list of users who have data in this context/plugin combination.
     * @return void
     */
    public static function get_users_in_context(userlist $userlist): void {
        global $DB;

        $context = $userlist->get_context();

        if (!$context instanceof context_user) {
            return;
        }

        if (
            $DB->record_exists('block_my_enrolled_courses', ['userid' => $context->instanceid])
                || $DB->record_exists('block_my_enrolled_courses_order', ['userid' => $context->instanceid])
        ) {
            $userlist->add_user($context->instanceid);
        }
    }

    /**
     * Export all user data for the specified user, in the specified contexts.
     *
     * @param approved_contextlist $contextlist The approved contexts to export information for.
     * @return void
     */
    public static function export_user_data(approved_contextlist $contextlist): void {
        global $DB;

        foreach ($contextlist->get_contexts() as $context) {
            if (!$context instanceof context_user || $context->instanceid != $contextlist->get_user()->id) {
                continue;
            }

            $hiddencourses = $DB->get_records(
                'block_my_enrolled_courses',
                ['userid' => $context->instanceid],
                '',
                'id, courseid, hide'
            );
            $order = $DB->get_record('block_my_enrolled_courses_order', ['userid' => $context->instanceid]);

            if (empty($hiddencourses) && empty($order)) {
                continue;
            }

            $data = [];
            if (!empty($hiddencourses)) {
                $data['hiddencourses'] = array_map(function ($record) {
                    return (object) [
                        'courseid' => $record->courseid,
                        'hidden' => transform::yesno($record->hide),
                    ];
                }, array_values($hiddencourses));
            }
            if (!empty($order)) {
                $data['courseorder'] = json_decode($order->courseorder);
            }

            writer::with_context($context)->export_data(
                [get_string('privacy:path', 'block_my_enrolled_courses')],
                (object) $data
            );
        }
    }

    /**
     * Delete all data for all users in the specified context.
     *
     * @param context $context The specific context to delete data for.
     * @return void
     */
    public static function delete_data_for_all_users_in_context(context $context): void {
        global $DB;

        if ($context->contextlevel != CONTEXT_USER) {
            return;
        }

        $DB->delete_records('block_my_enrolled_courses', ['userid' => $context->instanceid]);
        $DB->delete_records('block_my_enrolled_courses_order', ['userid' => $context->instanceid]);
    }

    /**
     * Delete all user data for the specified user, in the specified contexts.
     *
     * @param approved_contextlist $contextlist The approved contexts and user information to delete data for.
     * @return void
     */
    public static function delete_data_for_user(approved_contextlist $contextlist): void {
        global $DB;

        foreach ($contextlist->get_contexts() as $context) {
            if (!$context instanceof context_user || $context->instanceid != $contextlist->get_user()->id) {
                continue;
            }

            $DB->delete_records('block_my_enrolled_courses', ['userid' => $context->instanceid]);
            $DB->delete_records('block_my_enrolled_courses_order', ['userid' => $context->instanceid]);
        }
    }

    /**
     * Delete multiple users within a single context.
     *
     * @param approved_userlist $userlist The approved context and user information to delete data for.
     * @return void
     */
    public static function delete_data_for_users(approved_userlist $userlist): void {
        global $DB;

        $context = $userlist->get_context();

        if (!$context instanceof context_user || !in_array($context->instanceid, $userlist->get_userids())) {
            return;
        }

        $DB->delete_records('block_my_enrolled_courses', ['userid' => $context->instanceid]);
        $DB->delete_records('block_my_enrolled_courses_order', ['userid' => $context->instanceid]);
    }
}
