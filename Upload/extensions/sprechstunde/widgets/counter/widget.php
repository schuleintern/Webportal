<?php

/**
 *
 */
class extSprechstundeWidgetCounter extends Widget
{

    private function getCount() {
        include_once( $this->getData()['path'].DS.'models'.DS.'Date.class.php' );
        $data = extSprechstundeModelDate::getMyInFuture( DB::getSession()->getUser()->getUserID() );
        if ($data) {
            return count($data);
        }
        return false;
    }

    public function render() {
        $anz = $this->getCount();
         if ($anz) {
             $ret = '<a href="index.php?page=ext_sprechstunde&view=list" class="btn"><i class="fa fa-people-arrows"></i>';
             $ret .= '<span class="label bg-red">'.$anz.'</span>';
             $ret .= '</a>';
             return $ret;
         }
         return false;
    }
}