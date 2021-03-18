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
    /**
     * Constructor for the filtering class
     * @param array $fieldnames array of visible user fields
     * @param string $baseurl base url used for submission/return, null if the same of current page
     * @param array $extraparams extra page parameters
     * @param bool $filterbycat limit the amount of returned categories by the users capabilities
     */
    public function __construct($fieldnames = null, $baseurl = null, $extraparams = null, $filterbycat=false) {
        $this->filterbycat = $filterbycat;
        parent::__construct($fieldnames, $baseurl, $extraparams);
    }

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
            if ($this->filterbycat) {
                return new coursecategoryfilter('coursecategory', get_string('coursecategory', 'report_allbackups'),
                    $advanced, 'c.category', \core_course_category::make_categories_list('report/categorybackups:view'));
            } else {
                return new coursecategoryfilter('coursecategory', get_string('coursecategory', 'report_allbackups'),
                    $advanced, 'c.category', \core_course_category::make_categories_list());
            }
        }
        return parent::get_field($fieldname, $advanced);
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

        if ($pluginconfig->mdlbkponly) {
            if ($pluginconfig->enableactivities) {
                return array('automated' => 'automated', 'course' => 'course', 'activity' => 'activity');
            } else {
                return array('automated' => 'automated', 'course' => 'course');
            }
        } else {
            $sql = "SELECT DISTINCT filearea, filearea as name
                FROM {files}
                WHERE filename like '%.mbz' and component <> 'tool_recyclebin' and filearea <> 'draft'";
            return $DB->get_records_sql_menu($sql);
        }
    }
}
