<?php

class getMessage extends AbstractRest {
	
	protected $statusCode = 200;


	public function execute($input, $request) {


        $messageID = (string)$request[2];
        if (!$messageID) {
            return [
                'error' => true,
                'msg' => 'Missing ID'
            ];
        }

        $userID = DB::getSession()->getUser()->getUserID();
        if (!$userID) {
            return [
                'error' => true,
                'msg' => 'Missing User'
            ];
        }

        include_once PATH_EXTENSION . 'models' . DS . 'Message.class.php';

        $message = extMessageModelMessage::getMessageByID($messageID, $userID);

        if ($message) {
            return $message->getCollection(true);
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