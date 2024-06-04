<?php

/**
 *
 */
class extLerntutorenWidgetFreigaben extends Widget
{
    private $_tutoren = [];



    public function getScripts()
    {
        return [PATH_EXTENSIONS.'lerntutoren/widgets/freigaben/script/dist/main.js'];
    }
    public function getScriptData()
    {
        return [
            'count' => count($this->_tutoren),
            'tutoren' => $this->_tutoren,
            'apiURL' =>  "rest.php/lerntutoren"
        ];
    }




    public function render() {

        include_once ( $this->getData()['path'].DS.'models'.DS.'Tutoren.class.php' );
        $tutoren = extLerntutorenModelTutoren::getAllByStatus('created');

        foreach($tutoren as $item) {
            $this->_tutoren[] = $item->getCollection();
        }

        $html = '<div id="app-widget-lerntutoren-freigaben"></div>';

        return $html;
    }


}