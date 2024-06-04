<?php


 

class extExampleMenu extends AbstractMenu
{

    public function render()
    {

        //$html = $this->getMenuItem("ext_example", 'Example', "fa fa-user");

        // if return false: make default Menu Item
        return false;
    }
}