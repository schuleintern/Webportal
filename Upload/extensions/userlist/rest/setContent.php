<?php

class setContent extends AbstractRest {
	
	protected $statusCode = 200;


	public function execute($input, $request) {


        $userID = DB::getSession()->getUser()->getUserID();
        if (!$userID) {
            return [
                'error' => true,
                'msg' => 'Missing User ID'
            ];
        }
        $member_id = (int)$input['member_id'];
        $tab_id = (int)$input['tab_id'];

        $item_id = (int)$input['item_id'];
        $list_id = (int)$input['list_id'];

        if ( !$member_id  ) {
            //die('missing data');
            return [
                'error' => true,
                'msg' => 'Missing memberID'
            ];
        }
        if ( !$tab_id  ) {
            //die('missing data');
            return [
                'error' => true,
                'msg' => 'Missing tabID'
            ];
        }
        if ( !$list_id  ) {
            //die('missing data');
            return [
                'error' => true,
                'msg' => 'Missing listID'
            ];
        }

        $acl = $this->getAcl();
        if ((int)$acl['rights']['write'] !== 1 && (int)DB::getSession()->getUser()->isAnyAdmin() !== 1 ) {
            return [
                'error' => true,
                'msg' => 'Kein Zugriff'
            ];
        }

        $toggle = 0;
        if (isset($input['toggle'])) {
            if ( DB::getDB()->escapeString($input['toggle']) == 'true' ) {
                $toggle = 1;
            }
        }

        if ($item_id > 0) {

            // Update


            if (isset($input['toggle'])) {
                if (!DB::getDB()->query("UPDATE ext_userlist_list_content
                SET toggle= " . $toggle . "
                WHERE id=".$item_id
                )) {
                    return [
                        'error' => true,
                        'msg' => 'Fehler beim Speichern! (4)'
                    ];
                }
            }

            if ($input['info']) {
                if (!DB::getDB()->query("UPDATE ext_userlist_list_content
                SET info='" . DB::getDB()->escapeString($input['info']) . "'
                WHERE id=".$item_id
                )) {
                    return [
                        'error' => true,
                        'msg' => 'Fehler beim Speichern! (3)'
                    ];
                }
            }

            return [
                'error' => false,
                'update' => true
            ];


        } else {
            // Insert

            if (isset($input['toggle']) || $input['info']) {

                if (!DB::getDB()->query("INSERT INTO ext_userlist_list_content
                    (
                        list_id,
                        tab_id,
                        member_id,
                        toggle,
                        info
                    ) values(
                        " . $list_id . ",
                        " . $tab_id . ",
                        " . $member_id . ",
                        " . $toggle . ",
                        '" . DB::getDB()->escapeString($input['info']) . "'
                    )
                ")) {
                    return [
                        'error' => true,
                        'msg' => 'Fehler beim Speichern! (1)'
                    ];
                }

                return [
                    'error' => false,
                    'insert' => true
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