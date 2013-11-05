<?php

$config['path_root'] = "C:/Program Files (x86)/Apache Software Foundation/Apache2.2/htdocs/";
$config['path_file'] = "{$config['path_root']}coral-demo-requests/";
$config['path_base'] = "http://128.194.86.218:8080/coral-demo-requests/";
$config['path_css'] = "{$config['path_base']}css/";
$config['path_js'] = "{$config['path_base']}js/";
$config['title'] = "CORAL Demo Access Request";

$host = "localhost";
$username = "boss";
$password = "boss";
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

$mods = new moduleManager(array('licensing','organizations','resources'));

?>