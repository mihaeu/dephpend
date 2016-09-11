Feature: Text command
  In order to get quick information about a projects
  dependencies, users should be able to view them
  on the command line

  Scenario: Type hints in methods
    Given I run the text command
    And I have the following code:
      """
      class A {
        public function test(B $b, C $c, $d) {

        }
      }
      """
    Then I should see:
      """
      A --> B
      A --> C
      """

  Scenario: Extending other classes
    Given I run the text command
    And I have the following code:
      """
      class A extends B {}
      """
    Then I should see:
      """
      A --> B
      """

  Scenario: Implementing interfaces
    Given I run the text command
    And I have the following code:
      """
      class A implements B {}
      class C implements D, E {}
      """
    Then I should see:
      """
      A --> B
      C --> D
      C --> E
      """

  Scenario: Using traits
    Given I run the text command
    And I have the following code:
      """
      class A {
        use B;
        use C, D;
      }
      """
    Then I should see:
      """
      A --> B
      A --> C
      A --> D
      """

  Scenario: Creating objects
    Given I run the text command
    And I have the following code:
      """
      class A {
        function test() {
          new B();
          new C;
        }
      }
      """
    Then I should see:
      """
      A --> B
      A --> C
      """
