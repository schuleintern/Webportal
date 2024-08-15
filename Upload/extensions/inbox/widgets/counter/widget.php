<?php

/**
 *
 */
class extInboxWidgetCounter extends Widget
{
    private function getCount() {
        include_once( './../framework/lib/models/extensionsModel.class.php');
        include_once( $this->getData()['path'].DS.'models'.DS.'Inbox2.class.php' );
        include_once( $this->getData()['path'].DS.'models'.DS.'Message2.class.php' );
        $Inbox = new extInboxModelInbox2();
        $Message = new extInboxModelMessage2();
        $data = $Inbox->getByUserID( DB::getSession()->getUser()->getUserID() );
        if ($data) {
            $count = 0;
            foreach($data as $item) {
                $foo = $Message->getUnreadMessages($item->getData('id'), 1);
                if ($foo) {
                    $count += count($foo);
                }
            }
            return $count;
        }
        return false;
    }
    public function render() {

        $anz = $this->getCount();
        if ($anz) {
            return '<a href="index.php?page=ext_inbox" class="btn"><i class="fa fa-envelope"></i><span class="label bg-red">'.$anz.'</span></a>';
        } else {
            return '<a href="index.php?page=ext_inbox" class="btn"><i class="fa fa-envelope"></i></a>';
        }
        return false;

    }



}