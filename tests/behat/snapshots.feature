@local @local_behatsnapshots
Feature: Behat Snapshots plugin in the LMS.

  Background:
    Given I visit "/local/behatsnapshots/index.php"

  Scenario: It works without javascript.
    Then I should see "Behat Snapshots"
    And the HTML should match the snapshot

  @javascript
  Scenario: It works with javascript.
    Then I should see "Behat Snapshots"

    When I replace "Behat Snapshots" within "h1" with "Behat Snapshots with replacements"
    Then the HTML should match the snapshot
    And the UI should match the snapshot

  @javascript @app
  Scenario: It works in the app.
    Given I entered the app as "admin"
    Then the HTML should match the snapshot
    And the UI should match the snapshot
