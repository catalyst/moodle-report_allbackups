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
 * A report to display the all backup files on the site.
 *
 * @package    report_allbackups
 * @copyright  2020 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
use ZipStream\Option\Archive;
use ZipStream\ZipStream;
use core_reportbuilder\system_report_factory;
use report_allbackups\course_system_report;
use core_reportbuilder\table\system_report_table;

require_once('../../config.php');
require_login();
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir. '/filestorage/file_storage.php');

$PAGE->set_url(new moodle_url('/report/allbackups/index.php'));
$PAGE->set_context(context_system::instance());
$PAGE->requires->js('/report/allbackups/assets/allbackups.js');

$delete = optional_param('delete', '', PARAM_TEXT);
$filename = optional_param('filename', '', PARAM_TEXT);
$deleteselected = optional_param('deleteselectedfiles', '', PARAM_TEXT);
$downloadselected = optional_param('downloadallselectedfiles', '', PARAM_TEXT);
$currenttab = optional_param('tab', 'core', PARAM_TEXT);
$fileids =  optional_param_array('id', null, PARAM_INT);
$fileidsstring =  optional_param('fileids', null, PARAM_TEXT);

// admin_externalpage_setup('reportallbackups', '', array('tab' => $currenttab), '', array('pagelayout' => 'report'));

$backupdest = get_config('backup', 'backup_auto_destination');
if (empty($backupdest) && $currenttab == 'autobackup') {
    print_error(get_string("autobackupnotset", "report_allbackups"));
}

$context = context_system::instance();
if (has_capability('report/allbackups:delete', $context)) {
    if ($deleteselected) { // Delete action.

        if ($currenttab == "autobackup") {
            $post = data_submitted();
            foreach ($post as $k => $v) {
                if (preg_match('/^item(.*)/', $k, $m)) {
                    $fileids[] = $v; // Use value (filename) in array.
                }
            }
        }

        if (!empty($fileids)) {
            // Display confirmation box - are you really sure you want to delete this file?
            echo $OUTPUT->header();
            $params = array('deleteselectedfiles' => 1, 'confirm' => 1, 'fileids' => implode(',', $fileids), 'tab' => $currenttab);
            $deleteurl = new moodle_url($PAGE->url, $params);
            $numfiles = count($fileids);
            echo $OUTPUT->confirm(get_string('areyousurebulk', 'report_allbackups', $numfiles),
                $deleteurl, $CFG->wwwroot . '/report/allbackups/index.php');
            echo $OUTPUT->footer();
            exit;
        } else if (optional_param('confirm', false, PARAM_BOOL) && confirm_sesskey()) {
            $count = 0;
            $fileids = explode(',', $fileidsstring);
            foreach ($fileids as $id) {
                if ($currenttab == 'autobackup') {
                    // Check nothing weird passed in filename - protect against directory traversal etc.
                    // Check to make sure this is an mbz file.
                    if ($id == clean_param($id, PARAM_FILE) &&
                        pathinfo($id, PATHINFO_EXTENSION) == 'mbz' &&
                        is_readable($backupdest .'/'. $id)) {
                        unlink($backupdest .'/'. $id);
                        $event = \report_allbackups\event\autobackup_deleted::create(array(
                            'context' => context_system::instance(),
                            'objectid' => null,
                            'other' => array('filename' => $id)));
                        $event->trigger();
                        $count++;
                    } else {
                        \core\notification::add(get_string('couldnotdeletefile', 'report_allbackups', $id));
                    }
                } else {
                    $fs = new file_storage();
                    $file = $fs->get_file_by_id((int)$id);
                    $fileext = pathinfo($file->get_filename(), PATHINFO_EXTENSION);
                    // Make sure the file exists, and it is a backup file we are deleting.
                    if (!empty($file) && $fileext == 'mbz') {
                        $file->delete();
                        $event = \report_allbackups\event\backup_deleted::create(array(
                            'context' => context::instance_by_id($file->get_contextid()),
                            'objectid' => $file->get_id(),
                            'other' => array('filename' => $file->get_filename())));
                        $event->trigger();
                        $count++;
                    } else {
                        \core\notification::add(get_string('couldnotdeletefile', 'report_allbackups', $id));
                    }

                }
            }
            \core\notification::add(get_string('filesdeleted', 'report_allbackups', $count), \core\notification::SUCCESS);
        }
    }
}

