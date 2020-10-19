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
     * Contructor
     * @param array $fieldnames array of visible user fields
     * @param string $baseurl base url used for submission/return, null if the same of current page
     * @param array $extraparams extra page parameters
     */
    public function __construct($fieldnames = null, $baseurl = null, $extraparams = null) {
        // Adds custom "filename" to list of filters.
        $fieldnames = array('filename' => 0, 'realname' => 0, 'lastname' => 1, 'firstname' => 1, 'username' => 1, 'email' => 1,
                'city' => 1, 'country' => 1, 'confirmed' => 1, 'suspended' => 1, 'profile' => 1, 'courserole' => 1,
                'anycourses' => 1, 'systemrole' => 1, 'cohort' => 1, 'firstaccess' => 1, 'lastaccess' => 1,
                'neveraccessed' => 1, 'timemodified' => 1, 'nevermodified' => 1, 'auth' => 1, 'mnethostid' => 1,
                'idnumber' => 1);
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
        return parent::get_field($fieldname, $advanced);
    }
}
