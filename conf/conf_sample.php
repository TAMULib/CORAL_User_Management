<?php
//
// Copy this file to conf.php and make appropriate changes.
//
//
$config['path_root'] = "/data/apps/coraldemo/";
$config['path_file'] = "{$config['path_root']}coral-demo-requests/";
$config['path_base'] = "http://coraldemo.library.tamu.edu/usermanagement/";
$config['path_css'] = "{$config['path_base']}css/";
$config['path_js'] = "{$config['path_base']}js/";
$config['title'] = "CORAL Demo Access Request";

// (optionally) define the module names and their respective DBs
/*
$config['modules'] = array('auth'=>array('dbName'=>'coral_auth_prod'),'licensing'=>array('dbName'=>'coral_licensing_prod'),
							'organizations'=>array('dbName'=>'coral_organizations_prod'),
							'resources'=>array('dbName'=>'coral_resources_prod'));

*/
$host = "mysql2.l";
$username = "democoral";
$password = "";

if (!mysql_connect($host, $username, $password)) {
	die('No connection: '.mysql_error());
}

function __autoload($name) {
	$filename = "{$GLOBALS['config']['path_file']}classes/{$name}.class.php";
	if (is_file($filename)) {
		require $filename;
	} else {
		$filename = "{$GLOBALS['config']['path_file']}classes/common/{$name}.php";
		if (is_file($filename)) {
			require $filename;
		}
	}
}

if ($config['modules']) {
	$mods = new moduleManager($config['modules']);
} else {
	$mods = new moduleManager();
}
?>