<?php

class setMsg extends AbstractRest {
	
	protected $statusCode = 200;


	public function execute($input, $request) {

        $group_id = (int)$input['group_id'];
        if (!$group_id) {
            return [
                'error' => true,
                'msg' => 'Missing ID'
            ];
        }
        $msg = (string)$input['msg'];
        if (!$msg) {
            return [
                'error' => true,
                'msg' => 'Missing Message'
            ];
        }

        include_once PATH_EXTENSION . 'models' . DS . 'Chat.class.php';
        include_once PATH_EXTENSION . 'models' . DS . 'Member.class.php';
        include_once PATH_EXTENSION . 'models' . DS . 'Groups.class.php';

        if ( $msgObj = extChatModelChat::setMsg([
            "group_id" => $group_id,
            "msg" => $msg
        ]) ) {
            return [
                'error' => false,
                'msgObj' => $msgObj
            ];
        }
/*
        echo '<pre>';
        print_r($items);
        echo '</pre>';
        exit;
*/

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
	 * Ist eine System Authentifizierung nötig? (mit API key)
     * only if : needsUserAuth = false
	 * @return Boolean
	 */
	public function needsSystemAuth() {
		return true;
	}



}	

?>