<?php

class getDates extends AbstractRest {
	
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
        include_once PATH_EXTENSION . 'models' . DS . 'Date.class.php';

        $today = date('Y-m-d', time());
        $ret = [];


        // Fuer Lehrer
        $datesBySlots = [];
        $slots = extSprechstundeModelSlot::getAllByUser($userID);
        if (count($slots) > 0) {
            foreach ($slots as $slot) {

                $dates = extSprechstundeModelDate::getBySlotID($slot->getID());

                if (count($dates) > 0) {
                    foreach ($dates as $date) {
                        $date->slot = $slot->getCollection();
                        $datesBySlots[] = $date;
                    }
                }
            }
        }

        // Fuer Schueler
        $datesByUser = [];
        $dates = extSprechstundeModelDate::getByUserID($userID);
        if (count($dates) > 0) {
            foreach($dates as $date) {
                $slot = extSprechstundeModelSlot::getByID($date->getSlotID());

                $date->slot = $slot->getCollection();
                $date->user_id = $slot->getUserID();
                $date->user_user = $slot->getUser()->getCollection();

                $datesByUser[] = $date;

            }
        }


        $dates = array_merge($datesBySlots, $datesByUser);

        /*
        echo '<pre>';
        print_r($dates);
        echo '</pre>';


        exit;
        */



        foreach ($dates as $date) {
            $arr = $date->getCollection();
            if ($arr['user_id'] == $userID) {
                $arr['createdSelf'] = true;
                $arr['createdBy'] = $userID;
            }
            if ($date->user_id && $date->user_user ) {
                $arr['user_id'] = $date->user_id;
                $arr['user'] = $date->user_user;
            }
            if ($date->slot) {
                $arr['slot'] = $date->slot;
            }
            if ($today == $arr['date']) {
                $arr['today'] = true;
            }
            if ($today > $arr['date']) {
                $arr['done'] = true;
            }
            $ret[] = $arr;
        }

        usort($ret, function($a, $b) {
            return $b['date'] <=> $a['date'];
        });


        /*
        echo '<pre>';
        print_r($ret);
        echo '</pre>';
        */

        if (count($ret) > 0) {
            return $ret;
        }

        return [];

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