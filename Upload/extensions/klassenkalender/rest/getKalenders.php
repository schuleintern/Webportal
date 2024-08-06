<?php

class getKalenders extends AbstractRest {

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

        if ( !$this->canRead() ) {
            return [
                'error' => true,
                'msg' => 'Kein Zugriff'
            ];
        }




        include_once PATH_EXTENSION . 'models' . DS . 'Kalender.class.php';
        $class = new extKlassenkalenderModelKalender();
        $data = $class->getByState([1]);

        //$data = extKalenderModelKalender::getAll(1);
        $userType = DB::getSession()->getUser()->getUserTyp(true);
        $userCollection = DB::getSession()->getUser()->getCollection(true);
        $isAdmin = false;
        if ( DB::getSession()->isAdminOrGroupAdmin($this->getAdminGroup()) ) {
            $isAdmin = true;
        }
        $ret = [];
        if ($data && count($data) > 0) {
            foreach ($data as $item) {
                $arr = $item->getCollection(true);
                $show = false;
                if ( $isAdmin ) {
                    $show = true;
                    $arr['acl']['rights'] = ['read' => 1, 'write' => 1, 'delete' => 1];
                }
                if ( $this->getGroupACL( $arr['acl']['groups'], $userType ) === 1  ) {
                    $show = true;
                }
                if ($arr['admins']) {
                    $kadmins = json_decode($arr['admins']);
                    if ($kadmins) {
                        foreach ($kadmins as $kadmin) {
                            if ((int)$kadmin == $userID) {
                                $arr['acl']['rights'] = ['read' => 1, 'write' => 1, 'delete' => 1];
                                $show = true;
                                $arr['preSelect'] = 1;
                            }
                        }
                    }
                }
                if ($userType == 'isPupil') {
                    if ($userCollection['klasse']) {
                        if ($userCollection['klasse'] == $arr['title']) {
                            $show = true;
                            $arr['preSelect'] = 1;
                        }
                    }
                }
                if ($show) {
                    $ret[] = $arr;
                }


            }
        }
        return $ret;
    }

    function getGroupACL($groups,$userType) {

        if ( DB::getSession()->isAdminOrGroupAdmin($this->getAclGroup()) === true) {
            return 1;
        }
        if ($userType == 'isPupil') {
            return (int)$groups['schueler']['read'];
        }
        if ($userType == 'isTeacher') {
            return (int)$groups['lehrer']['read'];
        }
        if ($userType == 'isEltern') {
            return (int)$groups['eltern']['read'];
        }
        if ($userType == 'isNone') {
            return (int)$groups['none']['read'];
        }
        return false;
    }

    /**
     * Set Allowed Request Method
     * (GET, POST, ...)
     *
     * @return String
     */
    public function getAllowedMethod() {
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
    public function needsUserAuth() {
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
    public function needsSystemAuth() {
        return false;
    }

}

?>