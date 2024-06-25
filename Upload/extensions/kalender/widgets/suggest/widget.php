<?php

/**
 *
 */
class extKalenderWidgetSuggest extends Widget
{

    public function globals()
    {

    }

    public function render($dashboard = false) {


        include_once $this->getData()['path'] .DS. 'models' . DS . 'Event.class.php';

        $data = extKalenderModelEvent::getAllByStatus([2]);


        if (count($data) > 0) {
            return '<a href="index.php?page=ext_kalender&view=suggest&admin=true" class="btn">
                    <i class="fa fa-calendar-plus"></i>
                    <span class="label bg-red">'.count($data).'</span>
                </a>';
        }


    }



}