<?php

class getSlots extends AbstractRest {
	
	protected $statusCode = 200;

	public function execute($input, $request) {


        $acl = $this->getAcl();
        if ((int)$acl['rights']['read'] !== 1 && (int)DB::getSession()->getUser()->isAnyAdmin() !== 1 ) {
            return [
                'error' => true,
                'msg' => 'Kein Zugriff'
            ];
        }

        $userID = DB::getSession()->getUser()->getUserID();
        if (!$userID) {
            return [
                'error' => true,
                'msg' => 'Missing User ID'
            ];
        }

        include_once PATH_EXTENSION . 'models' . DS . 'Slot.class.php';

        $data = extSprechstundeModelSlot::getAllByUser($userID);

        $ret = [];
        if (count($data) > 0) {
            foreach ($data as $item) {
                $arr = $item->getCollection();
                if ($arr['user_id'] == $userID) {
                    $arr['createdSelf'] = true;
                    $arr['createdBy'] = $userID;
                }
                $ret[] = $arr;
            }
        }


        /*
        echo '<pre>';
        print_r($ret);
        echo '</pre>';
        */

        return $ret;



        return [
			'error' => true,
			'msg' => 'Return Data!'
		];

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
    public function aclModuleName() {
        return 'ext_sprechstunde';
    }
}	

?>