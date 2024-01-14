<?php

class getCalender extends AbstractRest
{

    protected $statusCode = 200;

    private $week = [];

    public function execute($input, $request)
    {

        $date_von = (int)$request[2];
        if (!$date_von) {
            return [
                'error' => true,
                'msg' => 'Missing Date von'
            ];
        }
        $date_bis = (int)$request[3];
        if (!$date_bis) {
            return [
                'error' => true,
                'msg' => 'Missing Date bis'
            ];
        }



        $userID = DB::getSession()->getUser()->getUserID();
        if (!$userID) {
            return [
                'error' => true,
                'msg' => 'Missing User ID'
            ];
        }

        $acl = $this->getAcl();
        if ((int)$acl['rights']['read'] !== 1 && (int)DB::getSession()->isMember($this->extension['adminGroupName']) !== 1) {
            return [
                'error' => true,
                'msg' => 'Kein Zugriff'
            ];
        }

        include_once PATH_EXTENSION . 'models' . DS . 'Day.class.php';

        $this->week = array(
            'mo' => DB::getSettings()->getValue("ext_ganztags-day-mo") ? true : false,
            'di' => DB::getSettings()->getValue("ext_ganztags-day-di") ? true : false,
            'mi' => DB::getSettings()->getValue("ext_ganztags-day-mi") ? true : false,
            'do' => DB::getSettings()->getValue("ext_ganztags-day-do") ? true : false,
            'fr' => DB::getSettings()->getValue("ext_ganztags-day-fr") ? true : false,
            'sa' => DB::getSettings()->getValue("ext_ganztags-day-sa") ? true : false,
            'so' => DB::getSettings()->getValue("ext_ganztags-day-so") ? true : false
        );
        $i = 0;
        foreach ($this->week as $day => $val) {
            if ($val) {
                $time = $date_von + (86400 * $i);
                $date = date('Y-m-d', $time );
                $this->week[$day] = (object)['date' => $date, 'content' => []  ] ;

                //setlocale(LC_TIME, 'de_DE', 'deu_deu');

                $day = date('D', $time);
                $days_arr = ['Mon' => 'mo','Tue' => 'di','Wed' => 'mi','Thu' => 'do','Fri' => 'fr','Sat' => 'sa','Son' => 'so'];
                $day = $days_arr[$day];
                $days = extGanztagsModelDay::getByDate($date);
                if ($days && $day) {
                    foreach ($days as $d) {
                        $d->getSchueler($day);
                        $this->week[$day]->content[] = $d->getCollection(true);
                    }
                }

            }
            $i++;
        }



        include_once PATH_EXTENSION . 'models' . DS . 'Activity.class.php';
        $activity = extGanztagsModelActivity::getAll();
        foreach($activity as $group) {
            $group->getSchueler();
        }
        $this->addToContent($activity);


        include_once PATH_EXTENSION . 'models' . DS . 'Groups.class.php';
        $groups = extGanztagsModelGroups::getAll();
        foreach($groups as $group) {
            $group->getSchueler();
            $group->getWeekSchueler();
        }
        $this->addToContent($groups);





        return $this->week;



	}

    private function addToContent($items) {

        if ($items && $this->week) {
            foreach ($items as $item) {
                $days = $item->getDays();


                foreach($days as $day => $val) {

                    if ( $val  && $this->week[$day]  && is_array($this->week[$day]->content) ) {
                        if ( $this->findContent($this->week[$day]->content, $item->getID()) == false ) {
                            array_unshift($this->week[$day]->content, $item->getCollection(true));
                        }
                    }
                }
            }
        }
    }

    private function findContent($arr, $val) {

        $found = false;
        if ($arr && count($arr) > 0 ) {
            foreach ($arr as $content) {
                if ($content['group_id'] && $content['group_id'] == $val ) {
                    $found = true;
                }
            }
        }
        return $found;
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