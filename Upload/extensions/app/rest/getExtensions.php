<?php

class getExtensions extends AbstractRest {
	
	protected $statusCode = 200;


	public function execute($input, $request) {


        $extensions = [];

        $result = DB::getDB()->query('SELECT `id`, `folder`, `name`, `uniqid`, `version` FROM extensions WHERE active = 1 ');

        if ($result) {
            while($row = DB::getDB()->fetch_array($result, true)) {
         
                if ( is_dir(PATH_EXTENSIONS.$row['folder'].'/app') ) {
                    $extensions[] = $row;
                }
            }
    
            return $extensions;
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
        return false;
    }

    public function needsAppAuth()
    {
        return true;
    }


}	

?>