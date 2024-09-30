<?php

class getListAdmin extends AbstractRest
{

    protected $statusCode = 200;


    public function execute($input, $request)
    {


        $ret = [];

        $status_str = (string)$request[2];
        if ($status_str == 'open') {
            $status = [1];
        } else if ($status_str == 'list') {
            $status = [1, 2, 21, 3];
        }

        //$acl = $this->getAcl();
        if (DB::getSession()->isAdminOrGroupAdmin($this->extension['adminGroupName']) !== true) {
            return [
                'error' => true,
                'msg' => 'Kein Zugriff'
            ];
        }


        include_once PATH_EXTENSION . 'models' . DS . 'Antrag.class.php';
        $class = new extBeurlaubungModelAntrag();
        $tmp_data = $class->getByStatus($status);


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