<?php

class setEvents extends AbstractRest
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

        if (!$input['kalender_id']) {
            return [
                'error' => true,
                'msg' => 'Missing Data: CalenderID'
            ];
        }


        $acl = $this->getAcl();


        include_once PATH_EXTENSION . 'models' . DS . 'Kalender.class.php';
        $Klaender = new extKlassenkalenderModelKalender();
        $calendar = $Klaender->getByID($input['kalender_id']);

        $access = false;
        if ($this->canWrite()) {
            $access = true;
        }
        if (!$access) {
            if ($kadmins = $calendar->getAdmins()) {
                if (in_array($userID, $kadmins)) {
                    $access = true;
                }
            }
        }

        if (!$access) {
            return [
                'error' => true,
                'msg' => 'Kein Zugriff'
            ];
        }

        $input['id'] = (int)$input['id'];
        $input['stunde'] = (int)$input['stunde'];
        $input['kalender_id'] = (int)$input['kalender_id'];
        $input['user_id'] = (int)$userID;

        if ($input['id']) {
            $input['modifiedTime'] = date('Y-m-d H:i', time());
        } else {
            $input['createdTime'] = date('Y-m-d H:i', time());
        }

        if (!$input['status']) {
            $input['status'] = 1;
        }
        if (!$input['typ']) {
            return [
                'error' => true,
                'msg' => 'Missing Data: Typ'
            ];
        }

        if ($input['typ'] == 'lnw') {

            if (!$input['stunde']) {
                return [
                    'error' => true,
                    'msg' => 'Missing Data: Stunde'
                ];
            }
            if (!$input['art']) {
                return [
                    'error' => true,
                    'msg' => 'Missing Data: Art'
                ];
            }
            if (!$input['fach']) {
                return [
                    'error' => true,
                    'msg' => 'Missing Data: Fach'
                ];
            }
            if (!$input['teacher']) {
                return [
                    'error' => true,
                    'msg' => 'Missing Data: Teacher'
                ];
            }

            $fach = fach::getByID($input['fach']);
            $teacher = lehrer::getByXMLID($input['teacher']);
            include_once PATH_EXTENSION . 'models' . DS . 'Lnw.class.php';
            $LNWS = new extKlassenkalenderModelLnws();
            $lnw = $LNWS->getByID($input['art']);

            $input['title'] = $calendar->getData('title').': '.$lnw->getData('short').' - '.$fach->getKurzform().' - '.$teacher->getKuerzel();

        }

        if (!$input['title']) {
            return [
                'error' => true,
                'msg' => 'Missing Data: Title'
            ];
        }
        if (!$input['dateStart']) {
            return [
                'error' => true,
                'msg' => 'Missing Data: Date'
            ];
        }
        if (!$input['kalender_id']) {
            return [
                'error' => true,
                'msg' => 'Missing Data: Kalender'
            ];
        }

        $dateStart = DB::getDB()->escapeString($input['dateStart']);
        if ( !$dateStart ||$dateStart == '0000-00-00') {
            $dateStart = NULL;
        } else {
            $dateStart = $dateStart;
        }
        $input['dateStart'] = $dateStart;
        $dateEnd = DB::getDB()->escapeString($input['dateEnd']);
        if ( !$dateEnd || $dateEnd == '0000-00-00') {
            $dateEnd = NULL;
        } else {
            $dateEnd = $dateEnd;
        }
        $input['dateEnd'] = $dateEnd;

        if (!$input['art']) {
            $input['art'] = 0;
        }


        include_once PATH_EXTENSION . 'models' . DS . 'Event.class.php';
        $class = new extKlassenkalenderModelEvent();
        if ($insert_id = $class->add($input)) {

            return [
                'success' => true,
                'id' => $insert_id
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