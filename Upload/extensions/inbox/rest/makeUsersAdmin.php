<?php

class makeUsersAdmin extends AbstractRest {
	
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


        include_once PATH_EXTENSION . 'models' . DS . 'Inbox2.class.php';
        include_once PATH_EXTENSION . 'models' . DS . 'Users.class.php';
        $Users = new extInboxModelUsers();
        $Inbox = new extInboxModelInbox2();

        $users = user::getAll();

        $i = 0;

        foreach($users as $user) {
            if ( $user->getUserID() && !$Inbox->getByUserID( $user->getUserID() ) ) {
                $db = $Inbox->save([
                    'title' => 'user-'.$user->getUserID(),
                    'type' => 'user',
                    'createdUserID' => 1,
                    'createdTime' => date('Y-m-d H:i:s', time())
                ]);
                if ($db->lastID) {
                    $Users->save([
                        'inbox_id' => $db->lastID,
                        'user_id' => $user->getUserID()
                    ]);
                    $i++;
                }
            }
        }

        return ['done' => true, 'count' => $i];


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