<?php

class getMyFolders extends AbstractRest {
	
	protected $statusCode = 200;


	public function execute($input, $request) {


        $user = DB::getSession()->getUser();

        if (!$user) {
            return [
                'error' => true,
                'msg' => 'Fehlender User'
            ];
        }

        include_once PATH_EXTENSION . 'models' . DS . 'Folder.class.php';

        $folders = extMessageModelFolder::getMyFolders($user);

/*
        echo '<pre>';
        print_r($folders);
        echo '</pre>';
        exit;
*/
        if ($folders) {
            return $folders;
        }

        return [
            'error' => true,
            'msg' => 'Error'
        ];

	}


	/**
	 * Set Allowed Request Method
	 * (GET, POST, ...)
	 * 
	 * @return String
	 */
	public function getAllowedMethod() {
		return 'GET';
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
	 * Ist eine System Authentifizierung nötig? (mit API key)
     * only if : needsUserAuth = false
	 * @return Boolean
	 */
	public function needsSystemAuth() {
		return true;
	}



}	

?>