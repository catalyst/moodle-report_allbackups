@report @report_allbackups @editor @editor_atto @atto @atto_media @_file_upload @javascript
Feature: All backups report.

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email |
      | student1 | Student | 1 | student1@example.com |
    And the following "blocks" exist:
      | blockname     | contextlevel | reference | pagetypepattern | defaultregion |
      | private_files | System       | 1         | my-index        | side-post     |
    And I log in as "student1"
    And I follow "Manage private files..."
    And I upload "report/allbackups/tests/fixtures/fakebackup1-UFC101-123456.mbz" file to "Files" filemanager
    And I upload "report/allbackups/tests/fixtures/fakebackup2-MATH314-15926536.mbz" file to "Files" filemanager
    And I click on "Save changes" "button"
    And I log out
    And I log in as "admin"

  Scenario: Admin can view backups
    When I navigate to "Reports > All backups" in site administration
    Then I should see "fakebackup1-UFC101-123456.mbz"
    And I should see "fakebackup2-MATH314-15926536.mbz"

  Scenario: Admin can filter backups
    When I navigate to "Reports > All backups" in site administration
    And I set the field "id_filename" to "MATH314"
    And I press "Add filter"
    And I should not see "fakebackup1-UFC101-123456.mbz"
    And I should see "fakebackup2-MATH314-15926536.mbz"
    And I press "Remove all filters"
    Then I should see "fakebackup1-UFC101-123456.mbz"
    And I should see "fakebackup2-MATH314-15926536.mbz"

  Scenario: Admin can delete backups.
    When I navigate to "Reports > All backups" in site administration
    And I click on "Delete" "link" in the "fakebackup1-UFC101-123456.mbz" "table_row"
    And I press "Continue"
    And I should not see "fakebackup1-UFC101-123456.mbz"
    And I should see "fakebackup2-MATH314-15926536.mbz"
