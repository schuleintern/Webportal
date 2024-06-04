<?php

class postRoomsAdmin extends AbstractRest {
	
	protected $statusCode = 200;

	public function execute($input, $request) {

        $post_rooms = json_decode($_POST['rooms']);

        if (!$post_rooms || count($post_rooms) <= 0) {
            return [
                'error' => true,
                'msg' => 'Missing Data'
            ];
        }

        $currentPlanID = stundenplandata::getCurrentStundenplanID();
        if (!($currentPlanID > 0)) {
            return [
                'error' => true,
                'msg' => 'Leider steht aktuell kein Stundenplan zur Verfügung!'
            ];
        }
        $stundenplan = new stundenplandata($currentPlanID);
        $rooms = $stundenplan->getAll('room');

        $json = [];
        foreach ($rooms as $room) {
            foreach ($post_rooms as $foo) {
                if ($foo->name == $room) {
                    if ($foo->checked) {
                        $json[] = $room;
                    }
                }
            }
        }
        DB::getSettings()->setValue('raumplan-rooms', json_encode($json));

        return [
			'error' => false
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