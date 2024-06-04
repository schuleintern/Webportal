<?php

class getGroups extends AbstractRest {
	
	protected $statusCode = 200;


	public function execute($input, $request) {

        include_once PATH_EXTENSION . 'models' . DS . 'Chat.class.php';
        include_once PATH_EXTENSION . 'models' . DS . 'Member.class.php';
        include_once PATH_EXTENSION . 'models' . DS . 'Groups.class.php';

        $ret = [];

        $items = extChatModelGroups::getMyByStatus(0); // 0 = open

        foreach ($items as $item) {

            $ret[] = [
                "id" => $item->getID(),
                "title" => $item->getTitle(),
                "lastMsgTime" => $item->getLastMsgTimeHuman(),
                "lastMsgText" => $item->getLastMsgShort(),
                "unread" => $item->getUnread()
            ];

        }

    /*
        echo '<pre>';
        print_r($items);
        echo '</pre>';
        exit;
    */

        if (count($ret) >= 0) {
            return $ret;
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