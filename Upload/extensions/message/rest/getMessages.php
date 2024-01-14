<?php

class getMessages extends AbstractRest {
	
	protected $statusCode = 200;


	public function execute($input, $request) {


        $folder = (string)$request[2];
        if (!$folder) {
            return [
                'error' => true,
                'msg' => 'Missing Folder'
            ];
        }

        $user = DB::getSession()->getUser();
        if (!$user) {
            return [
                'error' => true,
                'msg' => 'Missing User'
            ];
        }



        include_once PATH_EXTENSION . 'models' . DS . 'Folder.class.php';

        $folder = extMessageModelFolder::getFolder($user, $folder);

        $messages = $folder->getMessages();
/*
        echo '<pre>';
        print_r($folder);
        echo '</pre>';
        exit;
        */

        if ($messages) {
            return $messages;
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