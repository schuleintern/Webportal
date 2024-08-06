<?php

/**
 *
 */
class extKlassenkalenderWidgetDashboard extends Widget
{

    public function globals()
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

        $today = date('Y-m-d', time());
        $data = self::loadDate("today", $today, $kalenders);

        if ($data) {
            echo '<script>window._widget_klassenkalender_events = ' . json_encode($data) . ';</script>';
        } else {
            echo '<script>window._widget_klassenkalender_events = {};</script>';
        }


    }

    public function render($dashboard = false)
    {

        return '<div id="app-widget-klassenkalender-dashboard"></div>';


    }

    public function getScripts()
    {
        return [PATH_EXTENSIONS . 'klassenkalender/widgets/dashboard/script/dist/js/chunk-vendors.js',
            PATH_EXTENSIONS . 'klassenkalender/widgets/dashboard/script/dist/js/app.js'];

    }

    static function loadDate($var, $date, $kalenders)
    {

        $EVENT = new extKlassenkalenderModelEvent();

        $events = $EVENT->getDayByKalender($date, $kalenders);

        $ret = [];

        if ($events) {
            $eventsCollection = [];
            foreach ($events as $event) {
                $eventsCollection[] = $event->getCollection();
            }
            $ret[$var] = $eventsCollection;
            //echo '<script>>window._widget_kalender_events.'.$var.' = '.json_encode($eventsCollection).';</script>';
        }
        return $ret;
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