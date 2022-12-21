@local @local_behatsnapshots
Feature: Behat Snapshots plugin in the LMS.

  Background:
    Given I visit "/local/behatsnapshots/index.php"

  Scenario: It works.
    Then I should see "Behat Snapshots"
