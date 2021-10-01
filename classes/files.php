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

use context_course;
use context_helper;
use core_course_category;
use core_reportbuilder\local\entities\user;
use core_reportbuilder\local\filters\boolean_select;
use core_reportbuilder\local\filters\course_selector;
use core_reportbuilder\local\filters\date;
use core_reportbuilder\local\filters\select;
use core_reportbuilder\local\filters\text;
use core_reportbuilder\local\helpers\custom_fields;
use core_reportbuilder\local\helpers\format;
use core_reportbuilder\local\report\column;
use core_reportbuilder\local\report\filter;
use core_reportbuilder\local\entities\base;
use html_writer;
use lang_string;
use stdClass;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/course/lib.php');

/**
 * Course entity class implementation
 *
 * This entity defines all the course columns and filters to be used in any report.
 *
 * @package     core_reportbuilder
 * @copyright   2021 Sara Arjona <sara@moodle.com> based on Marina Glancy code.
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class files extends base {

    /**
     * Database tables that this entity uses and their default aliases.
     *
     * @return array
     */
    protected function get_default_table_aliases(): array {
        return [
            'files' => 'f',
        ];
    }

    /**
     * The default machine-readable name for this entity that will be used in the internal names of the columns/filters.
     *
     * @return string
     */
    public function get_default_entity_name(): string {
        return 'files';
    }

    /**
     * The default title for this entity in the list of columns/filters in the report builder.
     *
     * @return lang_string
     */
    protected function get_default_entity_title(): lang_string {
        return new lang_string('allbackups', 'report_allbackups');
    }

    /**
     * Initialise the entity, adding all course and custom course fields
     *
     * @return base
     */
    public function initialise(): base {
        return $this;
    }
}
