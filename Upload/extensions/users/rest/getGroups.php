<?php

namespace users\rest;
use AbstractRest;
use extUsersModelGroups;

class getGroups extends AbstractRest
{

    protected $statusCode = 200;


    public function execute($input, $request)
    {

        //$user = DB::getSession()->getUser();
        $acl = $this->getAcl();
        if (!$this->canAdmin()) {
            return [
                'error' => true,
                'msg' => 'Kein Zugriff'
            ];
        }


        include_once PATH_EXTENSION . 'models' . DS . 'Groups.class.php';
        $class = new extUsersModelGroups();
        $tmp_data = $class->getAll();
        $ret = [];
        foreach ($tmp_data as $item) {
            $ret[] = $item->getCollection(true);
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