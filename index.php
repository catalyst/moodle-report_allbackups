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

require_once('../../config.php');
require_login();

require_once($CFG->libdir.'/filelib.php');
require_once($CFG->libdir . '/accesslib.php');

// Moodle 3.9 doesn't have all required libs so add some extra ones.
require_once($CFG->dirroot . '/report/allbackups/_autoload.php');
require_once($CFG->dirroot . '/report/allbackups/.extlib/php-enum/Enum.php');
require_once($CFG->dirroot . '/report/allbackups/classes/filteringlib.php');

use ZipStream\Option\Archive;
use ZipStream\ZipStream;

$delete = optional_param('delete', '', PARAM_TEXT);
$filename = optional_param('filename', '', PARAM_TEXT);
$deleteselected = optional_param('deleteselectedfiles', '', PARAM_TEXT);
$downloadselected = optional_param('downloadallselectedfiles', '', PARAM_TEXT);
$fileids = optional_param('fileids', '', PARAM_TEXT);
$currenttab = optional_param('tab', 'core', PARAM_TEXT);
$contextid = optional_param('contextid', 0, PARAM_INT);

// Define URL params.
$urlparams = array("tab" => $currenttab);
if (!empty($delete)) {
    $urlparams['delete'] = $delete;
}
if (!empty($deleteselected)) {
    $urlparams['deleteselectedfiles'] = $deleteselected;
}
if (!empty($downloadselected)) {
    $urlparams['downloadallselectedfiles'] = $downloadselected;
}
if (!empty($filename)) {
    $urlparams['filename'] = $filename;
}
if (!empty($fileids)) {
    $urlparams['fileids'] = $fileids;
}
if (!empty($contextid)) {
    $urlparams['contextid'] = $contextid;
}

// Define context.
$context = null;
if (!empty($contextid)) {
    $context = context::instance_by_id($contextid);
} else {
    $context = context_system::instance();
}


$PAGE->set_url(new moodle_url('/report/allbackups/index.php', $urlparams));
$PAGE->set_context($context);
// Set this page as an instance of a category.
if ($context->contextlevel == CONTEXT_COURSECAT) {
    $PAGE->set_category_by_id($context->instanceid);
}
// Set page heading and title.
$PAGE->set_title(get_string('pluginname', 'report_allbackups'));
$PAGE->set_heading(get_string('pluginname', 'report_allbackups'));
// Set page layout as admin.
$PAGE->set_pagelayout('report');
// Check plugin config for category backup management mode.
$pluginconfig = get_config('report_allbackups');
$allowedcourses = array();
$filterbycat = false;
if ($context->contextlevel == CONTEXT_COURSECAT) {
    $filterbycat = true;
    // Add navigation link to the category this page belongs to.
    $PAGE->navbar->ignore_active();
    $PAGE->navbar->add($context->get_context_name(false), new moodle_url('/course/index.php', array(
        'categoryid' => $context->instanceid
    )));
    if (!$pluginconfig->categorybackupmgmt) {
        echo $OUTPUT->header();
        \core\notification::error(get_string('error:categorybackupmgmtmodedisabled', 'report_allbackups'));
        echo $OUTPUT->footer();
        exit;
    }
    if ($pluginconfig->categorybackupmgmtonlyexisting) {
        $allowedcourses = get_courses_under_context($context);
    }
} else if ($context->contextlevel == CONTEXT_SYSTEM) {
    // Add navigation link to the category this page belongs to.
    $PAGE->navbar->ignore_active();
    $PAGE->navbar->add($context->get_context_name(false), new moodle_url('/admin/index.php', array()));
} else {
    echo $OUTPUT->header();
    \core\notification::error(get_string('error:wrongcontext', 'report_allbackups'));
    echo $OUTPUT->footer();
    exit;
}

$backupdest = get_config('backup', 'backup_auto_destination');
if (empty($backupdest) && $currenttab == 'autobackup') {
    print_error(get_string("autobackupnotset", "report_allbackups"));
}

