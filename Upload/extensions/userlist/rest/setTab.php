<?php

class setTab extends AbstractRest {
	
	protected $statusCode = 200;


	public function execute($input, $request) {



        if ( (int)$input['list_id'] <= 0  ) {
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



        if ((int)$input['id'] > 0) {

            // UPDATE


            if (!DB::getDB()->query("UPDATE ext_userlist_list_tab
                SET title='" . DB::getDB()->escapeString($input['title']) . "'
                WHERE id=".(int)$input['id']
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

            // INSERT

            $title = DB::getDB()->escapeString($input['title']);
            if (!$title) {
                $title = date('d.m.', time());
            }
            if (!DB::getDB()->query("INSERT INTO ext_userlist_list_tab
				(
				    list_id,
				    title
				) values(
				    " . DB::getDB()->escapeString($input['list_id']) . ",
					'" . $title . "'
				)
		    ")) {
                return [
                    'error' => true,
                    'msg' => 'Fehler beim Hinzufügen! (list)'
                ];
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