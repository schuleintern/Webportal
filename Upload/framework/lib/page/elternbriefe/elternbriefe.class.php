<?php


class elternbriefe extends AbstractPage {

	private $isAdmin = false;
	
	public function __construct() {
		parent::__construct(array("Wichtige Dokumente"));
		
		$this->checkLogin();
		
		if(DB::getSession()->isMember("Webportal_Elternbriefe")) {
			$this->isAdmin = true;
		}
		
	}

	public function execute() {
		
		if(isset($_GET['mode']) && $_GET['mode'] == "upload") {
			
			if(!$this->isAdmin) {
				new errorPage("Request without permission (Upload without Elternbrief access!");
				exit(0);
			}
			
			if ($_FILES['newBriefFile']['error'] !== UPLOAD_ERR_OK) {
				new error("Es gab einen Fehler beim Upload: " . $_FILES['file']['error']);
				exit();
				
			}
			
			$mime = @mime_content_type($_FILES['newBriefFile']['tmp_name']);
			
			$ok = false;
			
			switch ($mime) {
				case 'application/pdf':
					$ok = true;
				break;
				default:
					new error("Die hochgeladene Datei ist keine PDF Datei!");
					exit();
				break;
			}
			
			DB::getDB()->query("INSERT INTO elternbriefe (briefTitel, briefDatum, briefUploadUserID, briefUploadTime) values('" . addslashes($_POST['newBriefTitel']) . "','" . addslashes(DateFunctions::getMySQLDateFromNaturalDate($_POST['briefDatum'])) . "', '" . DB::getUserID() . "',UNIX_TIMESTAMP())");
			
			$newID = DB::getDB()->insert_id();
						
			move_uploaded_file($_FILES["newBriefFile"]["tmp_name"], "elternbriefe/" . $newID . "_brief.pdf");
			
			if($_POST['newBriefInformUser'] > 0) {
				// 
				$senderUserID = DB::getUserID();
				
				$nets = DB::getDB()->query("SELECT DISTINCT userNetwork FROM users ORDER BY userNetwork ASC");
				while($net = DB::getDB()->fetch_array($nets)) {
					$users = DB::getDB()->query("SELECT userID FROM users WHERE userNetwork='" . $net['userNetwork'] . "'");
				
					$sql = "INSERT INTO infomessages
									(messageUserID, messageSenderUserID, messageSubject, messageBody, messageTime)
										values
										";
				
					$inserts = array();
				
					while($u = DB::getDB()->fetch_array($users)) {
								
							$inserts[] = "(
							{$u['userID']},
							$senderUserID,
							'" . addslashes("Neuer Elternbrief verfügbar") . "',
											'" . addslashes("Es ist ein neuer Elternbrief verfügbar.<br />Titel des Elternbriefes: " . $_POST['newBriefTitel'] . "<br /><br /><a href=\"index.php?page=elternbriefe\"><i class=\"fa fa-arrow-right\"></i> Hier gelangen Sie zu den Elternbriefen</a><!-- brief" . $newID . "-->") . "',
											UNIX_TIMESTAMP()
										)
									";
					}
				
					$sql .= implode(",",$inserts);
				
					DB::getDB()->query($sql);
				}
			}
			
			
			header("Location: index.php?page=elternbriefe");
			
			exit();
		}
		
		if(isset($_GET['mode']) && $_GET['mode'] == "edit") {
			if(!$this->isAdmin) die("DKIIDBG!");
			
			$brief = DB::getDB()->query_first("SELECT * FROM elternbriefe WHERE briefID='" . intval($_GET['briefID']) . "'");
			if($brief['briefID'] > 0) {
				if($_GET['doSave'] > 0) {
					DB::getDB()->query("UPDATE elternbriefe SET briefTitel='" . addslashes($_POST['briefTitel']) . "',briefDatum='" . addslashes($_POST['briefDatum']) . "' WHERE briefID='" . $brief['briefID'] . "'");
				
					
					$ok = false;
					$doUpload = true;
					
					if ($_FILES['newBriefFile']['error'] !== UPLOAD_ERR_OK) {
						$doUpload = false;
					
					}
						
					$mime = @mime_content_type($_FILES['newBriefFile']['tmp_name']);
						
					
						
					switch ($mime) {
						case 'application/pdf':
							$ok = true;
							break;
					}
						
					if($doUpload && $ok) {
						@unlink("elternbriefe/" . $brief['briefID']. "_brief.pdf");
						move_uploaded_file($_FILES["newBriefFile"]["tmp_name"], "elternbriefe/" . $brief['briefID'] . "_brief.pdf");
					}						
					
					header("Location: index.php?page=elternbriefe");
				}
				else {
					$brief['briefTitel'] = @htmlspecialchars(($brief['briefTitel']));
					eval("echo(\"" . DB::getTPL()->get("elternbriefe/edit") . "\");");
					exit(0);
				}
			}
			else {
				new error("Der angegebene Elternbrief existiert nicht!");
			}
		}
		
