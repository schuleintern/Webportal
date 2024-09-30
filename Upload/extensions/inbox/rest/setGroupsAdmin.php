<?php


class setGroupsAdmin extends AbstractRest
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

        $title = (string)$input['title'];
        if (!$title || $title == 'undefined') {
            return [
                'error' => true,
                'msg' => 'Missing Data'
            ];
        }

        include_once PATH_EXTENSION . 'models' . DS . 'Inbox2.class.php';
        $class = new extInboxModelInbox2();

        if ($db = $class->save([
            'id' => $id,
            'title' => $title,
            'type' => 'group'
        ])) {


            if (!$id && $db->lastID) {
                $id = $db->lastID;
            }
            $childs = (string)$_POST['childs'];
            if (isset($childs)) {
                include_once PATH_EXTENSION . 'models' . DS . 'Users.class.php';
                $Users = new extInboxModelUsers();
                $childs_obj = json_decode($childs);
                //if ($childs_obj) {
                    $oldData = $Users->getByParentID($id);
                    if ($oldData) {
                        foreach ($oldData as $oldChild) {
                            $found = false;
                            foreach ($childs_obj as $child) {
                                if ($oldChild->getID() == $child->id) {
                                    $found = true;
                                }
                            }
                            if ($found === false) {
                                $oldChild->delete();
                            }
                        }
                    }
                    foreach ($childs_obj as $child) {

                        $Users->save([
                            'id' => $child->id,
                            'inbox_id' => $id,
                            'user_id' => $child->user_id
                        ]);

                    }
                //}
            }

            return ['succeed' => true];
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