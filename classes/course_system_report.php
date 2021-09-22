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

declare(strict_types=1);

namespace report_allbackups;

use core_reportbuilder\local\filters\text;
use core_reportbuilder\local\filters\date;
use report_allbackups\files;
use core_reportbuilder\local\helpers\database;
use core_reportbuilder\system_report;
use core_reportbuilder\local\report\column;
use core_reportbuilder\local\entities\user;
use core_reportbuilder\local\report\filter;
use context_helper;
use lang_string;
use html_writer;

require_once($CFG->libdir . '/adminlib.php');


/**
 * System report for testing the course entity
 *
 * @package    report_allbackups
 * @copyright  2021 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class course_system_report extends system_report {

    /**
     * Initialise the report
     */
    protected function initialise(): void {

        global $USER;
        $entityfiles = new files();
        $entityuser = new user();

        $entituseralias = $entityuser->get_table_alias('user');
        $filestablealias = $entityfiles->get_table_alias('files');

        $backups = database::generate_param_name();
        $userid = database::generate_param_name();
        $filesize = database::generate_param_name();

        // Set the main report table.
        $this->set_main_table('files', $filestablealias);

        // Add files entity to the report.
        $this->add_entity($entityfiles);

        // Join the user table and the files table together.
        $this->add_entity($entityuser->add_join(
            "LEFT JOIN {user} {$entituseralias} ON {$entituseralias}.id = {$filestablealias}.userid"
        ));

        // Only show backups that belong to the logged in user's backups greater than 0 bits.
        $this->add_base_condition_sql(
            "$filestablealias.component = :$backups
            AND $filestablealias.userid = :$userid
            AND $filestablealias.filesize > :$filesize",
            [$backups => 'backup',
            $userid => $USER->id,
            $filesize => 0]
        );



        //
        // Create columns.
        //

        // Checkbox column.
        $column = (new column(
            'id',
            new lang_string('selectbackup', 'report_allbackups'),
            $entityfiles->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_is_sortable(true)
            ->add_field("$filestablealias.id")
            ->add_callback(static function () {
                return html_writer::empty_tag('input', array('type' => 'checkbox', 'name' => 'checkbox', 'value' => 1));
            });

        $this->add_column($column);

        // Component column.
        $column = (new column(
            'component',
            new lang_string('component', 'report_allbackups'),
            $entityfiles->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_is_sortable(true)
            ->add_field("$filestablealias.component");

        $this->add_column($column);

        // File area column.
        $column = (new column(
            'filearea',
            new lang_string('filearea', 'report_allbackups'),
            $entityfiles->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_is_sortable(true)
            ->add_field("$filestablealias.filearea");

        $this->add_column($column);

        // File name column.
        $column = (new column(
            'filename',
            new lang_string('name'),
            $entityfiles->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_is_sortable(true)
            ->add_field("$filestablealias.filename");

        $this->add_column($column);

        // Size column.
        $column = (new column(
            'size',
            new lang_string('size'),
            $entityfiles->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_is_sortable(true)
            ->add_field("$filestablealias.filesize");

        $this->add_column($column);

        // Username column.
        $column = (new column(
            'username',
            new lang_string('fullname'),
            $entityfiles->get_entity_name()
        ))
            ->add_join("LEFT JOIN {user} {$entituseralias} 
                        ON {$entituseralias}.id = {$filestablealias}.userid")
            ->set_is_sortable(true)
            ->add_field("{$entituseralias}.username");

        $this->add_column($column);

        // Time created column.
        $column = (new column(
            'timecreated',
            new lang_string('date'),
            $entityfiles->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_is_sortable(true)
            ->add_field("{$filestablealias}.timecreated");

        $this->add_column($column);

        //
        // Create table filters.
        //

        // Filter by backup name.
        $filter = (new filter(
            text::class,
            'fileselector',
            new lang_string('name'),
            $entityfiles->get_entity_name(),
            "{$filestablealias}.filename"
        ))
            ->add_joins($this->get_joins());

        $this->add_filter($filter);

        // Filter by time created.
        $filter = (new filter(
            date::class,
            'dateselector',
            new lang_string('date'),
            $entityfiles->get_entity_name(),
            "{$filestablealias}.timecreated"
        ))
            ->add_joins($this->get_joins());

        $this->add_filter($filter);

        // Filter by filearea created.
        $filter = (new filter(
            text::class,
            'filearea',
            new lang_string('filearea', 'report_allbackups'),
            $entityfiles->get_entity_name(),
            "{$filestablealias}.filearea"
        ))
            ->add_joins($this->get_joins());

        $this->add_filter($filter);

        // Set report as downloadable and set our custom file name.
        $this->set_downloadable(true, 'moodle_course');
    }

    /**
     * Ensure we can view the report
     *
     * @return bool
     */
    protected function can_view(): bool {
        return true;
    }

}
