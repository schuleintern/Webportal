<?php

class setLeader extends AbstractRest {
	
	protected $statusCode = 200;


	public function execute($input, $request) {


        $userID = DB::getSession()->getUser()->getUserID();
        if (!$userID) {
            return [
                'error' => true,
                'msg' => 'Missing User ID'
            ];
        }

        $acl = $this->getAcl();
        if ( !$this->canWrite() ) {
            return [
                'error' => true,
                'msg' => 'Kein Zugriff'
            ];
        }


        $user_id = (int)$input['user_id'];
        if (!$user_id) {
            return [
                'error' => true,
                'msg' => 'Missing Data'
            ];
        }

        $info = $input['info'];
        if (!$info || $info == 'undefined') {
            $info = '';
        }


        include_once PATH_EXTENSION . 'models' . DS . 'Leaders2.class.php';
        $class = new extGanztagsModelLeaders2();

        if ( $class->save([
            'id' => (int)$input['id'],
            'user_id' => (int)$input['user_id'],
            'info' => $info,
            'days' => $_POST['days']
        ]) ) {
            return [
                'success' => true
            ];
        }

        return [
            'error' => true,
            'msg' => 'Fehler beim Speichern!'
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