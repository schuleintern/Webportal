<?php


class getMyMessages extends AbstractRest
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


        include_once PATH_EXTENSION . 'models' . DS . 'Inbox2.class.php';
        $Inbox = new extInboxModelInbox2();
        include_once PATH_EXTENSION . 'models' . DS . 'Message2.class.php';
        $Message = new extInboxModelMessage2();

        $inboxs = $Inbox->getByUserID($userID);
        if (!$inboxs) {
            return [
                'error' => true,
                'msg' => 'Kein Postfach'
            ];
        }

        $ret = [];
        foreach ($inboxs as $inbox) {
            $retInbox = [];
            $inboxTemp = $inbox->getCollection(true);
            if ($inboxTemp['id']) {
                $messages = $Message->getUnreadMessages($inboxTemp['id'], 1);
                foreach ($messages as $item) {
                    $retInbox[] = $item->getCollection(true);
                }
                if (count($retInbox) > 0) {
                    $ret[] = [
                        "title" => $inboxTemp['title'],
                        "data" => $retInbox
                    ];
                }
            }
        }


        return $ret;

    }


    /**
     * Set Allowed Request Method
     * (GET, POST, ...)
     *
     * @return String
     */
    public function getAllowedMethod()
    {
        return 'GET';
    }


    /**
     * Muss der Benutzer eingeloggt sein?
     * Ist Eine Session vorhanden
     * @return Boolean
     */
    public function needsUserAuth()
    {
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
    public function needsSystemAuth()
    {
        return false;
    }

}

?>