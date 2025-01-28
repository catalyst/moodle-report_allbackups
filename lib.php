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

    if ($context->contextlevel != CONTEXT_SYSTEM) {
        return false;
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
 * Creates and sends a ZIP file to the browser using native PHP ZipArchive.
 *
 * @param array  $filepaths Array of files to be compressed. Each element must be an associative array with:
 *                         [
 *                           'filepath' => '/complete/path/to/file',
 *                           'filename' => 'nameInsideZIP.mbz'
 *                         ]
 * @param string $zipname   (Optional) Base name for the ZIP file to download; if not provided,
 *                         it will use a timestamp in the name
 * @throws moodle_exception If ZIP cannot be created or if no files exist
 */
function report_allbackups_download_zip(array $filepaths, string $zipname = '') {
    global $CFG;

    if (empty($filepaths)) {
        throw new moodle_exception('couldnotdownloadfile', 'report_allbackups');
    }

    // If no ZIP name provided, generate one with timestamp.
    if (empty($zipname)) {
        // Format: YYYYmmdd_HHMM_all_backups.zip
        $zipname = date('Ymd_Hi') . '_all_backups.zip';
    }

    // Create a temporary file.
    $tempzip = tempnam(sys_get_temp_dir(), 'moodle_allbackups_');

    $zip = new ZipArchive();
    if ($zip->open($tempzip, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
        throw new moodle_exception('couldnotdownloadfile', 'report_allbackups', '', null,
            'Unable to create temporary ZIP archive.');
    }

    // Add each file to the ZIP.
    foreach ($filepaths as $info) {
        if (!empty($info['filepath']) && !empty($info['filename'])) {
            $localpath = $info['filepath'];
            $nameinzip = $info['filename'];
            if (is_readable($localpath)) {
                $zip->addFile($localpath, $nameinzip);
            }
        }
    }
    $zip->close();

    // Send headers to force download.
    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="' . $zipname . '"');
    header('Content-Length: ' . filesize($tempzip));
    flush();

    readfile($tempzip);
    @unlink($tempzip);
    exit;
}