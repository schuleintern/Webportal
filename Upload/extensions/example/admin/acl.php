<?php

class extExampleAdminAcl extends AbstractPage {
	
	public static function getSiteDisplayName() {
		return 'Example Module - Admin Benutzerrechte';
	}

	public function __construct($request = [], $extension = []) {
		parent::__construct(array( self::getSiteDisplayName() ), false, false, false, $request, $extension);
		$this->checkLogin();
	}

	public function execute() {

		//$this->getRequest();
		//$this->getAcl();
		
		if (!DB::getSession()->isAdmin()) {
			new errorPage('Kein Zugriff');
		}

		$this->render([
			"tmplHTML" => '<div class="box"><div class="box-body"><div id=app></div></div></div>',
			"scripts" => [
				PATH_COMPONENTS.'system/adminAcl/dist/main.js'
			],
			"data" => [
				"selfURL" => URL_SELF,
				"acl" => $this->getAclAll(),
				"globalAdminGroup" => $this->getAdminGroupUsers('Webportal_Administrator'),
				"extensionAdminGroup" => $this->getAdminGroupUsers(self::getAdminGroup())
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
