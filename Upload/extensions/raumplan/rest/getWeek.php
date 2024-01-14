<?php

class getWeek extends AbstractRest {
	
	protected $statusCode = 200;

	public function execute($input, $request) {


        $userID = DB::getSession()->getUser()->getUserID();
        if (!$userID) {
            return [
                'error' => true,
                'msg' => 'Missing User ID'
            ];
        }

        if (!$input['bis']) {
            //die('missing data');
            return [
                'error' => true,
                'msg' => 'Missing Data: Bis'
            ];
        }
        if (!$input['von']) {
            //die('missing data');
            return [
                'error' => true,
                'msg' => 'Missing Data: Von'
            ];
        }
        if (!$input['room']) {
            //die('missing data');
            return [
                'error' => true,
                'msg' => 'Missing Data: Room'
            ];
        }
/*
        $week = [
            $input['von'],
            $input['von'] + (1 * 86400),
            $input['von'] + (2 * 86400),
            $input['von'] + (3 * 86400),
            $input['von'] + (4 * 86400),
            $input['von'] + (5 * 86400)
        ];
*/



/*
        for ($i = 0; i < 7; i++) {

            if ( $i == 0 && DB::getSettings()->getValue('extRaumplan-day-mo') ) {
                $week[] = $input['von'] + (0 * 86400);
            }

        }
        */
        if ( DB::getSettings()->getValue('extRaumplan-day-mo') ) {
            $week[] = $input['von'] + (0 * 86400);
        } else {
            $week[] = 0;
        }
        if ( DB::getSettings()->getValue('extRaumplan-day-di') ) {
            $week[] = $input['von'] + (1 * 86400);
        } else {
            $week[] = 0;
        }
        if ( DB::getSettings()->getValue('extRaumplan-day-mi') ) {
            $week[] = $input['von'] + (2 * 86400);
        } else {
            $week[] = 0;
        }
        if ( DB::getSettings()->getValue('extRaumplan-day-do') ) {
            $week[] = $input['von'] + (3 * 86400);
        } else {
            $week[] = 0;
        }
        if ( DB::getSettings()->getValue('extRaumplan-day-fr') ) {
            $week[] = $input['von'] + (4 * 86400);
        } else {
            $week[] = 0;
        }
        if ( DB::getSettings()->getValue('extRaumplan-day-sa') ) {
            $week[] = $input['von'] + (5 * 86400);
        } else {
            $week[] = 0;
        }
        if ( DB::getSettings()->getValue('extRaumplan-day-so') ) {
            $week[] = $input['von'] + (6 * 86400);
        } else {
            $week[] = 0;
        }


        //print_r($week);
        //setlocale(LC_TIME, "de_DE.utf8");
        //date_default_timezone_set("Europe/Berlin");

        /*$tage = [
            "Mon" => "Mo"
        ];*/

        $plan = array();
        for ($s = 0; $s < stundenplandata::getMaxStunden(); $s++) {

            $plan[] = array();

            $i = 0;
            foreach ($week as $date) {
                if ($week[$i]) {
                    $plan[$s][] = array("day" => [date('Y-m-d', $week[$i])]);
                } else {
                    $plan[$s][] = array();
                }

                $i++;
            }
        }
        //$room = 'A010';
        //echo $getRoom;
        //print_r($plan);
        //exit;

        $sql = "stundeRaum LIKE '" . DB::getDB()->escapeString($input['room']) . "'";

        $currentPlanID = stundenplandata::getCurrentStundenplanID();
        if (!($currentPlanID > 0)) {
            new errorPage("Leider steht aktuell kein Stundenplan zur Verfügung!");
        }
        $stundenplan = new stundenplandata($currentPlanID);

        $stundenData = DB::getDB()->query("SELECT * FROM stundenplan_stunden WHERE stundenplanID='" . $stundenplan->getID() . "' AND $sql");
        while ($s = DB::getDB()->fetch_array($stundenData)) {
            $stunde = array(
                "teacher" => $s['stundeLehrer'],
                "room" => $s['stundeRaum'],
                "grade" => $s['stundeKlasse'],
                "subject" => $s['stundeFach']
            );

            //$plan[$s['stundeStunde'] - 1][$s['stundeTag'] - 1][] = $stunde;

            $day = $s['stundeTag'] - 1;

            if ( $day == 0 && DB::getSettings()->getValue('extRaumplan-day-mo') ) {
                $plan[$s['stundeStunde'] - 1][$day][] = $stunde;
            }
            if ( $day == 1 && DB::getSettings()->getValue('extRaumplan-day-di') ) {
                $plan[$s['stundeStunde'] - 1][$day][] = $stunde;
            }
            if ( $day == 2 && DB::getSettings()->getValue('extRaumplan-day-mi') ) {
                $plan[$s['stundeStunde'] - 1][$day][] = $stunde;
            }
            if ( $day == 3 && DB::getSettings()->getValue('extRaumplan-day-do') ) {

                //print_r($stunde);
                $plan[$s['stundeStunde'] - 1][$day][] = $stunde;
                //print_r($plan[$s['stundeStunde'] - 1][$day]);
            }
            if ( $day == 4 && DB::getSettings()->getValue('extRaumplan-day-fr') ) {
                $plan[$s['stundeStunde'] - 1][$day][] = $stunde;
            }
            if ( $day == 5 && DB::getSettings()->getValue('extRaumplan-day-sa') ) {
                $plan[$s['stundeStunde'] - 1][$day][] = $stunde;
            }
            if ( $day == 6 && DB::getSettings()->getValue('extRaumplan-day-so') ) {
                $plan[$s['stundeStunde'] - 1][$day][] = $stunde;
            }


        }

        //print_r($plan);
        //exit;


        $a = 0;
        foreach ($week as $date) {

            $datum = date('Y-m-d', $date);

            $sql = ' stundeDatum = "'.$datum.'" AND stundeRaum LIKE "'.DB::getDB()->escapeString($input['room']).'"';
            $raumplanData = DB::getDB()->query("SELECT * FROM raumplan_stunden WHERE state = 1 AND stundenplanID='" . $stundenplan->getID() . "' AND $sql ");
            while ($s = DB::getDB()->fetch_array($raumplanData)) {
                $stunde = array(
                    "id" => $s['stundeID'],
                    "teacher" => $s['stundeLehrer'],
                    "room" => $s['stundeRaum'],
                    "grade" => $s['stundeKlasse'],
                    "subject" => $s['stundeFach'],
                    "state" => 'unique',
                    "createdBy" => $s['createdBy'],
                    "createdTime" => $s['createdTime']
                );
                if ($userID == $stunde['createdBy']) {
                    $stunde["createdSelf"] = true;
                }

                $plan[$s['stundeStunde'] - 1][ $a ][] = $stunde;
                //print_r($s);

            }
            $a++;
        }


/*
           echo '<pre>';
           print_r($plan);
           echo '</pre>';
*/
           //exit;

        if ($plan) {
            return $plan;
        }
        //echo json_encode($plan);



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

}	

?>