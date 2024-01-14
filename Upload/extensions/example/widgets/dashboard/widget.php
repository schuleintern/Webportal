<?php

/**
 *
 */
class extExampleWidgetDashboard extends Widget
{


    public function getScripts()
    {
        return [PATH_EXTENSIONS.'example/widgets/dashboard/script/dist/main.js'];
    }

    public function render() {
        return '<div id="app-widget-example-dashboard"></div>';
    }

}