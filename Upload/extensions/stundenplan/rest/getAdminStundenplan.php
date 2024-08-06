<?php


class getAdminStundenplan extends AbstractRest
{

    protected $statusCode = 200;


    public function execute($input, $request)
    {

        $id = $request[2];
        if (!$id) {
            return [
                'error' => true,
                'msg' => 'Missing ID'
            ];
        }

        $user = DB::getSession()->getUser();
        if (!$user) {
            return [
                'error' => true,
                'msg' => 'Missing User'
            ];
        }
        $acl = $this->getAcl();
        if (!$this->canRead()) {
            return [
                'error' => true,
                'msg' => 'Kein Zugriff'
            ];
        }

        include_once PATH_EXTENSION . 'models' . DS . 'Stundenplan.class.php';

        $class = new extStundenplanModelStundenplan();
        $tmp_data = $class->getByParentID($id);

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

    public function needsAppAuth()
    {
        return false;
    }


}

?>