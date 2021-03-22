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
 * Backups filtering class
 *
 * @package    report_allbackups
 * @copyright  2020 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_allbackups\output;
use report_allbackups\filters\coursecategoryfilter;
use report_allbackups\filters\externalcheckboxfilter;
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/user/filters/lib.php');

/**
 * Class filtering based on core user_filtering class, with extra filter for filename.
 *
 * @package    report_allbackups
 * @copyright  2020 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class filtering extends \user_filtering {
    /** @var array List of field names to exclude from the WHERE SQL query construction */
    private array $excludefields = array(
        "includeactivitybackups",
        "mdlbkponly"
    );

    /**
     * Adds handling for custom fieldnames.
     * @param string $fieldname
     * @param boolean $advanced
     * @return object filter
     */
    public function get_field($fieldname, $advanced) {
        if ($fieldname == 'filename') {
            return new \user_filter_text('filename', get_string('filename', 'report_allbackups'), $advanced, 'filename');
        }
        if ($fieldname == 'timecreated') {
            return new \user_filter_date('timecreated', get_string('date'), $advanced, 'f.timecreated');
        }
        if ($fieldname == 'filearea') {
            return new \user_filter_simpleselect('filearea', get_string('filearea', 'report_allbackups'),
                $advanced, 'f.filearea', $this->getfileareas());
        }
        if ($fieldname == 'coursecategory') {
            return new coursecategoryfilter('coursecategory', get_string('coursecategory', 'report_allbackups'),
                $advanced, 'c.category', \core_course_category::make_categories_list());
        }
        if ($fieldname == 'includeactivitybackups') {
            return new externalcheckboxfilter('includeactivitybackups', get_string('includeactivitybackups', 'report_allbackups'),
                $advanced, '');
        }
        if ($fieldname == 'mdlbkponly') {
            return new externalcheckboxfilter('mdlbkponly', get_string('mdlbkponly', 'report_allbackups'),
                $advanced, '');
        }
        return parent::get_field($fieldname, $advanced);
    }

    /**
     * Returns SQL WHERE statement based on active user filters
     * Excludes the field names set in $this->excludefields
     * @param string $extra sql
     * @param array $params named params (recommended prefix ex)
     * @return array sql string and $params
     */
    public function get_sql_filter($extra='', array $params=null) {
        global $SESSION;

        $sqls = array();
        if ($extra != '') {
            $sqls[] = $extra;
        }
        $params = (array)$params;

        if (!empty($SESSION->user_filtering)) {
            foreach ($SESSION->user_filtering as $fname => $datas) {
                if (!array_key_exists($fname, $this->_fields)
                        OR in_array($fname, $this->excludefields, $strict = true)) {
                    continue; // Filter not used.
                }
                $field = $this->_fields[$fname];
                foreach ($datas as $i => $data) {
                    list($s, $p) = $field->get_sql_filter($data);
                    $sqls[] = $s;
                    $params = $params + $p;
                }
            }
        }

        if (empty($sqls)) {
            return array('', array());
        } else {
            $sqls = implode(' AND ', $sqls);
            return array($sqls, $params);
        }
    }

    /**
     * Helper function to get list of fileareas to use in filter.
     *
     * @return array
     * @throws \dml_exception
     */
    private static function getfileareas() {
        global $DB;
        $pluginconfig = get_config('report_allbackups');
        if ($pluginconfig->mdlbkponly
                OR ($pluginconfig->allowmdlbkponly AND !empty($SESSION->user_filtering['mdlbkponly'][0]['value']))) {
            // Avoid an additional database query.
            if (!empty($SESSION->user_filtering['includeactivitybackups'][0]['value'])) {
                return array('automated' => 'automated', 'course' => 'course', 'activity' => 'activity');
            } else {
                return array('automated' => 'automated', 'course' => 'course');
            }
        } else {
            $sql = "SELECT DISTINCT filearea, filearea AS name
                    FROM {files}
                    WHERE filename LIKE '%.mbz' AND component <> 'tool_recyclebin' AND filearea <> 'draft'";
        }
        return $DB->get_records_sql_menu($sql);
    }
}
