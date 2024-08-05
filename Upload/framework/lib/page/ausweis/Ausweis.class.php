<?php


class Ausweis extends AbstractPage {
	

	public function __construct() {		
		parent::__construct(array("Ausweise"));
		
		$this->checkLogin(); 
	}

	public function execute() {

	    switch($_REQUEST['action']) {
	        case 'myAusweise':
	            $this->myAusweise();
	        break;
	        
	        case 'print':
	            $this->printAusweise();
	        break;
	        
	        case 'editMyPhoto':
	            $this->editMyFoto();
	        break;
	        
	        case 'uploadImage':
	            $result = [
                    'uploadOK' => false,
                    'attachmentURL' => '',
                    'attachmentID' => 0
	            ];
	            
	            $upload = FileUpload::uploadPicture('attachmentFile','');
	            
	            if($upload['result']) {
	                $uploadObject = $upload['uploadobject'];
	                
	                $result['uploadOK'] = true;
	                
	                $attachment = MessageAttachment::addAttachmentAndGetObject($uploadObject);
	                $result['attachmentID'] = $attachment->getID();
	                $result['attachmentURL'] = $attachment->getUpload()->getURLToFile(true);
	            }
	            
	            
	            header("Content-type: text/json");
	            echo json_encode($result);
	            exit(0);
	            
	        break;
	        
	        case 'approve':
	           $this->approveAusweise();
	        break;
	    }
	    
	}
	
	private function editMyFoto() {
	    
	}
	
	private function printAusweise() {
	    $access = false;
	    
	    if(DB::getSession()->isAdmin()) {
	        $access = true;
	    }
	    
	    if(DB::getSession()->isMember(self::getAdminGroup())) {
	        $access = true;
	    }
	    
	    if(DB::getSession()->isMember('Webportal_Ausweis_Drucken')) {
	        $access = true;
	    }
	    
	    if(!$access) new errorPage();
	    
	    $myAusweise = AbstractAusweis::getAusweiseToPrint();
	    
	    
	    if($_REQUEST['mode'] == 'setPrint') {
	        for($a = 0; $a < sizeof($myAusweise); $a++) {
	            if($myAusweise[$a]->getID() == $_REQUEST['ausweisID']) {
	                if($_REQUEST['status'] > 0) {
	                    $myAusweise[$a]->setStatus('ABGEHOLT');
	                }
	                else {
	                    $myAusweise[$a]->setStatus('ERSTELLT');
	                }
	                $myAusweise[$a]->setKommentar($_REQUEST['kommentar']);
	                $myAusweise[$a]->setEssenKundennummer($_REQUEST['kundennummer']);
	                
	                break;
	                
	            }
	        }
	        
	        header("Location: index.php?page=Ausweis&action=print");
	        exit(0);
	    }
	    
	    if($_REQUEST['mode'] == 'getVorderseite') {
	        for($a = 0; $a < sizeof($myAusweise); $a++) {
	            if($myAusweise[$a]->getID() == $_REQUEST['ausweisID']) {
	                $pdf = $myAusweise[$a]->getAusweisPDFFront();
	                $pdf->Output("Vorderseite.pdf");
	                exit(0);
	                
	            }
	        }
	        
	        header("Location: index.php?page=Ausweis&action=print");
	        exit(0);
	    }
	    
	    if($_REQUEST['mode'] == 'getRueckseite') {
	        for($a = 0; $a < sizeof($myAusweise); $a++) {
	            if($myAusweise[$a]->getID() == $_REQUEST['ausweisID']) {
	                $pdf = $myAusweise[$a]->getAusweisPDFBack();
	                $pdf->Output("Rueckseite.pdf");
	                exit(0);
	            }
	        }
	        
	        header("Location: index.php?page=Ausweis&action=print");
	        exit(0);
	    }
	    
	    
	    if(sizeof($myAusweise) == 0) {
	        $ausweiseHTML = "<tr><td colspan=\"4\">Bisher keine Ausweise beantragt</td></tr>";
	    }
	    else {
	        $ausweiseHTML = "";
	        
	        
	        for($a = 0; $a < sizeof($myAusweise); $a++) {
	            $ausweiseHTML .= "<tr>";
	            
	            $ausweiseHTML .= "<td>" . $myAusweise[$a]->getType() . "</td>";
	            
	            $ausweiseHTML .= "<td><b>" . $myAusweise[$a]->getName() . "</b><br />";
	            
	            $ausweiseHTML .= $myAusweise[$a]->getPLZ() . " " . $myAusweise[$a]->getOrt() . "<br />Geburtsdatum: " . DateFunctions::getNaturalDateFromMySQLDate($myAusweise[$a]->getGeburtsdatum()) . "</td>";
	            
	            if($myAusweise[$a]->getBild() != null) {
	                $ausweiseHTML .= "<td><img src=\"" . $myAusweise[$a]->getBild()->getURLToFile() . "\" height=\"100\"></td>";
	            }
	            else $ausweiseHTML .= "<td>Bild ungültig</td>";
	            
	            $ausweiseHTML .= "<td>";
	            
	            
	            $ausweiseHTML .= "<a href=\"index.php?page=Ausweis&action=print&mode=getVorderseite&ausweisID=" . $myAusweise[$a]->getID() . "\"><i class=\"fa fa-file-pdf-o\"></i> Vorderseite</a><br />";
	            $ausweiseHTML .= "<a href=\"index.php?page=Ausweis&action=print&mode=getRueckseite&ausweisID=" . $myAusweise[$a]->getID() . "\"><i class=\"fa fa-file-pdf-o\"></i> Rückseite</a><br />";
	            
	            $ausweiseHTML .= "</td>";
	            
	            $ausweiseHTML .= "<td>
	                
                <form action=\"index.php?page=Ausweis&action=print&mode=setPrint&ausweisID=" . $myAusweise[$a]->getID() . "\" method=\"post\">
                    
                <div class=\"form-group\"><label>Kundennummer Mittagessen:</label>
                    <input type=\"text\" name=\"kundennummer\" class=\"form-control\" value=\"" . htmlspecialchars($myAusweise[$a]->getEssenKundennummer()) . "\"></div>";
	            
	            
	            $ausweiseHTML .= "<div class=\"form-group\"><label>Kommentar:</label>
                    <input type=\"text\" name=\"kommentar\" class=\"form-control\" placeholder=\"z.B. Foto falsch.\" value=\"" . htmlspecialchars($myAusweise[$a]->getKommentar()) . "\"></div>";
	            
	            
	            $ausweiseHTML .= "<br /><button class=\"btn btn-success btn-xs\" type=\"submit\" name=\"status\" value=\"0\"><i class=\"fa fa-print\"></i> Gedruckt</button>";
	            
	            $ausweiseHTML .= " <button class=\"btn btn-success btn-xs\" type=\"submit\" name=\"status\" value=\"1\"><i class=\"fa fa-check\"></i> Ausweis abgeholt</button></form>";
	            
	            
	            $ausweiseHTML .= "</td>";
	            
	            
	            
	            $ausweiseHTML .= "</tr>";
	        }
	        
	    }
	    
	    
	    
	    
	    eval("DB::getTPL()->out(\"" . DB::getTPL()->get("ausweise/print/index") . "\");");
	    
	
	}
	
