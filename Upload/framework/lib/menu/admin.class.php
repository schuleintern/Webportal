<?php


/**
 * Menü der Seite
 * @author Christian
 *
 */
class SystemAdminMenu extends AbstractMenu {

    public $data = [];

  public $menu = [];

  public function __construct() {

      $this->menu =  Menue::getFromAlias('admin');

      $cats = $this->menu->getItemsDeep();

      foreach ($cats as $cat) {
          $this->data[] = [
              "id" => $cat['id'],
              "title" => $cat['title'],
              "html" => $this->getDBMenuItems($cat['id'])
          ];
      }


      //echo $this->menu;

  }


}



?>
