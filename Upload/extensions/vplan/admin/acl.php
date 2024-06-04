<?php



class extVplanAdminAcl extends AbstractPage {
	
	public static function getSiteDisplayName() {
		return '<i class="fas fa-user-shield"></i> Vertretungsplan - Benutzerrechte';
	}

	public function __construct($request = [], $extension = []) {
		parent::__construct(array( self::getSiteDisplayName() ), false, false, false, $request, $extension);
		$this->checkLogin();
	}

	public function execute() {

		//$this->getRequest();
		//$this->getAcl();

        $user = DB::getSession()->getUser();

        if ( !$this->canAdmin() ) {
            new errorPage('Kein Zugriff');
        }

        $form = [
            "title" => "Vertretungsplan anzeigen",
            "desc" => "Welcher Benutzer darf den Vertretungsplan sehen?",
            "acl" => [
                "schueler" => [
                    "read" => 1,
                    "write" => 0,
                    "delete" => 0,
                ],
                "lehrer" => [
                    "read" => 1,
                    "write" => 0,
                    "delete" => 0,
                ],
                "eltern" => [
                    "read" => 1,
                    "write" => 0,
                    "delete" => 0,
                ],
                "none" => [
                    "read" => 1,
                    "write" => 0,
                    "delete" => 0,
                ]
            ]
        ];

		$this->render([
			"tmplHTML" => '<div class="box"><div class="box-body"><div id=app></div></div></div>',
			"scripts" => [
                PATH_COMPONENTS.'system/adminAcl2/dist/js/chunk-vendors.js',
                PATH_COMPONENTS.'system/adminAcl2/dist/js/app.js'
			],
			"data" => [
				"selfURL" => URL_SELF,
                "form" => $form,
				"acl" => $this->getAclAll(),
                "adminList" => self::getGroupMembers('Webportal_Administrator'),
                "adminExtension" => self::getGroupMembers(self::getAdminGroup())
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
