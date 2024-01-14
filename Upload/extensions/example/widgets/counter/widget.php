<?php

/**
 *
 */
class extExampleWidgetCounter extends Widget
{


    public function render($dashboard = false) {
        return '<a href="index.php" class="btn">
                    <i class="fa fa-envelope"></i>
                    <span class="label bg-red">1</span>
                </a>';
    }

}