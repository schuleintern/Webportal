<?php

/**
 *
 */
class extSchulinfoWidgetInfo extends Widget
{


    public function getScripts()
    {
        return [PATH_EXTENSIONS.'schulinfo/widgets/info/script/dist/main.js'];
    }
    public function getScriptData()
    {
        return [
            'schulinfo_name' => DB::getSettings()->getValue("schulinfo-name"),
            "schulinfo_name_zusatz" => DB::getSettings()->getValue("schulinfo-name-zusatz"),
            "schulinfo_adresse1" => DB::getSettings()->getValue("schulinfo-adresse1"),
            "schulinfo_adresse2" => DB::getSettings()->getValue("schulinfo-adresse2"),
            "schulinfo_plz" => DB::getSettings()->getValue("schulinfo-plz"),
            "schulinfo_ort" => DB::getSettings()->getValue("schulinfo-ort"),
            "schulinfo_telefon" => DB::getSettings()->getValue("schulinfo-telefon"),
            "schulinfo_fax" => DB::getSettings()->getValue("schulinfo-fax"),
            "schulinfo_email" => DB::getSettings()->getValue("schulinfo-email"),
            "schulinfo_homepage" => DB::getSettings()->getValue("schulinfo-homepage")
        ];
    }



    public function render() {


        $html = '<div id="app-widget-schulinfo-info"></div>';

        return $html;
    }


}