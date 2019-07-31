<?php

class editPicture extends AbstractPage {
	private $sections = array(
		
	);
	
	public function __construct() {
		parent::__construct(array("Fotoseite", "Bild bearbeiten"));
		
		$this->checkAccessWithGroup("Webportal_Fotoseite");
	}

	public function execute() {
		$db = DB::getDB();
		
		if(!isset($_GET['personID'])) die("Keine Person angegeben!");
		
		$pID = intval($_GET['personID']);
		
		$person = $db->query_first("SELECT * FROM rsu_persons WHERE personID='$pID'");
		
		if($person['personID'] > 0) {
			$error = "";
			
			if(isset($_GET['save']) && $_GET['save'] > 0) {
				// Neues Foto hochladen
				if(isset($_POST['delete']) && $_POST['delete'] > 0) {
					// Foto löschen
					$db->query("UPDATE rsu_persons SET personhasPicture=0 WHERE personID='" . $pID . "'");
					@unlink("rsufotoseite/$pID.jpg");
				}
				else {				
					$error = "";
					
					
					
					$allowedExts = array("gif", "jpeg", "jpg", "png");
					$temp = explode(".", $_FILES["image"]["name"]);
					$extension = strtolower(end($temp));
					
					/* if ((($_FILES["image"]["type"] == "image/gif")
							|| ($_FILES["image"]["type"] == "image/jpeg")
							|| ($_FILES["image"]["type"] == "image/jpg")
							|| ($_FILES["image"]["type"] == "image/pjpeg")
							|| ($_FILES["image"]["type"] == "image/x-png")
							|| ($_FILES["image"]["type"] == "image/png"))
							&& in_array($extension, $allowedExts)) {*/
					if ((($_FILES["image"]["type"] == "image/gif")
							|| ($_FILES["image"]["type"] == "image/jpeg")
							|| ($_FILES["image"]["type"] == "image/jpg")
							|| ($_FILES["image"]["type"] == "image/pjpeg")
							|| ($_FILES["image"]["type"] == "image/x-png")
							|| ($_FILES["image"]["type"] == "image/png"))
							&& in_array($extension, $allowedExts)) {
						if ($_FILES["image"]["error"] > 0) {
							$error = "Es ist ein Fehler aufgetreten: " . $_FILES["file"]["error"];
						} else {
							if (file_exists("rsufotoseite/" . $pID . ".jpg")) {
								unlink("rsufotoseite/" . $pID . ".jpg");
							} 
								
								move_uploaded_file($_FILES["image"]["tmp_name"], "rsufotoseite/" . $pID . ".temp");
							
								// Überprüfen, ob das Bild wirklich das ist, was es vorgibt.
											
								$imageTypeCheck = exif_imagetype("rsufotoseite/" . $pID . ".temp");
								
								if ($extension == 'jpg' && $imageTypeCheck == 2) {
									$srcImg = imagecreatefromjpeg("rsufotoseite/" . $pID . ".temp");
								} else
								if ($extension == 'jpeg' && $imageTypeCheck == 2) {
									$srcImg = imagecreatefromjpeg("rsufotoseite/" . $pID . ".temp");
								} else
								if ($extension == 'png' && $imageTypeCheck == 3) {
									$srcImg = imagecreatefrompng("rsufotoseite/" . $pID . ".temp");
								} else
								if ($extension == 'gif' && $imageTypeCheck == 1) {
									$srcImg = imagecreatefromgif("rsufotoseite/" . $pID . ".temp");
								}
								else {
									// Bild ist kein gültiges Bild.
									// hochgeladenes Bild wieder löschen
									unlink("rsufotoseite/" . $this->uploadID . ".temp");
									
									$error = "Das hochgeladene Bild ist kein gültiges Bild: $imageTypeCheck";
								}
								
								$size = getImageSize("rsufotoseite/" . $pID . ".temp");
								// print_r($size);
									
								$saveImageJPG = imagecreatetruecolor($size[0],$size[1]);
									
								ImageCopyResized($saveImageJPG,$srcImg,0,0,0,0,$size[0],$size[1],$size[0],$size[1]);
								// echo("RESIZE OK");
									
								
								imagejpeg($saveImageJPG, "rsufotoseite/" . $pID . ".jpg", 90);
									
								unlink("rsufotoseite/" . $pID . ".temp");
								
								// weiterleiten
								
								
						}
					} else {
						$error = "Das hochgeladene Bild ist kein gültiges Bild:" .  $_FILES["image"]["type"];
					}
					
					// Weiter
					
					if($error == "") {
						$db->query("UPDATE rsu_persons SET personhasPicture=1 WHERE personID='" . $pID . "'");
						header("Location: ?page=editPicture&personID=$pID");
						exit(0);
					}
										
				}
			}
			
			$hasPicture = $db->query_first("SELECT personhasPicture FROM rsu_persons WHERE personID='" . $pID . "'");
				
			if($hasPicture['personhasPicture'] > 0) {
				$currentPicture = "<img src=\"index.php?page=getPicture&personID=$pID\" width=100><br />
				<a href=\"#\" onclick=\"window.open('imgeditor/?imagesrc=" . urlencode("../rsufotoseite/$pID.jpg") . "','EDITOR','with=500,height=500')\">Bild zuschneiden, verkleinern etc...</a>";
				
				eval("\$editPicture = \"" . DB::getTPL()->get("rsufotoseite/editPictureHasImage") . "\";");
			}
			else {
				$currentPicture = "Kein Bild vorhanden!";
				$editPicture = "";
			}
				
			eval("echo(\"".DB::getTPL()->get("rsufotoseite/editPicture")."\");");
		}
		else {
			die("Person nicht vorhanden!");
		}
		
		
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
		return 'Fotoseite: Bild bearbeiten';
	}
	
	/**
	 * Liest alle Nutzergruppen aus, die diese Seite verwendet. (Für die Benutzeradministration)
	 * @return array(array('groupName' => '', 'beschreibung' => ''))
	 */
	public static function getUserGroups() {
		return array();
	
	}
	
	public static function onlyForSchool() {
		return array(
				"0740"
		);
	}
}


?>