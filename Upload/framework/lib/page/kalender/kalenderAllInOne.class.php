<?php

/**
 * API Kalender
 *
 * @author Christian Marienfeld
 *
 */
class kalenderAllInOne extends AbstractPage {

  protected $title = "API Kalender";

  private $kalender = [];

  public function __construct() {

    parent::__construct(["Kalender"]);
    $this->checkLogin();


  }

  public function execute() {

    $acl = json_encode( $this->getAcl() );
    //$userID = DB::getSession()->getUserID();


      if ( $_REQUEST['action'] == 'icsfeed') {
          header("Location: rest.php/GetKalenderIcsFeed");
          exit;
      }



      $this->setSubmenu([
          [
              "label" => "ICS Feed",
              "href" => "?page=kalenderAllInOne&action=icsfeed&apiKey=".DB::getGlobalSettings()->apiKey,
              "labelClass" => "btn btn-blau margin-r-xs"
          ]
      ]);

    $submenuHTML = $this->getSubmenu();

    eval("echo(\"" . DB::getTPL()->get("kalender/kalenderAllInOne"). "\");");

  }

  

  public static function getEintragFromDate($datum) {
    
    if (!$datum) {
      return [];
    }

    $kalenders = [];
    $result = DB::getDB()->query("SELECT a.kalenderID, a.kalenderAcl, a.kalenderName, a.kalenderColor FROM kalender_allInOne as a ORDER BY a.kalenderSort");
    while($row = DB::getDB()->fetch_array($result)) {
      
      $acl = self::getAclByID($row['kalenderAcl'], true, self::getAdminGroup() );
      if ($acl && $acl['rights']['read'] == 1 ) {
        $kalenders[] = [
          'kalenderID' => $row['kalenderID'],
          'kalenderName' => $row['kalenderName'],
          'kalenderColor' => $row['kalenderColor'],
        ];
      }
    }

    $where = ' WHERE (( eintragDatumStart <= "'.$datum.'" AND  eintragDatumEnde >= "'.$datum.'" ) OR eintragDatumStart = "'.$datum.'" )';
    $where_cal = '';
    foreach ($kalenders as &$kalender) {
      if ($where_cal != '') { $where_cal .= ' OR '; }
			$where_cal .= 'kalenderID = '. intval($kalender['kalenderID']);
    }
    if ($where_cal) {
			$where .= ' AND ( '.$where_cal.' ) ';
    }
    

    $ret = [];
    $result = DB::getDB()->query("SELECT * FROM kalender_allInOne_eintrag ".$where);
		while($row = DB::getDB()->fetch_array($result)) {
			
			$createdUser = new user(array('userID' => intval($row['eintragUserID']) ));

			$item = [
				'eintragID' => $row['eintragID'],
				'kalenderID' => $row['kalenderID'],
				'eintragKategorieID' => $row['eintragKategorieID'],
				'eintragTitel' => DB::getDB()->decodeString($row['eintragTitel']),
				'eintragDatumStart' => $row['eintragDatumStart'],
				'eintragTimeStart' => $row['eintragTimeStart'],
				'eintragDatumEnde' => $row['eintragDatumEnde'],
				'eintragTimeEnde' => $row['eintragTimeEnde'],
				'eintragOrt' => DB::getDB()->decodeString($row['eintragOrt']),
				'eintragKommentar' => DB::getDB()->decodeString($row['eintragKommentar']),
				'eintragCreatedTime' => $row['eintragCreatedTime'],
				'eintragModifiedTime' => $row['eintragModifiedTime'],
				'eintragUserID' => $row['eintragUserID'],
				'eintragUserName' => $createdUser->getDisplayName()
      ];
      
      foreach ($kalenders as &$kalender) {
        if ($kalender['kalenderID'] == $item['kalenderID'] ) {
          $item['kalender'] = $kalender;
        }
      }

			$ret[] = $item;
    }
    

    // echo "<pre>";
    // print_r($ret);
    // echo "</pre>";


    return $ret;
  }



