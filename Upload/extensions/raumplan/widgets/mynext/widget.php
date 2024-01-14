<?php

/**
 *
 */
class extRaumplanWidgetMynext extends Widget
{
    private $_list = [];
    private $_count = 0;



    public function getScripts()
    {
        return [PATH_EXTENSIONS.'raumplan/widgets/mynext/script/dist/main.js'];
    }
    public function getScriptData()
    {
        return [
            'count' => $this->_count,
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
        $list = extRaumplanModelStunden::getAllByUserNext($user_id, $date);

        $this->_count = count($list);

        $max = 10;
        $i = 0;
        foreach($list as $item) {
            if ($i < $max) {
                $this->_list[] = $item->getCollection();
                $i++;
            }
        }



        $html = '<div id="app-widget-raumplan-mynext"></div>';

        return $html;
    }


}