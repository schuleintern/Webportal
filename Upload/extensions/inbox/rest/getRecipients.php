<?php

class getRecipients extends AbstractRest {
	
	protected $statusCode = 200;


	public function execute($input, $request) {


        $userID = DB::getSession()->getUser()->getUserID();
        if (!$userID) {
            return [
                'error' => true,
                'msg' => 'Missing User ID'
            ];
        }

        $acl = $this->getAcl();
        if ( !$this->canRead() ) {
            return [
                'error' => true,
                'msg' => 'Kein Zugriff'
            ];
        }



        /*
            Fachschaften
        */
        $fachschaften = [];
        $faecher = fach::getAll();
        foreach($faecher as $fach) {
            /*
            $users = [];
            $teachers = $fach->getFachLehrer();
            foreach($teachers as $teacher) {
                $foo = $teacher->getUser()->getCollection();
                $users[] = $foo['id'];
            }
            */
            $fachschaften[] = [
                'id' => $fach->getID(),
                'title' => $fach->getKurzform()
                //,'users' => $users
            ];

        }

        /*
            Gruppen
        */
        $groups = [];
        $dataSQL = DB::getDB()->query("SELECT id, title FROM ext_inboxs WHERE type = 'group'");
        while ($data = DB::getDB()->fetch_array($dataSQL, true)) {
            $groups[] = $data;
        }


        /*
            Klassen
        */
        $klassen = [];
        $klassenData = klasse::getAllKlassen();
        foreach($klassenData as $klasse) {
            $stufe = $klasse->getKlassenstufe() ? $klasse->getKlassenstufe() : (int)$klasse->getKlassenName(); 
            if ($stufe) {
                if ( !is_array($klassen[ $stufe ]) ) {
                    $klassen[ $stufe ] = [];
                }
                $klassen[ $stufe ][] = $klasse->getKlassenName();
            }
        }

        /*
            Verwaltung
        */

        $verwaltung = [];



        if ( $this->isAllowed('extInbox-acl-verwaltung') == 1 ) {
            $verwaltung = [
                ['id' => 'schulleitung', 'title' => 'Schulleitung'],
                ['id' => 'sekretariat', 'title' => 'Sekretariat'],
                ['id' => 'personalrat', 'title' => 'Personalrat'],
                ['id' => 'hausmeister', 'title' => 'Hausmeister']
            ];
        }


        return [
            'klassen' => $klassen,
            'group' => $groups,
            'fachschaft' => $fachschaften,
            'verwaltung' => $verwaltung
        ];



	}

    private function isAllowed($val) {

        $content = DB::getSettings()->getValue($val);
        if ($content) {
            $aclSetting = json_decode($content);
            if ($aclSetting) {
                return $aclSetting->{DB::getSession()->getUser()->getUserTyp(true)};

            }
        }
        return false;
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