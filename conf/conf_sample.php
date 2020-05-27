<?php
//
// Copy this file to conf.php and make appropriate changes.
//
//

$config['path_root'] = "/var/www/html/";
$config['path_file'] = "{$config['path_root']}usermanagement/";
$config['path_base'] = "https://coraldemo.library.tamu.edu/usermanagement/";
//$config['path_http'] = "index.php";
$config['path_css'] = "{$config['path_base']}css/";
$config['path_js'] = "{$config['path_base']}js/";
$config['title'] = "CORAL Demo Access Request";
$config['adminEmail'] = "";

// (optionally) define the module names and their respective DBs

$config['modules'] = array(
  'auth'=>array('dbName'=>'demo_coral_auth'),
  'licensing'=>array('dbName'=>'demo_coral_licensing'),
  'organizations'=>array('dbName'=>'demo_coral_organizations'),
  'resources'=>array('dbName'=>'demo_coral_resources'),
  'usage'=>array('dbName'=>'demo_coral_usage'),
  'management'=>array('dbName'=>'demo_coral_management')  
  );

$host = "";
$username = "";
$password = "";

$db_link = mysqli_connect($host, $username, $password, 'demo_coral_auth');
global $db_link;

if (mysqli_connect_errno()) {
    printf("Connect failed: %s\n", mysqli_connect_error());
    exit();
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
