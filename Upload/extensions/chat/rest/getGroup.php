<?php

class getGroup extends AbstractRest {
	
	protected $statusCode = 200;


	public function execute($input, $request) {

        $group_id = $request[2];
        if (!$group_id) {
            return [
                'error' => true,
                'msg' => 'Missing ID'
            ];
        }

        include_once PATH_EXTENSION . 'models' . DS . 'Chat.class.php';
        include_once PATH_EXTENSION . 'models' . DS . 'Member.class.php';
        include_once PATH_EXTENSION . 'models' . DS . 'Groups.class.php';

        $item = extChatModelGroups::getMyByID($group_id);

        if ($item) {
            $item->unsetUnread(0);
            $ret = [
                "id" => $item->getID(),
                "title" => $item->getTitle(),
                "lastMsgTime" => $item->getLastMsgTime(),
                "members" => $item->getMembersCollection(),
                "chat" => $item->getChatCollection()
            ];
        } else {
            return [];
        }


/*
        echo '<pre>';
        print_r($items);
        echo '</pre>';
        exit;
*/

        if ( $ret['id'] ) {
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