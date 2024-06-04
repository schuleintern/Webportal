<?php

/**
 *
 */
class extPausenWidgetDashboard extends Widget
{

    public function globals() {
        include_once PATH_EXTENSIONS.'pausen' .DS. 'models' . DS . 'Aufsicht.class.php';
        $class = new extPausenModelAufsicht();
        $tmp_data = $class->getToday();

        $ret = [];
        foreach($tmp_data as $item) {
            $data = $item->getCollection(true);
            $ret[] = [
                'title' => $data['pause'] ? $data['pause']['title'] : '',
                'start' => $data['pause'] ? $data['pause']['start'] : '',
                'end' => $data['pause'] ? $data['pause']['end'] : '',
                'user' => $data['user'] ? $data['user']['name'] : '',
                'day' => $data['day']
            ];
        }
        return 'window._widget_pausen_aufsicht = '.json_encode($ret).';';
    }
    public function render($dashboard = false) {

        return '<div id="app-widget-pausen-dashboard"></div>';

    }

    public function getScripts()
    {
        return [PATH_EXTENSIONS.'pausen/widgets/dashboard/script/dist/js/chunk-vendors.js' ,PATH_EXTENSIONS.'pausen/widgets/dashboard/script/dist/js/app.js'];
    }




}