<?php


class setStundenplan extends AbstractRest
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
        if (!$this->canWrite()) {
            return [
                'error' => true,
                'msg' => 'Kein Zugriff'
            ];
        }

        $stundenplanID = (string)$input['stundenplanID'];
        if (!$stundenplanID || $stundenplanID == 'undefined') {
            return [
                'error' => true,
                'msg' => 'Missing Data: stundenplanID'
            ];
        }

        $day = (int)$input['day'];
        if (!$day || $day == 'undefined') {
            return [
                'error' => true,
                'msg' => 'Missing Data: Tag'
            ];
        }
        $hour = (int)$input['hour'];
        if (!$hour || $hour == 'undefined') {
            return [
                'error' => true,
                'msg' => 'Missing Data: Stunde'
            ];
        }

        include_once PATH_EXTENSION . 'models' . DS . 'Stundenplan.class.php';
        $class = new extStundenplanModelStundenplan();

        if ($db = $class->save([
            'stundeID' => (int)$input['id'],
            'stundenplanID' => $stundenplanID,
            'stundeStunde' => $hour,
            'stundeTag' => $day,
            'stundeKlasse' => (string)$input['klasse'],
            'stundeRaum' => (string)$input['room'],
            'stundeLehrer' => (string)$input['teacher'],
            'stundeFach' => (string)$input['fach']
        ])) {


            return [
                'error' => false,
                'success' => true
            ];
        }

        return [
            'error' => true,
            'msg' => 'Error'
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