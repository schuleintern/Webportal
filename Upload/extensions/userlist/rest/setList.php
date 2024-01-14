<?php

class setList extends AbstractRest {
	
	protected $statusCode = 200;


	public function execute($input, $request) {


        $userID = DB::getSession()->getUser()->getUserID();
        if (!$userID) {
            return [
                'error' => true,
                'msg' => 'Missing User ID'
            ];
        }

        if (!$input['title'] || !$input['members']  ) {
            //die('missing data');
            return [
                'error' => true,
                'msg' => 'Missing Data'
            ];
        }

        $acl = $this->getAcl();
        if ((int)$acl['rights']['write'] !== 1 && (int)DB::getSession()->isMember($this->extension['adminGroupName']) !== 1 ) {
            return [
                'error' => true,
                'msg' => 'Kein Zugriff'
            ];
        }

        $list_id = (int)$input['id'];

        if ($list_id > 0) {




            if ((int)$acl['rights']['write'] !== 1 && (int)DB::getSession()->getUser()->isAnyAdmin() !== 1 ) {
                return [
                    'error' => true,
                    'msg' => 'Kein Zugriff (2)'
                ];
            }

            if (!DB::getDB()->query("UPDATE ext_userlist_list
                SET title='" . DB::getDB()->escapeString($input['title']) . "'
                WHERE id=".$list_id
            )) {
                return [
                    'error' => true,
                    'msg' => 'Fehler beim Hinzufügen!'
                ];
            }



            $members = array_map('intval',json_decode($_POST['members']) );
            if (count($members) > 0) {
                $userDB = [];
                $dataSQL = DB::getDB()->query("SELECT * FROM ext_userlist_list_members WHERE list_id = " . $list_id);
                while ($data = DB::getDB()->fetch_array($dataSQL, true)) {
                    $userDB[] = (int)$data['user_id'];
                }
                $diff_remove = array_diff( $userDB, $members );
                $diff_add = array_diff( $members, $userDB );
                foreach ($diff_remove as $user) {
                    if (!DB::getDB()->query("DELETE FROM ext_userlist_list_members WHERE list_id = ".$list_id." AND user_id=".$user )) {
                        return [
                            'error' => true,
                            'msg' => 'Fehler beim Löschen der Benutzer (3)!'
                        ];
                    }
                }
                foreach ($diff_add as $user) {
                    if (!DB::getDB()->query("INSERT INTO ext_userlist_list_members
                            (
                                list_id,
                                user_id
                            ) values(
                                ".$list_id.",
                                ".(int)$user."
                            )
                        ")) {
                        return [
                            'error' => true,
                            'msg' => 'Fehler beim Hinzufügen! (list_member)'
                        ];
                    }
                }
            }

            $owners = array_map('intval',json_decode($_POST['owners']) );
            $owners[] = $userID; // add self User
            if (count($owners) > 0) {
                $userDB = [];
                $dataSQL = DB::getDB()->query("SELECT * FROM ext_userlist_list_owner WHERE list_id = " . $list_id);
                while ($data = DB::getDB()->fetch_array($dataSQL, true)) {
                    $userDB[] = (int)$data['user_id'];
                }
                $diff_remove = array_diff( $userDB, $owners );
                $diff_add = array_diff( $owners, $userDB );
                foreach ($diff_remove as $user) {
                    if (!DB::getDB()->query("DELETE FROM ext_userlist_list_owner WHERE list_id = ".$list_id." AND user_id=".$user )) {
                        return [
                            'error' => true,
                            'msg' => 'Fehler beim Löschen der Benutzer (3)!'
                        ];
                    }
                }
                foreach ($diff_add as $user) {
                    if (!DB::getDB()->query("INSERT INTO ext_userlist_list_owner
                            (
                                list_id,
                                user_id
                            ) values(
                                ".$list_id.",
                                ".(int)$user."
                            )
                        ")) {
                        return [
                            'error' => true,
                            'msg' => 'Fehler beim Hinzufügen! (list_member)'
                        ];
                    }
                }
            }



            return [
                'error' => false,
                'insert' => true
            ];

        } else {

            if (!DB::getDB()->query("INSERT INTO ext_userlist_list
				(
				    createdTime,
				    createdBy,
					title
				) values(
				    '".date('Y-m-d H:i', time())."',
				    ".$userID.",
					'" . DB::getDB()->escapeString($input['title']) . "'
				)
		    ")) {
                return [
                    'error' => true,
                    'msg' => 'Fehler beim Hinzufügen! (list)'
                ];
            }
            $insertID = DB::getDB()->insert_id();

            if (!(int)$insertID ) {
                return [
                    'error' => true,
                    'msg' => 'Fehler beim Hinzufügen! (insert)'
                ];
            }


            if ($_POST['owners']) {
                $owners = json_decode($_POST['owners']);
                if (!is_array($owners)) {
                    $owners = [];
                }
            } else {
                $owners = [];
            }
            $owners[] = $userID; // add self User
            if (count($owners) > 0) {
                foreach($owners as $owner) {
                    if ((int)$owner && !DB::getDB()->query("INSERT INTO ext_userlist_list_owner
                        (
                            list_id,
                            user_id
                        ) values(
                            ".$insertID.",
                            ".(int)$owner."
                        )
                    ")) {
                        return [
                            'error' => true,
                            'msg' => 'Fehler beim Hinzufügen! (list_user)'
                        ];
                    }
                }
            }

            $members = json_decode($_POST['members']);
            if (count($members) > 0) {
                foreach($members as $member) {
                    if ((int)$member && !DB::getDB()->query("INSERT INTO ext_userlist_list_members
                        (
                            list_id,
                            user_id
                        ) values(
                            ".$insertID.",
                            ".(int)$member."
                        )
                    ")) {
                        return [
                            'error' => true,
                            'msg' => 'Fehler beim Hinzufügen! (list_user)'
                        ];
                    }
                }
            }


            return [
                'error' => false,
                'insert' => true
            ];
        }






        return [
			'error' => true,
			'msg' => 'Return Data!'
		];

	}


	/**
	 * Set Allowed Request Method
	 * (GET, POST, ...)
	 * 
	 * @return String
	 */
	public function getAllowedMethod() {
		return 'POST';
	}


    /**
     * Muss der Benutzer eingeloggt sein?
     * Ist Eine Session vorhanden
     * @return Boolean
     */
    public function needsUserAuth() {
        return true;
    }

    /**
     * Ist eine Admin berechtigung nötig?
     * only if : needsUserAuth = true
     * @return Boolean
     */
    public function needsAdminAuth()
    {
        return false;
    }
    /**
     * Ist eine System Authentifizierung nötig? (mit API key)
     * only if : needsUserAuth = false
     * @return Boolean
     */
    public function needsSystemAuth() {
        return false;
    }

}	

?>