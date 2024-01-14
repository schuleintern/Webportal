<?php

/**
 *
 */
class extFileshareWidgetAttachment extends Widget
{


    public function render($dashboard = false)
    {


        return '<div id="app-widget-fileshare-attachment"></div>';



    }

    public function getScriptData()
    {


        include_once ( PATH_EXTENSIONS.'fileshare/models/List.class.php' );



        return [
            "apiURL" => "rest.php/fileshare",
            "acl" => ['write' => 1],
            "randFolder" => extFileshareModelList::generateFolderName()
        ];
    }


    public function getScripts()
    {
        return [
            PATH_EXTENSIONS . 'fileshare/widgets/attachment/script/dist/js/chunk-vendors.js',
            PATH_EXTENSIONS . 'fileshare/widgets/attachment/script/dist/js/app.js'
        ];
    }


}