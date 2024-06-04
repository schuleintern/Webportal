<?php

/**
 *
 */
class extSprechstundeWidgetDashboard extends Widget
{


    public function render($dashboard = false) {


        include_once( $this->getData()['path'].DS.'models'.DS.'Date.class.php' );
        $data = extSprechstundeModelDate::getMyInFuture( DB::getSession()->getUser()->getUserID() );


        echo '<script>window._widget_sprechstunde_dates = '.json_encode($data).';</script>';

        return '<h4><i class="fa fas fa-people-arrows"></i> Sprechstunde</h4><div id="app-widget-sprechstunde-dashboard"></div>';


    }

    public function getScripts()
    {
        return [
            PATH_EXTENSIONS.'sprechstunde/widgets/dashboard/script/dist/js/chunk-vendors.js' ,
            PATH_EXTENSIONS.'sprechstunde/widgets/dashboard/script/dist/js/app.js'
        ];
    }


}