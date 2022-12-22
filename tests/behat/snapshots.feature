@local @local_behatsnapshots
Feature: Behat Snapshots plugin in the LMS.

  Background:
    Given I visit "/local/behatsnapshots/index.php"

  @javascript
  Scenario: It works.
    Then I should see "Behat Snapshots"
    And the HTML should match the snapshot
    And the UI should match the snapshot
