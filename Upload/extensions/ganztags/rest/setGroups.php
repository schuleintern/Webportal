<?php

class setGroups extends AbstractRest {
	
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

        $items = $_POST['items'];
        if (!$items) {
            return [
                'error' => true,
                'msg' => 'Missing Data'
            ];
        }
        $items = json_decode($_POST['items']);
        if (count($items) < 1 ) {
            return [
                'error' => true,
                'msg' => 'Missing Data (2)'
            ];
        }

        include_once PATH_EXTENSION . 'models' . DS . 'Groups.class.php';


        if ( !extGanztagsModelGroups::setAll($items) ) {
            return [
                'error' => true,
                'msg' => 'Fehler beim Speichern!'
            ];
        }

        $data = extGanztagsModelGroups::getAll();

        $ret = [];
        if (count($data) > 0) {
            foreach ($data as $item) {

                $ret[] = $item->getCollection();
            }
        }



        return $ret;

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