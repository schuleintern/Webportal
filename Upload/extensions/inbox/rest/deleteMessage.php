<?php

class deleteMessage extends AbstractRest {
	
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

        $message_id = (int)$input['mid'];
        if ( !$message_id ) {
            return [
                'error' => true,
                'msg' => 'Missing ID'
            ];
        }



        include_once PATH_EXTENSION . 'models' . DS . 'Message2.class.php';
        $class = new extInboxModelMessage2();
        $tmp_data = $class->getByID($message_id);

        include_once PATH_EXTENSION . 'models' . DS . 'Inbox2.class.php';
        $Inbox = new extInboxModelInbox2();
        if ( !$Inbox->isInboxFromUser($tmp_data->getData('inbox_id'), $userID) ) {
            return [
                'error' => true,
                'msg' => 'Kein Zugriff auf das Postfach'
            ];
        }

        $body_id = $tmp_data->getData('body_id');


        if ( !$tmp_data->delete() ) {
            return [
                'error' => true,
                'msg' => 'Nachricht konnte nicht gelöscht werden!'
            ];
        }

        $messagesAll = $class->getMessagesByBody($body_id);

        if ( count($messagesAll) < 1) {

            include_once PATH_EXTENSION . 'models' . DS . 'MessageBody2.class.php';
            $Body = new extInboxModelMessageBody2();
            $body = $Body->getByID($body_id);

            if ( !$body->delete() ) {
                return [
                    'error' => true,
                    'msg' => 'Nachrichttext konnte nicht gelöscht werden!'
                ];
            }

        }


        return [
            'done' => true
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