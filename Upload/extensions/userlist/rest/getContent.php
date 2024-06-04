<?php

class getContent extends AbstractRest {
	
	protected $statusCode = 200;


	public function execute($input, $request) {

        $tab_id = (int)$input['tab_id'];
        if (!$tab_id) {
            return [
                'error' => true,
                'msg' => 'Missing Tab ID'
            ];
        }

        $list_id = (int)$input['list_id'];
        if (!$list_id) {
            return [
                'error' => true,
                'msg' => 'Missing Tab ID'
            ];
        }

        $acl = $this->getAcl();
        if ((int)$acl['rights']['read'] !== 1 && (int)DB::getSession()->isMember($this->extension['adminGroupName']) !== 1 ) {
            return [
                'error' => true,
                'msg' => 'Kein Zugriff'
            ];
        }

        include_once PATH_EXTENSION . 'models' . DS . 'Content.class.php';

        $data = extUserlistModelContent::getMembersWithContentByTab($tab_id, $list_id);

        $ret = [];
        if (count($data) > 0) {
            foreach ($data as $item) {

                $ret[] = $item->getCollection(true);
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