	private function approveAusweise() {
	    $access = false;
	    
	    if(DB::getSession()->isAdmin()) {
	        $access = true;
	    }
	    
	    if(DB::getSession()->isMember(self::getAdminGroup())) {
	        $access = true;
	    }
	    
	    if(DB::getSession()->isMember('Webportal_Ausweis_Genehmigen')) {
	        $access = true;
	    }
	    
	    if(!$access) new errorPage();
	    
	    $myAusweise = AbstractAusweis::getAusweiseToApprove();
	    
	    
	    if($_REQUEST['mode'] == 'doApprove') {
	        for($a = 0; $a < sizeof($myAusweise); $a++) {
	            if($myAusweise[$a]->getID() == $_REQUEST['ausweisID']) {
	                
	                if($_REQUEST['status'] > 0) {
	                    $myAusweise[$a]->setStatus('GENEHMIGT');
	                }
	                else {
	                    $myAusweise[$a]->setStatus('NICHTGENEHMIGT');
	                }
	                
	                $myAusweise[$a]->setAblauf(DateFunctions::getMySQLDateFromNaturalDate($_REQUEST['ablauf']));
	                $myAusweise[$a]->setKommentar($_REQUEST['kommentar']);
	                
	                break;
	                
	            }
	        }
	        
	        header("Location: index.php?page=Ausweis&action=approve");
	        exit(0);
	    }
	    
	    
	    if(sizeof($myAusweise) == 0) {
	        $ausweiseHTML = "<tr><td colspan=\"4\">Bisher keine Ausweise beantragt</td></tr>";
	    }
	    else {
	        $ausweiseHTML = "";
	        
	        
	        for($a = 0; $a < sizeof($myAusweise); $a++) {
	            $ausweiseHTML .= "<tr>";
	            
	            $ausweiseHTML .= "<td>" . $myAusweise[$a]->getType() . "</td>";
	            
	            $ausweiseHTML .= "<td><b>" . $myAusweise[$a]->getName() . "</b><br />";
	            
	            $ausweiseHTML .= $myAusweise[$a]->getPLZ() . " " . $myAusweise[$a]->getOrt() . "<br />Geburtsdatum: " . DateFunctions::getNaturalDateFromMySQLDate($myAusweise[$a]->getGeburtsdatum()) . "</td>";
	            
	            if($myAusweise[$a]->getBild() != null) {
	                $ausweiseHTML .= "<td><img src=\"" . $myAusweise[$a]->getBild()->getURLToFile() . "\" height=\"100\"></td>";
	            }
	            else $ausweiseHTML .= "<td>Bild ungültig</td>";
	            
	            $ausweiseHTML .= "<td>

                <form action=\"index.php?page=Ausweis&action=approve&mode=doApprove&ausweisID=" . $myAusweise[$a]->getID() . "\" method=\"post\">

                <div class=\"form-group\"><label>Gültig bis:</label>
                    <input type=\"text\" name=\"ablauf\" class=\"form-control datePicker\" value=\"" . DateFunctions::getNaturalDateFromMySQLDate($myAusweise[$a]->getAblaufdatum()) . "\"></div>";
	            	                
	            
	            $ausweiseHTML .= "<div class=\"form-group\"><label>Kommentar:</label>
                    <input type=\"text\" name=\"kommentar\" class=\"form-control\" placeholder=\"z.B. Foto falsch.\"></div>";
	            
	            
	            $ausweiseHTML .= "<br /><button class=\"btn btn-danger btn-xs\" type=\"submit\" name=\"status\" value=\"0\"><i class=\"fa fa-ban\"></i> Antrag nicht genehmigen</button>";
	                
	            $ausweiseHTML .= " <button class=\"btn btn-success btn-xs\" type=\"submit\" name=\"status\" value=\"1\"><i class=\"fa fa-check\"></i> Antrag genehmigen</button></form>";
	                
	            
	            $ausweiseHTML .= "</td>";
	            
	            
	            
	            $ausweiseHTML .= "</tr>";
	        }
	        
	    }
	    
	    
	    
	    
	    eval("DB::getTPL()->out(\"" . DB::getTPL()->get("ausweise/approve/index") . "\");");
	    
	}
	
