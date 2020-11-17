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
 * Course category select filter.
 *
 * @package    report_allbackups
 * @copyright  2020 Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_allbackups\filters;

/**
 * Course category  filter based on a list of values.
 * @copyright  2020 Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class coursecategoryfilter extends \user_filter_simpleselect {
    /**
     * Returns the condition to be used with SQL where
     * @param array $data filter settings
     * @return array sql string and $params
     */
    public function get_sql_filter($data) {
        global $DB;
        $value = $data['value'];

        $field = $this->_field;
        if ($value == '') {
            return '';
        }
        $category = \core_course_category::get($value);
        $ids = $category->get_all_children_ids(); // Get all child course categories.
        $ids[] = $value; // Add parent id to this category as well.

        list($insql, $courseparams) = $DB->get_in_or_equal($ids, SQL_PARAMS_NAMED);

        return array("$field $insql", $courseparams);
    }
}