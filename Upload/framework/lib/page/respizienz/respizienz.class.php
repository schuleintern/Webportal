<?php


class respizienz extends AbstractPage {
	
	private $isSchulleitung = false;
	
	
	
	public function __construct() {
		
		parent::__construct ( array (
			"Respizienz" 
		) );
		
		$this->checkLogin();
		
		$accessOK = false;
		
		if(DB::getSession()->isTeacher()) {
		    $this->isSchulleitung = DB::getSession()->getTeacherObject()->isSchulleitung();
		    $accessOK = true;
		}
		
		if(DB::getSession()->getUser()->isSekretariat()) {
		    $this->isSchulleitung = true;
		    $accessOK = true;
		}
		
		
		if(!$accessOK) {
			new errorPage();
		}
	}
	
	public function execute() {		
        switch($_REQUEST['mode']) {
            case 'schulleitung':
                $lnws = LeistungsnachweisRespizienz::getBySchulleitung(DB::getSession()->getTeacherObject());
                $this->respizienzDialog($lnws, true, false);
                break;
            
            case 'fachbetreuer':
                $lnws = LeistungsnachweisRespizienz::getByFachbetreuer(DB::getSession()->getTeacherObject());
                $this->respizienzDialog($lnws, false, true);
                
            default:
                $lnws = LeistungsnachweisRespizienz::getbyTeacher(DB::getSession()->getTeacherObject());
                $this->respizienzDialog($lnws, false, false);
            break;
        }
	}
	
