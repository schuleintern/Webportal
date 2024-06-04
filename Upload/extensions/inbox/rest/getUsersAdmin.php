<?php

class getUsersAdmin extends AbstractRest
{

    protected $statusCode = 200;


    public function execute($input, $request)
    {

        //$user = DB::getSession()->getUser();
        $acl = $this->getAcl();
        if ( !$this->canAdmin() ) {
            return [
                'error' => true,
                'msg' => 'Kein Zugriff'
            ];
        }

        include_once PATH_EXTENSION . 'models' . DS . 'Inbox2.class.php';
        include_once PATH_EXTENSION . 'models' . DS . 'Users.class.php';

        $class = new extInboxModelInbox2;
        $tmp_data = $class->getByTypUser();

        //$classUsers = new extInboxModelUsers;

        $ret = [];
        foreach ($tmp_data as $item) {
            $collection = $item->getCollection();

            /*
            $collection['userName'] = ' - leer - ';
            $collection['user'] = [];

            //$collection['childs'] = [];
            $childs = $classUsers->getByParentID($collection['id'])[0];
            if ($childs) {
                $collection['user'] = $childs->getCollection(true);
                if ($collection['user'] && $collection['user']['user']) {
                    $collection['userName'] = $collection['user']['user']['name'];
                }
            }
*/
            

            $ret[] = $collection;
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