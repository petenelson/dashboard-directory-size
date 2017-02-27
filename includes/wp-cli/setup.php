<?php

// our wp-cli commands
$includes = array(
	'class-dashboard-directory-size-base-command.php',
	'class-dashboard-directory-size-command.php'
	);

foreach ( $includes as $include ) {
	require_once DASHBOARD_DIRECTORY_SIZE_ROOT . 'includes/wp-cli/' . $include;
}
