<?php

class getAntragOpen extends AbstractRest
{

    protected $statusCode = 200;


    public function execute($input, $request)
    {

        //$user = DB::getSession()->getUser();
        $acl = $this->getAcl();
        if ( !$this->canRead() ) {
            return [
                'error' => true,
                'msg' => 'Kein Zugriff'
            ];
        }

        $user = DB::getSession()->getUser();

        if ( !$user->isTeacher() && !$user->isAdmin() ) {
            new errorPage('Kein Zugriff');
        }

        
        $tmp_data = [];

        if ( $user->isTeacher() ) {
            $arr = [];
            $userObj = $user->getTeacherObject();
            $klassen = $userObj->getKlassenMitKlasseleitung();

            foreach($klassen as $klasse) {
                $arr[] = $klasse->getKlassenName();
            }

            include_once PATH_EXTENSION . 'models' . DS . 'Antrag.class.php';

            $tmp_data = extAusweisModelAntrag::getByKlassen($arr);
        }

        $ret = [];
        foreach ($tmp_data as $item) {
            $collection = $item->getCollection(true);
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