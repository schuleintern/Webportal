<?php

class setAntragDoneAdmin extends AbstractRest {
	
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

        $id = (int)$input['id'];
        if ( !$id ) {
            return [
                'error' => true,
                'msg' => 'Missing Data: Id'
            ];
        }

        $status = (int)$input['status'];
        if ( !$status ) {
            return [
                'error' => true,
                'msg' => 'Missing Data: Status'
            ];
        }


        $doneInfo = trim(nl2br($input['doneInfo']));
        $doneInfoIntern = trim(nl2br($input['doneInfoIntern']));



        include_once PATH_EXTENSION . 'models' . DS . 'Antrag.class.php';

        if ( extBeurlaubungModelAntrag::setDone($id, $status, $doneInfo, $userID, $doneInfoIntern) ) {
            return [
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