# All Backups changelog - ARNES

## [2021031700] - 2021-03-17

### Added

- Plugin settings page
  - System settings for enabling backups management in the context of a category
  - System settings for changing the backups search query to include only the backups, created on this Moodle instance
    - Significantly improves the query execution time, useful on large Moodle installations
  - System settings for including activities when querying only the backups, created on this Moodle instance
- Associated language strings along with slovenian translations
- New capabilities, specifically for managing category backups
- Additional `categorybackups.php` page for managing category backups
- An entry in lib.php to add a link to `/report/allbackups/categorybackups.php` when viewing a category
- Permissions-based category filtering in `classes/output/filtering.php`
- Appropriate context-based filtering in `classes/output/allbackups_table.php` and `classes/output/autobackups_table.php`
- Requiring the correct capability in `categorybackups.php` and `lib.php`
  - Require the `moodle/backup:downloadfile` capability in the `report_allbackups_pluginfile` function in `lib.php`
  - Require the `report/categorybackups:view` capability in the `categorybackups.php` file

### Changed
- `index.php` - enabled the functionality for querying only the backups, created on this specific Moodle instance
- A few comments and SQL queries, according to Moodle Code Style guide.
