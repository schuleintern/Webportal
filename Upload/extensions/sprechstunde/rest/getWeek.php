<?php

class getWeek extends AbstractRest {
	
	protected $statusCode = 200;

	public function execute($input, $request) {

        $acl = $this->getAcl();
        if ((int)$acl['rights']['read'] !== 1 && (int)DB::getSession()->getUser()->isAnyAdmin() !== 1 ) {
            return [
                'error' => true,
                'msg' => 'Kein Zugriff'
            ];
        }

        $user = DB::getSession()->getUser();
        $userID = $user->getUserID();
        if (!$userID) {
            return [
                'error' => true,
                'msg' => 'Missing User ID'
            ];
        }

        if (!$input['von']) {
            //die('missing data');
            return [
                'error' => true,
                'msg' => 'Missing Data: Von'
            ];
        }

        include_once PATH_EXTENSION . 'models' . DS . 'Helpers.class.php';
        include_once PATH_EXTENSION . 'models' . DS . 'Slot.class.php';
        include_once PATH_EXTENSION . 'models' . DS . 'Date.class.php';


        $start = DateTime::createFromFormat('H:i', extSprechstundeModelHelpers::getCalenderHourStart());
        $hours = extSprechstundeModelHelpers::getCalenderHours();

        $showDays = array(
            0 => DB::getSettings()->getValue('extSprechstunde-day-mo') || 0,
            1 => DB::getSettings()->getValue('extSprechstunde-day-di') || 0,
            2 => DB::getSettings()->getValue('extSprechstunde-day-mi') || 0,
            3 => DB::getSettings()->getValue('extSprechstunde-day-do') || 0,
            4 => DB::getSettings()->getValue('extSprechstunde-day-fr') || 0,
            5 => DB::getSettings()->getValue('extSprechstunde-day-sa') || 0,
            6 => DB::getSettings()->getValue('extSprechstunde-day-so') || 0,
        );


        $days = ['Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa', 'So'];



        $slots = [];

        if ( $user->isPupil() ) {
            $teachers = $this->getMyKlassenLehrer();
            $slots = array_merge($slots, extSprechstundeModelSlot::getByTeachers($teachers, 'schueler'));
        }

        if ( $user->isEltern() ) {
            $teachers = $this->getMyKlassenLehrer();
            $slots = array_merge($slots, extSprechstundeModelSlot::getByTeachers($teachers, 'eltern'));
        }

        //if ( $user->isTeacher() ) {
            //$slots = array_merge($slots, extSprechstundeModelSlot::getAllByUser($user->getUserID()));
        //}

        //if ( $input['admin'] && $user->isAnyAdmin() ) {
        if ( $input['admin'] && $this->canAdmin() ) {
            $slots = array_merge($slots, extSprechstundeModelSlot::getAll());
        } else {
            $slots = array_merge($slots, extSprechstundeModelSlot::getAllByUser($user->getUserID()));
        }


        $dates = extSprechstundeModelDate::getAllByWeek((int)$input['von']);

        $week = [];
        for ($i = 0; $i < $hours; $i++) {

            $week[$i] = [
                "label" => $start->format('H:i')
            ];

            $hourArr = explode(':', $start->format('H:i'));
            $hourInt = (int)( $hourArr[0].$hourArr[1]);
            $hourNextInt = $hourInt + 100;

            for ($d = 0; $d < 7; $d++) {

                if ( $showDays[$d] ) {
                    $day = (int)$input['von'] + ($d * 86400);

                    $arr = [
                        "day" => date('Y-m-d', $day)
                    ];

                    $dayName = $days[ date('w', $day) -1];

                    $findSlots = [];
                    foreach ($slots as $slot) {
                        $slotTimeArr = explode(':', $slot->getTime() );
                        $slotTimeInt = (int)( $slotTimeArr[0].$slotTimeArr[1]);
                        if ( $slot->getDay() == $dayName && $hourInt <= $slotTimeInt && $slotTimeInt < $hourNextInt ) {
                            //echo $hourInt. ' - '.$slotTimeInt.' - '.$hourNextInt.'<br>';
                            $foo = $slot->getCollection();

                            foreach ($dates as $date) {
                                if ($slot->getID() == $date->getSlotID()) {

                                    if ( $user->isPupil() || $user->isEltern() ) {
                                        if ($userID == $date->getUserID()) {
                                            $foo['date'] = $date->getCollection();
                                        } else {
                                            $foo['dateSet'] = true;
                                        }

                                    } else if ($user->isTeacher()) {
                                        $foo['date'] = $date->getCollection();

                                    } else if ( $this->canAdmin() ) {
                                        $foo['date'] = $date->getCollection();
                                    }


                                }
                            }

                            $findSlots[] = $foo;
                        }
                    }
                    if (count($findSlots) > 0) {

                        usort($findSlots, function($a, $b) {
                            return $a['time'] <=> $b['time'];
                        });

                        $arr['slots'] = $findSlots;
                    }

                    $week[$i][$d] = $arr;
                }

            }

            $start->add(new DateInterval('PT1H'));

        }
/*
           echo '<pre>';
           print_r($plan);
           echo '</pre>';
*/
           //exit;

        if (count($week) > 0) {
            return $week;
        }




        return [
			'error' => true,
			'msg' => 'Return Data!'
		];

	}

    /**
     *
     * TODO: SEHR LANGSAM !!!!
     *
     * @return array
     */
    private function getMyKlassenLehrer() {
        $ret = [];
        $klassen = klasse::getMyKlassen();
        if (count($klassen) > 0) {
            foreach($klassen as $klasse) {
                $lehrers = $klasse->getKlassenlehrer();
                foreach($lehrers as $lehrer) {
                    //echo $lehrer->getUserID().'-';
                    $ret[$lehrer->getUserID()] = true;
                }
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