<?php

class getICS extends AbstractRest {
	
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


        //include_once PATH_EXTENSION . 'models' . DS . 'Event.class.php';
        //$data = extKalenderModelEvent::getAllByKalenderID($kalenderIDs);

        $icsState = false;
        $url = DB::getGlobalSettings()->urlToIndexPHP;
        $url = str_replace('index.php','rest.php',$url);


        include_once PATH_EXTENSION . 'models' . DS . 'ICS.class.php';
        $Class = new extKalenderModelIcs();

        $data = $Class->getByParentID($userID);
        if ($data && $data[0]) {
            $privateURL = $data[0]->getCollection();
            $icsState = 1;
        }

        $ret = [
            "icsState" => $icsState,
            "icsPrivateURL" => $url."/kalender/ics/".$privateURL['keyCode'],
            "icsPublicURL" => $url."/kalender/ics"
        ];

        /*
        if (count($data) > 0) {
            foreach ($data as $item) {
                $ret[] = $item->getCollection(true);
            }
        }
        */

        return $ret;

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