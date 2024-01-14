<?php

class deleteList extends AbstractRest {
	
	protected $statusCode = 200;


	public function execute($input, $request) {


        $id = (int)$input['id'];

        if ( $id <= 0  ) {
            //die('missing data');
            return [
                'error' => true,
                'msg' => 'Missing ID'
            ];
        }

        $acl = $this->getAcl();
        if ((int)$acl['rights']['delete'] !== 1 && (int)DB::getSession()->isMember($this->extension['adminGroupName']) !== 1 ) {
            return [
                'error' => true,
                'msg' => 'Kein Zugriff'
            ];
        }

        if (!DB::getDB()->query("DELETE FROM ext_userlist_list WHERE id=".$id )) {
            return [
                'error' => true,
                'msg' => 'Fehler beim Löschen! (1)'
            ];
        }

        if (!DB::getDB()->query("DELETE FROM ext_userlist_list_tab WHERE list_id=".$id )) {
            return [
                'error' => true,
                'msg' => 'Fehler beim Löschen! (2)'
            ];
        }

        if (!DB::getDB()->query("DELETE FROM ext_userlist_list_content WHERE list_id=".$id )) {
            return [
                'error' => true,
                'msg' => 'Fehler beim Löschen! (3)'
            ];
        }

        if (!DB::getDB()->query("DELETE FROM ext_userlist_list_members WHERE list_id=".$id )) {
            return [
                'error' => true,
                'msg' => 'Fehler beim Löschen! (4)'
            ];
        }

        if (!DB::getDB()->query("DELETE FROM ext_userlist_list_owner WHERE list_id=".$id )) {
            return [
                'error' => true,
                'msg' => 'Fehler beim Löschen! (5)'
            ];
        }

        return [
            'error' => false,
            'delete' => true
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