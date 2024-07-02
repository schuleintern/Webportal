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

  private $DATA_eltern_email;
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


  public static function sendMessage($data = false) {

      if (!$data) {
          return false;
      }
      if ( DB::getSettings()->getValue("extInbox-global-messageSystem") ) {

          if ($data['receiver_leader_klasse']) {
              include_once PATH_EXTENSIONS. 'inbox' .DS . 'inboxs' . DS . 'leaders_klasse.class.php';
              $inboxs = extInboxRecipientLeadersKlasse::getInboxs($data['receiver_leader_klasse']);
              $arr = [];
              if ($inboxs['data']) {
                  foreach ($inboxs['data'] as $foo) {
                      $arr[] = $foo['id'];
                  }
              }
              $data['receiver'] = '[{"typ":"leaders::klasse","content":"'.$data['receiver_leader_klasse'].'","inboxs":'.json_encode($arr).'}]';
          }

          if (!$data['sender_id']) {
              $data['sender_id'] = 1;
          }

          include_once PATH_EXTENSIONS. 'inbox' .DS . 'models' . DS . 'Message2.class.php';
          $class = new extInboxModelMessage2();
          if (!$class->sendMessage([
              'receiver' => $data['receiver'],
              'receivers_cc' => $data['receivers_cc'],
              'sender_id' => $data['sender_id'],
              'subject' => $data['subject'],
              'text' => $data['text'],
              'confirm' => $data['confirm'],
              'priority' => $data['priority'],
              'noAnswer' => $data['noAnswer'],
              'isPrivat' => $data['isPrivat'],
              'files' => $data['files']
          ])) {
                return false;
          }
          return true;

      } else {

          $messageSender = new MessageSender();

          if ($data['receiver_leader_klasse']) {
              $messageRecipientHandler = new RecipientHandler("");
              $messageRecipientHandler->addRecipient(new KlassenteamRecipient($data['receiver_leader_klasse']));
              $messageSender->setRecipients($messageRecipientHandler);
          }

          if (!$data['sender_id']) {
              $messageSender->setSender(user::getSystemUser());
          }


          if ($data['noAnswer']) {
              $messageSender->dontAllowAnswer();
          }
          if ($data['isPrivat']) {
              $messageSender->setConfidential();  // Vertraulich
          }

          $messageSender->setSubject($data['subject']);
          $messageSender->setText($data['text']);

          $messageSender->send();
          return true;
      }

      return false;

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
    public function getMenuItemByPageAndParams($page = false, $params = true) {

        if ($page && $params) {
            foreach($this->DATA_menu_item as $item) {
                if ($item['page'] === $page && $item['params'] === $params) {
                    return $item;
                }
            }
        }
        return false;
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