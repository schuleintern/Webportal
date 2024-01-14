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

        /*

        if ( DB::getSession()->isAdminOrGroupAdmin($this->extension['adminGroupName']) !== true ) {
            if ((int)$acl['rights']['read'] !== 1) {
                return [
                    'error' => true,
                    'msg' => 'Kein Zugriff'
                ];
            }
        }
        */

        /*
        if ((int)$acl['rights']['read'] !== 1 || DB::getSession()->isAdminOrGroupAdmin($this->extension['adminGroupName']) !== true ) {
        //if ( (int)$acl['rights']['read'] !== 1 && (int)DB::getSession()->getUser()->isAnyAdmin() !== 1 ) {
            return [
                'error' => true,
                'msg' => 'Kein Zugriff'
            ];
        }
        */


        include_once PATH_EXTENSION . 'models' . DS . 'Kalender.class.php';
        $data = extKalenderModelKalender::getAll(1);
        $userType = DB::getSession()->getUser()->getUserTyp(true);
        $isAdmin = false;
        if ( DB::getSession()->isAdminOrGroupAdmin($this->getAdminGroup()) ) {
            $isAdmin = true;
        }
        $ret = [];
        if (count($data) > 0) {
            foreach ($data as $item) {
                $arr = $item->getCollection(true);

                if ( $isAdmin || $this->getGroupACL( $arr['acl']['groups'], $userType ) === 1  ) {
                    if ( $isAdmin ) {
                        $arr['acl']['rights'] = ['read' => 1, 'write' => 1, 'delete' => 1];
                    }
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


    /**
     * Muss der Benutzer eingeloggt sein?
     * Ist Eine Session vorhanden
     * @return Boolean
     */
    public function needsUserAuth() {
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
    public function needsSystemAuth() {
        return false;
    }

}	

?>