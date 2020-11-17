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
 * Plugin strings are defined here.
 *
 * @package     report_allbackups
 * @category    string
 * @copyright   2020 Catalyst IT
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['allbackups:delete'] = 'Delete backups';
$string['allbackups:view'] = 'View all backups report';
$string['areyousurebulk'] = 'Are you sure you want to delete the {$a} file(s) selected?';
$string['autobackup'] = 'Automated backups stored in specified server directory';
$string['autobackup_description'] = 'This report shows all *.mbz (Moodle backup files) stored in the directory specified in the automated backups settings.';
$string['autobackupnotset'] = 'Automated backup destination is not set - you cannot use this function';
$string['component'] = 'Component';
$string['couldnotdeletefile'] = 'The file with id: {$a} could not be found';
$string['coursecategory'] = 'Course category';
$string['deleteselectedfiles'] = 'Delete selected files';
$string['eventautobackupdeleted'] = 'An automated backup file was deleted';
$string['eventbackupdeleted'] = 'A backup file was deleted';
$string['eventreportdownloaded'] = 'All backups report downloaded';
$string['eventreportviewed'] = 'All backups report viewed';
$string['filearea'] = 'File area';
$string['filename'] = 'File name';
$string['filesdeleted'] = '{$a} file(s) were deleted';
$string['plugindescription'] = 'This report shows all *.mbz (Moodle backup files) on your site, please note that after deleting a file, Moodle can take up to 4 days before removing the file from disk storage.';
$string['pluginname'] = 'All backups';
$string['privacy:metadata'] = 'The all backups report plugin does not store any personal data';
$string['standardbackups'] = 'Standard backups';