		if(isset($_GET['mode']) && $_GET['mode'] == "download") {
			$brief = DB::getDB()->query_first("SELECT * FROM elternbriefe WHERE briefID='" . addslashes($_GET['briefID']) . "'");
			if($brief['briefID'] > 0) {
				header('Content-Description: Dateidownload');
			    header('Content-Type: application/octet-stream');
			    header('Content-Disposition: attachment; filename="'."Elternbrief_" . $brief['briefTitel'] . ".pdf".'"');
			    header('Expires: 0');
			    header('Cache-Control: must-revalidate');
			    header('Pragma: public');
			    header('Content-Length: ' . filesize("elternbriefe/" . $brief['briefID'] . "_brief.pdf"));
			    readfile("elternbriefe/" . $brief['briefID'] . "_brief.pdf");
				
				exit(0);
			}
			else {
				new error("Der angegebene Brief existiert nicht!");
			}
		}
		
		if(isset($_GET['mode']) && $_GET['mode'] == "deleteBrief" && $this->isAdmin) {
			$brief = DB::getDB()->query_first("SELECT * FROM elternbriefe WHERE briefID='" . addslashes($_GET['briefID']) . "'");
			if($brief['briefID'] > 0) {
				DB::getDB()->query("DELETE FROM elternbriefe WHERE briefID='" . $brief['briefID'] . "'");
				
				DB::getDB()->query("DELETE FROM infomessages WHERE messageSubject='Neuer Elternbrief verfügbar' AND messageBody LIKE '%<!-- brief" . $brief['briefID'] . "-->'");
				
				unlink("elternbriefe/" . $brief['briefID'] . "_brief.pdf");
				header("Location: index.php?page=elternbriefe");
				exit(0);
			}
			else {
				new error("Der angegebene Brief existiert nicht!");
			}
		}
		
		
		$briefe = DB::getDB()->query("SELECT * FROM elternbriefe ORDER BY briefDatum DESC");
		
		$briefeHTML = "";
		while($brief = DB::getDB()->fetch_array($briefe)) {
			$brief['briefDatum'] = functions::getFormatedDateFromSQLDate($brief['briefDatum']);
			
			if($this->isAdmin) {
				$adminOptions = "<td><a href=\"#brief" . $brief['briefID'] . "\" onclick=\"javascript: if(confirm('Soll der Brief wirklich gelöscht werden? Gesendete Benachrichtigungen werden auch gelöscht!')) document.location.href='index.php?page=elternbriefe&mode=deleteBrief&briefID=" . $brief['briefID'] . "';\"><i class=\"fa fa-trash\"></i> Elternbrief löschen</a><br />
						<a href=\"index.php?page=elternbriefe&mode=edit&briefID=" . $brief['briefID'] . "\"><i class=\"fa fas fa-pencil-alt\"></i> Bearbeiten</a><br />
						<!--<a href=\"index.php?page=elternbriefe&mode=moveUp&briefID=" . $brief['briefID'] . "\"><i class=\"fa fa-arrow-up\"></i> Einen Schritt nach oben schieben</a><br />
						<a href=\"index.php?page=elternbriefe&mode=moveDown&briefID=" . $brief['briefID'] . "\"><i class=\"fa fa-arrow-down\"></i> Einen Schritt nach unten schieben</a>-->
						</td>";
			}
			else $adminOptions = "";
			
			eval("\$briefeHTML .= \"" . DB::getTPL()->get("elternbriefe/bit") . "\";");			
		}
		
		if($this->isAdmin) {
			
			$userNames = array();
			$members = DB::getDB()->query("SELECT userName FROM users WHERE userID IN (SELECT userID FROM users_groups WHERE groupName='Webportal_Elternbriefe') ORDER BY userName ASC");
			while($member = DB::getDB()->fetch_array($members)) {
				$userNames[] = $member['userName'];
			}
			
			$userNames = implode(", ",$userNames);		// Liste draus machen
			
			
			$today = date("d.m.Y");
			
			eval("\$uploadForm = \"" . DB::getTPL()->get("elternbriefe/upload") . "\";");
			$adminTH = "<th>Administrationsoptionen</th>";
		}
		else {
			$uploadForm = "";
			$adminTH = "";
		}
		
		
		eval("echo(\"" . DB::getTPL()->get("elternbriefe/index") . "\");");
	}
	
	
	public static function hasSettings() {
		return false;
	}
	
	/**
	 * Stellt eine Beschreibung der Einstellungen bereit, die für das Modul nötig sind.
	 * @return array(String, String)
	 * array(
	 * 	   array(
	 * 		'name' => "Name der Einstellung (z.B. formblatt-isActive)",
	 *		'typ' => ZEILE | TEXT | NUMMER | BOOLEAN,
	 *      'titel' => "Titel der Beschreibung",
	 *      'text' => "Text der Beschreibung"
	 *     )
	 *     ,
	 *     .
	 *     .
	 *     .
	 *  )
	 */
	public static function getSettingsDescription() {
		return array();
	}
	
	
	public static function getSiteDisplayName() {
		return 'Elternbriefe';
	}
	
	/**
	 * Liest alle Nutzergruppen aus, die diese Seite verwendet. (Für die Benutzeradministration)
	 * @return array(array('groupName' => '', 'beschreibung' => ''))
	 */
	public static function getUserGroups() {
		return array(
				array(
						'groupName' => 'Webportal_Elternbriefe',
						'beschreibung' => 'Administrator für das Elternbriefarchiv'
				)
			);
	}
}


?>