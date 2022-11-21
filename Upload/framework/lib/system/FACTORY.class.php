<?php

/**
 * Globale FACTORY class
 * 
 * @author: Christian Marienfeld
 */

class FACTORY {

  private $DATA_lehrer;
  private $DATA_users_groups;
  private $DATA_schueler;
  private $DATA_users;
  private $DATA_users_groups_own;
  private $DATA_menu_item;

  public function __construct() {

    $this->DATA_users = $this->load_users();
    $this->DATA_users_groups_own = $this->load_users_groups_own();
    $this->DATA_eltern_email = $this->load_eltern_email();
    $this->DATA_schueler = $this->load_schueler();
    $this->DATA_lehrer = $this->load_lehrer();
    $this->DATA_users_groups = $this->load_users_groups();
    $this->DATA_menu_item = $this->load_menue_item_active();
    
  }


    /**
     * Table: menu_item
     */

    public function getMenuItemByParentID($id = false, $active = true) {

        $ret = [];
        $id = (int)$id;
        if ($id) {
            foreach($this->DATA_menu_item as $item) {
                if ((int)$item['parent_id'] === $id) {
                    if ($active == true && (int)$item['active'] === 1 ) {
                        $ret[] = $item;
                    } else {
                        $ret[] = $item;
                    }
                }
            }
        }
        return $ret;
    }
    public function getMenuItemByMenuID($id = false, $active = true) {

        $ret = [];
        $id = (int)$id;
        if ($id) {
            foreach($this->DATA_menu_item as $item) {
                if ((int)$item['menu_id'] === $id) {
                    if ($active == true && (int)$item['active'] === 1 ) {
                        $ret[] = $item;
                    } else {
                        $ret[] = $item;
                    }
                }
            }
        }
        return $ret;
    }

    private function load_menue_item_active() {

        $result = DB::getDB()->query("SELECT * FROM menu_item  ORDER BY sort");
        $arr = [];
        while($item = DB::getDB()->fetch_array($result, true)) {
            $arr[] = $item;
        }
        return $arr;
    }

  /**
   * Table: users
   */
  
  public function getUserByID($id = false) {

    if ($id) {
      foreach($this->DATA_users as $item) {
        if ($item['userID'] == $id) {
          return $item;
        }
      }
    }
    return false;
  }

  public function getUserByASV($id = false) {

    if ($id) {
      foreach($this->DATA_users as $item) {
        if ($item['userAsvID'] == $id) {
          return $item;
        }
      }
    }
    return false;
  }


  private function load_users() {

    $result = DB::getDB()->query("SELECT * FROM users ");
    $arr = [];
    while($item = DB::getDB()->fetch_array($result)) {
      $arr[] = $item;
    }
    return $arr;
  }


  /**
   * Table: users_groups_own
   */
  
  public function getUserGroupsOwnByName($name = false) {

    if ($name) {
      foreach($this->DATA_users_groups_own as $item) {
        if ($item['groupName'] == $name) {
          return $item;
        }
      }
    }
    return false;
  }

  private function load_users_groups_own() {

    $result = DB::getDB()->query("SELECT * FROM users_groups_own ");
    $arr = [];
    while($item = DB::getDB()->fetch_array($result)) {
      $arr[] = $item;
    }
    return $arr;
  }
  
  

  /**
   * Table: eltern_email
   */
  
  public function getElternEmailByUserID($id = false) {

    if ($id) {
      foreach($this->DATA_eltern_email as $item) {
        if ($item['elternUserID'] == $id) {
          return $item;
        }
      }
    }
    return false;
  }


  private function load_eltern_email() {

    $result = DB::getDB()->query("SELECT * FROM eltern_email ");
    $arr = [];
    while($item = DB::getDB()->fetch_array($result)) {
      $arr[] = $item;
    }
    return $arr;
  }

  /**
   * Table: schueler
   */
  
  public function getSchuelerByID($id = false) {

    if ($id) {
      foreach($this->DATA_schueler as $item) {
        if ($item['schuelerUserID'] == $id) {
          return $item;
        }
      }
    }
    return false;
  }


  private function load_schueler() {

    $result = DB::getDB()->query("SELECT * FROM schueler ");
    $arr = [];
    while($item = DB::getDB()->fetch_array($result)) {
      $arr[] = $item;
    }
    return $arr;
  }


  /**
   * Table: lehrer
   */
  
  public function getLehrerByID($id = false) {

    if ($id) {
      foreach($this->DATA_lehrer as $item) {
        if ($item['lehrerUserID'] == $id) {
          return $item;
        }
      }
    }
    return false;
  }


  private function load_lehrer() {

    $result = DB::getDB()->query("SELECT * FROM lehrer ");
    $arr = [];
    while($item = DB::getDB()->fetch_array($result)) {
      $arr[] = $item;
    }
    return $arr;
  }


  /**
   * Table: user_groups
   */

  public function getUserGroupsByID($id = false) {
    
    if ($id) {
      $ret = [];
      foreach($this->DATA_users_groups as $item) {
        if ($item['userID'] == $id) {
          $ret[] = $item;
        }
      }
      return $ret;
    }
    return false;
  }

  public function getGroupsByUserID($id = false) {

    if ($id) {
      $ret = [];
      foreach($this->DATA_users_groups as $item) {    
        if ($item['userID'] == $id) {
          $ret[] = usergroup::getGroupByName($item['groupName']);
        }
      }
      return $ret;
    }
    return false;
  }

  private function load_users_groups() {

    $result = DB::getDB()->query("SELECT * FROM users_groups ");
    $arr = [];
    while($item = DB::getDB()->fetch_array($result)) {
      $arr[] = $item;
    }
    return $arr;

  }


}




?>