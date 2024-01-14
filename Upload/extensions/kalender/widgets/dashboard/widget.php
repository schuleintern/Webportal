<?php

/**
 *
 */
class extKalenderWidgetDashboard extends Widget
{


    public function render($dashboard = false) {


        include_once($this->getData()['path'] . DS . 'models' . DS . 'Event.class.php');
        include_once $this->getData()['path'] .DS. 'models' . DS . 'Kalender.class.php';

        $kalenders = extKalenderModelKalender::getAllAllowed(1);


        $today = date('Y-m-d', time());

        echo '<script>window._widget_kalender_events = {}; </script>';
        self::loadDate("today", $today, $kalenders);



        return '<div id="app-widget-kalender-dashboard"></div>';


    }

    public function getScripts()
    {
        return [PATH_EXTENSIONS.'kalender/widgets/dashboard/script/dist/js/chunk-vendors.js' ,PATH_EXTENSIONS.'kalender/widgets/dashboard/script/dist/js/app.js'];
    }

    static function loadDate($var, $date, $kalenders) {
        $events = extKalenderModelEvent::getDayByKalender($date, $kalenders);
        if ( count($events) >= 1) {
            $eventsCollection = [];
            foreach($events as $event) {
                $eventsCollection[] = $event->getCollection();
            }

            echo '<script>window._widget_kalender_events.'.$var.' = '.json_encode($eventsCollection).';</script>';
        }
    }



}