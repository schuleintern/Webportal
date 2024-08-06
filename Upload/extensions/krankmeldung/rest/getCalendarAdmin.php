<?php

class getCalendarAdmin extends AbstractRest
{

    protected $statusCode = 200;


    public function execute($input, $request)
    {


        $ret = [];

        $status = [0, 1, 2];

        /*
        $status_str = (string)$request[2];
        if ($status_str == 'open') {
            $status = [1];
        } else if ($status_str == 'list') {
            $status = [1, 2, 3];
        }
        */

        //$acl = $this->getAcl();
        if (!$this->canAdmin()) {
            return [
                'error' => true,
                'msg' => 'Kein Zugriff'
            ];
        }

        date_default_timezone_set('Europe/Berlin');
        setlocale(LC_TIME, array('de_DE', 'de_DE'));

        if ($request[2] == 'prev') {
            $from = $request[3];
            if ($from) {
                $from = DateFunctions::getUnixTimeFromMySQLDate($from) - 86400;
            }
        } else if ($request[2] == 'next') {
            $from = $request[3];
            if ($from) {
                $from = DateFunctions::getUnixTimeFromMySQLDate($from) + 86400;
            }
        } else {
            $from = time();
        }

        if ($from) {
            $today = date('Y-m-d', $from);
            $todayNice = date('l - d.m.Y', $from);
            $todayNumber = date('N', $from);
        }


        if (!$today) {
            return [
                'error' => true,
                'msg' => 'Missing Date'
            ];
        }

        include_once PATH_EXTENSION . 'models' . DS . 'Antrag.class.php';
        $class = new extKrankmeldungModelAntrag();
        $tmp_data = $class->getByDate( $today );

        $ext_studenplan = EXTENSION::isActive('ext.zwiebelgasse.stundenplan');
        if ( $ext_studenplan ) {
            $currentPlan = stundenplandata::getCurrentStundenplan();
        }

        foreach ($tmp_data as $item) {
            $collection = $item->getCollection(true);

            $user = user::getUserByID($collection['user_id']);

            if ( $ext_studenplan && $currentPlan ) {
                if ($user) {
                    if ( $user->isPupil() ) {
                        $key = 'grade';
                        $value = $user->getPupilObject()->getKlasse();
                    } else if ( $user->isTeacher() ) {
                        $key = 'teacher';
                        $value = $user->getTeacherObject()->getKuerzel();
                    }

                    if ($key && $value) {
                        $plan = $currentPlan->getPlan( [$key, $value] );
                        if ($plan && $plan[$todayNumber-1]) {
                            $collection['plan'] = $plan[$todayNumber-1];
                        }
                    }
                }
            }

            if ($user && $user->isPupil() ) {
                $leistungsnachweis = Leistungsnachweis::getByClass([$value], $today, $today);
                if ($leistungsnachweis && count($leistungsnachweis) > 0) {
                    $collection['lnw'] = [];
                    foreach ($leistungsnachweis as $lnw) {
                        $collection['lnw'][] = [
                            'art' => $lnw->getArtLangtext(),
                            'fach' => $lnw->getFach(),
                            'user' => $lnw->getLehrer(),
                            'stunde' => join(',', $lnw->getStunden())
                        ];
                    }
                }
            }


            $ret[] = $collection;
        }

        return [
            'data' => $ret,
            'date' => $today,
            'dateNice' => $todayNice
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