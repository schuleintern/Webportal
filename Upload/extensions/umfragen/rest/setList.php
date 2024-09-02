<?php

class setList extends AbstractRest
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
        if ( !$this->canWrite() ) {
            return [
                'error' => true,
                'msg' => 'Kein Zugriff'
            ];
        }

        $id = (int)$input['id'];

        $title = (string)$input['title'];
        if ( !$title || $title == 'undefined' ) {
            return [
                'error' => true,
                'msg' => 'Missing Data: Title'
            ];
        }

        $state = (int)$input['state'];
        if ( !$state || $state == 'undefined' ) {
            $state = 0;
        }

        
        $childs = $_POST['childs'];
        if ( !$childs || $childs == 'undefined' ) {
            return [
                'error' => true,
                'msg' => 'Missing Data: Fragen'
            ];
        }

        if ($childs) {
            $childs = json_decode($childs);
        }

        $userlist = (string)$_POST['userlist'];
        $users = [];
        if ($userlist) {
            $userlist = json_decode($userlist);
            foreach( $userlist as $foo) {
                $users[] = (int)$foo->id;
            }
        }

        include_once PATH_EXTENSIONS.'umfragen'.DS . 'models' . DS .'List.class.php';
        $class = new extUmfragenModelList();
        if ( $class->setListWithItems([
            'id' => $id,
            'title' => $title,
            'state' => $state,
            'createdTime' => date('Y-m-d H:i', time()),
            'createdUserID' => $user->getUserID(),
            'userlist' => $users
        ], $childs) ) {
            return [
                'success' => true
            ];
        }


        /*
        include_once PATH_EXTENSION . 'models' . DS .'List.class.php';
        include_once PATH_EXTENSION . 'models' . DS .'Item.class.php';
        $class = new extUmfragenModelList();
        $sub = new extUmfragenModelItem();

        if ( $db = $class->save([
            'id' => $id,
            'title' => $title,
            'state' => $state,
            'createdTime' => date('Y-m-d H:i', time()),
            'createdUserID' => $user->getUserID(),
            'userlist' => json_encode($users)
        ]) ) {

            if (!$id) {
                $id = $db->lastID;
            }
            
            if ($childs && $id) {
                $i = 1;
                foreach($childs as $child) {
                    $sub->save([
                        'id' => $child->id,
                        'list_id' => $id,
                        'title' => $child->title,
                        'typ' => $child->typ,
                        'sort' => $i
                    ]);
                    $i++;
                }
            }
            return [
                'success' => true
            ];

        }
        */


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