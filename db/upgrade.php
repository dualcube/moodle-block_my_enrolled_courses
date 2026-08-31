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
 * upgrade
 *
 * @package    block_my_enrolled_courses
 * @copyright  DualCube (https://dualcube.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Upgrade steps for block_my_enrolled_courses.
 *
 * @param int $oldversion The version we are upgrading from.
 * @return bool
 */
function xmldb_block_my_enrolled_courses_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2014102202) {
        $table = new xmldb_table('block_myenrolledcoursesorder');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('courseorder', XMLDB_TYPE_TEXT, 'long', null, XMLDB_NOTNULL, null, null);
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_index('userid', XMLDB_INDEX_NOTUNIQUE, ['userid']);
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
        upgrade_block_savepoint(true, 2014102202, 'my_enrolled_courses');
    }

    if ($oldversion < 2026083102) {
        // Renamed to be prefixed with the full component name, as required by the plugin validator.
        $oldtable = new xmldb_table('block_myenrolledcoursesorder');
        if ($dbman->table_exists($oldtable) && !$dbman->table_exists('block_my_enrolled_courses_order')) {
            $dbman->rename_table($oldtable, 'block_my_enrolled_courses_order');
        }
        upgrade_block_savepoint(true, 2026083102, 'my_enrolled_courses');
    }

    return true;
}
