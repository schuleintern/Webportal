<?php

class getEvents extends AbstractRest {
	
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

        $kalenderIDs = $input['kalenders'];
        if (!$kalenderIDs) {
            return [];
        }
        $kalenderIDs = explode(',',$kalenderIDs);
        if (count($kalenderIDs) <= 0) {
            return [];
        }

        include_once PATH_EXTENSION . 'models' . DS . 'Event.class.php';
        $data = extKalenderModelEvent::getAllByKalenderID($kalenderIDs);

        $ret = [];
        if (count($data) > 0) {
            foreach ($data as $item) {
                $ret[] = $item->getCollection(true);
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
	public function getAllowedMethod() {
		return 'POST';
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