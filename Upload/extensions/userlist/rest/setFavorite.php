<?php

class setFavorite extends AbstractRest {
	
	protected $statusCode = 200;


	public function execute($input, $request) {


        $userID = DB::getSession()->getUser()->getUserID();
        if (!$userID) {
            return [
                'error' => true,
                'msg' => 'Missing User ID'
            ];
        }

        $item_id = (int)$input['id'];
        if ( !$item_id  ) {
            //die('missing data');
            return [
                'error' => true,
                'msg' => 'Missing ID'
            ];
        }

        $acl = $this->getAcl();
        if ((int)$acl['rights']['write'] !== 1 && (int)DB::getSession()->isMember($this->extension['adminGroupName']) !== 1 ) {
            return [
                'error' => true,
                'msg' => 'Kein Zugriff'
            ];
        }

        $fav = 0;

        $data = DB::getDB()->query_first("SELECT * FROM ext_userlist_list_owner WHERE list_id =".$item_id." AND user_id = ".(int)$userID);

        if ( $data['id'] ) {

            if ( $data['favorite'] == 1 ) {
                $fav = 0;
            } else {
                $fav = 1;
            }

            if (!DB::getDB()->query("UPDATE ext_userlist_list_owner
            SET favorite=".$fav."
            WHERE id=".$data['id']
            )) {
                return [
                    'error' => true,
                    'msg' => 'Fehler beim Speichern!'
                ];
            } else {
                return [
                    'error' => false,
                    'update' => true,
                    'favorite' => $fav
                ];
            }

        }



        return [
			'error' => true,
			'msg' => 'Return Error!'
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