<?php

class setStundenplaene extends AbstractRest {
	
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
            return [
                'error' => true,
                'msg' => 'Kein Zugriff'
            ];
        }

        $title = (string)$input['title'];
        if ( !$title || $title == 'undefined' ) {
            return [
                'error' => true,
                'msg' => 'Missing Data: Title'
            ];
        }
        
        $start = (string)$input['start'];
        if ( !$start || $start == 'undefined' ) {
            return [
                'error' => true,
                'msg' => 'Missing Data: Start'
            ];
        }

        $data = [
            'stundenplanAb' => $start,
            'stundenplanUploadUserID' => $userID,
            'stundenplanName' => $title
        ];

        $end = (string)$input['end'];
        if ( $end && $end != 'undefined' ) {
            $data['stundenplanBis'] = $end;
        } else {
            unset($data['stundenplanBis']);
        }

        include_once PATH_EXTENSION . 'models' . DS . 'Stundenplaene.class.php';
        $class = new extStundenplanModelStundenplaene();

        if ( $db = $class->save($data) ) {

     
            return [
                'error' => false,
                'success' => true
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