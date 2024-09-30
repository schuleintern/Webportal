<?php

/**
 *
 */
class extKalenderWidgetDashboard extends Widget
{

    public function globals() {
        include_once($this->getData()['path'] . DS . 'models' . DS . 'Event.class.php');
        include_once $this->getData()['path'] .DS. 'models' . DS . 'Kalender.class.php';

        $kalenders = extKalenderModelKalender::getAllAllowed(1);

        $today = date('Y-m-d', time());
        $data = self::loadDate("today", $today, $kalenders);

        if ($data) {
            echo '<script>window._widget_kalender_events = '.json_encode($data).';</script>';
        } else {
            echo '<script>window._widget_kalender_events = {};</script>';
        }


    }

    public function render($dashboard = false) {

        return '<div id="app-widget-kalender-dashboard"></div>';


    }

    public function getScripts()
    {
        return [PATH_EXTENSIONS.'kalender/widgets/dashboard/script/dist/js/chunk-vendors.js' ,PATH_EXTENSIONS.'kalender/widgets/dashboard/script/dist/js/app.js'];
    }

    static function loadDate($var, $date, $kalenders) {
        $events = extKalenderModelEvent::getDayByKalender($date, $kalenders);

        $ret = [];

        if ( $events ) {
            $eventsCollection = [];
            foreach($events as $event) {
                $eventsCollection[] = $event->getCollection();
            }
            $ret[$var] = $eventsCollection;
            //echo '<script>>window._widget_kalender_events.'.$var.' = '.json_encode($eventsCollection).';</script>';
        }
        return $ret;
    }



}