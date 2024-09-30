<?php

class setAntrag extends AbstractRest
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
        if (DB::getSession()->getUser()->isPupil()) {
            $volljaehrige = DB::getSettings()->getBoolean("extKrankmeldung-form-volljaehrige");
            if ($volljaehrige == 1) {
                $this->acl['rights']['write'] = 0;
                $alter = (int)DB::getSession()->getPupilObject()->getAlter();
                if ($alter >= 18) {
                    $this->acl['rights']['write'] = 1;
                }
            }
        }

        if (!$this->canWrite()) {
            return [
                'error' => true,
                'msg' => 'Kein Zugriff'
            ];
        }

        $info = trim(nl2br($input['info']));
        if ($info == 'undefined') {
            $info = '';
        }


        $user = (int)$input['user'];
        if (!$user) {
            return [
                'error' => true,
                'msg' => 'Missing Data: Benutzer*in'
            ];
        }

        $dateStart = $input['dateStart'];
        if (!$dateStart) {
            return [
                'error' => true,
                'msg' => 'Missing Data: Datum Start'
            ];
        }

        $dateAdd = (int)$input['dateAdd'];
        if (!$dateAdd) {
            return [
                'error' => true,
                'msg' => 'Missing Data: Datum Bis'
            ];
        }

        $dateEnd = DateFunctions::addDaysToMySqlDate($dateStart,$dateAdd-1);
        if (!$dateEnd) {
            return [
                'error' => true,
                'msg' => 'Missing Data: Datum Ende'
            ];
        }

        if(!DateFunctions::isSQLDateTodayOrLater($dateStart)) {
            return [
                'error' => true,
                'msg' => 'Fehler: Datum ist vor Heute'
            ];
        }



        include_once PATH_EXTENSION . 'models' . DS . 'Antrag.class.php';
        $class = new extKrankmeldungModelAntrag();


        if ($class->add($userID, $user, $dateStart, $dateEnd,$dateAdd,  $info)) {

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