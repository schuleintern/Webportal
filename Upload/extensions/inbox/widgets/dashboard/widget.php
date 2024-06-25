<?php

/**
 *
 */
class extInboxWidgetDashboard extends Widget
{

    public function globals() {

    }

    public function render($dashboard = false) {

        return '<div id="app-widget-inbox-dashboard"></div>';

    }

    public function getScripts()
    {
        return [PATH_EXTENSIONS.'inbox/widgets/dashboard/script/dist/js/chunk-vendors.js' ,PATH_EXTENSIONS.'inbox/widgets/dashboard/script/dist/js/app.js'];
    }




}