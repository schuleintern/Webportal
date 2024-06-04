<?php

class setDateAktivity extends AbstractRest {
	
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
        if ( !$this->canWrite() ) {
            return [
                'error' => true,
                'msg' => 'Kein Zugriff'
            ];
        }

        $arr = (object)[
            'date' => $input['date'],
            'title' => $input['title'],
            'type' => $input['type'],
            'leader_id' => (int)$input['leader'],
            'group_id' => (int)$input['group'],
            'duration' => (int)$input['duration']
        ];



        if (!$arr->leader_id) {
            return [
                'error' => true,
                'msg' => 'Missing leaderID'
            ];
        }

        if (!$arr->date) {
            return [
                'error' => true,
                'msg' => 'Missing date'
            ];
        }

        if ($arr->group_id) {
            include_once PATH_EXTENSION . 'models' . DS . 'Activity2.class.php';
            $class = new extGanztagsModelActivity2();
            $group = $class->getByID($arr->group_id);
            if ($group) {
                $arr->color = $group->getData('color');
                $arr->info = $group->getData('info');
                $arr->room = $group->getData('room');
                $arr->duration = $group->getData('duration');
            }

        }

        $arr->createdBy = $userID;
        $arr->createdTime = date("Y-m-d H:i:s",time());


        include_once PATH_EXTENSION . 'models' . DS . 'Day2.class.php';
        $Day = new extGanztagsModelDay2();
        if ( $Day->save((array)$arr) ) {
            return [
                'success' => true
            ];
        }

        return [
            'error' => true,
            'msg' => 'Fehler beim Speichern!'
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