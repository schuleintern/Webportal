<?php

/**
 *
 */
class extKalenderWidgetCounter extends Widget
{


    public function render($dashboard = false) {

        include_once($this->getData()['path'] . DS . 'models' . DS . 'Event.class.php');
        include_once $this->getData()['path'] .DS. 'models' . DS . 'Kalender.class.php';

        $kalenderDB = extKalenderModelKalender::getAll(1);
        $userType = DB::getSession()->getUser()->getUserTyp(true);
        $kalenders = [];
        if (count($kalenderDB) > 0) {
            foreach ($kalenderDB as $item) {
                $arr = $item->getCollection(true);
                if ( $this->getGroupACL( $arr['acl']['groups'], $userType ) === 1 ) {
                    $kalenders[] = $arr['id'];
                }
            }
        }
        $today = date('Y-m-d', time());
        $events = extKalenderModelEvent::getDayByKalender($today, $kalenders);
        $anz = count($events);
        if ( count($events) >= 1) {
            return '<a href="index.php?page=ext_kalender&view=default" class="btn">
                    <i class="fa fa-calendar"></i>
                    <span class="label bg-red">'.$anz.'</span>
                </a>';
        }


    }


    function getGroupACL($groups,$userType) {
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