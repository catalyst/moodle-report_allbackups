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
 * All backups table.
 *
 * @package    report_allbackups
 * @copyright  2020 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_allbackups\output;

defined('MOODLE_INTERNAL') || die();

use moodle_url, html_writer, context_system;

require_once($CFG->libdir . '/tablelib.php');

/**
 * Table to display list backups.
 *
 * @package    report_allbackups
 * @copyright  2020 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class allbackups_table extends \table_sql {

    /** @var context $context holds the current context */
    public $context;

    /**
     * Constructor
     * @param int $uniqueid All tables have to have a unique id, this is used
     *      as a key when storing table properties like sort order in the session.
     * @param context $context The current context to be used.
     */
    public function __construct($uniqueid, $context) {
        global $OUTPUT;
        parent::__construct($uniqueid);

        if (!optional_param('downloadallselectedfiles', 0, PARAM_ALPHA)) {
            // Set Download flag so we can check it before defining columns/headers to show.
            // Don't set if downloading files.
            $this->is_downloading(optional_param('download', '', PARAM_ALPHA), 'allbackups');
        }

        $this->context = $context;
        $this->useridfield = 'userid';

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

        $columns = array_merge($columns, array('component', 'filearea', 'filename', 'filesize', 'fullname', 'timecreated'));
        $headers = array_merge($headers, array(get_string('component', 'report_allbackups'),
                         get_string('filearea', 'report_allbackups'),
                         get_string('name'),
                         get_string('size'),
                         get_string('user'),
                         get_string('date')));

        // Add actions column if not downloading this report.
        if (!$this->is_downloading()) {
            array_push($columns, 'action');
            array_push($headers, get_string('action'));
        }
        $this->define_columns($columns);
        $this->define_headers($headers);
        $this->no_sorting('action');
        $this->no_sorting('selector');
        $this->initialbars(false);
    }

    /**
     * Function to display the checkbox for bulk actions.
     *
     * @param object $row the data from the db containing all fields from the current row.
     * @return string
     */
    public function col_selector($row) {
        global $OUTPUT;
        if ($this->is_downloading()) {
            return '';
        }
        $options = [
            'id' => 'item'.$row->id,
            'name' => 'item'.$row->id,
            'value' => $row->id,
        ];
        $itemcheckbox = new \core\output\checkbox_toggleall('items', false, $options);
        return $OUTPUT->render($itemcheckbox);
    }

    /**
     * Function to display the available actions for each record.
     *
     * @param object $row the data from the db containing all fields from the current row.
     * @return string
     */
    public function col_action($row) {
        $fileurl = moodle_url::make_pluginfile_url(
            $row->contextid,
            $row->component,
            $row->filearea,
            null,
            $row->filepath,
            $row->filename,
            true
        );
        $output = \html_writer::link($fileurl, get_string('download'));

        if (has_capability('moodle/restore:restorecourse', $this->context)) {
            $params = array();
            $params['action'] = 'choosebackupfile';
            $params['filename'] = $row->filename;
            $params['filepath'] = $row->filepath;
            $params['component'] = $row->component;
            $params['filearea'] = $row->filearea;
            $params['filecontextid'] = $row->contextid;
            $params['contextid'] = $this->context->id;
            $params['itemid'] = $row->itemid;
            $restoreurl = new moodle_url('/backup/restorefile.php', $params);

            $output .= ' | '. html_writer::link($restoreurl, get_string('restore'));
        }

        if (($this->context->contextlevel == CONTEXT_SYSTEM AND has_capability('report/allbackups:delete', $this->context))
                OR ($this->context->contextlevel == CONTEXT_COURSECAT AND has_capability('report/categorybackups:delete', $this->context))) {
            $params = array('delete' => $row->id, 'filename' => $row->filename, 'contextid' => $this->context->id);
            $deleteurl = new moodle_url('/report/allbackups/index.php', $params);
            $output .= ' | '. html_writer::link($deleteurl, get_string('delete'));
        }
        return $output;
    }

    /**
     * Function to display the human readable filesize of this file.
     *
     * @param object $row the data from the db containing all fields from the current row.
     * @return string
     */
    public function col_filesize($row) {
        return display_size($row->filesize);
    }

    /**
     * Function to display the human readable time this file was created.
     *
     * @param object $row the data from the db containing all fields from the current row.
     * @return string
     */
    public function col_timecreated($row) {
        return userdate($row->timecreated);
    }
}
