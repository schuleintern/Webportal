<?php

class adminExampleAcl extends AbstractPage {
	
	public static function getSiteDisplayName() {
		return 'Admin - ACL';
	}


	public function __construct($request = [], $extension = []) {
		parent::__construct(array( self::getSiteDisplayName() ), false, false, false, 'module', $request, $extension);
		$this->checkLogin();
	}

	public function aclModuleName() {
		return 'example';
	}
	
	public static function getAdminGroup() {
		return 'Admin_GAGA';
	}

	public function execute() {

		//$this->getRequest();
		//$this->getAcl();
		

		$this->render([
			"tmpl" => "acl",
			"scripts" => [
				PATH_COMPONENTS.'system/adminAcl/dist/main.js'
			],
			"data" => [
				"selfURL" => URL_SELF,
				"acl" => $this->getAclAll(),
				"globalAdminGroup" => $this->getAdminGroupUsers('Webportal_Administrator'),
				"extensionAdminGroup" => $this->getAdminGroupUsers(self::getAdminGroup())
			],
			"submenu" => [
				[
					"url" => "index.php?page=example",
					"title" => "Default",
					"icon" => "fa fa-cogs"
				],
				[
					"url" => "index.php?page=example&view=list",
					"title" => "List",
					"icon" => "fa fa-book"
				],

				[
					"admin" => true,
					"url" => "index.php?page=example&view=default&admin=true",
					"title" => "Einstellungen",
					"icon" => "fa fa-book"
				],
				[
					"admin" => true,
					"url" => "index.php?page=example&view=acl&admin=true",
					"title" => "Benutzerrechte",
					"icon" => "fa fa-book"
				],
				[
					"admin" => true,
					"url" => "index.php?page=example&view=custom&admin=true",
					"title" => "Admin Custom",
					"icon" => "fa fa-book"
				]
			]
		]);

	}

	public function taskSaveACL($postData) {

		if (  DB::getSession()->isAdmin() ) {
			if ($postData['acl']) {
				echo json_encode(ACL::setAcl($postData['acl']));
				exit;
			}
		}
		echo json_encode(array("error" => true));
		exit;
	}


	public function taskCompleteUserName($postData) {

		if ($postData['input']) {
			$users = DB::getDB()->query_all("SELECT userID, userName, userFirstName, userLastName FROM users WHERE userName LIKE '%" . $postData['input'] . "%' OR userFirstName LIKE '%" . $postData['input'] . "%' OR userLastName LIKE '%" . $postData['input'] . "%'");
			echo json_encode(array("users" => $users));
			exit;
		}
		echo json_encode(array("error" => true));
		exit;
	}


	public function taskAddAdmin($postData) {

		if ( DB::getSession()->isAdmin() && $postData['userID'] ) {
			DB::getDB()->query("INSERT INTO users_groups (userID, groupName) values('" . $postData['userID'] . "','" . self::getAdminGroup() . "') ON DUPLICATE KEY UPDATE groupName=groupName");
			echo json_encode(array("users" => $this->getAdminGroupUsers(self::getAdminGroup()) ));
			exit;
		}
		echo json_encode(array("error" => true));
		exit;

	}


	public function taskRemoveAdmin($postData) {

		if ( DB::getSession()->isAdmin() && $postData['userID'] ) {
			DB::getDB()->query("DELETE FROM users_groups WHERE userID=" . intval($postData['userID']) . " AND groupName='" . self::getAdminGroup() . "'");
			echo json_encode(array("users" => $this->getAdminGroupUsers(self::getAdminGroup()) ));
			exit;
		}
		echo json_encode(array("error" => true));
		exit;

	}


	private function getAdminGroupUsers($groupName) {
		
		if (!$groupName) {
			return false;
		}
		$obj = usergroup::getGroupByName($groupName);
		$list = $obj->getMembers();
		foreach($list as $key => $item) {
			$list[$key] = [
				"name" => $item->getDisplayName(),
				"userID" => $item->getUserID(),
				"userType" => $item->getUserTyp(),
			];
		}
		return $list;
	}

}
