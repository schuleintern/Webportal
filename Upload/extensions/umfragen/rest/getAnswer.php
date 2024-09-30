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
        if ( !$this->canRead() ) {
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

        //include_once PATH_EXTENSIONS . 'inbox' . DS . 'models' . DS . 'MessageBody2.class.php';
        //$MessageBodyClass = new extInboxModelMessageBody2();
        //include_once PATH_EXTENSIONS . 'inbox' . DS . 'models' . DS . 'Message2.class.php';
        //$MessageClass = new extInboxModelMessage2();

        $ret = [];
        if ($tmp_data) {
            foreach ($tmp_data as $item) {
                $foo = $item->getCollection();

    /*
                $body = $MessageBodyClass->getByUmfrage($foo['parent_id']);
                if ($body) {
                    $messages = $MessageClass->getMessagesByBody($body->getID());
                    if ($messages) {
                        foreach ($messages as $message) {
                            if ($message->getData('folder_id') != 2) { // nicht aus den "gesendet" Ordner
                                $inbox_tmp = PAGE::getFactory()->getInboxByID($message->getData('inbox_id'));
                                $collection_tmp = $inbox_tmp->getCollection(true);

                                echo '<pre>';
                                print_r($collection_tmp);
                                echo '</pre>';
                            }
                        }
                    }
                }
                */



                if (!$foo['parent_id']) {
                    $foo['parent_id'] = $foo['createdUserID'];
                }


                if (!is_array($ret[$foo['parent_id']])) {

                    $inbox = PAGE::getFactory()->getInboxByID($foo['parent_id']);
                    if ($inbox) {
                        $inbox_coll = $inbox->getCollection(true);
                        if ($inbox_coll['user_id']) {
                            $temp_user = user::getUserByID($inbox_coll['user_id']);
                            if ($temp_user) {
                                $temp_user = $temp_user->getCollection();
                            }
                            if (!$temp_user) {
                                $temp_user = [];
                            }

                            $ret[$foo['parent_id']] = [
                                'data' => [],
                                'user' => $temp_user
                            ];
                        }
                    }

                }


                $ret[$foo['parent_id']]['data'][] = $foo;
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