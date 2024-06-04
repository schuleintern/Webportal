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

        if (!$input['klasse'] || !$input['lehrer'] || !$input['room'] || !$input['stunde'] || !$input['fach'] || !$input['date']) {
            //die('missing data');
            return [
                'error' => true,
                'msg' => 'Missing Data'
            ];
        }

        $currentPlanID = stundenplandata::getCurrentStundenplanID();

        if (!($currentPlanID > 0)) {
            return [
                'error' => true,
                'msg' => 'Es wurde kein Stundenplan angegeben.'
            ];
        }

        $time = date('Y-m-d H:i:s');
        if (!DB::getDB()->query("INSERT INTO raumplan_stunden
				(
					stundenplanID,
					stundeKlasse,
					stundeLehrer,
					stundeFach,
					stundeRaum,
					stundeDatum,
				    stundeStunde,
				    createdBy,
				    createdTime,
				    state
				) 
				values(
					'" . DB::getDB()->escapeString($currentPlanID) . "',
					'" . DB::getDB()->escapeString($input['klasse']) . "',
					'" . DB::getDB()->escapeString($input['lehrer']) . "',
					'" . DB::getDB()->escapeString($input['fach']) . "',
					'" . DB::getDB()->escapeString($input['room']) . "',
					'" . DB::getDB()->escapeString($input['date']) . "',
					'" . DB::getDB()->escapeString($input['stunde']) . "',
					$userID,
					'" . $time ."',
					1
				)
		    ")) {
            return [
                'error' => true,
                'msg' => 'EFehler beim Hinzufügen!'
            ];
        }

        return [
            'error' => false,
            'insert' => true
        ];




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

}	

?>