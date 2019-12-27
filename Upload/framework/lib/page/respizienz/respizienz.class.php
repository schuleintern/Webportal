<?php


class respizienz extends AbstractPage {
	
	private $isSchulleitung = false;
	
	
	
	public function __construct() {
		
		parent::__construct ( array (
			DB::getSettings()->getValue("resp-name")
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
                if(!DB::getSettings()->getBoolean('resp-activate-sl')) {
                    header("Location: index.php?page=respizienz");
                    exit(0);
                }
                $lnws = LeistungsnachweisRespizienz::getBySchulleitung(DB::getSession()->getTeacherObject());
                $this->respizienzDialog($lnws, true, false);
                break;
            
            case 'fachbetreuer':
                if(!DB::getSettings()->getBoolean('resp-activate-fb')) {
                    header("Location: index.php?page=respizienz");
                    exit(0);
                }

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


	    $modus = DB::getSettings()->getValue('resp-mode');      // RESP / ARCH

        if($modus == 'ARCH') {
            $ueberschrift = "Digitale Archivierung von Leistungsnachweisen";
        }
        else {
            $ueberschrift = "Digitale Respizienz und Archivierung von Leistungsnachweisen";
        }


	    $meineHTML = '';
	    $dialoge = '';
	    for($i = 0; $i < sizeof($meine); $i++) {

	        if($meine[$i]->isActive()) {

                if (!$isFachbetreuer && !$isSchulleitung) {

                    if ($_REQUEST['action'] == 'setAnalog' && $_REQUEST['respizienzID'] == $meine[$i]->getID()) {
                        $meine[$i]->setAnalog(true);
                    }

                    if ($_REQUEST['action'] == 'setDigital' && $_REQUEST['respizienzID'] == $meine[$i]->getID()) {
                        $meine[$i]->setAnalog(false);
                    }

                }


                if ($_REQUEST['action'] == 'uploadFile' && $_REQUEST['respizienzID'] == $meine[$i]->getID()) {


                    if ($_REQUEST['noFile'] > 0) {
                        if ($isSchulleitung) {
                            $meine[$i]->addSLFile(null, $_REQUEST['kommentar']);
                        } else if ($isFachbetreuer) {

                            $meine[$i]->addFSLFile(null, $_REQUEST['kommentar']);
                        }
                    } else {
                        $upload = FileUpload::uploadPDFOrZip('pdfFile', $meine[$i]->getLangname() . " - " . $meine[$i]->getKlasse() . " - " . $meine[$i]->getDatumAsNaturalDate());


                        if ($upload['result'] > 0) {


                            if ($isSchulleitung) {
                                $meine[$i]->addSLFile($upload['uploadobject']->getID(), $_REQUEST['kommentar']);
                            } else if ($isFachbetreuer) {
                                $meine[$i]->addFSLFile($upload['uploadobject']->getID(), $_REQUEST['kommentar']);

                            } else {

                                if (sizeof($_REQUEST['schuelerAsvIDs']) == 0) {
                                    $schueler = null;
                                } else $schueler = $_REQUEST['schuelerAsvIDs'];

                                $schnitt = $this->tofloat($_REQUEST['schnitt']);

                                $meine[$i]->addLehrerFile($upload['uploadobject']->getID(), $_REQUEST['kommentar'], $schueler, $schnitt);
                            }
                        }
                    }

                    header("Location: index.php?page=respizienz" . $mode);
                    exit(0);

                }

                if ($_REQUEST['action'] == 'deleteFile' && $_REQUEST['respizienzID'] == $meine[$i]->getID()) {
                    $uploadFile = null;

                    if ($isSchulleitung) {
                        $meine[$i]->deleteSLFile($_REQUEST['uploadID']);
                    } else if ($isFachbetreuer) {
                        $meine[$i]->deleteFSLFile($_REQUEST['uploadID']);
                    } else {
                        $meine[$i]->deleteSLFile($_REQUEST['uploadID']);
                    }

                    $uploadFile = FileUpload::getByID($_REQUEST['uploadID']);


                    if ($uploadFile != null) {
                        $uploadFile->delete();
                    }


                    header("Location: index.php?page=respizienz" . $mode);
                    exit(0);

                }

                $downloadFile = "";

                if (sizeof($meine[$i]->getFiles()) > 0) {

                    $files = $meine[$i]->getFiles();

                    for ($f = 0; $f < sizeof($files); $f++) {

                        $file = FileUpload::getByID($files[$f]->uploadID);

                        if ($file != null) {


                            if ($file->getExtension() == 'zip') {
                                $icon = "fa fa-file-zip";
                            } else $icon = "fa fa-file-pdf";

                            $downloadFile .= "<a href=\"" . $file->getURLToFile() . "\" class='btn btn-default btn-xs'><i class=\"$icon\"></i> Download</a>";


                            if ($files[$f]->kommentar != "") {
                                $downloadFileFSL .= " <button class=\"btn btn-xs btn-default\" type=\"button\" onclick=\"showKommentar('" . addslashes($files[$f]->kommentar) . "');\"><i class=\"fa fa-comment\"></i></button>";
                            }


                            if (!$isSchulleitung && !$isFachbetreuer) {
                                $downloadFile .= " <button type=\"button\" onclick=\"javascript:confirmAction('Soll die hinterlegte Datei wirklich gelöscht werden?','index.php?page=respizienz&action=deleteFile&respizienzID={$meine[$i]->getID()}&uploadID={$file->getID()}{$mode}');\" class=\"btn btn-xs btn-danger\"><i class=\"fa fa-trash\"></i></button><br />";
                            } else {
                                $downloadFile .= "<br />";
                            }

                            $downloadFile .= "<i class=\"fa fa-thumb-tack\"></i> " . number_format($files[$f]->notenschnitt, 2, ",",".") . " <br />";

                            if (sizeof($files[$f]->pupils) == 0 && $meine[$i]->isAngekuendigt()) $downloadFile .= "<small>Alle Schüler</small><br />";
                            else {
                                for ($s = 0; $s < sizeof($files[$f]->pupils); $s++) {
                                    $schuelerObject = schueler::getByAsvID($files[$f]->pupils[$s]);
                                    if ($schuelerObject != null) {
                                        $downloadFile .= $schuelerObject->getCompleteSchuelerName() . "<br />";
                                    }
                                }
                            }

                        }
                    }

                } else {
                    $downloadFile = '<i>Bisher keine Datei</i>';
                }

                if (!$isSchulleitung && !$isFachbetreuer) {

                    $isNotEx = $meine[$i]->getArt() != "STEGREIFAUFGABE" && $meine[$i]->getArt() != "PLNW";

                    $downloadFile .= "<br /><button type=\"button\" class=\"btn btn-sm btn-primary\" onclick=\"uploadFileTeacher(" . $meine[$i]->getID() . ",'" . $meine[$i]->getKlasse() . "'," . $isNotEx . ");\"><i class=\"fa fa-upload\"></i> Datei hochladen</button>";
                }


                $downloadFileFSL = "";

                if (sizeof($meine[$i]->getFSLFiles()) > 0) {

                    $files = $meine[$i]->getFSLFiles();

                    for ($f = 0; $f < sizeof($files); $f++) {

                        $file = FileUpload::getByID($files[$f]->uploadID);

                        if ($file != null) {


                            if ($file->getExtension() == 'zip') {
                                $icon = "fa fa-file-zip";
                            } else $icon = "fa fa-file-pdf";

                            $downloadFileFSL .= "<a href=\"" . $file->getURLToFile() . "\"><i class=\"$icon\"></i> Download</a>";

                            if ($isFachbetreuer) {
                                $downloadFileFSL .= " <button type=\"button\" onclick=\"javascript:confirmAction('Soll die hinterlegte Datei wirklich gelöscht werden?','index.php?page=respizienz&action=deleteFile&respizienzID={$meine[$i]->getID()}&uploadID={$file->getID()}{$mode}');\" class=\"btn btn-xs btn-danger\"><i class=\"fa fa-trash\"></i></button>";
                            }


                        } else {
                            $downloadFileFSL .= "<i class='fa fa-check'></i> Ohne Datei erledigt.";
                            if ($isFachbetreuer) $downloadFileFSL .= " <button type=\"button\" onclick=\"javascript:confirmAction('Soll die hinterlegte Datei wirklich gelöscht werden?','index.php?page=respizienz&action=deleteFile&respizienzID={$meine[$i]->getID()}&uploadID=null{$mode}');\" class=\"btn btn-xs btn-danger\"><i class=\"fa fa-trash\"></i></button> ";

                        }


                        if ($files[$f]->kommentar != "") {
                            $downloadFileFSL .= " <button class=\"btn btn-xs\" type=\"button\" onclick=\"showKommentar('" . addslashes($files[$f]->kommentar) . "');\"><i class=\"fa fa-comment\"></i></button>";
                        }


                    }

                } else {
                    $downloadFileFSL = '<i>Bisher keine Datei</i>';
                }

                if ($isFachbetreuer) {
                    $downloadFileFSL .= "<br /><button type=\"button\" class=\"btn btn-sm\" onclick=\"uploadFile(" . $meine[$i]->getID() . ");\"><i class=\"fa fa-upload\"></i> Datei hochladen</button>";
                }


                $downloadFileSL = "";

                if (sizeof($meine[$i]->getSLFiles()) > 0) {

                    $files = $meine[$i]->getSLFiles();

                    for ($f = 0; $f < sizeof($files); $f++) {

                        $file = FileUpload::getByID($files[$f]->uploadID);

                        if ($file != null) {


                            if ($file->getExtension() == 'zip') {
                                $icon = "fa fa-file-zip";
                            } else $icon = "fa fa-file-pdf";

                            $downloadFileSL .= "<a href=\"" . $file->getURLToFile() . "\"><i class=\"$icon\"></i> Download</a>";

                            if ($isSchulleitung) {
                                $downloadFileSL .= " <button type=\"button\" onclick=\"javascript:confirmAction('Soll die hinterlegte Datei wirklich gelöscht werden?','index.php?page=respizienz&action=deleteFile&respizienzID={$meine[$i]->getID()}&uploadID={$file->getID()}{$mode}');\" class=\"btn btn-xs btn-danger\"><i class=\"fa fa-trash\"></i></button>";
                            }


                        } else {
                            $downloadFileSL .= "<i class='fa fa-check'></i> Ohne Datei erledigt.";
                            if ($isSchulleitung) $downloadFileSL .= " <button type=\"button\" onclick=\"javascript:confirmAction('Soll die hinterlegte Datei wirklich gelöscht werden?','index.php?page=respizienz&action=deleteFile&respizienzID={$meine[$i]->getID()}&uploadID=null{$mode}');\" class=\"btn btn-xs btn-danger\"><i class=\"fa fa-trash\"></i></button> ";

                        }


                        if ($files[$f]->kommentar != "") {
                            $downloadFileSL .= " <button class=\"btn btn-xs\" type=\"button\" onclick=\"showKommentar('" . addslashes($files[$f]->kommentar) . "');\"><i class=\"fa fa-comment\"></i></button>";
                        }


                    }

                } else {
                    $downloadFileSL = '<i>Bisher keine Datei</i>';
                }

                if ($isSchulleitung) {
                    $downloadFileSL .= "<br /><button type=\"button\" class=\"btn btn-sm\" onclick=\"uploadFile(" . $meine[$i]->getID() . ");\"><i class=\"fa fa-upload\"></i> Datei hochladen</button>";
                }

                /**if($meine[$i]->getFSLFile() != null) {
                 * $fileFSL = $meine[$i]->getFSLFile();
                 * $downloadFileFSL = "<a href=\"" . $fileFSL->getURLToFile() . "\"><i class=\"fa fa-file-pdf-o\"></i> Download</a>";
                 *
                 * $lehrer = $meine[$i]->getFSLLehrer();
                 * if($lehrer != null) $downloadFileFSL .= "<br />Repiziert von " . $lehrer->getDisplayNameMitAmtsbezeichnung();
                 *
                 * if($isFachbetreuer) {
                 * $downloadFileFSL .= "<br /><button type=\"button\" class=\"btn btn-sm\" onclick=\"uploadFile(" . $meine[$i]->getID() . ");\"><i class=\"fa fa-upload\"></i> PDF neu hochladen</button>";
                 * $downloadFileFSL .= " <button type=\"button\" onclick=\"javascript:confirmAction('Soll die hinterlegte PDF Datei wirklich gelöscht werden?','index.php?page=respizienz&action=deleteFile&respizienzID={$meine[$i]->getID()}{$mode}');\" class=\"btn btn-sm btn-danger\"><i class=\"fa fa-trash\"></i></button>";
                 * }
                 * }
                 * else {
                 * $downloadFileFSL = '<i>Bisher keine Datei</i>';
                 *
                 * if($isFachbetreuer) {
                 * $downloadFileFSL .= "<br /><button type=\"button\" class=\"btn btn-sm\" onclick=\"uploadFile(" . $meine[$i]->getID() . ");\"><i class=\"fa fa-upload\"></i> PDF hochladen</button>";
                 * }
                 * }
                 *
                 * if($meine[$i]->getSLFile() != null) {
                 * $fileSL = $meine[$i]->getSLFile();
                 * $downloadFileSL = "<a href=\"" . $fileSL->getURLToFile() . "\"><i class=\"fa fa-file-pdf-o\"></i> Download</a>";
                 *
                 * $lehrer = $meine[$i]->getSLLehrer();
                 * if($lehrer != null) $downloadFileSL .= "<br />Repiziert von " . $lehrer->getDisplayNameMitAmtsbezeichnung();
                 *
                 * if($isSchulleitung) {
                 * $downloadFileSL .= "<br /><button type=\"button\" class=\"btn btn-sm\" onclick=\"uploadFile(" . $meine[$i]->getID() . ");\"><i class=\"fa fa-upload\"></i> PDF neu hochladen</button>";
                 * $downloadFileSL .= " <button type=\"button\" onclick=\"javascript:confirmAction('Soll die hinterlegte PDF Datei wirklich gelöscht werden?','index.php?page=respizienz&action=deleteFile&respizienzID={$meine[$i]->getID()}{$mode}');\" class=\"btn btn-sm btn-danger\"><i class=\"fa fa-trash\"></i></button>";
                 * }
                 * }
                 * else {
                 * $downloadFileSL = '<i>Bisher keine Datei</i>';
                 * if($isSchulleitung) {
                 * $downloadFileSL .= "<br /><button type=\"button\" class=\"btn btn-sm\" onclick=\"uploadFile(" . $meine[$i]->getID() . ");\"><i class=\"fa fa-upload\"></i> PDF hochladen</button>";
                 * }
                 * }
                 **/

                $notenHTML = "";

                $noten = $meine[$i]->getSchuelerMitNoten();

                for ($n = 0; $n < sizeof($noten); $n++) {
                    $notenHTML .= "<tr><td>" . $noten[$n]['schueler']->getCompleteSchuelerName() . "</td><td>" . $noten[$n]['note'] . "</td></tr>";
                }


                eval("\$meineHTML .= \"" . DB::getTPL()->get("respizienz/bit") . "\";");
            }
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
		return true;
	}

    public static function getSettingsDescription() {
        return [
            [
                'name' => 'resp-mode',
                'titel' => 'Modus',
                'typ' => 'SELECT',
                'options' => [
                    [
                        'value' => 'RESP',
                        'name' => 'Respizienz und Online Archivierung'
                    ],
                    [
                        'value' => 'ARCH',
                        'name' => 'Online Archivierung'
                    ]
                ]
            ],
            [
                'name' => 'resp-name',
                'titel' => 'Name des Moduls',
                'text' => 'Name des Moduls. (z.B. "digitale Respizienz" oder "digitale Archivierung")',
                'typ' => 'ZEILE'
            ],
            [
                'name' => 'resp-activate-fb',
                'titel' => 'Zugriff für Fachbetreuer aktivieren',
                'text' => 'Damit haben Fachbetreuer Zugriff auf die Dateien der Fachlehrer',
                'typ' => 'BOOLEAN'
            ],
            [
                'name' => 'resp-activate-sl',
                'titel' => 'Zugriff für Schulleitung aktivieren',
                'text' => 'Damit haben Schulleitung Zugriff auf die Dateien aller Fachlehrer',
                'typ' => 'BOOLEAN'
            ],
        ];
    }

	public static function getSiteDisplayName() {
		return 'Digitale Archivierung LNW';
	}

	public static function getAdminMenuIcon() {
	    return 'fas fa-archive';
    }

    /**
	 * Liest alle Nutzergruppen aus, die diese Seite verwendet. (Für die Benutzeradministration)
	 * @return array(array('groupName' => '', 'beschreibung' => ''))
	 */
	public static function getUserGroups() {
		return array();
	}
	
	public static function hasAdmin() {
		return true;
	}

	public static function displayAdministration($selfURL) {

	    $faecher = fach::getAll();


	    if($_REQUEST['action'] == 'save') {
            for($i = 0; $i < sizeof($faecher); $i++) {
                $asdID = $faecher[$i]->getASDID();
                DB::getSettings()->setValue('resp-' . $asdID . '-SA', $_POST['resp-' . $asdID . '-SA'] > 0);
                DB::getSettings()->setValue('resp-' . $asdID . '-EX', $_POST['resp-' . $asdID . '-EX'] > 0);
                DB::getSettings()->setValue('resp-' . $asdID . '-PLNW', $_POST['resp-' . $asdID . '-PLNW'] > 0);
                DB::getSettings()->setValue('resp-' . $asdID . '-KA', $_POST['resp-' . $asdID . '-KA'] > 0);
                DB::getSettings()->setValue('resp-' . $asdID . '-MODUS', $_POST['resp-' . $asdID . '-MODUS'] > 0);
            }
        }



	    $table = "<table class='table table-striped table-bordered'><tr>
                <th>Fach</th>
                <th>Schulaufgaben
                <br /><button type='button' class='btn btn-xs' onclick=\"selectAll('resp-sa')\"><i class='fas fa-check-circle'></i></button> 
                <button type='button' class='btn btn-xs' onclick=\"unSelectAll('resp-sa')\"><i class='fas fa-ban'></i></button>
                </th>
                <th>Stegreifaufgaben
                <br /><button type='button' class='btn btn-xs' onclick=\"selectAll('resp-ex')\"><i class='fas fa-check-circle'></i></button> 
                <button type='button' class='btn btn-xs' onclick=\"unSelectAll('resp-ex')\"><i class='fas fa-ban'></i></button></th>
                <th>Praktische Leistungsnachweise
                <br /><button type='button' class='btn btn-xs' onclick=\"selectAll('resp-plnw')\"><i class='fas fa-check-circle'></i></button> 
                <button type='button' class='btn btn-xs' onclick=\"unSelectAll('resp-plnw')\"><i class='fas fa-ban'></i></button></th>
                <th>Kurzarbeiten
                <br /><button type='button' class='btn btn-xs' onclick=\"selectAll('resp-ka')\"><i class='fas fa-check-circle'></i></button> 
                <button type='button' class='btn btn-xs' onclick=\"unSelectAll('resp-ka')\"><i class='fas fa-ban'></i></button></th>
                <th>Modus
                <br /><button type='button' class='btn btn-xs' onclick=\"selectAll('resp-modus')\"><i class='fas fa-check-circle'></i></button> 
                <button type='button' class='btn btn-xs' onclick=\"unSelectAll('resp-modus')\"><i class='fas fa-ban'></i></button></th>
               </tr>";

	    for($i = 0; $i < sizeof($faecher); $i++) {
	        $asdID = $faecher[$i]->getASDID();

            $archivSA = DB::getSettings()->getBoolean('resp-' . $asdID . '-SA');
            $archivEX = DB::getSettings()->getBoolean('resp-' . $asdID . '-EX');
            $archivPLNW = DB::getSettings()->getBoolean('resp-' . $asdID . '-PLNW');
            $archivKA = DB::getSettings()->getBoolean('resp-' . $asdID . '-KA');
            $archivMODUS = DB::getSettings()->getBoolean('resp-' . $asdID . '-MODUS');

            $checked = [
                'sa' => $archivSA ? ' checked="checked"' : '',
                'ex' => $archivEX ? ' checked="checked"' : '',
                'plnw' => $archivPLNW ? ' checked="checked"' : '',
                'ka' => $archivKA ? ' checked="checked"' : '',
                'modus' => $archivMODUS ? ' checked="checked"' : '',

            ];


            $className = md5($faecher[$i]->getASDID() . rand());

	        $table.= "<tr>
                <td>" . $faecher[$i]->getLangform() . "
                
                <br /><button type='button' class='btn btn-xs' onclick=\"selectAll('$className')\"><i class='fas fa-check-circle'></i></button> 
                <button type='button' class='btn btn-xs' onclick=\"unSelectAll('$className')\"><i class='fas fa-ban'></i></button>
                
                </td>
                <td><input type='checkbox' value='1' class='lnwtype resp-sa $className' name='resp-$asdID-SA'" . $checked['sa'] . ">" . "</td>
                <td><input type='checkbox' value='1' class='lnwtype resp-ex $className' name='resp-$asdID-EX'" . $checked['ex'] . ">" . "</td>
                <td><input type='checkbox' value='1' class='lnwtype resp-plnw $className' name='resp-$asdID-PLNW'" . $checked['plnw'] . ">" . "</td>
                <td><input type='checkbox' value='1' class='lnwtype resp-ka $className' name='resp-$asdID-KA'" . $checked['ka'] . ">" . "</td>
                <td><input type='checkbox' value='1' class='lnwtype resp-modus $className ' name='resp-$asdID-MODUS'" . $checked['modus'] . ">" . "</td>               
               </tr>
	        ";
        }

	    $table .= "</table>";

	    $html = "";

	    eval("\$html = \"" . DB::getTPL()->get("respizienz/admin/index") . "\";");

	    return $html;
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