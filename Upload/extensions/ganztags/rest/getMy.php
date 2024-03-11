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


        include_once PATH_EXTENSION . 'models' . DS . 'Leaders2.class.php';
        include_once PATH_EXTENSION . 'models' . DS . 'Day2.class.php';

        $Leaders = new extGanztagsModelLeaders2();
        $Day = new extGanztagsModelDay2();

        $leader = $Leaders->getByParentID($userID);

        if ($leader[0]) {
            $leader = $leader[0];
        }

        if ($leader) {
            $leader_id = $leader->getID();

            $dates = $Day->getByDate($today);

            foreach ($dates as $date) {
                if ($date->getData('leader_id') == $leader_id) {
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

        if ($data) {
            foreach ($data as $group) {

                $html = '';

                $group->getSchueler($day);
                $foo = $group->getCollection(true);
                if ($foo['date']) {
                    $foo['date'] = DateFunctions::getNaturalDateFromMySQLDate($foo['date']);
                }


                if ($foo['schueler']) {
                    foreach($foo['schueler'] as $key => $schueler) {

                        foreach($absenzen as $absenz) {
                            if  ( $schueler['asvid'] == $absenz->getSchueler()->getAsvID() ) {
                                $foo['schueler'][$key]['absenz'] = $absenz->getStundenAsString().' Stunde<br><i>'.nl2br($absenz->getBemerkung()).'</i> '.nl2br($absenz->getGanztagsNotiz());
                            }
                        }
                    }
                }




                $ret[] = $foo;


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
