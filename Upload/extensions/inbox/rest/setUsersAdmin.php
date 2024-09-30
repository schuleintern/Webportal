<?php


class setUsersAdmin extends AbstractRest
{

    protected $statusCode = 200;


    public function execute($input, $request)
    {

        //$user = DB::getSession()->getUser();
        $acl = $this->getAcl();
        if (!$this->canAdmin()) {
            return [
                'error' => true,
                'msg' => 'Kein Zugriff'
            ];
        }

        $id = (int)$input['id'];
        if (!$id) {
            return [
                'error' => true,
                'msg' => 'Missing ID'
            ];
        }

        $isPublic = (string)$_POST['isPublic'];

        $kill = true;
        $isPublicObj = json_decode($isPublic);
        foreach ($isPublicObj as $item ) {
            if($item) {
                $kill = false;
            }
        }
        if ($kill) {
            $isPublic = NULL;
        }


        include_once PATH_EXTENSION . 'models' . DS . 'Users.class.php';
        $class = new extInboxModelUsers();

        $inboxUser = $class->getByParentID($id)[0];
        if ($inboxUser) {
            if ( $class->update([
                'id' => $inboxUser->getID(),
                'isPublic' => $isPublic
            ])) {
                return ['succeed' => true];
            }
        }



        return ['error' => true, 'msg' => 'Fehler bei Speichern!'];

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