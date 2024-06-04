<?php

class getFormfields extends AbstractRest {
	
	protected $statusCode = 200;


	public function execute($input, $request) {

        if (!(int)$request[2]) {
            return [
                'error' => true,
                'msg' => 'Missing ID'
            ];
        }

        $userID = DB::getSession()->getUser()->getUserID();
        if (!$userID) {
            return [
                'error' => true,
                'msg' => 'Missing User ID'
            ];
        }

        $acl = $this->getAcl();
        if ((int)$acl['rights']['read'] !== 1 && (int)DB::getSession()->getUser()->isAnyAdmin() !== 1 ) {
            return [
                'error' => true,
                'msg' => 'Kein Zugriff'
            ];
        }

        include_once PATH_EXTENSION . 'models' . DS . 'Form.class.php';
        include_once PATH_EXTENSION . 'models' . DS . 'Fieldtyp.class.php';

        $data = extCckModelForm::getAllFields((int)$request[2]);
        $fieldtyps = extCckModelFieldtyp::getAll();


        //$ret = [];
        foreach($data as $k => $item) {

            if ($item['id']) {

                foreach($fieldtyps as $fieldtyp) {
                    if ($fieldtyp->getID() == $item['field_id']) {
                        $data[$k]['field'] = $fieldtyp->getCollection();
                    }
                }

            }
            //$ret[] = $item->getCollection();
        }


        return $data;

	}


	/**
	 * Set Allowed Request Method
	 * (GET, POST, ...)
	 * 
	 * @return String
	 */
	public function getAllowedMethod() {
		return 'GET';
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