	private function myAusweise() {
	    $types = [
	        'LEHRER',
	        'SCHUELER',
	        'MITARBEITER',
	        'GAST'
	    ];
	    
	    
	    if(in_array($_REQUEST['type'], $types)) {
	        $type = $_REQUEST['type'];
	    }
	    else {
	        new errorPage();
	    }
	    
	    $access = false;
	    
	    if(DB::getSession()->isAdmin()) {
	        $access = true;
	    }
	    
	    if(DB::getSession()->isMember(self::getAdminGroup())) {
	        $access = true;
	    }
	    
	    if($type == 'SCHUELER') {
	        $schueler = [];
	        
	        if(!$access) {
    	        if(DB::getSession()->isTeacher() && DB::getSettings()->getBoolean('ausweis-schuelerausweis-lehrer')) {
    	            $access = true;
    	            $schueler = schueler::getAll();
    	        }
    	        else if(DB::getSession()->isEltern() && DB::getSettings()->getBoolean('ausweis-schuelerausweis-eltern')) {
    	            $access = true;
    	            $schueler = DB::getSession()->getElternObject()->getMySchueler();
    	        }
    	        else if(DB::getSession()->isPupil() && DB::getSettings()->getBoolean('ausweis-schuelerausweis-schueler')) {
    	            $access = true;
    	            $schueler = [DB::getSession()->getSchuelerObject()];
    	        }
    	        else if(DB::getSession()->isMember('Webportal_Ausweis_Schueler_Antrag')) {
    	           $access = true;
    	           $schueler = schueler::getAll();
    	        }
    	        
	        }
	        else {
	            $schueler = schueler::getAll();
	        }
	        
	        if($access) {
	            $this->mySchuelerAusweise($schueler);
	        }
	        
	    }
	    
	    if($type == 'LEHRER') {
	        $lehrer = [];
	        
	        if(!$access) {
	            if(DB::getSession()->isTeacher() && DB::getSettings()->getBoolean('ausweis-lehrerausweis-lehrer')) {
	                $access = true;
	                $lehrer= [DB::getSession()->getTeacherObject()];
	            }
	            else if(DB::getSession()->isMember('Webportal_Ausweis_Lehrer_Antrag')) {
	                $access = true;
	                $lehrer = lehrer::getAll();
	            }
	            
	        }
	        else {
	            $lehrer = lehrer::getAll();
	        }
	        
	        
	        
	        if($access) {
	            $this->myLehrerausweise($lehrer);
	        }
	        else new errorPage("Kein Zugriff!");	        
	    }
	    
	    if($type == 'MITARBEITER') {
	        
	        if(!$access) {
                if(DB::getSession()->isMember('Webportal_Ausweis_Mitarbeiter_Antrag')) {
	                $access = true;
	            }
	            
	        }      
	        
	        
	        if($access) {
	            $this->myMitarbeiterAusweise();
	        }
	        else new errorPage("Kein Zugriff!");
	    }
	    
	    
	}
	
