<?php

class setUser extends AbstractRest
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
        if (!$this->canWrite()) {
            return [
                'error' => true,
                'msg' => 'Kein Zugriff'
            ];
        }

        $id = (int)$input['id'];


        $vorname = (string)$input['vorname'];
        if (!$vorname) {
            return [
                'error' => true,
                'msg' => 'Missing Data: Vorname'
            ];
        }
        $nachname = (string)$input['nachname'];
        if (!$nachname) {
            return [
                'error' => true,
                'msg' => 'Missing Data: Nachname'
            ];
        }
        $username = (string)$input['username'];
        if (!$username) {
            return [
                'error' => true,
                'msg' => 'Missing Data: Benutzername'
            ];
        }
        $data = [
            'userID' => $id,
            'userName' => $username,
            'userFirstName' => $vorname,
            'userLastName' => $nachname
        ];

        $password = (string)$input['password'];
        if ($password) {
            $data['userCachedPasswordHash'] = login::hash($password);
            $data['userCachedPasswordHashTime'] = time();
        }

        if (!$data['userID']) {
            if (!$password) {
                return [
                    'error' => true,
                    'msg' => 'Missing Data: Passwort'
                ];
            }
            $data['userNetwork'] = 'SCHULEINTERN';

            $exist = user::getByUsername($data['userName']);
            if ($exist) {
                return [
                    'error' => true,
                    'msg' => 'Fehler: Benutzername schon vergeben'
                ];
            }
        }


        include_once PATH_EXTENSION . 'models' . DS . 'Users.class.php';
        $class = new extUsersModelUsers();

        if ($class->save($data)) {

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