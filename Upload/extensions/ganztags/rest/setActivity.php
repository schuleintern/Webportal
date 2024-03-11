<?php

class setActivity extends AbstractRest {
	
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
        if (!$this->canWrite() ) {
            return [
                'error' => true,
                'msg' => 'Kein Zugriff'
            ];
        }


        $title = $input['title'];
        if (!$title) {
            return [
                'error' => true,
                'msg' => 'Missing Data'
            ];
        }
        $room = $input['room'];
        if (!$room || $room == 'undefined') {
            $room = '';
        }
        $info = $input['info'];
        if (!$info || $info == 'undefined') {
            $info = '';
        }
        $color = $input['color'];
        if (!$color || $color == 'undefined') {
            $color = '';
        }


        include_once PATH_EXTENSION . 'models' . DS . 'Activity2.class.php';
        $class = new extGanztagsModelActivity2();

        if ( $class->save([
            'id' => (int)$input['id'],
            'title' => $title,
            'leader_id' => (int)$input['leader_id'],
            'type' => 'activity',
            'room' => $room,
            'color' => $color,
            'info' => $info,
            'days' => $_POST['days'],
            'duration' => (int)$input['duration']
        ]) ) {
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