	private function myMitarbeiterAusweise() {
	    
	    $myAusweise = AbstractAusweis::getMyAusweise(DB::getSession()->getUserID(),'MITARBEITER');
	    
	    if($_REQUEST['mode'] == 'Antrag') {

	        
	        $foto = FileUpload::uploadPicture("passfoto", "");
	        
	        // Debugger::debugObject($foto,1);
	        
	        if($foto['result']) {
	            
	            $newAusweis = AbstractAusweis::getMyAusweisObject();
	            
	            $newAusweis->createNew();
	            $newAusweis->setName($_REQUEST['name']);
	            $newAusweis->setBarcode($_REQUEST['barcode']);
	            $newAusweis->setMitarbeiterausweis();
	            $newAusweis->setErsteller(DB::getSession()->getUser());
	            $newAusweis->setStatus('BEANTRAGT');
	            
	            $newAusweis->setPLZ($_REQUEST['plz']);
	            $newAusweis->setOrt($_REQUEST['ort']);
	            
	            $newAusweis->setBild($foto['uploadobject']);
	            $newAusweis->setPreis(DB::getSettings()->getValue('ausweis-mitarbeiterausweis-kosten'));
	            $newAusweis->setAblauf($newAusweis->getGueltigkeitForNewAusweis('MITARBEITER'));
	            
	            
	            $newAusweis->setGeburtsdatum(DateFunctions::getMySQLDateFromNaturalDate($_REQUEST['gebdatum']));
	            
	            header("Location: index.php?page=Ausweis&action=myAusweise&type=MITARBEITER&mode=editFoto&ausweisID=" . $newAusweis->getID());
	            exit();
	            
	            exit(0);
	            
	        }
	        else {
	            new errorPage("Das Foto ist nicht gültig!");
	            exit();
	        }
	        
	    }
	    
	    if($_REQUEST['mode'] == 'deleteAntrag') {
	        for($a = 0; $a < sizeof($myAusweise); $a++) {
	            if($myAusweise[$a]->getID() == $_REQUEST['ausweisID']) {
	                $myAusweise[$a]->delete();
	                header("Location: index.php?page=Ausweis&action=myAusweise&type=MITARBEITER");
	                exit(0);
	            }
	        }
	    }
	    
	    if($_REQUEST['mode'] == 'editFoto') {
	        for($a = 0; $a < sizeof($myAusweise); $a++) {
	            if($myAusweise[$a]->getID() == $_REQUEST['ausweisID']) {
	                if(!$myAusweise[$a]->isGenehmigt()) {
	                    // Solange nicht genehmigt
	                    
	                    $foto = $myAusweise[$a]->getBild();
	                    
	                    $fotoURL = $foto->getURLToFile();
	                    
	                    if($_REQUEST['modeAction'] == 'saveEditedFoto') {
	                        
	                        $foto->reuploadJPEGImageFromBase64($_REQUEST['fotoBase64']);
	                        
	                        header("Location: index.php?page=Ausweis&action=myAusweise&type=MITARBEITER");
	                        exit(0);
	                    }
	                    if($_REQUEST['modeAction'] == 'reupload') {
	                        $foto = FileUpload::uploadPicture("passfoto", "");
	                        
	                        if($foto['result']) {
	                            $myAusweise[$a]->setBild($foto['uploadobject']);
	                            
	                            header("Location: index.php?page=Ausweis&action=myAusweise&type=MITARBEITER&mode=editFoto&ausweisID=" . $myAusweise[$a]->getID());
	                            exit();
	                        }

	                    }
	                    
	                    $type ='MITARBEITER';
	                    
	                    eval("DB::getTPL()->out(\"" . DB::getTPL()->get("ausweise/editfoto/index") . "\");");
	                    
	                    PAGE::kill(true);
											//exit(0);
	                    
	                    
	                }
	            }
	        }
	    }
	    
	    if(sizeof($myAusweise) == 0) {
	        $ausweiseHTML = "<tr><td colspan=\"4\">Bisher keine Ausweise beantragt</td></tr>";
	    }
	    else {
	        $ausweiseHTML = "";
	        
	        
	        for($a = 0; $a < sizeof($myAusweise); $a++) {
	            $ausweiseHTML .= "<tr>";
	            
	            $ausweiseHTML .= "<td><b>" . $myAusweise[$a]->getName() . "</b><br />";
	            
	            $ausweiseHTML .= $myAusweise[$a]->getPLZ() . " " . $myAusweise[$a]->getOrt() . "<br />Geburtsdatum: " . DateFunctions::getNaturalDateFromMySQLDate($myAusweise[$a]->getGeburtsdatum()) . "</td>";
	            
	            if($myAusweise[$a]->getBild() != null) {
	                $ausweiseHTML .= "<td><img src=\"" . $myAusweise[$a]->getBild()->getURLToFile() . "\" height=\"100\">";
	                
	                
	                if(!$myAusweise[$a]->isGenehmigt() && !$myAusweise[$a]->isNotGenehmigt()) $ausweiseHTML .= "<br ><button class=\"btn btn-xs\" type=\"button\" onclick=\"window.location.href='index.php?page=Ausweis&action=myAusweise&type=MITARBEITER&mode=editFoto&ausweisID=" . $myAusweise[$a]->getID() . "'\"><i class=\"fa fa-photo\"></i> Bild bearbeiten</button>";
	                
	                
	                $ausweiseHTML .= "</td>";}
	            else $ausweiseHTML .= "<td>Bild ungültig</td>";
	            
	            $ausweiseHTML .= "<td>Voraussichtlich " . DateFunctions::getNaturalDateFromMySQLDate($myAusweise[$a]->getAblaufdatum()) . "</td>";
	            
	            $ausweiseHTML .= "<td>";
	            
	            if($myAusweise[$a]->isAbgeholt()) {
	                $ausweiseHTML .= "<i class=\"fa fa-check\"></i> Abgeholt";
	            }
	            elseif($myAusweise[$a]->isErstellt()) {
	                $ausweiseHTML .= "<i class=\"fa fa-check\"></i> Erstellt. Abholbereit.";
	            }
	            elseif($myAusweise[$a]->isGenehmigt()) {
	                $ausweiseHTML .= "<i class=\"fa fa-check\"></i> Genehmigt. Noch nicht abholbereit.";
	            }
	            elseif($myAusweise[$a]->isBeantragt()) {
	                $ausweiseHTML .= "<i class=\"fa fa-question\"></i> Noch nicht genehmigt.";
	                
	                $ausweiseHTML .= "<br ><button class=\"btn btn-danger btn-xs\" type=\"button\" onclick=\"confirmAction('Soll der Antrag wirklich gelöscht werden?','index.php?page=Ausweis&action=myAusweise&type=SCHUELER&mode=deleteAntrag&ausweisID=" . $myAusweise[$a]->getID() . "')\"><i class=\"fa fa-trash\"></i> Antrag zurückziehen</button>";
	                
	            }
	            
	            $ausweiseHTML .= "</td>";
	            
	            
	            
	            $ausweiseHTML .= "</tr>";
	        }
	        
	    }
	    

	    
	    $preis = number_format(DB::getSettings()->getValue('ausweis-mitarbeiterausweis-kosten') / 100, 2, ",", ".");
	    
	    
	    eval("DB::getTPL()->out(\"" . DB::getTPL()->get("ausweise/mitarbeiter/index") . "\");");
	    
	    
	    
	}
	