	/**
	 * 
	 * @param LeistungsnachweisRespizienz[] $meine
	 */
	private function respizienzDialog($meine, $isSchulleitung, $isFachbetreuer) {
	    // $meine = LeistungsnachweisRespizienz::getByFachbetreuer(DB::getSession()->getTeacherObject());
	    
	    
	    if($isSchulleitung) $mode = "&mode=schulleitung";
	    if($isFachbetreuer) $mode = "&mode=fachbetreuer";
	    
	    
	    $meineHTML = '';
	    $dialoge = '';
	    for($i = 0; $i < sizeof($meine); $i++) {
	        
	        if(!$isFachbetreuer && !$isSchulleitung) {
	        
    	        if($_REQUEST['action'] == 'setAnalog' && $_REQUEST['respizienzID'] == $meine[$i]->getID()) {
    	            $meine[$i]->setAnalog(true);
    	        }
    	        
    	        if($_REQUEST['action'] == 'setDigital' && $_REQUEST['respizienzID'] == $meine[$i]->getID()) {
    	            $meine[$i]->setAnalog(false);
    	        }
	        
	        }
	        
	        
	        if($_REQUEST['action'] == 'uploadFile' && $_REQUEST['respizienzID'] == $meine[$i]->getID()) {
	            
	            
	            if($_REQUEST['noFile'] > 0) {
	                if($isSchulleitung) {
	                    $meine[$i]->addSLFile(null, $_REQUEST['kommentar']);
	                }
	                else if($isFachbetreuer) {
	                    
	                    $meine[$i]->addFSLFile(null, $_REQUEST['kommentar']);
	                }
	            }
	            else {
    	            $upload = FileUpload::uploadPDFOrZip('pdfFile', $meine[$i]->getLangname() . " - " . $meine[$i]->getKlasse() . " - " . $meine[$i]->getDatumAsNaturalDate());
    	            
    	            
    	            if($upload['result'] > 0) {
    	                
    	                
    	                
    	                if($isSchulleitung) {
    	                    $meine[$i]->addSLFile($upload['uploadobject']->getID(), $_REQUEST['kommentar']);
    	                }
    	                else if($isFachbetreuer) {
    	                    $meine[$i]->addFSLFile($upload['uploadobject']->getID(), $_REQUEST['kommentar']);
    	                    
    	                }
    	                else {
    	                    
    	                    if(sizeof($_REQUEST['schuelerAsvIDs']) == 0) {
    	                        $schueler = null;
    	                    }
    	                    else $schueler = $_REQUEST['schuelerAsvIDs'];
    	                    
    	                    $schnitt = $this->tofloat($_REQUEST['schnitt']);
    	                    
    	                    $meine[$i]->addLehrerFile($upload['uploadobject']->getID(), $_REQUEST['kommentar'], $schueler, $schnitt);
    	                }
    	            }
	            }
	            
	            header("Location: index.php?page=respizienz" . $mode);
	            exit(0);
	            
	        }
	        
	        if($_REQUEST['action'] == 'deleteFile' && $_REQUEST['respizienzID'] == $meine[$i]->getID()) {
	            $uploadFile = null;
	            
	            if($isSchulleitung) {
	                $meine[$i]->deleteSLFile($_REQUEST['uploadID']);
	            }
	            else if($isFachbetreuer) {
	                $meine[$i]->deleteFSLFile($_REQUEST['uploadID']);
	            }
	            else {
	                $meine[$i]->deleteSLFile($_REQUEST['uploadID']);
	            }
	            
	            $uploadFile = FileUpload::getByID($_REQUEST['uploadID']);
	            
	            
	            if($uploadFile != null) {
	                $uploadFile->delete();
	            }
	            
	            
	            header("Location: index.php?page=respizienz" . $mode);
	            exit(0);
	            
	        }
	        
	        $downloadFile = "";
	        
	        if(sizeof($meine[$i]->getFiles()) > 0) {
	            
	            $files = $meine[$i]->getFiles();
	            
	            for($f = 0; $f < sizeof($files); $f++) {
	                
	                $file = FileUpload::getByID($files[$f]->uploadID);
	                
	                if($file != null) {
	                
	                    
	                    if($file->getExtension()  == 'zip') {
	                        $icon = "fa fa-file-zip";
	                    }
	                    else $icon = "fa fa-file-pdf";
	                    
        	            $downloadFile .= "<a href=\"" . $file->getURLToFile() . "\"><i class=\"$icon\"></i> Download</a>";
        	            
        	            
        	            if($files[$f]->kommentar != "") {
        	                $downloadFileFSL .= " <button class=\"btn btn-xs\" type=\"button\" onclick=\"showKommentar('" . addslashes($files[$f]->kommentar) . "');\"><i class=\"fa fa-comment\"></i></button>";
        	            }
        	            
        	            
        	            if(!$isSchulleitung && !$isFachbetreuer) {
        	                $downloadFile .= " <button type=\"button\" onclick=\"javascript:confirmAction('Soll die hinterlegte Datei wirklich gelöscht werden?','index.php?page=respizienz&action=deleteFile&respizienzID={$meine[$i]->getID()}&uploadID={$file->getID()}{$mode}');\" class=\"btn btn-xs btn-danger\"><i class=\"fa fa-trash\"></i></button><br />";        	                
        	            }
        	            else {
        	                $downloadFile .= "<br />";
        	            }
        	            
        	            $downloadFile .= "<i class=\"fa fa-thumb-tack\"></i> " . $files[$f]->notenschnitt . " <br />";
        	            
        	            if(sizeof($files[$f]->pupils) == 0 && $meine[$i]->isAngekuendigt()) $downloadFile .= "<small>Alle Schüler</small><br />";
        	            else {
        	                for($s = 0; $s < sizeof($files[$f]->pupils); $s++) {
        	                    $schuelerObject = schueler::getByAsvID($files[$f]->pupils[$s]);
        	                    if($schuelerObject != null) {
        	                        $downloadFile .= $schuelerObject->getCompleteSchuelerName() . "<br />";
        	                    }
        	                }
        	            }
        	            
	                }
	            }
	            
	        }
	        else {
	            $downloadFile = '<i>Bisher keine Datei</i>';
	        }
	        
	        if(!$isSchulleitung && !$isFachbetreuer) {
	            
	            $isNotEx = $meine[$i]->getArt() != "STEGREIFAUFGABE" && $meine[$i]->getArt() != "PLNW";
	            
	            $downloadFile .= "<br /><button type=\"button\" class=\"btn btn-sm\" onclick=\"uploadFileTeacher(" . $meine[$i]->getID() . ",'" . $meine[$i]->getKlasse() . "'," . $isNotEx . ");\"><i class=\"fa fa-upload\"></i> Datei hochladen</button>";
	        }
	        
	        
	        $downloadFileFSL = "";
	        
	        if(sizeof($meine[$i]->getFSLFiles()) > 0) {
	            
	            $files = $meine[$i]->getFSLFiles();
	            
	            for($f = 0; $f < sizeof($files); $f++) {
	                
	                $file = FileUpload::getByID($files[$f]->uploadID);
	                
	                if($file != null) {
	                    
	                    
	                    if($file->getExtension()  == 'zip') {
	                        $icon = "fa fa-file-zip";
	                    }
	                    else $icon = "fa fa-file-pdf";
	                    
	                    $downloadFileFSL .= "<a href=\"" . $file->getURLToFile() . "\"><i class=\"$icon\"></i> Download</a>";

	                    if($isFachbetreuer) {
	                        $downloadFileFSL .= " <button type=\"button\" onclick=\"javascript:confirmAction('Soll die hinterlegte Datei wirklich gelöscht werden?','index.php?page=respizienz&action=deleteFile&respizienzID={$meine[$i]->getID()}&uploadID={$file->getID()}{$mode}');\" class=\"btn btn-xs btn-danger\"><i class=\"fa fa-trash\"></i></button>";
	                    }


	                }
	                else {
	                    $downloadFileFSL .= "<i class='fa fa-check'></i> Ohne Datei erledigt.";
	                    if($isFachbetreuer) $downloadFileFSL .= " <button type=\"button\" onclick=\"javascript:confirmAction('Soll die hinterlegte Datei wirklich gelöscht werden?','index.php?page=respizienz&action=deleteFile&respizienzID={$meine[$i]->getID()}&uploadID=null{$mode}');\" class=\"btn btn-xs btn-danger\"><i class=\"fa fa-trash\"></i></button> ";
	                    
	                }
	                
	                
	                if($files[$f]->kommentar != "") {
	                    $downloadFileFSL .= " <button class=\"btn btn-xs\" type=\"button\" onclick=\"showKommentar('" . addslashes($files[$f]->kommentar) . "');\"><i class=\"fa fa-comment\"></i></button>";
	                }
	                
	                

	            }
	            
	        }
	        else {
	            $downloadFileFSL = '<i>Bisher keine Datei</i>';
	        }
	        
	        if($isFachbetreuer) {
	            $downloadFileFSL .= "<br /><button type=\"button\" class=\"btn btn-sm\" onclick=\"uploadFile(" . $meine[$i]->getID() . ");\"><i class=\"fa fa-upload\"></i> Datei hochladen</button>";
	        }
	        
	        
	        $downloadFileSL = "";
	        
	        if(sizeof($meine[$i]->getSLFiles()) > 0) {
	            
	            $files = $meine[$i]->getSLFiles();
	            
	            for($f = 0; $f < sizeof($files); $f++) {
	                
	                $file = FileUpload::getByID($files[$f]->uploadID);
	                
	                if($file != null) {
	                    
	                    
	                    if($file->getExtension()  == 'zip') {
	                        $icon = "fa fa-file-zip";
	                    }
	                    else $icon = "fa fa-file-pdf";
	                    
	                    $downloadFileSL .= "<a href=\"" . $file->getURLToFile() . "\"><i class=\"$icon\"></i> Download</a>";
	                    
	                    if($isSchulleitung) {
	                        $downloadFileSL .= " <button type=\"button\" onclick=\"javascript:confirmAction('Soll die hinterlegte Datei wirklich gelöscht werden?','index.php?page=respizienz&action=deleteFile&respizienzID={$meine[$i]->getID()}&uploadID={$file->getID()}{$mode}');\" class=\"btn btn-xs btn-danger\"><i class=\"fa fa-trash\"></i></button>";
	                    }
	                    
	                    
	                }
	                else {
	                    $downloadFileSL .= "<i class='fa fa-check'></i> Ohne Datei erledigt.";
	                    if($isSchulleitung) $downloadFileSL .= " <button type=\"button\" onclick=\"javascript:confirmAction('Soll die hinterlegte Datei wirklich gelöscht werden?','index.php?page=respizienz&action=deleteFile&respizienzID={$meine[$i]->getID()}&uploadID=null{$mode}');\" class=\"btn btn-xs btn-danger\"><i class=\"fa fa-trash\"></i></button> ";
	                    
	                }
	                
	                
	                if($files[$f]->kommentar != "") {
	                    $downloadFileSL .= " <button class=\"btn btn-xs\" type=\"button\" onclick=\"showKommentar('" . addslashes($files[$f]->kommentar) . "');\"><i class=\"fa fa-comment\"></i></button>";
	                }
	                
	                
	                
	            }
	            
	        }
	        else {
	            $downloadFileSL = '<i>Bisher keine Datei</i>';
	        }
	        
	        if($isSchulleitung) {
	            $downloadFileSL .= "<br /><button type=\"button\" class=\"btn btn-sm\" onclick=\"uploadFile(" . $meine[$i]->getID() . ");\"><i class=\"fa fa-upload\"></i> Datei hochladen</button>";
	        }
	        
	        /**if($meine[$i]->getFSLFile() != null) {
	            $fileFSL = $meine[$i]->getFSLFile();
	            $downloadFileFSL = "<a href=\"" . $fileFSL->getURLToFile() . "\"><i class=\"fa fa-file-pdf-o\"></i> Download</a>";
	            
	            $lehrer = $meine[$i]->getFSLLehrer();
	            if($lehrer != null) $downloadFileFSL .= "<br />Repiziert von " . $lehrer->getDisplayNameMitAmtsbezeichnung();
	        
	            if($isFachbetreuer) {
	                $downloadFileFSL .= "<br /><button type=\"button\" class=\"btn btn-sm\" onclick=\"uploadFile(" . $meine[$i]->getID() . ");\"><i class=\"fa fa-upload\"></i> PDF neu hochladen</button>";
	                $downloadFileFSL .= " <button type=\"button\" onclick=\"javascript:confirmAction('Soll die hinterlegte PDF Datei wirklich gelöscht werden?','index.php?page=respizienz&action=deleteFile&respizienzID={$meine[$i]->getID()}{$mode}');\" class=\"btn btn-sm btn-danger\"><i class=\"fa fa-trash\"></i></button>";
	            }
	        }
	        else {
	            $downloadFileFSL = '<i>Bisher keine Datei</i>';
	            
	            if($isFachbetreuer) {
	                $downloadFileFSL .= "<br /><button type=\"button\" class=\"btn btn-sm\" onclick=\"uploadFile(" . $meine[$i]->getID() . ");\"><i class=\"fa fa-upload\"></i> PDF hochladen</button>";
	            }
	        }
	        
	        if($meine[$i]->getSLFile() != null) {
	            $fileSL = $meine[$i]->getSLFile();
	            $downloadFileSL = "<a href=\"" . $fileSL->getURLToFile() . "\"><i class=\"fa fa-file-pdf-o\"></i> Download</a>";
	            
	            $lehrer = $meine[$i]->getSLLehrer();
	            if($lehrer != null) $downloadFileSL .= "<br />Repiziert von " . $lehrer->getDisplayNameMitAmtsbezeichnung();
	            
	            if($isSchulleitung) {
	                $downloadFileSL .= "<br /><button type=\"button\" class=\"btn btn-sm\" onclick=\"uploadFile(" . $meine[$i]->getID() . ");\"><i class=\"fa fa-upload\"></i> PDF neu hochladen</button>";
	                $downloadFileSL .= " <button type=\"button\" onclick=\"javascript:confirmAction('Soll die hinterlegte PDF Datei wirklich gelöscht werden?','index.php?page=respizienz&action=deleteFile&respizienzID={$meine[$i]->getID()}{$mode}');\" class=\"btn btn-sm btn-danger\"><i class=\"fa fa-trash\"></i></button>";
	            }
	        }
	        else {
	            $downloadFileSL = '<i>Bisher keine Datei</i>';
	            if($isSchulleitung) {
	                $downloadFileSL .= "<br /><button type=\"button\" class=\"btn btn-sm\" onclick=\"uploadFile(" . $meine[$i]->getID() . ");\"><i class=\"fa fa-upload\"></i> PDF hochladen</button>";
	            }
	        }
	        **/
	        
	        $notenHTML = "";
	        
	        $noten = $meine[$i]->getSchuelerMitNoten();
	        
	        for($n = 0; $n < sizeof($noten); $n++) {
	            $notenHTML .= "<tr><td>" . $noten[$n]['schueler']->getCompleteSchuelerName() . "</td><td>" . $noten[$n]['note'] . "</td></tr>";
	        }
	        
	        
	        eval("\$meineHTML .= \"" . DB::getTPL()->get("respizienz/bit") . "\";");
	    }

	    
	    
	    eval("DB::getTPL()->out(\"" . DB::getTPL()->get('respizienz/index') . "\");");
	    exit();
	    
	}
	
