<?php

class setList extends AbstractRest {
	
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

        $id = (int)$input['id'];
        /*
        if (!$id) {
            return [
                'error' => true,
                'msg' => 'Missing ID'
            ];
        }
        */
        // bugfix:
        $input['members'] = $_POST['members'];
        /*
         * if (count($members) < 1 ) {
            return [
                'error' => true,
                'msg' => 'Missing Members'
            ];
        }
        */



        include_once PATH_EXTENSION . 'models' . DS . 'Collection.class.php';

        $insertID = extFilecollectModelCollection::set($input, $id);
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

        //$data = extFilecollectModelCollection::getByID($insertID);
        //return $data->getCollection();

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