	/**
	 *
	 * @param lehrer[] $lehrer
	 */
	private function myLehrerausweise($lehrer) {
	    	    
	    $myAusweise = AbstractAusweis::getMyAusweise(DB::getSession()->getUserID(),'LEHRER');
	    
	    if($_REQUEST['mode'] == 'Antrag') {
	        // Neuen Ausweis beantragen.
	        
	        $s = null;
	        
	        for($i = 0; $i < sizeof($lehrer); $i++) {
	            if($_POST['lehrerAsvID'] == $lehrer[$i]->getAsvID()) {
	                $s = $lehrer[$i];
	            }
	        }
	        	        
	        
	        if($s == null) new errorPage("Accessviolation!");
	        
	        $foto = FileUpload::uploadPicture("passfoto", "");
	        
	        // Debugger::debugObject($foto,1);
	        
	        if($foto['result']) {
	            
	            $newAusweis = AbstractAusweis::getMyAusweisObject();
	            
	            $newAusweis->createNew();
	            $newAusweis->setName($s->getDisplayNameMitAmtsbezeichnung());
	            $newAusweis->setBarcode($s->getAsvID());
	            $newAusweis->setLehrerausweis();
	            $newAusweis->setErsteller(DB::getSession()->getUser());
	            $newAusweis->setStatus('BEANTRAGT');
	            
	            $newAusweis->setPLZ($_REQUEST['plz']);
	            $newAusweis->setOrt($_REQUEST['ort']);
	            
	            $newAusweis->setBild($foto['uploadobject']);
	            $newAusweis->setPreis(DB::getSettings()->getValue('ausweis-lehrerausweis-kosten'));
	            $newAusweis->setAblauf($newAusweis->getGueltigkeitForNewAusweis('LEHRER'));
	            
	            
	            $newAusweis->setGeburtsdatum(DateFunctions::getMySQLDateFromNaturalDate($_REQUEST['gebdatum']));
	            
	            header("Location: index.php?page=Ausweis&action=myAusweise&type=LEHRER&mode=editFoto&ausweisID=" . $newAusweis->getID());
	            exit(0);
	            	            
	        }
	        else {
	            new errorPage("Das Foto ist nicht gültig!");
	            exit();
	        }
	        
	    }
	    
	    if($_REQUEST['mode'] == 'deleteAntrag') {
	        for($a = 0; $a < sizeof($myAusweise); $a++) {
	            if($myAusweise[$a]->getID() == $_REQUEST['ausweisID']) {
	                $myAusweise[$a]->delete();
	                header("Location: index.php?page=Ausweis&action=myAusweise&type=LEHRER");
	                exit(0);
	            }
	        }
	    }
	    
	    if($_REQUEST['mode'] == 'editFoto') {
	        for($a = 0; $a < sizeof($myAusweise); $a++) {
	            if($myAusweise[$a]->getID() == $_REQUEST['ausweisID']) {
	                if(!$myAusweise[$a]->isGenehmigt()) {
	                    // Solange nicht genehmigt
	                    
	                    $foto = $myAusweise[$a]->getBild();
	                    
	                    $fotoURL = $foto->getURLToFile();
	                    
	                    if($_REQUEST['modeAction'] == 'saveEditedFoto') {
	                        
	                        $foto->reuploadJPEGImageFromBase64($_REQUEST['fotoBase64']);
	                        
	                        header("Location: index.php?page=Ausweis&action=myAusweise&type=LEHRER");
	                        exit(0);
	                    }
	                    if($_REQUEST['modeAction'] == 'reupload') {
	                        $foto = FileUpload::uploadPicture("passfoto", "");
	                        
	                        if($foto['result']) {
	                            $myAusweise[$a]->setBild($foto['uploadobject']);
	                            
	                            header("Location: index.php?page=Ausweis&action=myAusweise&type=LEHRER&mode=editFoto&ausweisID=" . $myAusweise[$a]->getID());
	                            exit();
	                        }
	                    }
	                    
	                    $type ='LEHRER';
	                    
	                    eval("DB::getTPL()->out(\"" . DB::getTPL()->get("ausweise/editfoto/index") . "\");");
	                    
	                    PAGE::kill(true);
											//exit(0);
	                    
	                    
	                }
	            }
	        }
	    }
	    
	    if(sizeof($myAusweise) == 0) {
	        $ausweiseHTML = "<tr><td colspan=\"4\">Bisher keine Ausweise beantragt</td></tr>";
	    }
	    else {
	        $ausweiseHTML = "";
	        
	        
	        for($a = 0; $a < sizeof($myAusweise); $a++) {
	            $ausweiseHTML .= "<tr>";
	            
	            $ausweiseHTML .= "<td><b>" . $myAusweise[$a]->getName() . "</b><br />";
	            
	            $ausweiseHTML .= $myAusweise[$a]->getPLZ() . " " . $myAusweise[$a]->getOrt() . "<br />Geburtsdatum: " . DateFunctions::getNaturalDateFromMySQLDate($myAusweise[$a]->getGeburtsdatum()) . "</td>";
	            
	            if($myAusweise[$a]->getBild() != null) {
	                $ausweiseHTML .= "<td><img src=\"" . $myAusweise[$a]->getBild()->getURLToFile() . "\" height=\"100\">";
	                
	                
	                if(!$myAusweise[$a]->isGenehmigt() && !$myAusweise[$a]->isNotGenehmigt()) $ausweiseHTML .= "<br ><button class=\"btn btn-xs\" type=\"button\" onclick=\"window.location.href='index.php?page=Ausweis&action=myAusweise&type=LEHRER&mode=editFoto&ausweisID=" . $myAusweise[$a]->getID() . "'\"><i class=\"fa fa-photo\"></i> Bild bearbeiten</button>";
	                
	                
	                $ausweiseHTML .= "</td>";
	            }
	            else $ausweiseHTML .= "<td>Bild ungültig</td>";
	            
	            $ausweiseHTML .= "<td>Voraussichtlich " . DateFunctions::getNaturalDateFromMySQLDate($myAusweise[$a]->getAblaufdatum()) . "</td>";
	            
	            $ausweiseHTML .= "<td>";
	            
	            if($myAusweise[$a]->isAbgeholt()) {
	                $ausweiseHTML .= "<i class=\"fa fa-check\"></i> Abgeholt";
	            }
	            elseif($myAusweise[$a]->isErstellt()) {
	                $ausweiseHTML .= "<i class=\"fa fa-check\"></i> Erstellt. Abholbereit.";
	            }
	            elseif($myAusweise[$a]->isGenehmigt()) {
	                $ausweiseHTML .= "<i class=\"fa fa-check\"></i> Genehmigt. Noch nicht abholbereit.";
	            }
	            elseif($myAusweise[$a]->isBeantragt()) {
	                $ausweiseHTML .= "<i class=\"fa fa-question\"></i> Noch nicht genehmigt.";
	                
	                $ausweiseHTML .= "<br ><button class=\"btn btn-danger btn-xs\" type=\"button\" onclick=\"confirmAction('Soll der Antrag wirklich gelöscht werden?','index.php?page=Ausweis&action=myAusweise&type=SCHUELER&mode=deleteAntrag&ausweisID=" . $myAusweise[$a]->getID() . "')\"><i class=\"fa fa-trash\"></i> Antrag zurückziehen</button>";
	                
	            }
	            
	            $ausweiseHTML .= "</td>";
	            
	            
	            
	            $ausweiseHTML .= "</tr>";
	        }
	        
	    }
	    
	    
	    $preis = number_format(DB::getSettings()->getValue('ausweis-lehrerausweis-kosten') / 100, 2, ",", ".");
	    
	    
	    $lehrerSelectHTML = "";
	    
	    for($i = 0; $i < sizeof($lehrer); $i++) {
	        $lehrerSelectHTML .= "<option value=\"" . $lehrer[$i]->getAsvID() . "\">" . $lehrer[$i]->getDisplayNameMitAmtsbezeichnung() . "</option>\r\n";
	    }
	    
	    
	    eval("DB::getTPL()->out(\"" . DB::getTPL()->get("ausweise/lehrer/index") . "\");");
	    
	    
	    
	}
	
	
	/**
	 * 
	 * @param schueler[] $schueler
	 */
	private function mySchuelerAusweise($schueler) {
	    /** @var AbstractAusweis[] $myAusweise */
	    $myAusweise = AbstractAusweis::getMyAusweise(DB::getSession()->getUserID(),'SCHUELER');
	    
	    if($_REQUEST['mode'] == 'Antrag') {
	        // Neuen Ausweis beantragen.
	        
	        $s = null;
	        
	        for($i = 0; $i < sizeof($schueler); $i++) {
	            if($_POST['schuelerAsvID'] == $schueler[$i]->getAsvID()) {
	                $s = $schueler[$i];
	            }
	        }
	        
	        // Debugger::debugObject($_REQUEST,1);
	        
	        
	        if($s == null) new errorPage("Accessviolation!");	        
	        
	        $foto = FileUpload::uploadPicture("passfoto", "");
	        
// 	        Debugger::debugObject($foto,1);
	        
	        if($foto['result']) {
	            
	            $newAusweis = AbstractAusweis::getMyAusweisObject();
	            
	            $newAusweis->createNew();
	            $newAusweis->setName($s->getCompleteSchuelerName());
	            $newAusweis->setBarcode($s->getAsvID());
	            $newAusweis->setSchuelerausweis();
	            $newAusweis->setErsteller(DB::getSession()->getUser());
	            $newAusweis->setStatus('BEANTRAGT');
	            
	            $adressen = $s->getAdressen();
	            	            
	            for($a = 0; $a < sizeof($adressen); $a++) {
	                if($adressen[$a]->isSchueler()) {
	                    $newAusweis->setPLZ($adressen[$a]->getPLZ());
	                    $newAusweis->setOrt($adressen[$a]->getOrt());
	                }
	            }
	            
	            $newAusweis->setBild($foto['uploadobject']);
	            $newAusweis->setPreis(DB::getSettings()->getValue('ausweis-schuelerausweis-kosten'));
	            $newAusweis->setAblauf($newAusweis->getGueltigkeitForNewAusweis('SCHUELER'));
	            $newAusweis->setGeburtsdatum($s->getGeburtstagAsSQLDate());
	            
	            header("Location: index.php?page=Ausweis&action=myAusweise&type=SCHUELER&mode=editFoto&ausweisID=" . $newAusweis->getID());
	            exit(0);
	            	            	            
	        }
	        else {
	            new errorPage("Das Foto ist nicht gültig!");
	            exit();
	        }
	        
	    }
	    
	    if($_REQUEST['mode'] == 'deleteAntrag') {
	        for($a = 0; $a < sizeof($myAusweise); $a++) {
	            if($myAusweise[$a]->getID() == $_REQUEST['ausweisID']) {
	                $myAusweise[$a]->delete();
	                header("Location: index.php?page=Ausweis&action=myAusweise&type=SCHUELER");
	                exit(0);
	            }
	        }
	    }
	    
	    if($_REQUEST['mode'] == 'editFoto') {
	        for($a = 0; $a < sizeof($myAusweise); $a++) {
	            if($myAusweise[$a]->getID() == $_REQUEST['ausweisID']) {
	                if(!$myAusweise[$a]->isGenehmigt()) {
	                    // Solange nicht genehmigt
	                    
	                    $foto = $myAusweise[$a]->getBild();
	                    
	                    $fotoURL = $foto->getURLToFile();
	                    
	                    if($_REQUEST['modeAction'] == 'saveEditedFoto') {
	                        
	                        $foto->reuploadJPEGImageFromBase64($_REQUEST['fotoBase64']);
	                        
	                        header("Location: index.php?page=Ausweis&action=myAusweise&type=SCHUELER");
	                        exit(0);
	                    }
	                    if($_REQUEST['modeAction'] == 'reupload') {
	                        $foto = FileUpload::uploadPicture("passfoto", "");

	                        if($foto['result']) {
	                            $myAusweise[$a]->setBild($foto['uploadobject']);
	                            
	                            header("Location: index.php?page=Ausweis&action=myAusweise&type=SCHUELER&mode=editFoto&ausweisID=" . $myAusweise[$a]->getID());
	                            exit();
	                        }

	                    }
	                    
	                    $type ='SCHUELER';
	                    
	                    eval("DB::getTPL()->out(\"" . DB::getTPL()->get("ausweise/editfoto/index") . "\");");
	                    
	                    PAGE::kill(true);
											//exit(0);
	                    
	                    
	                }
	            }
	        }
	    }
	    
	    if(sizeof($myAusweise) == 0) {
	        $ausweiseHTML = "<tr><td colspan=\"4\">Bisher keine Ausweise beantragt</td></tr>";
	    }
	    else {
	        $ausweiseHTML = "";
	        
	        
	        for($a = 0; $a < sizeof($myAusweise); $a++) {
	            $ausweiseHTML .= "<tr>";
	            
	            $ausweiseHTML .= "<td><b>" . $myAusweise[$a]->getName();
	            
	            $ausweiseHTML .= "</b><br />" . $myAusweise[$a]->getPLZ() . " " . $myAusweise[$a]->getOrt() . "</td>";
	            
	            if($myAusweise[$a]->getBild() != null) {
	                $ausweiseHTML .= "<td><img src=\"" . $myAusweise[$a]->getBild()->getURLToFile() . "\" height=\"100\">";
	                
	                
	                if(!$myAusweise[$a]->isGenehmigt() && !$myAusweise[$a]->isNotGenehmigt()) $ausweiseHTML .= "<br ><button class=\"btn btn-xs\" type=\"button\" onclick=\"window.location.href='index.php?page=Ausweis&action=myAusweise&type=SCHUELER&mode=editFoto&ausweisID=" . $myAusweise[$a]->getID() . "'\"><i class=\"fa fa-photo\"></i> Bild bearbeiten</button>";
	                
	                
	                $ausweiseHTML .= "</td>";
	            }
	            else $ausweiseHTML .= "<td>Bild ungültig</td>";
	            
	            $ausweiseHTML .= "<td>Voraussichtlich " . DateFunctions::getNaturalDateFromMySQLDate($myAusweise[$a]->getAblaufdatum()) . "</td>";
	            
	            $ausweiseHTML .= "<td>";
	            
	            if($myAusweise[$a]->isAbgeholt()) {
	                $ausweiseHTML .= "<i class=\"fa fa-check\"></i> Abgeholt";
	            }
	            elseif($myAusweise[$a]->isErstellt()) {
	                $ausweiseHTML .= "<i class=\"fa fa-check\"></i> Erstellt. Abholbereit.";
	            }
	            elseif($myAusweise[$a]->isGenehmigt()) {
	                $ausweiseHTML .= "<i class=\"fa fa-check\"></i> Genehmigt. Noch nicht abholbereit.";
	            }
	            elseif($myAusweise[$a]->isNotGenehmigt()) {
	                $ausweiseHTML .= "<i class=\"fa fa-ban\"></i> <b>Nicht</b> Genehmigt.";
	            }
	            elseif($myAusweise[$a]->isBeantragt()) {
	                $ausweiseHTML .= "<i class=\"fa fa-question\"></i> Noch nicht genehmigt.";
	                
	                $ausweiseHTML .= "<br ><button class=\"btn btn-danger btn-xs\" type=\"button\" onclick=\"confirmAction('Soll der Antrag wirklich gelöscht werden?','index.php?page=Ausweis&action=myAusweise&type=SCHUELER&mode=deleteAntrag&ausweisID=" . $myAusweise[$a]->getID() . "')\"><i class=\"fa fa-trash\"></i> Antrag zurückziehen</button>";
	                
	            }
	            
	            if($myAusweise[$a]->getKommentar() != "") {
	                $ausweiseHTML .= "<br />Kommentar: " . $myAusweise[$a]->getKommentar();
	            }
	            
	            $ausweiseHTML .= "</td>";
	            
	            
	            
	            $ausweiseHTML .= "</tr>";
	        }
	        
	    }
	    
	    
	    
	    $schuelerSelectHTML = "";
	    
	    for($i = 0; $i < sizeof($schueler); $i++) {
	        $schuelerSelectHTML .= "<option value=\"" . $schueler[$i]->getAsvID() . "\">" . $schueler[$i]->getCompleteSchuelerName() . "</option>\r\n";
	    }
	    
	    $preis = number_format(DB::getSettings()->getValue('ausweis-schuelerausweis-kosten') / 100, 2, ",", ".");
	    
	    // <li>Kosten für den Ausweis: &euro; {$preis}</li>
	    
	    
	    eval("DB::getTPL()->out(\"" . DB::getTPL()->get("ausweise/schueler/index") . "\");");
	    
	    
	    
	}
	
	
	public static function hasSettings() {
		return true;
	}
	
	
	
