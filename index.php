<?php
include "conf/conf.php";

?>
<!DOCTYPE html>
<html>
	<head>
		<title><?php echo $config['title'];?></title>
   		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<link rel="stylesheet" type="text/css" href="<?php echo $config['path_css'];?>style.css" media="screen" />
		<script type="text/javascript" src="<?php echo $config['path_js'];?>jquery.min.js"></script>
	</head>
	<body>
		<div class="header">
			<h1>CORAL Demo Access Request</h1>
		</div>
		<div class="wrap">
			<div class="content">
<?php
$mods->userExists('coral');
//validate and process request
if ($_POST['request']) {
	//make sure we have all data about the user
	foreach (array_merge($_POST['request']['userdata'],$_POST['request']['extras']) as $field=>$val) {
		if (!$val) {
			$valError = true;
			break;
		}
	}
	//make sure we're error free and have at least one module to work with
	if (!$valError && is_array($_POST['request']['modules'])) {
		$flag = 0;
		//make sure each requested module has a requested privilege level and that they haven't hacked in the admin level
		foreach ($_POST['request']['modules'] as $name) {
			 if ($_POST['request']['modulePrivilege'][$name] && $_POST['request']['modulePrivilege'][$name] != 1) {
				$flag++;
			}
		}
		if ($flag == 0) {
			$valError = true;
		}
	} else {
		$valError = true;
	}
	if (!$valError) {
		//make sure this user name is free
		if (!($userExists = $mods->userExists($_POST['request']['userdata']['loginID']))) {
			//create the user
			if ($mods->processRequest($_POST['request'])) {
				$sysMsg = 'User created.';
				//send confirmation emails here
			} else {
				$sysMsg = 'Error creating user.';
			}
		} else {
			$sysMsg = 'That username is already in use.';
		}
	} elseif ($valError) {
		$sysMsg = 'The supplied information is not valid';
	}
} 
if ($sysMsg) {
	echo '<div class="message">'.$sysMsg.'</div>';
}
//show request form if we have no request or a request with errors
if (!$_POST['request'] || $valError || $userExists) {
	echo '		<form name="makeRequest" id="makeRequest" method="POST" action="'.$config['path_http'].'">
					<fieldset>
						<legend>User Information</legend>
						<div class="col">
							<label for="request[userdata][firstName]">First Name</label>
							<input type="text" name="request[userdata][firstName]" id="firstName" value="'.$_POST['request']['userdata']['firstName'].'" />
							<label for="request[userdata][lastName]">Last Name</label>
							<input type="text" name="request[userdata][lastName]" id="lastName" value="'.$_POST['request']['userdata']['lastName'].'" />
							<label for="request[userdata][loginID]">User Name</label>
							<input type="text" name="request[userdata][loginID]" id="user" value="'.$_POST['request']['userdata']['loginID'].'" />
							<label for="request[extras][email]">Email</label>
							<input type="text" name="request[extras][email]" id="email" value="'.$_POST['request']['extras']['email'].'"/>
						</div>
						<div class="col">
							<label for="request[userdata][password]">Password</label>
							<input type="password" name="request[userdata][password]" id="password" />
							<label for="confirmPassword">Confirm Password</label>
							<input type="password" name="confirmPassword" id="confirmPassword" />
							<label>Are You Alive?</label>
							two + three = <input class="mini" type="text" id="jqMath" name="jqMath" />
						</div>
					</fieldset>
					<fieldset>
						<legend>Access Information</legend>
						<label for="request[modules][]">Select Modules</label>';
	foreach ($mods->getModuleConfs() as $name=>$privileges) {
		echo "			<div class=\"moduleDetails\">
							<input class=\"jqModule\" type=\"checkbox\" name=\"request[modules][]\" id=\"module_{$name}\" value=\"{$name}\"".(($_POST['request']['modules'] && in_array($name,$_POST['request']['modules'])) ? ' checked="checked"':'')." /> <span class=\"capitalize\">{$name}</span>
							<ul>";
		foreach ($privileges as $privilege) {
			echo "				<li class=\"capitalize\"><input class=\"jqPrivileges\" disabled=\"disabled\" type=\"radio\" name=\"request[modulePrivilege][{$name}]\" id=\"moduleprops_{$name}\" value=\"{$privilege['privilegeID']}\"".(($_POST['request']['modulePrivilege'][$name] == $privilege['privilegeID']) ? ' checked="checked"':'')." /> {$privilege['shortName']}</li>";
		}
		echo '				</ul>
						</div>';
	}
?>
					</fieldset>
					<input class="center" type="submit" name="submitRequest" value="Request Access" />
				</form>
				<script type="text/javascript" src="<?php echo $config['path_js'];?>main.js"></script>
<?php
}
?>
			</div>
		</div>
	</body>
</html>
