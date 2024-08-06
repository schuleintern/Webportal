<?php


/**
 *
 */
class extStundenplanWidgetDashboard extends Widget
{


    public function render($dashboard = false)
    {


        /*
        include_once PATH_EXTENSIONS . 'vplan' . DS. 'models' . DS . 'List.class.php';

        include_once $this->getData()['path'] .DS. 'models' . DS . 'Kalender.class.php';

        $kalenders = extKalenderModelKalender::getAllAllowed(1);


        $today = date('Y-m-d', time());

        echo '<script>window._widget_kalender_events = {}; </script>';
        self::loadDate("today", $today, $kalenders);
*/


        return '<div id="app-widget-stundenplan-dashboard"></div>';


    }

    public function getScripts()
    {
        return [PATH_EXTENSIONS . 'stundenplan/widgets/dashboard/script/dist/app.js'];
    }


}