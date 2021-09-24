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
            'context' => 'cctx',
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
        return new lang_string('entitycourse', 'core_reportbuilder');
    }

    /**
     * Get custom fields helper
     * @param string $tablefieldalias table alias and the field name (table.field) that matches the customfield instanceid.
     * @param string $entityname name of the entity in the report where we add custom fields.
     * @param string $component component name of full frankenstyle plugin name.
     * @param string $area name of the area (each component/plugin may define handlers for multiple areas).
     * @param int $itemid item id if the area uses them (usually not used).
     * @return custom_fields
     */
    protected function get_custom_fields(): custom_fields {


    }

    /**
     * Initialise the entity, adding all course and custom course fields
     *
     * @return base
     */
    public function initialise(): base {

        $columns = $this->get_all_columns();
        foreach ($columns as $column) {
            $this->add_column($column);
        }

        $filters = $this->get_all_filters();
        foreach ($filters as $filter) {
            $this->add_filter($filter);
        }

        return $this;
    }

    /**
     * Course fields.
     *
     * @return array
     */
    protected function get_course_fields(): array {
        return [
            'fullname' => new lang_string('fullnamecourse'),
            'shortname' => new lang_string('shortnamecourse'),
            'category' => new lang_string('coursecategory'),
            'idnumber' => new lang_string('idnumbercourse'),
            'summary' => new lang_string('coursesummary'),
            'format' => new lang_string('format'),
            'startdate' => new lang_string('startdate'),
            'enddate' => new lang_string('enddate'),
            'visible' => new lang_string('coursevisibility'),
            'groupmode' => new lang_string('groupmode', 'group'),
            'groupmodeforce' => new lang_string('groupmodeforce', 'group'),
            'lang' => new lang_string('forcelanguage'),
            'calendartype' => new lang_string('forcecalendartype', 'calendar'),
            'theme' => new lang_string('forcetheme'),
            'enablecompletion' => new lang_string('enablecompletion', 'completion'),
            'downloadcontent' => new lang_string('downloadcoursecontent', 'course'),
        ];
    }

    /**
     * Check if this field is sortable
     *
     * @param string $fieldname
     * @return bool
     */
    protected function is_sortable(string $fieldname): bool {
        // Some columns can't be sorted, like longtext or images.
        $nonsortable = [
            'summary',
        ];

        return !in_array($fieldname, $nonsortable);
    }

    /**
     * Return appropriate column type for given user field
     *
     * @param string $coursefield
     * @return int
     */
    protected function get_course_field_type(string $coursefield): int {
        switch ($coursefield) {
            case 'downloadcontent':
            case 'enablecompletion':
            case 'groupmodeforce':
            case 'visible':
                $fieldtype = column::TYPE_BOOLEAN;
                break;
            case 'startdate':
            case 'enddate':
                $fieldtype = column::TYPE_TIMESTAMP;
                break;
            case 'summary':
                $fieldtype = column::TYPE_LONGTEXT;
                break;
            case 'category':
            case 'groupmode':
                $fieldtype = column::TYPE_INTEGER;
                break;
            case 'calendartype':
            case 'idnumber':
            case 'format':
            case 'fullname':
            case 'lang':
            case 'shortname':
            case 'theme':
            default:
                $fieldtype = column::TYPE_TEXT;
                break;
        }

        return $fieldtype;
    }

    /**
     * Returns list of all available columns.
     *
     * These are all the columns available to use in any report that uses this entity.
     *
     * @return column[]
     */
    protected function get_all_columns(): array {

        $columns = [];
        global $USER;
        $entityuser = new user();

        $entituseralias = $entityuser->get_table_alias('user');
        $filestablealias = $this->get_table_alias('files');

        // Checkbox column.
        $columns[] = (new column(
            'id',
            new lang_string('selectbackup', 'report_allbackups'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_is_sortable(true)
            ->add_field("$filestablealias.id")
            ->add_callback(static function () {
                return html_writer::empty_tag('input', array('type' => 'checkbox', 'name' => 'checkbox', 'value' => 1));
            });

        // Component column.
        $columns[] = (new column(
            'component',
            new lang_string('component', 'report_allbackups'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_is_sortable(true)
            ->add_field("component");

        // File area column.
        $columns[] = (new column(
            'filearea',
            new lang_string('filearea', 'report_allbackups'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_is_sortable(true)
            ->add_field("$filestablealias.filearea");

        // File name column.
        $columns[] = (new column(
            'filename',
            new lang_string('name'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_is_sortable(true)
            ->add_field("$filestablealias.filename");

        // Size column.
        $columns[] = (new column(
            'size',
            new lang_string('size'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_is_sortable(true)
            ->add_field("$filestablealias.filesize")
            ->add_callback(static function ($value): string {
                $kilobytes = round(((int)$value / 1000), 2);
                return $kilobytes . 'KB';
            });

        // Username column.
        $columns[] = (new column(
            'username',
            new lang_string('username'),
            $this->get_entity_name()
        ))
            ->add_join("LEFT JOIN {user} {$entituseralias} 
                        ON {$entituseralias}.id = {$filestablealias}.userid")
            ->set_is_sortable(true)
            ->add_field("{$entituseralias}.username");

        // Time created column.
        $columns[] = (new column(
            'timecreated',
            new lang_string('date'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_is_sortable(true)
            ->add_field("{$filestablealias}.timecreated")
            ->add_callback(static function ($value): string {
                return userdate($value);
            });

        // // Actions created column.
        // $columns[] = (new column(
        //     'actions',
        //     new lang_string('actions'),
        //     $this->get_entity_name()
        // ))
        // ->add_joins($this->get_joins())
        // ->set_is_sortable(true)
        // ->add_field("
        //                 {$filestablealias}.contextid,
        //                 {$filestablealias}.component,
        //                 {$filestablealias}.filearea,
        //                 {$filestablealias}.filepath,
        //                 {$filestablealias}.filename,
        //                 {$filestablealias}.id,
        //             ")
        // ->add_callback(static function () {

        //     $context = \context_system::instance();
        //     $fileurl = moodle_url::make_pluginfile_url(
        //         $row->contextid,
        //         $row->component,
        //         $row->filearea,
        //         null,
        //         $row->filepath,
        //         $row->filename,
        //         true
        //     );

        //     $output = \html_writer::link($fileurl, get_string('download'));

        //     if (has_capability('moodle/restore:restorecourse', $context)) {
        //         $params = array();
        //         $params['action'] = 'choosebackupfile';
        //         $params['filename'] = $row->filename;
        //         $params['filepath'] = $row->filepath;
        //         $params['component'] = $row->component;
        //         $params['filearea'] = $row->filearea;
        //         $params['filecontextid'] = $row->contextid;
        //         $params['contextid'] = context_system::instance()->id;
        //         $params['itemid'] = $row->id;
        //         $restoreurl = new moodle_url('/backup/restorefile.php', $params);

        //         $output .= ' | '. html_writer::link($restoreurl, get_string('restore'));
        //     }

        //     if (has_capability('report/allbackups:delete', $context)) {
        //         $params = array('delete' => $row->id, 'filename' => $row->filename);
        //         $deleteurl = new moodle_url('/report/allbackups/index.php', $params);
        //         $output .= ' | '. html_writer::link($deleteurl, get_string('delete'));
        //     }

        //     return $output;
        // });

        return $columns;
    }

    /**
     * Returns list of all available filters
     *
     * @return array
     */
    protected function get_all_filters(): array {

        $filestablealias = $this->get_table_alias('files');

        // Filter by backup name.
        $filters[] = (new filter(
            text::class,
            'fileselector',
            new lang_string('name'),
            $this->get_entity_name(),
            "{$filestablealias}.filename"
        ))
            ->add_joins($this->get_joins());

        // Filter by time created.
        $filters[] = (new filter(
            date::class,
            'dateselector',
            new lang_string('date'),
            $this->get_entity_name(),
            "{$filestablealias}.timecreated"
        ))
            ->add_joins($this->get_joins());

        // Filter by filearea created.
        $filters[] = (new filter(
            text::class,
            'filearea',
            new lang_string('filearea', 'report_allbackups'),
            $this->get_entity_name(),
            "{$filestablealias}.filearea"
        ))
            ->add_joins($this->get_joins());

        return $filters;
    }

    /**
     * Gets list of options if the filter supports it
     *
     * @param string $fieldname
     * @return null|array
     */
    protected function get_options_for(string $fieldname): ?array {
        static $cached = [];
        if (!array_key_exists($fieldname, $cached)) {
            $callable = [static::class, 'get_options_for_' . $fieldname];
            if (is_callable($callable)) {
                $cached[$fieldname] = $callable();
            } else {
                $cached[$fieldname] = null;
            }
        }
        return $cached[$fieldname];
    }

    /**
     * List of options for the field groupmode.
     *
     * @return array
     */
    public static function get_options_for_groupmode(): array {
        return [
            NOGROUPS => get_string('groupsnone', 'group'),
            SEPARATEGROUPS => get_string('groupsseparate', 'group'),
            VISIBLEGROUPS => get_string('groupsvisible', 'group'),
        ];
    }

    /**
     * List of options for the field category.
     *
     * @return array
     */
    public static function get_options_for_category(): array {
        return core_course_category::make_categories_list('moodle/category:viewcourselist');
    }

    /**
     * List of options for the field format.
     *
     * @return array
     */
    public static function get_options_for_format(): array {
        global $CFG;
        require_once($CFG->dirroot.'/course/lib.php');

        $options = [];

        $courseformats = get_sorted_course_formats(true);
        foreach ($courseformats as $courseformat) {
            $options[$courseformat] = get_string('pluginname', "format_{$courseformat}");
        }

        return $options;
    }

    /**
     * List of options for the field theme.
     *
     * @return array
     */
    public static function get_options_for_theme(): array {
        $options = [];

        $themeobjects = get_list_of_themes();
        foreach ($themeobjects as $key => $theme) {
            if (empty($theme->hidefromselector)) {
                $options[$key] = get_string('pluginname', "theme_{$theme->name}");
            }
        }

        return $options;
    }

    /**
     * List of options for the field lang.
     *
     * @return array
     */
    public static function get_options_for_lang(): array {
        return get_string_manager()->get_list_of_translations();
    }

    /**
     * List of options for the field.
     *
     * @return array
     */
    public static function get_options_for_calendartype(): array {
        return \core_calendar\type_factory::get_list_of_calendar_types();
    }

    /**
     * Formats the course field for display.
     *
     * @param mixed $value Current field value.
     * @param stdClass $row Complete row.
     * @param string $fieldname Name of the field to format.
     * @return string
     */
    public function format($value, stdClass $row, string $fieldname): string {
        if ($this->get_course_field_type($fieldname) === column::TYPE_TIMESTAMP) {
            return format::userdate($value, $row);
        }

        $options = $this->get_options_for($fieldname);
        if ($options !== null && array_key_exists($value, $options)) {
            return $options[$value];
        }

        if ($this->get_course_field_type($fieldname) === column::TYPE_BOOLEAN) {
            return format::boolean_as_text($value);
        }

        if (in_array($fieldname, ['fullname', 'shortname'])) {
            if (!$row->courseid) {
                return '';
            }
            context_helper::preload_from_record($row);
            $context = context_course::instance($row->courseid);
            return format_string($value, true, ['context' => $context->id, 'escape' => false]);
        }

        if (in_array($fieldname, ['summary'])) {
            if (!$row->courseid) {
                return '';
            }
            context_helper::preload_from_record($row);
            $context = context_course::instance($row->courseid);
            $summary = file_rewrite_pluginfile_urls($row->summary, 'pluginfile.php', $context->id, 'course', 'summary', null);
            return format_text($summary);
        }

        return s($value);
    }
}
