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
 * Filtering helper functions.
 *
 * @package    report_allbackups
 * @copyright  2021 Arnes
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Fetch an associative array of courses that are under the $context->path
 *
 * @param context $context The context under which the courses should reside
 * @return array Array of course IDs
 */
function get_courses_under_context($context) {
    global $DB;
    if (empty($context)) {
        return array();
    }
    if ($context->contextlevel == CONTEXT_COURSE) {
        return array("$context->instanceid" => $context->instanceid);
    }
    if ($context->contextlevel != CONTEXT_SYSTEM AND $context->contextlevel != CONTEXT_COURSECAT) {
        return array();
    }
    $sql = "SELECT instanceid as id, instanceid
        FROM {context}
        WHERE contextlevel = ".CONTEXT_COURSE." AND path LIKE '".$context->path."%'";
    $allowedcourses = $DB->get_records_sql_menu($sql);
    return $allowedcourses;
}

/**
 * Parse the Moodle backup filename, get its course ID number and check
 * if it is included in the provided array
 *
 * @param string $filename Filename of the automatic backup file
 * @param array $array Array of course IDs to look in
 * @return bool|int
 */
function is_autobackup_filename_in_course_array($filename, $array) {
    $courseid = explode("-", $filename)[3];
    return array_key_exists($courseid, $array);
}