	public static function getSiteDisplayName() {
		return 'Ausweise (deprecated!)';
	}
	
	public static function siteIsAlwaysActive() {
		return false;
	}
	
	public static function getSettingsDescription() {
		return [
			[
				'name' => "ausweis-schuelerausweis-schueler",
				'typ' => 'BOOLEAN',
				'titel' => "Beantragung von Schülerausweisen durch Schüler?",
				'text' => ""
			],
		    [
		        'name' => "ausweis-schuelerausweis-lehrer",
		        'typ' => 'BOOLEAN',
		        'titel' => "Beantragung von Schülerausweisen durch Lehrer?",
		        'text' => ""
		    ],
		    [
		        'name' => "ausweis-schuelerausweis-eltern",
		        'typ' => 'BOOLEAN',
		        'titel' => "Beantragung von Schülerausweisen durch Eltern?",
		        'text' => ""
		    ],
		    [
		        'name' => "ausweis-lehrerausweis-lehrer",
		        'typ' => 'BOOLEAN',
		        'titel' => "Beantragung von Lehrerausweisen durch Lehrer?",
		        'text' => ""
		    ],
		    [
		        'name' => "ausweis-gastausweis-schueler",
		        'typ' => 'BOOLEAN',
		        'titel' => "Beantragung von Gastausweisen durch Schüler?",
		        'text' => ""
		    ],
		    [
		        'name' => "ausweis-gastausweis-lehrer",
		        'typ' => 'BOOLEAN',
		        'titel' => "Beantragung von Gastausweisen durch Lehrer?",
		        'text' => ""
		    ],
		    [
		        'name' => "ausweis-gastausweis-eltern",
		        'typ' => 'BOOLEAN',
		        'titel' => "Beantragung von Gastausweisen durch Eltern?",
		        'text' => ""
		    ],
		    [
		        'name' => "ausweis-schuelerausweis-kosten",
		        'typ' => 'NUMMER',
		        'titel' => "Kosten eines Schülerausweises in CENT.",
		        'text' => ""
		    ],
		    [
		        'name' => "ausweis-lehrerausweis-kosten",
		        'typ' => 'NUMMER',
		        'titel' => "Kosten eines Lehrerausweises in CENT.",
		        'text' => ""
		    ],
		    [
		        'name' => "ausweis-mitarbeiterausweis-kosten",
		        'typ' => 'NUMMER',
		        'titel' => "Kosten eines Mitarbeiterausweis in CENT.",
		        'text' => ""
		    ],
		    [
		        'name' => "ausweis-gastausweis-kosten",
		        'typ' => 'NUMMER',
		        'titel' => "Kosten eines Gastausweises in CENT.",
		        'text' => ""
		    ]
		];
	}
	
