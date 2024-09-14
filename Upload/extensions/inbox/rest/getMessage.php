<?php


class getMessage extends AbstractRest
{

    protected $statusCode = 200;


    public function execute($input, $request)
    {


        $userID = DB::getSession()->getUser()->getUserID();
        if (!$userID) {
            return [
                'error' => true,
                'msg' => 'Missing User ID'
            ];
        }

        $acl = $this->getAcl();
        if (!$this->canRead()) {
            return [
                'error' => true,
                'msg' => 'Kein Zugriff'
            ];
        }

        $message_id = (int)$input['message_id'];
        if (!$message_id) {
            return [
                'error' => true,
                'msg' => 'Missing Data: ID'
            ];
        }


        /*
        include_once PATH_EXTENSION . 'models' . DS . 'Message.class.php';
        $tmp_data = extInboxModelMessage::getMessageByID($message_id);

        include_once PATH_EXTENSION . 'models' . DS . 'Inbox.class.php';
        if ( !extInboxModelInbox::isInboxFromUser($tmp_data->getInboxID(), $userID) ) {
            return [
                'error' => true,
                'msg' => 'Kein Zugriff auf das Postfach'
            ];
        }
        */

        include_once PATH_EXTENSION . 'models' . DS . 'Message2.class.php';
        $class = new extInboxModelMessage2();
        $tmp_data = $class->getByID($message_id);

        include_once PATH_EXTENSION . 'models' . DS . 'Inbox2.class.php';
        $InboxClass = new extInboxModelInbox2();
        $inbox = $InboxClass->isInboxFromUser($tmp_data->getData('inbox_id'), $userID);
        if (!$inbox || !$inbox->getID()) {
            return [
                'error' => true,
                'msg' => 'Kein Zugriff auf das Postfach'
            ];
        }


        if ( (int)$tmp_data->getData('isRead') <= 1 ) {
            if (!$tmp_data->setRead($userID)) {
                return [
                    'error' => true,
                    'msg' => 'Nachricht konnte nicht als gelesen markiert werden'
                ];
            }
        } else {
            if ($inbox->getData('type') == 'group') {
                if ($tmp_data->getData('isReadUser') != $userID) {
                    include_once PATH_EXTENSION . 'models' . DS . 'MessageIsRead.class.php';
                    $MessageIsRead = new extInboxModelMessageIsRead();
                    $isRead = $MessageIsRead->getByMessageAndUser($message_id, $userID);
                    if (!$isRead) {
                        $MessageIsRead->save([
                            'message_id' => $message_id,
                            'isRead' => time(),
                            'isReadUser' => $userID
                        ]);
                    }
                }
            }
        }



        return $tmp_data->getCollection(true, true, true, true, true);

    }


    /**
     * Set Allowed Request Method
     * (GET, POST, ...)
     *
     * @return String
     */
    public function getAllowedMethod()
    {
        return 'POST';
    }


    public function needsAppAuth() {
        return true;
    }
    /**
     * Muss der Benutzer eingeloggt sein?
     * Ist Eine Session vorhanden
     * @return Boolean
     */
    public function needsUserAuth()
    {
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
    public function needsSystemAuth()
    {
        return false;
    }

}

?>