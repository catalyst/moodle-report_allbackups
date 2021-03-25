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
 * Plugin administration pages are defined here.
 *
 * @package     report_allbackups
 * @category    admin
 * @copyright   2020 Catalyst IT
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    if ($ADMIN->fulltree) {
        $settings->add(new admin_setting_configcheckbox(
            'report_allbackups/categorybackupmgmt',
            new lang_string('categorybackupmgmt', 'report_allbackups'),
            new lang_string('categorybackupmgmt_desc', 'report_allbackups'),
            0
        ));

        $settings->add(new admin_setting_configcheckbox(
            'report_allbackups/categorybackupmgmtonlyexisting',
            new lang_string('categorybackupmgmtonlyexisting', 'report_allbackups'),
            new lang_string('categorybackupmgmtonlyexisting_desc', 'report_allbackups'),
            0
        ));

        $settings->hide_if(
            'report_allbackups/categorybackupmgmt',
            'report_allbackups/categorybackupmgmtonlyexisting',
            'notchecked'
        );
    }
}

$ADMIN->add('reports', new admin_externalpage('reportallbackups_report', get_string('pluginname', 'report_allbackups'),
    "$CFG->wwwroot/report/allbackups/index.php", 'report/allbackups:view'));
