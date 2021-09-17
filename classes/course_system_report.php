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

use core_reportbuilder\local\entities\course;
use core_reportbuilder\local\helpers\database;
use core_reportbuilder\system_report;
use core_reportbuilder\local\report\column;
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

        $entity = new course();

        $coursetablealias = $entity->get_table_alias('course');
        $param = database::generate_param_name();

        // Set the main report table.
        $this->set_main_table('course', $coursetablealias);
        // Add course entity to the report.
        $this->add_entity($entity);
        // Add a base condition to hide the site course.
        $this->add_base_condition_sql("$coursetablealias.id <> :$param", [$param => SITEID]);

        // Checkbox column.
        $column = (new column(
            'id',
            new lang_string('selectbackup', 'report_allbackups'),
            $entity->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_BOOLEAN)
            ->set_is_sortable(true)
            ->add_field("$coursetablealias.id")
            ->add_callback(static function () {

                return html_writer::empty_tag('input', array('type' => 'checkbox', 'name' => 'checkbox', 'value' => 0));
            });

        $this->add_column($column);
        
        // Add other columns to the report.
        $columns = [
            'course:coursefullnamewithlink',
            'course:shortname',
            'course:category',
            'course:format',
            'course:startdate',
            'course:enddate',
            'course:visible',
        ];
        $this->add_columns_from_entities($columns);

        // Add filters to our report.
        $filters = [
            'course:fullname',
            'course:shortname',
            'course:category',
            'course:format',
            'course:startdate',
            'course:enddate',
            'course:visible',
        ];

        $this->add_filters_from_entities($filters);

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

    /**
     * Ensure we can view the report
     *
     * @return bool
     */
    public function addcheckbox($id)
    {
        echo html_writer::empty_tag('input', array('type' => 'checkbox', 'name' => 'fieldname'));
    }
}
