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
 * Library file for report_allbackups
 *
 * @package    report_allbackups
 * @copyright  2020 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Plugin file handler to allow backups to be downloaded on autobackups report.
 *
 * @param stdClass $course the course object
 * @param stdClass $cm the course module object
 * @param context $context the newmodule's context
 * @param string $filearea the name of the file area
 * @param array $args extra arguments (itemid, path)
 * @param bool $forcedownload whether or not force download
 * @param array $options additional options affecting the file serving
 * @return false
 * @throws coding_exception
 * @throws dml_exception
 * @throws moodle_exception
 * @throws require_login_exception
 */
function report_allbackups_pluginfile($course,
        $cm,
        context $context,
        $filearea,
        $args,
        $forcedownload,
        array $options = array()) {

    if ($context->contextlevel != CONTEXT_SYSTEM && $context->contextlevel != CONTEXT_COURSECAT) {
        return false;
    }

    if ($context->contextlevel == CONTEXT_COURSECAT) {
        if (!has_capability('moodle/backup:downloadfile', $context)) {
            return false;
        }
    }

    require_login($course, false, $cm);
    if (!has_capability('report/allbackups:view', $context)) {
        return false;
    }
    // Make sure backup dir is set.
    $backupdest = get_config('backup', 'backup_auto_destination');
    if (empty($backupdest)) {
        return false;
    }

    $filename = implode('/', $args);

    // Check nothing weird passed in filename - protect against directory traversal etc.
    if ($filename != clean_param($filename, PARAM_FILE)) {
        return false;
    }

    // Check to make sure this is an mbz file.
    if (pathinfo($filename, PATHINFO_EXTENSION) != 'mbz') {
        return false;
    }
    $file = $backupdest .'/'. $filename;
    if (is_readable($file)) {
        $dontdie = ($options && isset($options['dontdie']));
        send_file($file, $filename, null , 0, false, $forcedownload, '', $dontdie);
    } else {
        send_file_not_found();
    }
}

/**
 * Extend category navigation to display a link to the category backups.
 *
 * @param navigation_node $settingsnav the navigation object
 * @param context_coursecat $context the context of the current category
 */
function report_allbackups_extend_navigation_category_settings(navigation_node $settingsnav, context_coursecat $context) {
    global $CFG, $PAGE, $USER;
    if (!$PAGE->category OR $USER->id <= 1) {
        // Only add this settings item on non-site category pages.
        return;
    }
    if (!has_capability('report/categorybackups:view', $context)) {
        // Require this capability on the course category context.
        return;
    }
    $config = get_config('report_allbackups');
    if (!$config->categorybackupmgmt) {
        // Require category backup management mode to be enabled in settings.
        return;
    }
    // Add category settings node.
    $settingsnav->add(
        get_string('pluginname', 'report_allbackups'),
        new moodle_url('/report/allbackups/categorybackups.php', array("contextid" => $context->id)),
        navigation_node::NODETYPE_LEAF,
        'categorybackups',
        'categorybackups',
        new pix_icon('i/files', 'files')
    );
}
