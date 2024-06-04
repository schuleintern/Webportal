<?php

class setUpload extends AbstractRest {
	
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
        if ((int)$acl['rights']['write'] !== 1 && (int)DB::getSession()->isMember($this->extension['adminGroupName']) !== 1 ) {
            return [
                'error' => true,
                'msg' => 'Kein Zugriff'
            ];
        }

        $files = $_FILES['files'];
        if (count($files['name']) <= 0) {
            return [
                'error' => true,
                'msg' => 'Missing Files'
            ];
        }

        $folderid = (int)$input['folderid'];
        if (!$folderid) {
            return [
                'error' => true,
                'msg' => 'Missing Folder ID'
            ];
        }



        include_once PATH_EXTENSION . 'models' . DS . 'File.class.php';

        $insertID = extFilecollectModelFile::upload($userID, $files, $folderid);
        if ( $insertID == false ) {
            return [
                'error' => true,
                'msg' => 'Fehler beim Speichern!'
            ];
        }

        return [
            'error' => false,
            'insert' => true
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