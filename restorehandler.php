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
 * A helper to create a stored file for the restore process to use.
 *
 * @package    report_allbackups
 * @copyright  2020 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');

$filename = required_param('filename', PARAM_FILE);
require_login();
require_all_capabilities(array('report/allbackups:view', 'moodle/restore:restorecourse'), context_system::instance());

// Create file based on filename.
// Make sure backup dir is set.
$backupdest = get_config('backup', 'backup_auto_destination');
if (empty($backupdest) || pathinfo($filename, PATHINFO_EXTENSION) != 'mbz') {
    redirect($CFG->wwwroot);
}

$file = $backupdest . '/' . $filename;
if (is_readable($file)) {
    $fs = get_file_storage();
    $record = (object)[
        'filearea' => 'draft',
        'component' => 'user',
        'filepath' => '/',
        'itemid' => file_get_unused_draft_itemid(),
        'license' => $CFG->sitedefaultlicense,
        'author' => '',
        'filename' => $filename,
        'contextid' => \context_user::instance($USER->id)->id,
        'userid' => $USER->id,
    ];
    $storedfile = $fs->create_file_from_pathname($record, $file);

    $params = array();
    $params['action'] = 'choosebackupfile';
    $params['filename'] = $filename;
    $params['filepath'] = '/';
    $params['component'] = 'user';
    $params['filearea'] = 'draft';
    $params['filecontextid'] = \context_user::instance($USER->id)->id;
    $params['contextid'] = \context_user::instance($USER->id)->id;
    $params['itemid'] = $record->itemid;
    $restoreurl = new moodle_url('/backup/restorefile.php', $params);
    redirect($restoreurl);
}

redirect($CFG->wwwroot);
