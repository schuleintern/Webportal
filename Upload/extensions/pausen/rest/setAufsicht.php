<?php

class setAufsicht extends AbstractRest
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

        $day = (int)$input['day'];
        if ( !$day ) {
            return [
                'error' => true,
                'msg' => 'Missing Data: Tag'
            ];
        }
        $pausen_id = (int)$input['pausen_id'];
        if ( !$pausen_id ) {
            return [
                'error' => true,
                'msg' => 'Missing Data: Pause'
            ];
        }
        $user_id = (int)$input['user_id'];
        if ( !$user_id ) {
            return [
                'error' => true,
                'msg' => 'Missing Data: Benutzer'
            ];
        }
        $second_id = (int)$input['second_id'];
        if ( !$second_id ) {
            $second_id = NULL;
        }
        $state = (int)$input['state'];
        if ( !$state ) {
            $state = 0;
        }

        include_once PATH_EXTENSION . 'models' . DS .'Aufsicht.class.php';
        $class = new extPausenModelAufsicht();

        if ( $class->save([
            'id' => $id,
            'day' => $day,
            'pausen_id' => $pausen_id,
            'user_id' => $user_id,
            'second_id' => $second_id,
            'state' => $state,
            'createdTime' => date('Y-m-d', time()),
            'createdUserID' => $user->getUserID()
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