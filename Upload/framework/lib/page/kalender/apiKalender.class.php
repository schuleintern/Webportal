<?php

/**
 * API Kalender
 *
 * @author Christian Marienfeld
 *
 */
class apiKalender extends AbstractPage {

  protected $title = "API Kalender";

  private $kalender = [];

  public function __construct() {

    parent::__construct(["Kalender"]);
    $this->checkLogin();


  }

  public function execute() {

    $acl = json_encode( $this->getAcl() );
    //$userID = DB::getSession()->getUserID();
    
    eval("echo(\"" . DB::getTPL()->get("kalender/apiKalender"). "\");");

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
    return "API Kalender";
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
    return 'Webportal_API_Kalender_Admin';
  }

  // public function aclModuleName() {
	// 	return 'apiKalender';
	// }

  public static function displayAdministration($selfURL) {


    if($_REQUEST['action'] == 'edit') {

      if (!$_POST['data']) {
        return false;
      }

      $data = json_decode($_POST['data']);
      
      // echo "<pre>";
      // print_r($data);
      // echo "</pre>";

      if (!$data) {
        return false;
      }

      foreach($data as $item) {
        if ( $item->kalenderName ) {

          // echo "<pre>";
          // print_r($item);
          // echo "</pre>";

          // if ( !isset($item->kalenderAcl->id) ) {
          //   $item->kalenderAcl->id = 0;
          // }
          
          $item->kalenderAcl->aclModuleClassParent = self::aclModuleName();


          $return = ACL::setAcl( (array)$item->kalenderAcl );
          if (is_array($return) && $return['error']) {
            return $return;
          }

//           echo "<pre>";
//           print_r($return);
//           echo "</pre>";
// exit;

          if ( $item->kalenderID != 0 ) {
            $dbRow = DB::getDB()->query_first("SELECT kalenderID FROM kalender_api WHERE kalenderID = " . intval($item->kalenderID) . "");
            if ($dbRow['kalenderID'] && $item->kalenderName != 'DELETE') {
              DB::getDB()->query("UPDATE kalender_api SET
                kalenderName = '".DB::getDB()->escapeString($item->kalenderName)."',
                kalenderColor = '".DB::getDB()->escapeString($item->kalenderColor)."',
                kalenderSort = '".DB::getDB()->escapeString($item->kalenderSort)."',
                kalenderPreSelect = '".DB::getDB()->escapeString($item->kalenderPreSelect)."',
                kalenderFerien = '".DB::getDB()->escapeString($item->kalenderFerien)."',
                kalenderAcl = ".$return['aclID']."
                WHERE kalenderID = " . intval($item->kalenderID) . ";");
                
            } else if ($item->delete == 1 && $item->kalenderName == 'DELETE') {
              DB::getDB()->query("DELETE FROM kalender_api WHERE kalenderID = ". intval($item->kalenderID));
            }
          } else {
            DB::getDB()->query("INSERT INTO kalender_api (kalenderID, kalenderName, kalenderColor, kalenderSort, kalenderPreSelect, kalenderFerien, kalenderAcl ) values(
            '" . DB::getDB()->escapeString($item->kalenderID) . "',
            '" . DB::getDB()->escapeString($item->kalenderName) . "',
            '" . DB::getDB()->escapeString($item->kalenderColor) . "',
            '" . DB::getDB()->escapeString($item->kalenderSort) . "',
            '" . DB::getDB()->escapeString($item->kalenderPreSelect) . "',
            '" . DB::getDB()->escapeString($item->kalenderFerien) . "',
            ".$return['aclID']."
            )");
          }

          
          
        }
      }


			header("Location: $selfURL");
			exit();
    }


//     echo self::aclModuleName();

//     $acl = DB::getDB()->query_first("SELECT * FROM acl WHERE moduleClass = '".self::aclModuleName()."'");

//     $acl = json_encode($acl);

//     echo "<pre>";
// print_r($acl);
// echo "</pre>";

    $html = '';
		eval("\$html = \"" . DB::getTPL()->get("kalender/admin/apiKalender") . "\";");
		return $html;
  }
}

