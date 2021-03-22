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
 * An empty checkbox filter, that returns no sql for filtering.
 *
 * @package    report_allbackups
 * @copyright  2021 Arnes
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_allbackups\filters;

/**
 * A checkbox filter that returns no sql for filtering, but belongs inside a filter form anyways.
 * @copyright  2021 Arnes
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class externalcheckboxfilter extends \user_filter_checkbox {
    /**
     * Returns the condition to be used with SQL where
     * @param array $data filter settings
     * @return array sql string and $params array
     */
    public function get_sql_filter($data) {
        // Do not return any SQL; the query is set up from the global $SESSION variable elsewhere.
        return array('', array());
    }
}