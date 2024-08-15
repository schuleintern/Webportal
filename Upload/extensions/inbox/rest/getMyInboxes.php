<?php


class getMyInboxes extends AbstractRest
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

        $ret = [];
        include_once PATH_EXTENSION . 'models' . DS . 'Inbox2.class.php';
        $class = new extInboxModelInbox2();
        $tmp_data = $class->getByUserID($userID);

        if ($tmp_data) {
            foreach ($tmp_data as $item) {
                $ret[] = $item->getCollection(true, true, false, true);
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