<?php

class setEvents extends AbstractRest {
	
	protected $statusCode = 200;


	public function execute($input, $request) {

        $userID = DB::getSession()->getUser()->getUserID();
        if (!$userID) {
            return [
                'error' => true,
                'msg' => 'Missing User ID'
            ];
        }

        if ( !$input['kalender_id'] ) {
            return [
                'error' => true,
                'msg' => 'Missing Data: CalenderID'
            ];
        }


        $acl = $this->getAcl();


        $access = false;

        if ($input['status'] == 2) { // status 2 = vorschlag
            $access = true;
        }
        if ( $this->canWrite() ) {
            $access = true;
        }
         if (!$access) {
             include_once PATH_EXTENSION . 'models' . DS . 'Kalender.class.php';
             $calendars = extKalenderModelKalender::getAll(1);
             foreach ($calendars as $calendar) {
                 if ($input['kalender_id'] == $calendar->getID()) {
                     if ($kadmins = $calendar->getAdmins()) {
                         if (in_array($userID,$kadmins )) {
                             $access = true;
                         }
                     }
                 }
             }
         }

        if (!$access) {
            return [
                'error' => true,
                'msg' => 'Kein Zugriff'
            ];
        }




        if ( !$input['title'] ) {
            return [
                'error' => true,
                'msg' => 'Missing Data: Title'
            ];
        }
        if ( !$input['dateStart'] ) {
            return [
                'error' => true,
                'msg' => 'Missing Data: Date'
            ];
        }


        include_once PATH_EXTENSION . 'models' . DS . 'Event.class.php';

        if ( $insert_id = extKalenderModelEvent::submitData($input, $userID) ) {

            return [
                'success' => true,
                'id' => $insert_id
            ];
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