<?php

class saveDate extends AbstractRest {
	
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

        if (!$input['date'] || !$input['slot_id'] ) {
            //die('missing data');
            return [
                'error' => true,
                'msg' => 'Missing Data'
            ];
        }

        include_once PATH_EXTENSION . 'models' . DS . 'Slot.class.php';
        $slot = extSprechstundeModelSlot::getByID($input['slot_id']);

        if (!$slot || !$slot->getUserID() ) {
            return [
                'error' => true,
                'msg' => 'Missing Slot'
            ];
        }

        //$time = DateTime::createFromFormat('H:i', $input['timeHour'].':'.$input['timeMinute'] );
        //$time_str = $time->format('H:i');

        $medium = trim(DB::getDB()->escapeString($input['medium']));
        if (!$medium || $medium == 'undefined') {
            $medium = '';
        }
        $info = trim(DB::getDB()->escapeString($input['info']));
        if (!$info || $info == 'undefined') {
            $info = '';
        }
        $block = 0;
        if ($userID == $slot->getUserID() || (int)$input['block'] == 1 ) {
            $block = 1;
        }
        if (!DB::getDB()->query("INSERT INTO ext_sprechstunde_dates
				(
				    date,
					slot_id,
					info,
					user_id,
				    block,
				    status,
                    medium
				) values(
					'" . DB::getDB()->escapeString($input['date']) . "',
					'" . (int)$input['slot_id'] . "',
					'" . $info . "',
					$userID,
					$block,
					0,
                    '" .$medium ."'
				)
		    ")) {
            return [
                'error' => true,
                'msg' => 'Fehler beim Hinzufügen!'
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
    public function aclModuleName() {
        return 'ext_sprechstunde';
    }
}	

?>