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

        $acl = $this->getAcl();

        if ( !$this->canWrite() ) {
        //if ((int)$acl['rights']['write'] !== 1 || DB::getSession()->isAdminOrGroupAdmin($this->extension['adminGroupName']) !== true ) {
        //if ((int)$acl['rights']['write'] !== 1 && (int)DB::getSession()->getUser()->isAnyAdmin() !== 1 ) {
            return [
                'error' => true,
                'msg' => 'Kein Zugriff'
            ];
        }



        if ( !$input['kalender_id'] ) {
            return [
                'error' => true,
                'msg' => 'Missing Data: CalenderID'
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