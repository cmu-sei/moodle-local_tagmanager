@local @local_tagmanager @javascript
Feature: Tag Manager adds import and export controls to Manage tags
  In order to move tags between sites in bulk
  As a manager
  I need import and export controls on the core Manage tags pages

  Background:
    Given the following "tags" exist:
      | name     | isstandard |
      | Existing | 1          |

  Scenario: The collections overview gets import and export icons that open the import form
    Given I log in as "admin"
    When I navigate to "Appearance > Manage tags" in site administration
    Then "Import tags" "link" should exist in the "//table[contains(@class,'tag-collections-table')]//tr[contains(.,'Default collection')]" "xpath_element"
    And "Export" "link" should exist in the "//table[contains(@class,'tag-collections-table')]//tr[contains(.,'Default collection')]" "xpath_element"
    And I click on "Import tags" "link" in the "//table[contains(@class,'tag-collections-table')]//tr[contains(.,'Default collection')]" "xpath_element"
    And I should see "Import tags" in the "page-header" "region"
    And I should see "CSV file"
    And "Upload Tag File" "button" should exist

  Scenario: A collection page gets Export selected and Import standard tags buttons
    Given I log in as "admin"
    And I navigate to "Appearance > Manage tags" in site administration
    When I follow "Default collection"
    Then "Export selected" "button" should exist
    And "Import standard tags" "button" should exist
    And I press "Export selected"
    And I should see "Please select at least one tag to export."
    And I press "Import standard tags"
    And I should see "CSV file"
    And "Upload Tag File" "button" should exist

  Scenario: A tag manager without the Tag Manager capability gets none of the controls
    Given the following "users" exist:
      | username   | firstname | lastname | email                  |
      | tagmanager | Tag       | Manager  | tagmanager@example.com |
    And the following "roles" exist:
      | name         | shortname   |
      | Tag curator  | tagcurator  |
    And the following "role capability" exists:
      | role              | tagcurator |
      | moodle/tag:manage | allow      |
    And the following "system role assigns" exist:
      | user       | course               | role       |
      | tagmanager | Acceptance test site | tagcurator |
    And I log in as "tagmanager"
    When I visit "/tag/manage.php"
    Then I should see "Default collection"
    And "Import tags" "link" should not exist
    And I follow "Default collection"
    And "Add standard tags" "button" should exist
    And "Export selected" "button" should not exist
    And "Import standard tags" "button" should not exist

  @_file_upload
  Scenario: Importing a CSV creates the new tags and reports the ones that already exist
    Given I log in as "admin"
    And I navigate to "Appearance > Manage tags" in site administration
    And I follow "Default collection"
    And I press "Import standard tags"
    When I upload "local/tagmanager/tests/fixtures/tags.csv" file to "CSV file" filemanager
    And I press "Upload Tag File"
    Then I should see "Created tag: Alpha, Beta"
    And I should see "Tag already exists: Existing"
    And I should see "Alpha" in the "reportbuilder-table" "table"
    And I should see "Beta" in the "reportbuilder-table" "table"
