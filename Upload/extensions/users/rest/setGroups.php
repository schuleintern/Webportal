<?php

namespace users\rest;
use AbstractRest;
use DB;
use EXTENSION;
use extInboxModelInbox2;
use extUsersModelGroups;

class setGroups extends AbstractRest
{

    protected $statusCode = 200;


    public function execute($input, $request)
    {

        $myUser = DB::getSession()->getUser();
        if (!$myUser) {
            return [
                'error' => true,
                'msg' => 'Missing User'
            ];
        }
        $acl = $this->getAcl();
        if (!$this->canWrite()) {
            return [
                'error' => true,
                'msg' => 'Kein Zugriff'
            ];
        }

        $id = (int)$input['id'];
        $state = (int)$input['state'];

        if (!$id || !$state) {
            $state = 1;
        }

        $title = (string)$input['title'];
        if (!$title) {
            return [
                'error' => true,
                'msg' => 'Missing Data: Title'
            ];
        }

        $users = json_decode((string)$_POST['users']);
        if (!$users || count($users) < 1) {
            return [
                'error' => true,
                'msg' => 'Missing Data: Users'
            ];
        }

        $userlist = [];
        foreach ($users as $user) {
            if ($user->id) {
                $userlist[] = $user->id;
            }
        }


        include_once PATH_EXTENSION . 'models' . DS . 'Groups.class.php';
        $class = new extUsersModelGroups();

        if ($class->save([
            'id' => $id,
            'title' => $title,
            'state' => $state,
            'users' => json_encode($userlist),
            'createdTime' => date('Y-m-d H:i', time()),
            'createdBy' => $myUser->getUserID()
        ])) {


            if (EXTENSION::isActive('ext.zwiebelgasse.inbox')) {
                include_once PATH_EXTENSIONS . 'inbox' . DS . 'models' . DS . 'Inbox2.class.php';
                $class = new extInboxModelInbox2();
                $class->syncUserGroups();
            }

            return [
                'success' => true
            ];

        }


        return [
            'error' => true,
            'msg' => 'Nicht Erfolgreich!'
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
        return 'POST';
    }


    /**
     * Muss der Benutzer eingeloggt sein?
     * Ist Eine Session vorhanden
     * @return Boolean
     */
    public function needsUserAuth()
    {
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
    public function needsSystemAuth()
    {
        return false;
    }

}

?>