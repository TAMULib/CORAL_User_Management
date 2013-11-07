<?php

/* A class that provides the CORAL module names and metadata and automates user account creation across modules - Jason Savell
*/
class moduleManager {
	private $moduleNames;
	private $moduleDBs;
	private $modulePrivileges;

	function __construct($config = NULL) {
		if ($config) {
			$names = array_keys($config);
			$this->setModuleNames($names);
			//use module dbName from $config, if provided
			foreach ($config as $module=>$details) {
				$this->setModuleDB($module,($details['dbName']) ? $details['dbName']:"coral_{$module}_prod");
			}
		} else {
			$this->setModuleNames(array('licensing','management','organizations','resources'));
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
		//exclude admin privilegeID
		$sql = "SELECT * FROM `{$this->moduleDBs[$module]}`.`Privilege` ORDER BY `privilegeID`";
		if ($result = mysql_query($sql)) {
			$temp = array();
			while ($row = mysql_fetch_array($result,MYSQL_ASSOC)) {
				$temp[$row['privilegeID']] = $row;
			}
			return $temp;
		}
		return false;
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
		//hashnsalt password with CORAL Utility class
		$util = new Utility();
		$prefix = $util->randomString(45);
		$data['userdata']['password'] = $util->hashString('sha512', $prefix.$data['userdata']['password']);
		$error = NULL;
		//insert into user table of the auth module
		$sql = "INSERT INTO `coral_auth_prod`.`User` SET `loginID`='".mysql_real_escape_string($data['userdata']['loginID'])."',`password`='{$data['userdata']['password']}',`adminInd`='N',`passwordPrefix`='{$prefix}'";
		if (mysql_query($sql)) {
			unset($data['userdata']['password']);
			//loop through module names
			foreach ($data['modules'] as $module) {
				if (in_array($module,$this->moduleNames)) {
					//insert into user table for the current module
					$sql = "INSERT INTO `{$this->moduleDBs[$module]}`.`User` SET ";
					foreach ($data['userdata'] as $field=>$val) {
						$sql .= "`{$field}`='".mysql_real_escape_string($val)."',";
					}
					$sql = rtrim($sql,',');
					if ($data['modulePrivilege'][$module]) {
						$sql .= ",`privilegeID`=".mysql_real_escape_string($data['modulePrivilege'][$module]);
					}
					if ($module == 'resources') {
						$sql .= ",`emailAddress`='".mysql_real_escape_string($data['extras']['email'])."'";
					}
					if (!mysql_query($sql)) {
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
		$x = 1;
		foreach ($this->moduleNames as $module) {
			$meta['fields'] .= " l{$x}.`loginID`,";
			$meta['tables'] .= " `{$this->moduleDBs[$module]}`.`User` l{$x},";
			$meta['params'] .= " l{$x}.`loginID`,";
			$x++;
		}
		foreach ($meta as $field=>$val) {
			$meta[$field] = rtrim($val,',');
		}
		$sql = "SELECT {$meta['fields']}
					FROM {$meta['tables']}
					WHERE '".mysql_real_escape_string($loginID)."' IN ({$meta['params']}) LIMIT 0,1";

		$result = mysql_query($sql);
		if (mysql_num_rows($result) > 0) {
			return true;
		}
		return false;
	}
}