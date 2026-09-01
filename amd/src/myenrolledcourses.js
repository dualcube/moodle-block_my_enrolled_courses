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
 * Sorting and show/hide behaviour for the My enrolled courses block.
 *
 * @module     block_my_enrolled_courses/myenrolledcourses
 * @copyright  DualCube (https://dualcube.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['jquery', 'core/ajax', 'core/sortable_list'], function($, Ajax, SortableList) {

    /**
     * Read the current course order from the DOM and persist it for the logged-in user.
     *
     * @param {jQuery} root The block's course list root element.
     * @return {Promise}
     */
    const saveCourseOrder = (root) => {
        const courseids = [];
        root.find('.li_course').each((index, element) => {
            const id = $(element).data('id');
            if (id !== null && !courseids.includes(id)) {
                courseids.push(id);
            }
        });

        return Ajax.call([{
            methodname: 'block_my_enrolled_courses_shorting',
            args: {courseids: courseids},
        }])[0];
    };

    /**
     * Expand or collapse the list of activities for a course.
     *
     * @param {Event} event
     */
    const toggleModules = (event) => {
        const icon = $(event.currentTarget);
        icon.closest('.course_list_item_in_block').find('.course_modules').slideToggle('slow');
        icon.text(icon.text() === '+' ? '-' : '+');
    };

    return {
        /**
         * Initialise course drag-and-drop reordering and the expand/collapse icons.
         */
        sorting: function() {
            const root = $('ul#course_list_in_block');

            new SortableList('ul#course_list_in_block');
            root.on(SortableList.EVENTS.DROP, () => saveCourseOrder(root));

            root.off('click', '.expandable_icon');
            root.on('click', '.expandable_icon', toggleModules);
        },

        /**
         * Initialise the show/hide courses form.
         */
        showhide: function() {
            $('#hide').attr('disabled', 'disabled');
            $('#visible').on('change', () => $('#hide').removeAttr('disabled'));

            $('#show').attr('disabled', 'disabled');
            $('#hidden').on('change', () => $('#show').removeAttr('disabled'));
        },
    };
});
