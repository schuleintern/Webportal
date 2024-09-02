<?php

class setItem extends AbstractRest
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

        $user_id = (int)$input['user_id'];
        if ( !$user_id || $user_id == 'undefined' ) {
            return [
                'error' => true,
                'msg' => 'Missing Data: User'
            ];
        }

        $text = (string)$input['text'];
        if ( !$text || $text == 'undefined' ) {
            return [
                'error' => true,
                'msg' => 'Missing Data: Title'
            ];
        }

        $tags = $_POST['tags'];
        if ( !$tags || $tags == 'undefined' ) {
            $tags = '';
        }



        include_once PATH_EXTENSION . 'models' . DS .'Item.class.php';
        $class = new extAkteModelItem();

        if ( $db = $class->save([
            'id' => $id,
            'text' => $text,
            'user_id' => $user_id,
            'tags' => $tags,
            'state' => 1,
            'createdUserID' => $user->getUserID(),
            'createdTime' => date('Y-m-d H:i', time())
        ]) ) {

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