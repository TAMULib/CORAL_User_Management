<?php

/* A class that provides the CORAL module names and metadata and automates user account creation across modules - Jason Savell
*/
class moduleManager {
	private $authModuleDB;
	private $moduleNames;
	private $moduleDBs;
	private $modulePrivileges;

	function __construct($config = NULL) {
		if ($config) {
			if ($config['auth'] && $config['auth']['dbName']) {
				$this->setAuthModuleDB($config['auth']['dbName']);
				unset($config['auth']);
			} else {
				$this->setAuthModuleDB = 'coral_auth_prod';
			}
			$names = array_keys($config);
			$this->setModuleNames($names);
			//use module dbName from $config, if provided
			foreach ($config as $module=>$details) {
				$this->setModuleDB($module,($details['dbName']) ? $details['dbName']:"coral_{$module}_prod");
			}
		} else {
			$this->setAuthModuleDB = 'coral_auth_prod';
			$this->setModuleNames(array('licensing','management','organizations','resources','usage'));
			foreach ($this->moduleNames as $module) {
				$this->setModuleDB($module,"coral_{$module}_prod");
			}
		}
		$this->buildModulePrivileges();
	}

	//build an array of module configurations
	private function buildModulePrivileges() {
		foreach ($this->moduleNames as $module) {
			$this->modulePrivileges[$module] = $this->getPrivilege($module);
		}
	}

	//get the privileges from the DB for the given module
	private function getPrivilege($module) {
		global $db_link;
		//exclude admin privilegeID
		$sql = "SELECT * FROM `{$this->moduleDBs[$module]}`.`Privilege` ORDER BY `privilegeID`";
		
		if ($result = mysqli_query($db_link, $sql)) {
//			var_dump($result);
			$temp = array();
			while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
				$temp[$row['privilegeID']] = $row;
			}
			return $temp;
		}
		return false;
	}

	private function setAuthModuleDB($db) {
		$this->authModuleDB = $db;
	}

	private function setModuleDB($name,$db) {
		$this->moduleDBs[$name] = $db;
	}

	public function getModuleNames() {
		return $this->moduleNames();
	}

	public function setModuleNames($names) {
		$this->moduleNames = $names;
	}

	public function getModulePrivileges() {
		return $this->modulePrivileges;
	}

	//add new user to each requested module
	public function processRequest($data) {
		global $db_link;
		//hashnsalt password with CORAL Utility class
		$util = new Utility();
		$prefix = $util->randomString(45);
		$data['userdata']['password'] = $util->hashString('sha512', $prefix.$data['userdata']['password']);
		$error = NULL;
		//insert into user table of the auth module
		$sql = "INSERT INTO `{$this->authModuleDB}`.`User` SET `loginID`='".mysqli_real_escape_string($db_link, $data['userdata']['loginID'])."',`password`='{$data['userdata']['password']}',`adminInd`='N',`passwordPrefix`='{$prefix}'";
		
		if (mysqli_query($db_link, $sql)) {
			unset($data['userdata']['password']);
			//loop through module names
			foreach ($data['modules'] as $module) {
				if (in_array($module,$this->moduleNames)) {
					//insert into user table for the current module
					$sql = "INSERT INTO `{$this->moduleDBs[$module]}`.`User` SET ";
					foreach ($data['userdata'] as $field=>$val) {
						$sql .= "`{$field}`='".mysqli_real_escape_string($db_link, $val)."',";
					}
					$sql = rtrim($sql,',');
					if ($data['modulePrivilege'][$module]) {
						$sql .= ",`privilegeID`=".mysqli_real_escape_string($db_link, $data['modulePrivilege'][$module]);
					}
					if ($module == 'resources') {
						$sql .= ",`emailAddress`='".mysqli_real_escape_string($db_link, $data['extras']['email'])."'";
					}
					if (!mysqli_query($db_link, $sql)) {
						$error[] = $module;
					}
				}
			}
			if (!$error) {
				return true;
			}
		}
		return false;
	}

	//boolean check for username availability
	function userExists($loginID) {
		global $db_link;
		$x = 1;
		
		$sql = "SELECT ";
		
		foreach ($this->moduleNames as $module) {
			$sql = $sql . "`loginID` FROM `{$this->moduleDBs[$module]}`.`User`  WHERE `loginID` = '".mysqli_real_escape_string($db_link, $loginID)."'";
			$sql = $sql . "UNION SELECT ";
		}
		$sql = rtrim($sql, "UNION SELECT ");

		$result = mysqli_query($db_link, $sql);
		if (mysqli_num_rows($result) > 0) {
			return true;
		}
		return false;
	}
}