<?php


class deleteGroupsAdmin extends AbstractRest
{

    protected $statusCode = 200;


    public function execute($input, $request)
    {


        $userID = DB::getSession()->getUser()->getUserID();
        if (!$userID) {
            return [
                'error' => true,
                'msg' => 'Missing User ID'
            ];
        }

        $acl = $this->getAcl();
        if (!$this->canDelete()) {
            return [
                'error' => true,
                'msg' => 'Kein Zugriff'
            ];
        }

        $id = (int)$input['id'];


        include_once PATH_EXTENSION . 'models' . DS . 'Inbox2.class.php';

        $class = new extInboxModelInbox2();
        $data = $class->getByID($id);
        if ($data) {
            if ($data->delete()) {

                include_once PATH_EXTENSION . 'models' . DS . 'Users.class.php';
                $Users = new extInboxModelUsers();
                $users = $Users->getByParentID($id);
                if ($users) {
                    foreach ($users as $user) {
                        $user->delete();
                    }
                }

                return ['succeed' => true];
            }
        }


        return ['error' => true, 'msg' => 'Fehler bei Löschen!'];


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