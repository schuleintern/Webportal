<?php

class setFormfield extends AbstractRest {
	
	protected $statusCode = 200;


	public function execute($input, $request) {


        $userID = DB::getSession()->getUser()->getUserID();
        if (!$userID) {
            return [
                'error' => true,
                'msg' => 'Missing User ID'
            ];
        }

        if (!$input['form_id']  ) {
            //die('missing data');
            return [
                'error' => true,
                'msg' => 'Missing Data'
            ];
        }

        $acl = $this->getAcl();
        if ((int)$acl['rights']['write'] !== 1 && (int)DB::getSession()->getUser()->isAnyAdmin() !== 1 ) {
            return [
                'error' => true,
                'msg' => 'Kein Zugriff'
            ];
        }



        if ((int)$input['id'] > 0) {


            if ((int)$acl['rights']['write'] !== 1 && (int)DB::getSession()->getUser()->isAnyAdmin() !== 1 ) {
                return [
                    'error' => true,
                    'msg' => 'Kein Zugriff (2)'
                ];
            }

            if (!DB::getDB()->query("UPDATE cck_formfields
                SET field_id=" . (int)DB::getDB()->escapeString($input['field_id']) . "
                WHERE id=".(int)$input['id']." AND form_id=".(int)$input['form_id']
            )) {
                return [
                    'error' => true,
                    'msg' => 'Fehler beim Hinzufügen!'
                ];
            }

            return [
                'error' => false,
                'insert' => true
            ];


        } else {

            if (!DB::getDB()->query("INSERT INTO cck_formfields
				(
				    form_id,
				    field_id
				) values(
					" . (int)DB::getDB()->escapeString($input['form_id']) . ",
					0
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


/*
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
*/

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