<?php

/**
 *
 */
class extChatWidgetCounter extends Widget
{


    public function getCount() {
        include_once ( $this->getData()['path'].DS.'models'.DS.'Tutoren.class.php' );
        $tutoren = extLerntutorenModelTutoren::getAllByStatus('created');
        return count($tutoren);
    }

    public function render() {
        return '<a href="index.php" class="btn">
                    <i class="fa fa-comments"></i>
                    <span class="label bg-red">'.$this->getCount().'</span>
                </a>';
    }

}