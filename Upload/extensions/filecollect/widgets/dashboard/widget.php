<?php

/**
 *
 */
class extFilecollectWidgetDashboard extends Widget
{


    public function getScripts()
    {
        return [PATH_EXTENSIONS.'filecollect/widgets/dashboard/script/dist/main.js'];
    }

    public function render() {


        return '<div id="app-widget-filecollect-dashboard"></div>';
    }

}