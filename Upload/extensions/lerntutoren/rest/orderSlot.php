<?php

class orderSlot extends AbstractRest {
	
	protected $statusCode = 200;


	public function execute($input, $request) {

        //print_r($input);

        $reserveID = (int)$input['id'];
        if (!$reserveID) {
            return [
                'error' => true,
                'msg' => 'Missing ID'
            ];
        }

        $einheiten = (int)$input['einheiten'];
        if (!$einheiten) {
            return [
                'error' => true,
                'msg' => 'Missing Einheiten'
            ];
        }

        $tutorenSchuelerAsvID = DB::getSession()->getUser()->getData('userAsvID');
        if (!$tutorenSchuelerAsvID) {
            return [
                'error' => true,
                'msg' => 'Missing ASV ID'
            ];
        }



        if ( DB::getDB()->query("INSERT INTO tutoren_slots
            (
                slotTutorenID,
                slotStatus,
                slotSchuelerAsvID,
                slotEinheiten,
                slotCreated
            )
            values(
                   $reserveID,
                   'reserve',
                '" . $tutorenSchuelerAsvID . "',
                '" . DB::getDB()->escapeString($einheiten) . "',
                '" . date("Y-m-d H:i:s") . "'
            )") ) {
            return [
                'error' => false
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
	 * Ist eine System Authentifizierung nötig? (mit API key)
     * only if : needsUserAuth = false
	 * @return Boolean
	 */
	public function needsSystemAuth() {
		return false;
	}



}	

?>