	private function tofloat($num) {
	    $dotPos = strrpos($num, '.');
	    $commaPos = strrpos($num, ',');
	    $sep = (($dotPos > $commaPos) && $dotPos) ? $dotPos :
	    ((($commaPos > $dotPos) && $commaPos) ? $commaPos : false);
	    
	    if (!$sep) {
	        return floatval(preg_replace("/[^0-9]/", "", $num));
	    }
	    
	    return floatval(
	        preg_replace("/[^0-9]/", "", substr($num, 0, $sep)) . '.' .
	        preg_replace("/[^0-9]/", "", substr($num, $sep+1, strlen($num)))
	        );
	}
	
	public static function hasSettings() {
		return false;
	}
	
	public static function getSettingsDescription() {
		return [];
	}
	
	
	public static function getSiteDisplayName() {
		return 'Online Respizienz';
	}
	
	/**
	 * Liest alle Nutzergruppen aus, die diese Seite verwendet. (Für die Benutzeradministration)
	 * @return array(array('groupName' => '', 'beschreibung' => ''))
	 */
	public static function getUserGroups() {
		return array();
	}
	
	public static function hasAdmin() {
		return false;
	}

	public static function dependsPage() {
		return [];
	}
	
	public static function userHasAccess($user) {
	    
	    if(DB::getSession()->isTeacher()) {
	        return true;
	    }
	
		if(DB::getSession()->getUser()->isSekretariat()) return true;
	    
		return false;
	}
	
}

?>