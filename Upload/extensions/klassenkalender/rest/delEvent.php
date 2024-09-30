<?php

class delEvent extends AbstractRest {
	
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

        if ( !$this->canDelete() ) {

            $access = false;
            include_once PATH_EXTENSION . 'models' . DS . 'Kalender.class.php';
            $Klaender = new extKlassenkalenderModelKalender();
            $calendar = $Klaender->getByID($input['kalender_id']);
            if ($kadmins = $calendar->getAdmins()) {
                if (in_array($userID, $kadmins)) {
                    $access = true;
                }
            }

            if ($access != true) {
                return [
                    'error' => true,
                    'msg' => 'Kein Zugriff'
                ];
            }


        }

        if ( !$input['id'] ) {
            return [
                'error' => true,
                'msg' => 'Missing Data: id'
            ];
        }

        include_once PATH_EXTENSION . 'models' . DS . 'Event.class.php';
        $class = new extKlassenkalenderModelEvent();
        $data = $class->getByID($input['id']);
        if ( $data ) {
            if ( $data->delete() ) {
                return [
                    'success' => true
                ];
            }
        }

        return [
            'error' => true,
            'msg' => 'Error'
        ];

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