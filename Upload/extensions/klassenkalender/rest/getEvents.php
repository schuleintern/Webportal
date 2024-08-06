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


        $kalenderIDs = $input['kalenders'];
        if (!$kalenderIDs) {
            return [];
        }
        $kalenderIDs = explode(',',$kalenderIDs);
        if (count($kalenderIDs) <= 0) {
            return [];
        }

        include_once PATH_EXTENSION . 'models' . DS . 'Event.class.php';
        $class = new extKlassenkalenderModelEvent();
        $ret = [];
        foreach ($kalenderIDs as $kalenderID) {
            $data = $class->getByParentID($kalenderID);

            if ($data && count($data) > 0) {
                foreach ($data as $item) {
                    $ret[] = $item->getCollection(true);
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
	public function getAllowedMethod() {
		return 'POST';
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