Feature: Text command
  In order to get quick information about a projects
  dependencies, users should be able to view them
  on the command line

  Scenario: Simple dependencies
    Given I run the text command
    And I have the following code:
    """
    class A {
      public function test(B $b) {

      }
    }
    """
    Then I should see:
    """
    A --> B
    """
