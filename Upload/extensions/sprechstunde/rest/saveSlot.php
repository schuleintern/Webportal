<?php

class saveSlot extends AbstractRest {
	
	protected $statusCode = 200;



	public function execute($input, $request) {


        $userID = DB::getSession()->getUser()->getUserID();
        if (!$userID) {
            return [
                'error' => true,
                'msg' => 'Missing User ID'
            ];
        }

        if (!$input['timeHour'] || !$input['timeMinute'] || !$input['title'] || !$input['day'] || !$input['typ'] ) {
            //die('missing data');
            return [
                'error' => true,
                'msg' => 'Missing Data'
            ];
        }

        $acl = $this->getAcl();
        if ((int)$acl['rights']['write'] !== 1 && (int)DB::getSession()->getUser()->isAnyAdmin() !== 1 ) {
            return [
                'error' => true,
                'msg' => 'Kein Zugriff'
            ];
        }


        $time = DateTime::createFromFormat('H:i', $input['timeHour'].':'.$input['timeMinute'] );
        $time_str = $time->format('H:i');

        if ((int)$input['id'] > 0) {

            if ((int)$acl['rights']['write'] !== 1 && (int)DB::getSession()->getUser()->isAnyAdmin() !== 1 ) {
                return [
                    'error' => true,
                    'msg' => 'Kein Zugriff (2)'
                ];
            }




                if (!DB::getDB()->query("UPDATE ext_sprechstunde_slots
                    SET title='" . DB::getDB()->escapeString($input['title']) . "',
                    time='" . $time_str . "',
                    day='" . DB::getDB()->escapeString($input['day']) . "',
                    duration='" . DB::getDB()->escapeString($input['duration']) . "',
                    typ='" . $_POST['typ'] . "'
                    WHERE id=".(int)$input['id']
                )) {
                return [
                    'error' => true,
                    'msg' => 'Fehler beim Hinzufügen!'
                ];
            }

            return [
                'error' => false,
                'insert' => true
            ];

        } else {
            if (!DB::getDB()->query("INSERT INTO ext_sprechstunde_slots
				(
				    state,
				    user_id,
					title,
					time,
					day,
				    duration,
				    typ
				) values(
				    1,
				    $userID,
					'" . DB::getDB()->escapeString($input['title']) . "',
					'" . $time_str . "',
					'" . DB::getDB()->escapeString($input['day']) . "',
					'" . DB::getDB()->escapeString($input['duration']) . "',
					'" . $_POST['typ'] . "'
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