	public static function getUserGroups() {
		return array();
	}
	
	
	public static function hasAdmin() {
		return true;
	}
	
	public static function getAdminGroup() {
		return 'Webportal_Reservierung_Admin';
	}
	
	public static function displayAdministration($selfURL) {
	    $html = "";
	    
	    $usergroup = usergroup::getGroupByName('Webportal_Ausweis_Schueler_Antrag');
	    
	    if($_REQUEST['action'] == 'addUserAntragS') {
	        $usergroup->addUser($_POST['userID']);
	        header("Location: $selfURL&userAdded=1");
	        exit(0);
	        
	    }
	    
	    if($_REQUEST['action'] == 'deleteUserAntragS') {
	        $usergroup->removeUser($_REQUEST['userID']);
	        header("Location: $selfURL&userDeleted=1");
	        exit(0);
	    }
	    
	    // Aktuelle Benutzer suchen, die Zugriff haben
	    

	    $boxAntragSchueler = administrationmodule::getUserListWithAddFunction(
	        $selfURL,
	        "saa",
	        "addUserAntragS",
	        "deleteUserAntragS",
	        "Benutzer, die Schülerausweise beantragen können",
	        "Antrag durch Eltern und Schüler können in den Einstellungen festgelegt werden::",
	        'Webportal_Ausweis_Schueler_Antrag'
	        );
	    
	    
	    
	    $usergroup = usergroup::getGroupByName('Webportal_Ausweis_Lehrer_Antrag');
	    
	    if($_REQUEST['action'] == 'addUserAntragL') {
	        $usergroup->addUser($_POST['userID']);
	        header("Location: $selfURL&userAdded=1");
	        exit(0);
	        
	    }
	    
	    if($_REQUEST['action'] == 'deleteUserAntragL') {
	        $usergroup->removeUser($_REQUEST['userID']);
	        header("Location: $selfURL&userDeleted=1");
	        exit(0);
	    }
	    
	    // Aktuelle Benutzer suchen, die Zugriff haben
	    
	    $boxAntragLehrer = administrationmodule::getUserListWithAddFunction(
	        $selfURL,
	        "laa",
	        "addUserAntragL",
	        "deleteUserAntragL",
	        "Benutzer, die Lehrerausweise beantragen können",
	        "Antrag durch Lehrer können in den Einstellungen festgelegt werden::",
	        'Webportal_Ausweis_Lehrer_Antrag'
	    );
	    
	    $usergroup = usergroup::getGroupByName('Webportal_Ausweis_Mitarbeiter_Antrag');
	    
	    if($_REQUEST['action'] == 'addUserAntragM') {
	        $usergroup->addUser($_POST['userID']);
	        header("Location: $selfURL&userAdded=1");
	        exit(0);
	        
	    }
	    
	    if($_REQUEST['action'] == 'deleteUserAntragM') {
	        $usergroup->removeUser($_REQUEST['userID']);
	        header("Location: $selfURL&userDeleted=1");
	        exit(0);
	    }
	    
	    // Aktuelle Benutzer suchen, die Zugriff haben
	    
	    $boxAntragMitarbeiter = administrationmodule::getUserListWithAddFunction(
	        $selfURL,
	        "maa",
	        "addUserAntragM",
	        "deleteUserAntragM",
	        "Benutzer, die Mitarbeiterausweise beantragen können",
	        "",
	        'Webportal_Ausweis_Mitarbeiter_Antrag'
	        );
	    

	    
	    $usergroup = usergroup::getGroupByName('Webportal_Ausweis_Genehmigen');
	    
	    if($_REQUEST['action'] == 'addUserAntragG') {
	        $usergroup->addUser($_POST['userID']);
	        header("Location: $selfURL&userAdded=1");
	        exit(0);
	        
	    }
	    
	    if($_REQUEST['action'] == 'deleteUserAntragG') {
	        $usergroup->removeUser($_REQUEST['userID']);
	        header("Location: $selfURL&userDeleted=1");
	        exit(0);
	    }
	    
	    // Aktuelle Benutzer suchen, die Zugriff haben
	    
	    $boxGenehmigen = administrationmodule::getUserListWithAddFunction(
	        $selfURL,
	        "gen",
	        "addUserAntragG",
	        "deleteUserAntragG",
	        "Benutzer, die Ausweise genehmigen können",
	        "",
	        'Webportal_Ausweis_Genehmigen'
	        );
	    
	    
	    $usergroup = usergroup::getGroupByName('Webportal_Ausweis_Drucken');
	    
	    if($_REQUEST['action'] == 'addUserAntragD') {
	        $usergroup->addUser($_POST['userID']);
	        header("Location: $selfURL&userAdded=1");
	        exit(0);
	        
	    }
	    
	    if($_REQUEST['action'] == 'deleteUserAntragD') {
	        $usergroup->removeUser($_REQUEST['userID']);
	        header("Location: $selfURL&userDeleted=1");
	        exit(0);
	    }
	    
	    // Aktuelle Benutzer suchen, die Zugriff haben
	    
	    $boxDrucken = administrationmodule::getUserListWithAddFunction(
	        $selfURL,
	        "dr",
	        "addUserAntragD",
	        "deleteUserAntragD",
	        "Benutzer, die Ausweise drucken können",
	        "",
	        'Webportal_Ausweis_Drucken'
	        );
	    
	    eval("\$html = \"" . DB::getTPL()->get("ausweise/admin/index") . "\";");
	    
	    return $html;
	}
	
	public static function getAdminMenuGroup() {
		return 'Kleinere Module';
	}
	
	public static function getAdminMenuGroupIcon() {
		return 'fa fa-file';
	}

}


?>