// Triggers when "Download all select files" is clicked.
if (!empty($downloadselected)) {
    // Triggers when "Download all select files" is clicked.

    // Raise memory limit - each file is loaded in PHP memory, so this much be larger than the largest backup file.
    raise_memory_limit(MEMORY_HUGE);

    // Initialize zip for saving multiple selected files at once.
    $options = new Archive();
    $options->setSendHttpHeaders(true);
    $zip = new ZipStream('all_backups.zip', $options);

    if ($currenttab == 'autobackup') {
        // Get list of names from the checked backups.
        $post = data_submitted();
        foreach ($post as $k => $v) {
            if (preg_match('/^item(.*)/', $k, $m)) {
                $fileids[] = $v; // Use value (filename) in array.
            }
        }
    }
    if (!empty($fileids)) {
 
        if ($currenttab == 'autobackup') {
       
            // Check nothing weird passed in filename - protect against directory traversal etc.
            // Check to make sure this is an mbz file.
            foreach ($fileids as $filename) {

                if ($filename == clean_param($filename, PARAM_FILE) &&
                    pathinfo($filename, PATHINFO_EXTENSION) == 'mbz' &&
                    is_readable($backupdest .'/'. $filename)) {
                        $file = $backupdest.'/'.$filename;
                        $filecontents = file_get_contents($file, FILE_USE_INCLUDE_PATH);
                        $zip->addFile($filename, $filecontents);
                } else {
                    \core\notification::add(get_string('couldnotdownloadfile', 'report_allbackups'));
                }
            }
            $zip->finish();
            exit;
        } else {
            if (!empty($fileids)) {
                // Check nothing weird passed in filename - protect against directory traversal etc.
                // Check to make sure this is an mbz file.
                foreach ($fileids as $id) {
                    // Translate the file id into file name / contents.
                    $fs = new file_storage();
                    $file = $fs->get_file_by_id((int)$id);
                    $fileext = pathinfo($file->get_filename(), PATHINFO_EXTENSION);

                    // Make sure the file exists, and it is a backup file we are downloading.
                    if (!empty($file) && $fileext == 'mbz') {
                        $zip->addFile($file->get_filename(), $file->get_content());
                    } else {
                        \core\notification::add(get_string('couldnotdownloadfile', 'report_allbackups'));
                    }
                }
            }
            $zip->finish();
            exit;
        }
    }
}

if ($currenttab == 'autobackup') {
    $filters = array('filename' => 0, 'timecreated' => 0);
} else {
    $filters = array('filename' => 0, 'realname' => 0, 'coursecategory' => 0, 'filearea' => 0, 'timecreated' => 0);
}
if ($currenttab == 'autobackup') {
    $table = new \report_allbackups\output\autobackups_table('autobackups');
} else {
    $report = system_report_factory::create(course_system_report::class, context_system::instance());
    $table = system_report_table::create(3, []);
    $table->define_baseurl($PAGE->url);
}

$ufiltering = new \report_allbackups\output\filtering($filters, $PAGE->url);
if (!$table->is_downloading()) {
    // Only print headers if not asked to download data
    // Print the page header.
    $PAGE->set_title(get_string('pluginname', 'report_allbackups'));
    echo $OUTPUT->header();
    if (!empty(get_config('backup', 'backup_auto_destination'))) {
        $row = $tabs = array();
        $row[] = new tabobject('core',
            $CFG->wwwroot.'/report/allbackups',
            get_string('standardbackups', 'report_allbackups'));
        $row[] = new tabobject('autobackup',
            $CFG->wwwroot.'/report/allbackups/index.php?tab=autobackup',
            get_string('autobackup', 'report_allbackups'));
        $tabs[] = $row;
        print_tabs($tabs, $currenttab);
    }
    if ($currenttab == 'autobackup') {
        echo $OUTPUT->box(get_string('autobackup_description', 'report_allbackups'));
    } else {
        echo $OUTPUT->box(get_string('plugindescription', 'report_allbackups'));
    }

    if ($currenttab == 'autobackup') {
        $ufiltering->display_add();
        $ufiltering->display_active();
    
        echo '<form action="index.php" method="post" id="allbackupsform">';
        echo html_writer::start_div();
        echo html_writer::tag('input', '', array('type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()));
        echo html_writer::tag('input', '', array('type' => 'hidden', 'name' => 'returnto', 'value' => s($PAGE->url->out(false))));
        echo html_writer::tag('input', '', array('type' => 'hidden', 'name' => 'tab', 'value' => $currenttab));
    }

} else {
    // Trigger downloaded event.
    $event = \report_allbackups\event\report_downloaded::create();
    $event->trigger();
}
if ($currenttab == 'autobackup') {
    // Get list of files from backup.
    $table->adddata($ufiltering);
} else {
    echo $report->output();
}

if (!$table->is_downloading()) {

    echo html_writer::tag('input', "", array('name' => 'deleteselectedfiles', 'type' => 'submit',
        'id' => 'deleteallselected', 'class' => 'btn btn-secondary',
        'value' => get_string('deleteselectedfiles', 'report_allbackups')));
    echo html_writer::tag('input', "", array('name' => 'downloadallselectedfiles', 'style' => 'margin: 10px', 'type' => 'submit',
        'id' => 'downloadallselected', 'class' => 'btn btn-secondary',
        'value' => get_string('downloadallselectedfiles', 'report_allbackups')));

    if ($currenttab == 'autobackup') {
        echo html_writer::end_div();
        echo html_writer::end_tag('form');
        $event = \report_allbackups\event\report_viewed::create();
        $event->trigger();
    }

    echo $OUTPUT->footer();
}