if (($context->contextlevel == CONTEXT_SYSTEM AND has_capability('report/allbackups:delete', $context))
        OR ($context->contextlevel == CONTEXT_COURSECAT AND has_capability('report/categorybackups:delete', $context))) {

    if (!empty($deleteselected) || !empty($delete)) { // Delete action.

        if (empty($fileids)) {
            $fileids = array();
            // First time form submit - get list of ids from checkboxes or from single delete action.
            if (!empty($delete)) {
                // This is a single delete action.
                $fileids[] = $delete;
            } else {
                // Get list of ids from checkboxes.
                $post = data_submitted();
                if ($currenttab == "autobackup") {
                    foreach ($post as $k => $v) {
                        if (preg_match('/^item(.*)/', $k, $m)) {
                            $fileids[] = $v; // Use value (filename) in array.
                        }
                    }
                } else {
                    foreach ($post as $k => $v) {
                        if (preg_match('/^item(\d+)$/', $k, $m)) {
                            $fileids[] = $m[1];
                        }
                    }
                }
            }
            // Display confirmation box - are you really sure you want to delete this file?
            echo $OUTPUT->header();
            $params = array(
                'deleteselectedfiles' => 1,
                'confirm' => 1,
                'fileids' => implode(',', $fileids),
                'tab' => $currenttab,
            );
            if (!empty($contextid)) {
                $params['contextid'] = $contextid;
            }
            $deleteurl = new moodle_url($PAGE->url, $params);
            $deleteurltarget = $CFG->wwwroot . '/report/allbackups/index.php';
            if (!empty($contextid)) {
                $deleteurltarget .= '?contextid='.$context->id;
            }
            $numfiles = count($fileids);
            echo $OUTPUT->confirm(get_string('areyousurebulk', 'report_allbackups', $numfiles),
                $deleteurl, $deleteurltarget);

            echo $OUTPUT->footer();
            exit;
        } else if (optional_param('confirm', false, PARAM_BOOL) && confirm_sesskey()) {
            $count = 0;
            $fileids = explode(',', $fileids);
            foreach ($fileids as $id) {
                if ($currenttab == 'autobackup') {
                    // Check nothing weird passed in filename - protect against directory traversal etc.
                    // Check to make sure this is an mbz file.
                    // Check to make sure the user is allowed to delete this course backup.
                    if ($id == clean_param($id, PARAM_FILE) &&
                            pathinfo($id, PATHINFO_EXTENSION) == 'mbz' &&
                            is_readable($backupdest .'/'. $id) &&
                            (empty($allowedcourses) || is_autobackup_filename_in_course_array($id, $allowedcourses))) {
                        unlink($backupdest .'/'. $id);
                        $event = \report_allbackups\event\autobackup_deleted::create(array(
                            'context' => $context,
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
                    if (!empty($file) &&
                            $fileext == 'mbz' &&
                            has_capability('moodle/course:delete', context::instance_by_id($file->get_contextid()))) {
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
if (!empty($downloadselected) && confirm_sesskey()) {
    if (empty($fileids)) {

        $fileids = array();
        // Raise memory limit - each file is loaded in PHP memory, so this much be larger than the largest backup file.
        raise_memory_limit(MEMORY_HUGE);

        // Initialize zip for saving multiple selected files at once.
        $options = new Archive();
        $options->setSendHttpHeaders(true);
        $zip = new ZipStream('all_backups.zip', $options);

        // Get list of ids from the checked checkboxes.
        $post = data_submitted();

        if ($currenttab == 'autobackup') {
            // Get list of names from the checked backups.
            foreach ($post as $k => $v) {
                if (preg_match('/^item(.*)/', $k, $m)) {
                    $fileids[] = $v; // Use value (filename) in array.
                }
            }

            // Check nothing weird passed in filename - protect against directory traversal etc.
            // Check to make sure this is an mbz file.
            foreach ($fileids as $filename) {

                if ($filename == clean_param($filename, PARAM_FILE) &&
                        pathinfo($filename, PATHINFO_EXTENSION) == 'mbz' &&
                        is_readable($backupdest .'/'. $filename) &&
                        (empty($allowedcourses) || is_autobackup_filename_in_course_array($filename, $allowedcourses))) {
                    $file = $backupdest.'/'.$filename;
                    $filecontents = file_get_contents($file, FILE_USE_INCLUDE_PATH);
                    $zip->addFile($filename, $filecontents);
                } else {
                    \core\notification::add(get_string('couldnotdownloadfile', 'report_allbackups'));
                }
            }
        } else {
            // Get list of ids from the checked backups.
            foreach ($post as $k => $v) {
                if (preg_match('/^item(\d+)$/', $k, $m)) {
                    $fileids[] = $m[1];
                }
            }

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

if ($currenttab == 'autobackup') {
    $filters = array('filename' => 0, 'timecreated' => 0);
} else {
    $filters = array('filename' => 0, 'realname' => 0, 'coursecategory' => 0, 'filearea' => 0, 'timecreated' => 0);
}
if ($currenttab == 'autobackup') {
    $table = new \report_allbackups\output\autobackups_table('autobackups', $context, $allowedcourses);
} else {
    $table = new \report_allbackups\output\allbackups_table('allbackups', $context);
    $table->define_baseurl($PAGE->url);
}

$ufiltering = new \report_allbackups\output\filtering($filters, $PAGE->url, null, $filterbycat);
if (!$table->is_downloading()) {
    // Only print headers if not asked to download data
    // Print the page header.
    $PAGE->set_title(get_string('pluginname', 'report_allbackups'));
    echo $OUTPUT->header();
    $targeturlrelativeendpoint = 'index.php';
    $targeturl = $CFG->wwwroot.'report/allbackups/index.php';
    $targeturltabauto = $targeturl.'?tab=autobackup';
    if ($context->contextlevel != CONTEXT_SYSTEM) {
        $targeturlrelativeendpoint .= '?contextid='.$context->id;
        $targeturl .= '?contextid='.$context->id;
        $targeturltabauto .= '&contextid='.$context->id;
    }
    if (!empty(get_config('backup', 'backup_auto_destination'))) {
        $row = $tabs = array();
        $row[] = new tabobject('core',
            $targeturl,
            get_string('standardbackups', 'report_allbackups'));
        $row[] = new tabobject('autobackup',
            $targeturltabauto,
            get_string('autobackup', 'report_allbackups'));
        $tabs[] = $row;
        print_tabs($tabs, $currenttab);
    }
    if ($currenttab == 'autobackup') {
        echo $OUTPUT->box(get_string('autobackup_description', 'report_allbackups'));
    } else {
        echo $OUTPUT->box(get_string('plugindescription', 'report_allbackups'));
    }
    $ufiltering->display_add();
    $ufiltering->display_active();

    echo '<form action="'.$targeturlrelativeendpoint.'" method="post" id="allbackupsform">';
    echo html_writer::start_div();
    echo html_writer::tag('input', '', array('type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()));
    echo html_writer::tag('input', '', array('type' => 'hidden', 'name' => 'returnto', 'value' => s($PAGE->url->out(false))));
    echo html_writer::tag('input', '', array('type' => 'hidden', 'name' => 'tab', 'value' => $currenttab));
} else {
    // Trigger downloaded event.
    $event = \report_allbackups\event\report_downloaded::create();
    $event->trigger();
}
if ($currenttab == 'autobackup') {
    // Get list of files from backup.
    $table->adddata($ufiltering);
} else {
    list($extrasql, $params) = $ufiltering->get_sql_filter();
    $fields = 'f.id, f.contextid, f.component, f.filearea, f.filename, f.userid, f.filesize, f.timecreated, f.filepath, f.itemid, ';
    $fields .= get_all_user_name_fields(true, 'u');
    $from = '{files} f JOIN {user} u on u.id = f.userid';
    if (strpos($extrasql, 'c.category') !== false OR $context->contextlevel != CONTEXT_SYSTEM) {
        // Category filter included, Join with course table.
        $from .= ' JOIN {context} cx ON cx.id = f.contextid AND cx.contextlevel = '.CONTEXT_COURSE.
                 ' JOIN {course} c ON c.id = cx.instanceid';
    }
    $where = "f.filename like '%.mbz' and f.component <> 'tool_recyclebin' and f.filearea <> 'draft'";
    if ($context->contextlevel != CONTEXT_SYSTEM) {
        $where .= " and cx.path like :cxpath";
        $params['cxpath'] = $context->path.'%';
    }
    if (!empty($extrasql)) {
        $where .= " and ".$extrasql;
    }

    $table->set_sql($fields, $from, $where, $params);
    $table->out(40, true);
}

if (!$table->is_downloading()) {

    echo html_writer::tag('input', "", array('name' => 'deleteselectedfiles', 'type' => 'submit',
        'id' => 'deleteallselected', 'class' => 'btn btn-secondary',
        'value' => get_string('deleteselectedfiles', 'report_allbackups')));
    echo html_writer::tag('input', "", array('name' => 'downloadallselectedfiles', 'style' => 'margin: 10px', 'type' => 'submit',
        'id' => 'downloadallselected', 'class' => 'btn btn-secondary',
        'value' => get_string('downloadallselectedfiles', 'report_allbackups')));

    echo html_writer::end_div();
    echo html_writer::end_tag('form');
    $event = \report_allbackups\event\report_viewed::create();
    $event->trigger();
    echo $OUTPUT->footer();
}