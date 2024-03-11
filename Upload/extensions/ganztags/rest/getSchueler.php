<?php

class getSchueler extends AbstractRest {
	
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
        if (!$this->canRead()) {
            return [
                'error' => true,
                'msg' => 'Kein Zugriff'
            ];
        }

        include_once PATH_EXTENSION . 'models' . DS . 'Schueler2.class.php';
        $class = new extGanztagsModelSchueler2();
        $data = $class->getAll();

        $tage_summe = 0;
        $tage_diff = 0;
        $schueler_summe = 0;

        $ret = [];
        if (count($data) > 0) {
            foreach ($data as $item) {

                $foo = $item->getCollection(true);
                $tage_summe += $foo['anz'];
                $tage_diff += $foo['diff'];
                $schueler_summe++;
                $ret[] = $foo;
            }
        }

        return [
            'list' => $ret,
            'details' => [
                'tage_summe' => $tage_summe,
                'tage_zaehl' => $tage_summe/4,
                'tage_diff' => $tage_diff,
                'schueler_summe' => $schueler_summe
            ]
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