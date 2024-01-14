<?php

class cancelSlot extends AbstractRest {
	
	protected $statusCode = 200;

	public function execute($input, $request) {


        $userID = DB::getSession()->getUser()->getUserID();
        if (!$userID) {
            return [
                'error' => true,
                'msg' => 'Missing User ID'
            ];
        }

        if ( !$input['id'] || !$input['createdBy'] ) {
            //die('missing data');
            return [
                'error' => true,
                'msg' => 'Missing Data'
            ];
        }

        if ( $userID != $input['createdBy'] ) {
            return [
                'error' => true,
                'msg' => 'ACL not Allowed (1)'
            ];
        }

        $acl = $this->getAcl();
        if ((int)$acl['rights']['delete'] !== 1 && (int)DB::getSession()->getUser()->isAnyAdmin() !== 1) {
            return [
                'error' => true,
                'msg' => 'Kein Zugriff'
            ];
        }

        $data = DB::getDB()->query_first("SELECT id, user_id FROM ext_sprechstunde_slots WHERE id = ".(int)$input['id'] , true);

        if ( !$data ) {
            return [
                'error' => true,
                'msg' => 'Missing Item'
            ];
        }

        if ( $userID != $data['user_id'] ) {
            return [
                'error' => true,
                'msg' => 'ACL not Allowed (2)'
            ];
        }

        if (!DB::getDB()->query("UPDATE ext_sprechstunde_slots SET state = 0 WHERE id = " . (int)$input['id']  )) {
        //if (!DB::getDB()->query("DELETE FROM ext_sprechstunde_slots WHERE id = " . (int)$input['id']  )) {
            return [
                'error' => true,
                'msg' => 'Fehler beim Entfernen!'
            ];
        }

        return [
            'error' => false,
            'insert' => true
        ];




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
    public function aclModuleName() {
        return 'ext_sprechstunde';
    }
}	

?>