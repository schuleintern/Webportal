<?php

/**
 *
 */
class extRaumplanWidgetMytoday extends Widget
{
    private $_list = [];



    public function getScripts()
    {
        return [PATH_EXTENSIONS.'raumplan/widgets/mytoday/script/dist/main.js'];
    }
    public function getScriptData()
    {
        return [
            'count' => count($this->_list),
            'list' => $this->_list
        ];
    }



    public function render() {

        $user_id = DB::getSession()->getUserID();

        if (!$user_id) {
            return '';
        }

        $date = date('Y-m-d', time());


        include_once ( $this->getData()['path'].DS.'models'.DS.'Stunden.class.php' );
        $list = extRaumplanModelStunden::getAllByUserDate($user_id, $date);


        foreach($list as $item) {
            $this->_list[] = $item->getCollection();
        }



        $html = '<div id="app-widget-raumplan-myheute"></div>';

        return $html;
    }


}