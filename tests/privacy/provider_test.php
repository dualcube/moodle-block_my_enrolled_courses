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
 * Block my_enrolled_courses privacy provider tests.
 *
 * @package    block_my_enrolled_courses
 * @copyright  DualCube (https://dualcube.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_my_enrolled_courses\privacy;

use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;

/**
 * Block my_enrolled_courses privacy provider tests.
 *
 * @package    block_my_enrolled_courses
 * @copyright  DualCube (https://dualcube.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class provider_test extends \core_privacy\tests\provider_testcase {
    /**
     * Insert a hidden-course record directly, bypassing the block's own API.
     *
     * @param int $userid
     * @param int $courseid
     * @return void
     */
    protected function create_hidden_course(int $userid, int $courseid): void {
        global $DB;

        $DB->insert_record('block_my_enrolled_courses', (object) [
            'userid' => $userid,
            'courseid' => $courseid,
            'hide' => 1,
        ]);
    }

    /**
     * Insert a course-order record directly, bypassing the block's own API.
     *
     * @param int $userid
     * @param int[] $courseids
     * @return void
     */
    protected function create_course_order(int $userid, array $courseids): void {
        global $DB;

        $DB->insert_record('block_my_enrolled_courses_order', (object) [
            'userid' => $userid,
            'courseorder' => json_encode($courseids),
        ]);
    }

    /**
     * Test fetching information about user data stored.
     */
    public function test_get_metadata(): void {
        $collection = new \core_privacy\local\metadata\collection('block_my_enrolled_courses');
        $newcollection = provider::get_metadata($collection);
        $itemcollection = $newcollection->get_collection();
        $this->assertCount(2, $itemcollection);

        $tablesbyname = [];
        foreach ($itemcollection as $table) {
            $tablesbyname[$table->get_name()] = $table;
        }

        $this->assertArrayHasKey('block_my_enrolled_courses', $tablesbyname);
        $hiddenfields = $tablesbyname['block_my_enrolled_courses']->get_privacy_fields();
        $this->assertCount(3, $hiddenfields);
        $this->assertArrayHasKey('userid', $hiddenfields);
        $this->assertArrayHasKey('courseid', $hiddenfields);
        $this->assertArrayHasKey('hide', $hiddenfields);

        $this->assertArrayHasKey('block_my_enrolled_courses_order', $tablesbyname);
        $orderfields = $tablesbyname['block_my_enrolled_courses_order']->get_privacy_fields();
        $this->assertCount(2, $orderfields);
        $this->assertArrayHasKey('userid', $orderfields);
        $this->assertArrayHasKey('courseorder', $orderfields);
    }

    /**
     * Test getting the context for the user ID related to this plugin.
     */
    public function test_get_contexts_for_userid(): void {
        $this->resetAfterTest();
        $generator = $this->getDataGenerator();

        $student = $generator->create_user();
        $studentcontext = \context_user::instance($student->id);
        $teacher = $generator->create_user();
        $teachercontext = \context_user::instance($teacher->id);
        $course = $generator->create_course();

        // Nothing stored yet.
        $this->assertCount(0, provider::get_contexts_for_userid($student->id));
        $this->assertCount(0, provider::get_contexts_for_userid($teacher->id));

        // Student has a hidden course, teacher has a stored course order.
        $this->create_hidden_course($student->id, $course->id);
        $this->create_course_order($teacher->id, [$course->id]);

        $contextlist1 = provider::get_contexts_for_userid($student->id);
        $this->assertCount(1, $contextlist1);
        $this->assertEquals($studentcontext, $contextlist1->current());

        $contextlist2 = provider::get_contexts_for_userid($teacher->id);
        $this->assertCount(1, $contextlist2);
        $this->assertEquals($teachercontext, $contextlist2->current());
    }

    /**
     * Test getting users in the context ID related to this plugin.
     */
    public function test_get_users_in_context(): void {
        $this->resetAfterTest();
        $generator = $this->getDataGenerator();
        $component = 'block_my_enrolled_courses';

        $student = $generator->create_user();
        $studentcontext = \context_user::instance($student->id);
        $teacher = $generator->create_user();
        $teachercontext = \context_user::instance($teacher->id);
        $course = $generator->create_course();

        $userlist1 = new userlist($studentcontext, $component);
        provider::get_users_in_context($userlist1);
        $this->assertCount(0, $userlist1);

        $this->create_hidden_course($student->id, $course->id);
        $this->create_course_order($teacher->id, [$course->id]);

        $userlist1 = new userlist($studentcontext, $component);
        provider::get_users_in_context($userlist1);
        $this->assertCount(1, $userlist1);
        $this->assertEquals($student->id, $userlist1->current()->id);

        $userlist2 = new userlist($teachercontext, $component);
        provider::get_users_in_context($userlist2);
        $this->assertCount(1, $userlist2);
        $this->assertEquals($teacher->id, $userlist2->current()->id);

        // A non-user context should never yield any users.
        $systemlist = new userlist(\context_system::instance(), $component);
        provider::get_users_in_context($systemlist);
        $this->assertCount(0, $systemlist);
    }

    /**
     * Test exporting data for an approved contextlist.
     */
    public function test_export_user_data(): void {
        $this->resetAfterTest();
        $generator = $this->getDataGenerator();
        $component = 'block_my_enrolled_courses';

        $student = $generator->create_user();
        $studentcontext = \context_user::instance($student->id);
        $course = $generator->create_course();

        $this->create_hidden_course($student->id, $course->id);
        $this->create_course_order($student->id, [$course->id]);

        $approvedlist = new approved_contextlist($student, $component, [$studentcontext->id]);
        provider::export_user_data($approvedlist);

        $writer = writer::with_context($studentcontext);
        $this->assertTrue($writer->has_any_data());

        $exported = (array) $writer->get_data([get_string('privacy:path', $component)]);
        $this->assertArrayHasKey('hiddencourses', $exported);
        $this->assertArrayHasKey('courseorder', $exported);
        $this->assertEquals($course->id, $exported['hiddencourses'][0]->courseid);
    }

    /**
     * Test exporting data for a user with nothing stored produces no data.
     */
    public function test_export_user_data_nothing_stored(): void {
        $this->resetAfterTest();
        $generator = $this->getDataGenerator();
        $component = 'block_my_enrolled_courses';

        $student = $generator->create_user();
        $studentcontext = \context_user::instance($student->id);

        $approvedlist = new approved_contextlist($student, $component, [$studentcontext->id]);
        provider::export_user_data($approvedlist);

        $writer = writer::with_context($studentcontext);
        $this->assertFalse($writer->has_any_data());
    }

    /**
     * Test deleting data for all users within a context.
     */
    public function test_delete_data_for_all_users_in_context(): void {
        global $DB;

        $this->resetAfterTest();
        $generator = $this->getDataGenerator();

        $student = $generator->create_user();
        $studentcontext = \context_user::instance($student->id);
        $teacher = $generator->create_user();
        $course = $generator->create_course();

        $this->create_hidden_course($student->id, $course->id);
        $this->create_course_order($student->id, [$course->id]);
        $this->create_hidden_course($teacher->id, $course->id);
        $this->create_course_order($teacher->id, [$course->id]);

        // A system context deletion should have no effect.
        provider::delete_data_for_all_users_in_context(\context_system::instance());
        $this->assertEquals(2, $DB->count_records('block_my_enrolled_courses'));
        $this->assertEquals(2, $DB->count_records('block_my_enrolled_courses_order'));

        // Deleting in the student's own context only removes the student's data.
        provider::delete_data_for_all_users_in_context($studentcontext);
        $this->assertEquals(0, $DB->count_records('block_my_enrolled_courses', ['userid' => $student->id]));
        $this->assertEquals(0, $DB->count_records('block_my_enrolled_courses_order', ['userid' => $student->id]));
        $this->assertEquals(1, $DB->count_records('block_my_enrolled_courses', ['userid' => $teacher->id]));
        $this->assertEquals(1, $DB->count_records('block_my_enrolled_courses_order', ['userid' => $teacher->id]));
    }

    /**
     * Test deleting data within an approved contextlist for a single user.
     */
    public function test_delete_data_for_user(): void {
        global $DB;

        $this->resetAfterTest();
        $generator = $this->getDataGenerator();
        $component = 'block_my_enrolled_courses';

        $student = $generator->create_user();
        $teacher = $generator->create_user();
        $teachercontext = \context_user::instance($teacher->id);
        $course = $generator->create_course();

        $this->create_hidden_course($student->id, $course->id);
        $this->create_course_order($student->id, [$course->id]);
        $this->create_hidden_course($teacher->id, $course->id);
        $this->create_course_order($teacher->id, [$course->id]);

        // Deleting via a context that isn't the teacher's own should have no effect.
        $approvedlist = new approved_contextlist($teacher, $component, [\context_system::instance()->id]);
        provider::delete_data_for_user($approvedlist);
        $this->assertEquals(1, $DB->count_records('block_my_enrolled_courses', ['userid' => $teacher->id]));

        // Deleting via the teacher's own context removes only their data.
        $approvedlist = new approved_contextlist($teacher, $component, [$teachercontext->id]);
        provider::delete_data_for_user($approvedlist);
        $this->assertEquals(0, $DB->count_records('block_my_enrolled_courses', ['userid' => $teacher->id]));
        $this->assertEquals(0, $DB->count_records('block_my_enrolled_courses_order', ['userid' => $teacher->id]));
        $this->assertEquals(1, $DB->count_records('block_my_enrolled_courses', ['userid' => $student->id]));
        $this->assertEquals(1, $DB->count_records('block_my_enrolled_courses_order', ['userid' => $student->id]));
    }

    /**
     * Test deleting data within a context for an approved userlist.
     */
    public function test_delete_data_for_users(): void {
        global $DB;

        $this->resetAfterTest();
        $generator = $this->getDataGenerator();
        $component = 'block_my_enrolled_courses';

        $student = $generator->create_user();
        $studentcontext = \context_user::instance($student->id);
        $teacher = $generator->create_user();
        $teachercontext = \context_user::instance($teacher->id);
        $course = $generator->create_course();

        $this->create_hidden_course($student->id, $course->id);
        $this->create_course_order($student->id, [$course->id]);
        $this->create_hidden_course($teacher->id, $course->id);
        $this->create_course_order($teacher->id, [$course->id]);

        // Deleting in the student's context for the teacher's id should have no effect.
        $approvedlist = new approved_userlist($studentcontext, $component, [$teacher->id]);
        provider::delete_data_for_users($approvedlist);
        $this->assertEquals(1, $DB->count_records('block_my_enrolled_courses', ['userid' => $teacher->id]));

        // Deleting in the teacher's own context for the teacher's id removes only their data.
        $approvedlist = new approved_userlist($teachercontext, $component, [$student->id, $teacher->id]);
        provider::delete_data_for_users($approvedlist);
        $this->assertEquals(0, $DB->count_records('block_my_enrolled_courses', ['userid' => $teacher->id]));
        $this->assertEquals(0, $DB->count_records('block_my_enrolled_courses_order', ['userid' => $teacher->id]));
        $this->assertEquals(1, $DB->count_records('block_my_enrolled_courses', ['userid' => $student->id]));
        $this->assertEquals(1, $DB->count_records('block_my_enrolled_courses_order', ['userid' => $student->id]));
    }
}
