<?php

class deleteFile extends AbstractRest {
	
	protected $statusCode = 200;


	public function execute($input, $request) {


        $id = (int)$input['fiid'];
        if ( $id <= 0  ) {
            //die('missing data');
            return [
                'error' => true,
                'msg' => 'Missing ID'
            ];
        }

        $acl = $this->getAcl();
        if ((int)$acl['rights']['delete'] !== 1 && (int)DB::getSession()->isMember($this->extension['adminGroupName']) !== 1) {
            return [
                'error' => true,
                'msg' => 'Kein Zugriff'
            ];
        }

        include_once PATH_EXTENSION . 'models' . DS . 'File.class.php';
        if ( !extFilecollectModelFile::delete($id)) {
            return [
                'error' => true,
                'msg' => 'Fehler beim Löschen!'
            ];
        }

        return [
            'error' => false,
            'delete' => true
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