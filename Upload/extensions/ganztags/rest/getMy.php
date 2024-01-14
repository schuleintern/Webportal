<?php

class getMy extends AbstractRest
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
        if (!$this->canRead()) {
            return [
                'error' => true,
                'msg' => 'Kein Zugriff'
            ];
        }

        $data = [];
        $today = date('Y-m-d', time());

        include_once PATH_EXTENSION . 'models' . DS . 'Leaders.class.php';
        include_once PATH_EXTENSION . 'models' . DS . 'Day.class.php';

        $leader = extGanztagsModelLeaders::getByUserID($userID);

        if ($leader) {
            $leader_id = $leader->getID();

            $dates = extGanztagsModelDay::getByDate($today);

            foreach ($dates as $date) {
                if ($date->getLeaderID() == $leader_id) {
                    $data[] = $date;
                }
            }
         
        }


        $day = date('D',strtotime($today));
        $days_arr = ['Mon' => 'mo','Tue' => 'di','Wed' => 'mi','Thu' => 'do','Fri' => 'fr','Sat' => 'sa','Son' => 'so'];
        $day = $days_arr[$day];


        include_once("../framework/lib/data/absenzen/Absenz.class.php");
        $absenzen = Absenz::getAbsenzenForDate($today, null, "");

        $ret = [];

        foreach ($data as $group) {

            $html = '';

            $group->getSchueler($day);
            $ret[] = $group->getCollection(true);

            /*
            foreach($absenzen as $absenz) {
                if  ( $schueler['asvid'] == $absenz->getSchueler()->getAsvID() ) {
                    $isAbsenz = true;
                    if ($schueler['info']) {
                        $schueler['info'] .= '<br>';
                    }
                    $schueler['info'] .= '<b>Absenz:</b> '.$absenz->getStundenAsString().' Stunde<br><i>'.nl2br($absenz->getBemerkung()).'</i> '.nl2br($absenz->getGanztagsNotiz());
                }
            }
            */

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
