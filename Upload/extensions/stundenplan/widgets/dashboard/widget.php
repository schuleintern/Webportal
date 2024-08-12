<?php

/**
 *
 */
class extStundenplanWidgetDashboard extends Widget
{

    public function globals() {

        $today = time();
        $day = date('N', $today) -1;

        echo '<script>
                window._widget_stundenplan_apiKey = "'.DB::getGlobalSettings()->apiKey.'";
                window._widget_stundenplan_day = '.$day.'
              </script>';

    }

    public function render($dashboard = false) {

        return '<div id="app-widget-stundenplan-dashboard"></div>';

    }

    public function getScripts()
    {
        return [PATH_EXTENSIONS.'stundenplan/widgets/dashboard/script/dist/js/chunk-vendors.js' ,PATH_EXTENSIONS.'stundenplan/widgets/dashboard/script/dist/js/app.js'];
    }




}