  public static function hasSettings() {
    return false;
  }


  public static function getSettingsDescription() {
    return [];
  }


  /*
   * 	 * 		'name' => "Name der Einstellung (z.B. formblatt-isActive)",
   *		'typ' => ZEILE | TEXT | NUMMER | BOOLEAN,
   *      'titel' => "Titel der Beschreibung",
   *      'text' => "Text der Beschreibung"
   */


  public static function getSiteDisplayName(){
    return "Kalender AllInOne (Beta)";
  }

  /**
   * Liest alle Nutzergruppen aus, die diese Seite verwendet. (Für die Benutzeradministration)
   * @return array(array('groupName' => '', 'beschreibung' => ''))
   */
  public static function getUserGroups() {
    return [];
  }

  public static function hasAdmin() {
    return true;
  }

  public static function getAdminMenuGroup() {
    return 'Kalender';
  }

  public static function getAdminMenuGroupIcon() {
    return 'fa fa-calendar';
  }

  public static function getAdminMenuIcon() {
    return 'fa fa-male';
  }

  public static function getAdminGroup() {
    return 'Webportal_Kalender_allInOne_Admin';
  }


  public static function displayAdministration($selfURL) {


    if($_REQUEST['action'] == 'edit') {

      if (!$_POST['data']) {
        return false;
      }

      $data = json_decode($_POST['data']);
      
      if (!$data) {
        return false;
      }

      foreach($data as $item) {
        if ( $item->kalenderName ) {

          $item->kalenderAcl->aclModuleClassParent = self::aclModuleName();


          $return = ACL::setAcl( (array)$item->kalenderAcl );
          if (is_array($return) && $return['error']) {
            return $return;
          }

          if ( $item->kalenderID != 0 ) {
            $dbRow = DB::getDB()->query_first("SELECT kalenderID FROM kalender_allInOne WHERE kalenderID = " . intval($item->kalenderID) . "");
            if ($dbRow['kalenderID'] && $item->kalenderName != 'DELETE') {
              DB::getDB()->query("UPDATE kalender_allInOne SET
                kalenderName = '".DB::getDB()->escapeString($item->kalenderName)."',
                kalenderColor = '".DB::getDB()->escapeString($item->kalenderColor)."',
                kalenderSort = '".DB::getDB()->escapeString($item->kalenderSort)."',
                kalenderPreSelect = '".DB::getDB()->escapeString($item->kalenderPreSelect)."',
                kalenderFerien = '".DB::getDB()->escapeString($item->kalenderFerien)."',
                kalenderPublic = '".DB::getDB()->escapeString($item->kalenderPublic)."',
                kalenderAcl = ".$return['aclID']."
                WHERE kalenderID = " . intval($item->kalenderID) . ";");
                
            } else if ($item->delete == 1 && $item->kalenderName == 'DELETE') {
              DB::getDB()->query("DELETE FROM kalender_allInOne WHERE kalenderID = ". intval($item->kalenderID));
            }
          } else {
            DB::getDB()->query("INSERT INTO kalender_allInOne (kalenderID, kalenderName, kalenderColor, kalenderSort, kalenderPreSelect, kalenderFerien, kalenderPublic, kalenderAcl ) values(
            '" . DB::getDB()->escapeString($item->kalenderID) . "',
            '" . DB::getDB()->escapeString($item->kalenderName) . "',
            '" . DB::getDB()->escapeString($item->kalenderColor) . "',
            '" . DB::getDB()->escapeString($item->kalenderSort) . "',
            '" . DB::getDB()->escapeString($item->kalenderPreSelect) . "',
            '" . DB::getDB()->escapeString($item->kalenderFerien) . "',
            '" . DB::getDB()->escapeString($item->kalenderPublic) . "',
            ".$return['aclID']."
            )");
          }

        }
      }


			header("Location: $selfURL");
			exit();
    }



    $html = '';
		eval("\$html = \"" . DB::getTPL()->get("kalender/admin/kalenderAllInOne") . "\";");
		return $html;
  }
}

