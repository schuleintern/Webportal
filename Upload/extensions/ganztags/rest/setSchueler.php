<?php

class setSchueler extends AbstractRest {
	
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
        if (!$this->canWrite() ) {
            return [
                'error' => true,
                'msg' => 'Kein Zugriff'
            ];
        }

        $id = $input['id'];
        if (!$id) {
            return [
                'error' => true,
                'msg' => 'Missing ID'
            ];
        }
        $days = json_decode($_POST['days']);
        if (count($days) < 1 ) {
            return [
                'error' => true,
                'msg' => 'Missing Data'
            ];
        }

        include_once PATH_EXTENSION . 'models' . DS . 'Schueler.class.php';

        if (!$input['info'] || $input['info'] == 'null') {
            $input['info'] = '';
        }
        if ( !extGanztagsModelSchueler::updateSchueler($id, json_encode($days), $input['info'], $input['anz']) ) {
            return [
                'error' => true,
                'msg' => 'Fehler beim Speichern!'
            ];
        }

        $data = extGanztagsModelSchueler::getByID($id);
        return $data->getCollection();


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