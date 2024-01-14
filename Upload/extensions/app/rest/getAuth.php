<?php

class getAuth extends AbstractRest {
	
	protected $statusCode = 200;


	public function execute($input, $request) {


        if ( $input['username'] && $input['password'] ) {

            $userDB = DB::getDB()->query_first("SELECT * FROM users WHERE userName = '".(string)$input['username']."' ", true);
            $full_salt = substr($userDB['userCachedPasswordHash'], 0, 29);
            $new_hash = crypt($input['password'], $full_salt);

            if ( $userDB['userCachedPasswordHash'] == $new_hash ) {

                //$session = session::loginAndCreateSession($userDB['userID'], true);

                $sessionID = session::createSessionForApi($userDB['userID'], 0, 'APP');

                return [
                    'error' => false,
                    'auth' => true,
                    'id' => $userDB['userID'],
                    'session' => $sessionID
                ];
            }

        }

        return [
            'error' => true,
            'auth' => false
        ];
        


	}

    public static function check_password($hash, $password) {
        $full_salt = substr($hash, 0, 29);
        $new_hash = crypt($password, $full_salt);
        return ($hash == $new_hash);
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
        return false;
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
        return true;
    }

}	

?>