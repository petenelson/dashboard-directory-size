Feature: Test the list command

  Scenario: List directories
    Given a WP install

    When I run `wp plugin activate dashboard-directory-size`
    And I run `wp dashboard-directory-size list`
    Then STDOUT should contain:
      """
      WP Database
      """
    And STDOUT should contain:
      """
      themes
      """
    And STDOUT should contain:
      """
      plugins
      """
