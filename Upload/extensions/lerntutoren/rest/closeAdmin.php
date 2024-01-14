<?php

class closeAdmin extends AbstractRest {
	
	protected $statusCode = 200;


	public function execute($input, $request) {

        //print_r($input);

        $itemID = (int)$input['id'];
        if (!$itemID) {
            return [
                'error' => true,
                'msg' => 'Missing ID'
            ];
        }

        if ( !$this->canAdmin() ) {
            return [
                'error' => true,
                'msg' => 'Kein Zugriff'
            ];
        }

        include_once PATH_EXTENSION . 'models' . DS . 'Tutoren.class.php';

        $tutor = new extLerntutorenModelTutoren(["tutorenID" => $itemID]);

        if ( $tutor->setStatusClose() ) {
            return [
                'error' => false
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
        return true;
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