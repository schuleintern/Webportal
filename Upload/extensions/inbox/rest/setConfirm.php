<?php


class setConfirm extends AbstractRest
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

        $message_id = (int)$input['mid'];
        if (!$message_id) {
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
        if (!$Inbox->isInboxFromUser($tmp_data->getData('inbox_id'), $userID)) {
            return [
                'error' => true,
                'msg' => 'Kein Zugriff auf das Postfach'
            ];
        }


        if (!$tmp_data->setConfirm()) {
            return [
                'error' => true,
                'msg' => 'Lesebestätigung konnte nicht gesendet werden'
            ];
        }


        return [
            'done' => true
            //'isConfirm' => $tmp_data->getIsConfirm()
        ];

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