Feature: Test the refresh command

  Scenario: List directories
    Given a WP install

    When I run `wp plugin activate dashboard-directory-size`
    And I run `wp dashboard-directory-size refresh`
    Then STDOUT should contain:
      """
      Refreshing directory sizes...
      Success: Done
      """
