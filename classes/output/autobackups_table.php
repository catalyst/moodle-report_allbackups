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
 * Auto backups table.
 *
 * @package    report_allbackups
 * @copyright  2020 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_allbackups\output;

defined('MOODLE_INTERNAL') || die();

use moodle_url, html_writer, context_system, RecursiveDirectoryIterator, RecursiveIteratorIterator;

require_once($CFG->libdir . '/tablelib.php');

/**
 * Table to display automated backups stored on disk.
 *
 * @package    report_allbackups
 * @copyright  2020 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class autobackups_table extends \flexible_table {
    /**
     * autobackups_table constructor.
     *
     * @param string $uniqueid
     * @param context|null $context determine which backups can be viewed and/or managed by the user
     * @throws \coding_exception
     */
    public function __construct($uniqueid, $context=null) {
        global $PAGE, $OUTPUT;
        parent::__construct($uniqueid);
        $url = $PAGE->url;
        $url->param('tab', 'autobackup');
        $this->define_baseurl($url);
        $this->pageable(true);

        // Set Download flag so we can check it before defining columns/headers to show.
        $this->is_downloading(optional_param('download', '', PARAM_ALPHA), 'allbackups');

        if ($context) {
            $this->context = $context;
        } else {
            $this->context = \context_system::instance();
        }
        $this->useridfield = "userid";

        // Define the list of columns to show.
        $columns = array();
        $headers = array();

        // Add selector column if not downloading report.
        if (!$this->is_downloading()) {
            // Add selector column to report.
            $columns[] = 'selector';

            $options = [
                'id' => 'check-items',
                'name' => 'check-items',
                'value' => 1,
            ];
            $mastercheckbox = new \core\output\checkbox_toggleall('items', true, $options);

            $headers[] = $OUTPUT->render($mastercheckbox);
        }
        $columns = array_merge($columns, array('filename', 'size', 'timemodified'));
        $headers = array_merge($headers, array(get_string('name'), get_string('size'), get_string('date')));

        // Add actions column if not downloading this report.
        if (!$this->is_downloading()) {
            array_push($columns, 'action');
            array_push($headers, get_string('action'));
        }

        $this->define_columns($columns);
        $this->define_headers($headers);
        $this->sortable(true);
        $this->no_sorting('action');
        $this->no_sorting('selector');
        $this->setup();
    }

    /**
     * Helper function to add data to table.
     * Implements custom sort/pagination as we don't use sql to build this table.
     * @param context $context current context
     * 
     * @throws \dml_exception
     */
    public function adddata($context) {
        global $SESSION, $DB;

        $rows = array();
        $backupdest = get_config('backup', 'backup_auto_destination');
        $directory = new RecursiveDirectoryIterator($backupdest);
        $iterator = new RecursiveIteratorIterator($directory);

        if ($context->contextlevel != CONTEXT_SYSTEM) {
            $sql = "SELECT instanceid as id, instanceid
                    FROM {context}
                    WHERE contextlevel = ".CONTEXT_COURSE." AND path LIKE '".$context->path."%'";
            $allowedcourses = $DB->get_records_sql_menu($sql);
        }

        foreach ($iterator as $file) {
            // Sanity check and only include .mbz files.
            if ($file->isFile() && $file->getExtension() == 'mbz') {
                $filename = $file->getFilename();

                // Filter out backups from other courses (ones that the user is not a manager of).
                if ($context->contextlevel != CONTEXT_SYSTEM && !$this->filter_context($filename, $allowedcourses)) {
                    continue;
                }

                // Check against filename filters.
                if (!$this->filter_filename($filename)) {
                    continue;
                }
                $row = new \stdClass();
                $row->filename = $filename;
                $row->timemodified = $file->getMTime();
                $row->size = $file->getSize();

                // Check against timemodified filters.
                if (!$this->filter_timemodified($row->timemodified)) {
                    continue;
                }

                $rows[] = $row;
            }
        }
        // Sort based on fields.
        $tsort = optional_param('tsort', '', PARAM_TEXT);
        $tdir = optional_param('tdir', 0, PARAM_INT);
        if (!empty($tsort) && array_key_exists($tsort, $this->get_sort_columns())) {
            $sort = array_column($rows, $tsort);
            array_multisort($sort, $tdir, $rows);
        }

        $this->totalrows = count($rows);

        if (!$this->is_downloading()) {
            // Only add rows that we want based on page.
            $rowstoprint = array_slice($rows, $this->get_page_start(), $this->get_page_size());
        } else {
            $rowstoprint = $rows;
        }
        $this->format_and_add_array_of_rows($rowstoprint);
    }

    /**
     * Display action row.
     *
     * @param \stdClass $row
     * @return string
     */
    public function col_action($row) {
        global $USER;
        $fileurl = moodle_url::make_pluginfile_url(
            $context->id,
            'report_allbackups',
            'autobackups',
            null,
            '/',
            $row->filename,
            true
        );
        $output = \html_writer::link($fileurl, get_string('download'));

        if (has_capability('moodle/restore:restorecourse', $this->context)) {
            $params = array();
            $params['filename'] = $row->filename;
            $restoreurl = new moodle_url('/report/allbackups/restorehandler.php', $params);

            $output .= ' | '. html_writer::link($restoreurl, get_string('restore'));
        }

        if (has_capability('report/allbackups:delete', $this->context)) {
            $params = array('delete' => $row->filename, 'filename' => $row->filename, 'tab' => 'autobackup');
            $deleteurl = new moodle_url('/report/allbackups/index.php', $params);
            $output .= ' | '. html_writer::link($deleteurl, get_string('delete'));
        }
        return $output;

    }

    /**
     * Display size row.
     *
     * @param \stdClass $row
     * @return \lang_string|string
     */
    public function col_size($row) {
        return display_size($row->size);
    }

    /**
     * Display timemodified.
     *
     * @param \stdClass $row
     * @return string
     */
    public function col_timemodified($row) {
        return userdate($row->timemodified);
    }

    /**
     * Function to display the checkbox for bulk actions.
     *
     * @param \stdClass $row the data from the db containing all fields from the current row.
     * @return string
     */
    public function col_selector($row) {
        global $OUTPUT;
        if ($this->is_downloading()) {
            return '';
        }
        $options = [
            'id' => 'item'.$row->filename,
            'name' => 'item'.$row->filename,
            'value' => $row->filename,
        ];
        $itemcheckbox = new \core\output\checkbox_toggleall('items', false, $options);
        return $OUTPUT->render($itemcheckbox);
    }

    /**
     * Function to filter results using user context.
     *
     * @param string $filename
     * @param array $allowedcourses
     * @return bool|int
     */
    private function filter_context($filename, $allowedcourses) {
        $courseid = explode("-", $filename)[3];
        return array_key_exists($courseid, $allowedcourses);
    }

    /**
     * Function to filter results using the filename.
     *
     * @param string $filename
     * @return bool|int
     */
    private function filter_filename($filename) {
        global $SESSION;
        $found = true;

        if (!empty($SESSION->user_filtering['filename'])) {
            foreach ($SESSION->user_filtering['filename'] as $filter) {
                $found = $this->filter_filename_helper($filter['operator'], $filter['value'], $filename);
            }
        }
        return $found;
    }

    /**
     * Helper function to select appropriate regex for filter.
     *
     * @param string $operator
     * @param string $value
     * @param string $filename
     * @return false|int
     */
    private function filter_filename_helper($operator, $value, $filename) {
        // Filter rows based on any filters set.
        switch ($operator) {
            case 0: // Contains.
                $regex = "/" . $value . "/";
                break;
            case 1: // Does not contain.
                $regex = "/^((?!" . $value . ").)*$/";
                break;
            case 2: // Equal to.
                $regex = "/^" . $value . "$/";
                break;
            case 3: // Starts with.
                $regex = "/^" . $value . "/";
                break;
            case 4: // Ends with.
                $regex = "/" . $value . "$/";
                break;
            case 5: // Empty.
                $regex = "/^$/";
                break;
        }
        return preg_match($regex, $filename);
    }

    /**
     * Filter for timemodified.
     *
     * @param int $timemodified
     * @return bool
     */
    private function filter_timemodified($timemodified) {
        global $SESSION;
        $found = true;
        if (!empty($SESSION->user_filtering['timecreated'])) {
            foreach ($SESSION->user_filtering['timecreated'] as $filter) {
                $found = $this->filter_timemodified_helper($filter['before'], $filter['after'], $timemodified);
            }
        }

        return $found;
    }

    /**
     * Helper function for filter_timemodified.
     *
     * @param int $before
     * @param int $after
     * @param int $timemodified
     * @return bool
     */
    private function filter_timemodified_helper($before, $after, $timemodified) {
        $found = true;
        if (!empty($before)) { // Only if the before value is not empty.
            if ($before < $timemodified) {
                $found = false;
            }
        }
        if (!empty($after)) {
            if ($after > $timemodified) {
                $found = false;
            }
        }
        return $found;
    }
}
