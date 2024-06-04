<?php

class getRoomsAdmin extends AbstractRest {
	
	protected $statusCode = 200;

	public function execute($input, $request) {


        $currentPlanID = stundenplandata::getCurrentStundenplanID();
        if (!($currentPlanID > 0)) {
            return [
                'error' => true,
                'msg' => 'Leider steht aktuell kein Stundenplan zur Verfügung!'
            ];
        }
        $stundenplan = new stundenplandata($currentPlanID);

        $rooms = $stundenplan->getAll('room');


        $settingsRooms = json_decode(DB::getSettings()->getValue('raumplan-rooms'));

        $ret = [];
        foreach ($rooms as $room) {
            $foo = [
                'name' => $room,
                'checked' => false
            ];
            if ($settingsRooms && in_array($room, $settingsRooms)) {
                $foo['checked'] = true;
            }
            $ret[] = $foo;
        }

        if (count($ret) > 0) {
            return $ret;
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
        return true;
    }
    /**
     * Ist eine System Authentifizierung nötig? (mit API key)
     * only if : needsUserAuth = false
     * @return Boolean
     */
    public function needsSystemAuth() {
        return true;
    }

}	

?>