<?php


class getDefault extends AbstractRest
{

    protected $statusCode = 200;


    public function execute($input, $request)
    {

        $user = DB::getSession()->getUser();
        if (!$user) {
            return [
                'error' => true,
                'msg' => 'Missing User'
            ];
        }
        $acl = $this->getAcl();
        if (!$this->canRead()) {
            return [
                'error' => true,
                'msg' => 'Kein Zugriff'
            ];
        }

        $showFilter = false;

        //$currentPlanID = stundenplandata::getCurrentStundenplanID();

        //echo $currentPlanID;

        //$currentPlan = stundenplandata::getCurrentStundenplan();
        //$maxStunden = stundenplandata::getMaxStunden();


        $anzStunden = DB::getSettings()->getValue("ext-stundenplan-anzahlstunden");
        if (!$anzStunden) {
            $anzStunden = 6;
        }
        $stunden = [];
        for ($i = 0; $i < $anzStunden; $i++) {
            $stunden[] = $i;
        }

        $stundenZeiten = [];
        for ($i = 1; $i < 6; $i++) {
            if (DB::getSettings()->getValue("ext-stundenplan-everydayothertimes") > 0 || $i == 1) {
                for ($s = 1; $s <= $anzStunden; $s++) {
                    $stundenZeiten[] = [
                        'begin' => DB::getSettings()->getValue("ext-stundenplan-stunde-$i-$s-start"),
                        'ende' => DB::getSettings()->getValue("ext-stundenplan-stunde-$i-$s-ende")
                    ];
                }
            }
        }


        $klassen = [];
        $klassenData = klasse::getAllKlassen();
        foreach ($klassenData as $klasse) {

            $stufe = $klasse->getKlassenstufe() ? $klasse->getKlassenstufe() : (int)$klasse->getKlassenName();
            if ($stufe) {
                if (!is_array($klassen[$stufe])) {
                    $klassen[$stufe] = [];
                }
                $klassen[$stufe][] = $klasse->getKlassenName();
            } else {
                $klassen[] = $klasse->getKlassenName();
            }
        }

        $teachers = [];
        $teacherData = lehrer::getAll();
        foreach ($teacherData as $teacher) {
            $teacher_user = $teacher->getUser();
            if ($teacher_user) {
                $teachers[] = $teacher_user->getCollection(true);
            }
        }


        $currentPlan = stundenplandata::getCurrentStundenplan();
        $rooms = $currentPlan->getAll('room');
        $fach = $currentPlan->getAll('subject');


        $myKlassen = [];
        if ($user->isPupil()) {

        }
        if ($user->isTeacher()) {
            $myKlassen = klasseDB::getByTeacher($user->getTeacherObject());
            $showFilter = true;
        }
        if ($user->isEltern()) {
            $myKlassen = $user->getElternObject()->getKlassenAsArray();
        }
        if ($user->isNone()) {
            $showFilter = true;
        }
        if ($this->canAdmin()) {
            $showFilter = true;
        }

        $dayNum = DATE('N', time());


        return [
            'stunden' => $stunden,
            'zeiten' => $stundenZeiten,
            'klassen' => $klassen,
            'teachers' => $teachers,
            'rooms' => $rooms,
            'fach' => $fach,
            'myKlassen' => $myKlassen,
            'showFilter' => $showFilter,
            'daynum' => $dayNum,
            'userType' => $user->getUserTyp(true)
        ];

    }


    /**
     * Set Allowed Request Method
     * (GET, POST, ...)
     *
     * @return String
     */
    public function getAllowedMethod()
    {
        return 'GET';
    }


    /**
     * Muss der Benutzer eingeloggt sein?
     * Ist Eine Session vorhanden
     * @return Boolean
     */
    public function needsUserAuth()
    {
        return false;
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
    public function needsSystemAuth()
    {
        return false;
    }

    public function needsAppAuth()
    {
        return true;
    }


}

?>