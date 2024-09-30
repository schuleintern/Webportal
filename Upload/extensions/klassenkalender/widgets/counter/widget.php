<?php

/**
 *
 */
class extKlassenkalenderWidgetCounter extends Widget
{

    public function globals()
    {

    }

    public function render($dashboard = false)
    {

        include_once($this->getData()['path'] . DS . 'models' . DS . 'Event.class.php');
        include_once $this->getData()['path'] . DS . 'models' . DS . 'Kalender.class.php';
        $KALENDER = new extKlassenkalenderModelKalender();
        $kalenderDB = $KALENDER->getByState([1]);
        $userType = DB::getSession()->getUser()->getUserTyp(true);
        $kalenders = [];
        if ($kalenderDB && count($kalenderDB) > 0) {
            foreach ($kalenderDB as $item) {
                $arr = $item->getCollection(true);
                if ($this->getGroupACL($arr['acl']['groups'], $userType) === 1) {
                    $kalenders[] = $arr;
                }
            }
        }

        $EVENT = new extKlassenkalenderModelEvent();
        $today = date('Y-m-d', time());
        $events = $EVENT->getDayByKalender($today, $kalenders);
        if ($events && count($events) >= 1) {
            $anz = count($events);
            return '<a href="index.php?page=ext_klassenkalender&view=default" class="btn">
                    <i class="fa fa-graduation-cap"></i>
                    <span class="label bg-red">' . $anz . '</span>
                </a>';
        }


    }


    function getGroupACL($groups, $userType)
    {
        if ((int)DB::getSession()->getUser()->isAnyAdmin() === 1) {
            return 1;
        }
        if ($userType == 'isPupil') {
            return (int)$groups['schueler']['read'];
        }
        if ($userType == 'isTeacher') {
            return (int)$groups['lehrer']['read'];
        }
        if ($userType == 'isEltern') {
            return (int)$groups['eltern']['read'];
        }
        if ($userType == 'isNone') {
            return (int)$groups['none']['read'];
        }
        return false;
    }


}