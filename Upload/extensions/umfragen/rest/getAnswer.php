<?php

class getAnswer extends AbstractRest
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

        $id = (int)$request[2];
        if (!$id) {
            return [
                'error' => true,
                'msg' => 'Missing ID'
            ];
        }




        include_once PATH_EXTENSION . 'models' . DS .'Answer.class.php';

        $class = new extUmfragenModelAnswer();
        $tmp_data = $class->getByParentID($id);


        $ret = [];
        if ($tmp_data) {
            foreach ($tmp_data as $item) {
                $foo = $item->getCollection();
                if ( !is_array($ret[$foo['createdUserID']]) ) {

                    $temp_user = user::getUserByID( $foo['createdUserID'] );
                    if ($temp_user) {
                        $temp_user = $temp_user->getCollection();
                    }
                    if (!$temp_user) {
                        $temp_user = [];
                    }
                    
                    $ret[$foo['createdUserID']] = [
                        'data' => [],
                        'user' => $temp_user
                    ];
                }
                $ret[$foo['createdUserID']]['data'][] = $foo;
            }
        }

        return $ret;

    }


    /**
     * Set Allowed Request Method
     * (GET, POST, ...)
     *
     * @return String
     */
    public function getAllowedMethod()
    {
        return 'GET';
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