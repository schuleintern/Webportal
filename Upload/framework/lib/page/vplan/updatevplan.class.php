<?php


class updatevplan extends AbstractPage {
    public function __construct() {
        parent::__construct("Startseite");

        header("Content-Type: text/html; charset=UTF-8");
        
        if($_REQUEST['key'] != DB::getSettings()->getValue("vplan-updateKey")) die(("Kein Zugriff, da Zugriffstoken nicht korrekt! (Fehler 7)"));
    }
    
    public function execute() {

        if(!is_dir("./vplan")) @mkdir("./vplan");
        
        $vplandir = opendir("./vplan");
        
        switch($_REQUEST['mode']) {
            case "startUpload":
                // $this->emptyDir($this->getPlanAction());     Entfernt Okt. 2022 (Spitschka) Nicht nötig. Macht nur Probleme.
                break;
                
            case "uploadFile":
                $this->uploadFile($this->getPlanAction());
                break;
                
            case "finishUpload":
                $this->updateVplan($this->getPlanAction(), DB::getSettings()->getValue("vplan-censor-" . $this->getPlanAction()));
                $this->updateVplan($this->getPlanAction(), false, 'vplanContentUncensored');
                break;
                
            case 'updateTimeVplan':
                $this->updateTimePlan();
            break;
                
            default:
                die(("Unknown Action!"));

        }
    }
    
    private function updateTimePlan() {
        if($_REQUEST['planName'] == 'schueler') {
            TIMEUpdate::updateSchuelerFromFile($_FILES['vplanFile']['tmp_name']);
        }
        if($_REQUEST['planName'] == 'lehrer') {
            TIMEUpdate::updateLehrerFromFile($_FILES['vplanFile']['tmp_name']);
        }
    }
    
    private function emptyDir($name) {
        for($i = 1; $i < 20; $i++) {
            if(file_exists("./vplan/$name/seite$i.htm")) {
                unlink("./vplan/$name/seite$i.htm");
            }
        }
        
        echo("OK");
    }
    
    private function uploadFile($name) {
        $number = intval($_POST['pagenr']);
        
        if($number >= 1 && $number <= 20) {
            if(move_uploaded_file($_FILES['pageFile']['tmp_name'], "./vplan/$name/seite$number.htm")) {
                echo("OK");
                exit();
            }
            else file_put_contents("error.txt", $_FILES['pageFile']['tmp_name']);
            
        }
        
        exit(0);
    }
    
    private function getPlanAction() {
        if(isset($_REQUEST['plan'])) {
            if(in_array($_REQUEST['plan'], array("lehrerheute","lehrermorgen","schuelermorgen","schuelerheute"))) {
                return $_REQUEST['plan'];
            }
        }
        
        die("Ungültige Planangabe");
    }
    
    private function updateVplan($name, $censor = false, $saveToField='vplanContent') {
        $datum = "";
        
        $content = "";
        $contentUncensored = "";
        
        $infoText = "";
        
        $allLehrer = lehrer::getAllKuerzel();
        $alleLehrerObjekte = lehrer::getAll();
        
        if(DB::getGlobalSettings()->stundenplanSoftware == "UNTIS") {
            for($i = 1; $i < 20; $i++) {
                // Versuche 20 Seiten zu laden. Sollte reichen. ;-)
                $page = file("./vplan/$name/seite$i.htm");
                
                // Datum suchen
                for($z = 0; $z < sizeof($page); $z++) {
                    if(strpos($page[$z], "Stand: ")) {
                        // Stand gefunden
                        $line = explode("Stand: ", $page[$z]);
                        $stand = str_replace("\n","",str_replace("\r","",str_Replace("</p>","",$line[1])));
                        if($datum == "") $datum = $stand;
                        break;
                    }
                }
                
                if($i == 1) {
                    // Anzeigedatum auf der ersten Seite suchen
                    $first = true;
                    for($z = 0; $z < sizeof($page); $z++) {
                        if(strpos($page[$z], "mon_title")) {
                            if($first) $first = !$first;
                            else {
                                // Stand gefunden
                                $line = str_replace("<div class=\"mon_title\">","",str_replace("</div>","",str_replace("\n","",str_replace("\r","",str_replace(", Woche A","",str_replace("Woche B","",$page[$z]))))));
                                $line = explode(" ", $line);
                                $planDate = trim(str_Replace(",","",$line[1])) . ", " . trim(str_Replace(",","",$line[0]));
                                break;
                            }
                        }
                    }
                    
                    // Info Text suchen
                    
                    $isInfoText = false;
                    $firstZ = 0;
                    $isFirstLine = false;
                    $textA = array();
                    $fromNowOnTillEnd = false;
                    
                    for($z = 0; $z < sizeof($page); $z++) {
                        if(strpos($page[$z], "class=\"info\"")) {
                            $firstZ = $z;
                            $isInfoText = true;
                            $isFirstLine = true;
                            // $infoText .= $page[$z];
                        }
                        
                        else if($isInfoText && strpos($page[$z], "/table")) {
                            $isInfoText = false;
                            $infoText .= $page[$z];
                            // die("Ende gefunden ($z)");
                            break;
                        }
                        
                        if($isInfoText) {
                            
                            if($censor && !$fromNowOnTillEnd) {
                                if(strpos($page[$z], "colspan") > 0 && !strpos($page[$z], "\"info\"")) {
                                    $textA[] = ($page[$z]);
                                    $isFirstLine = false;
                                    $fromNowOnTillEnd = true;
                                }
                            }
                            elseif($fromNowOnTillEnd){
                                $textA[] = ($page[$z]);
                                $isFirstLine = false;
                            }
                            else {
                                $textA[] = ($page[$z]);
                                $isFirstLine = false;
                            }
                            
                            
                        }
                    }
                    
                    if($censor) {
                        $infoText = "<table class=\"info\">";
                    }
                    
                    if($textA != FALSE)	{
                        $infoText .= "<br />" . implode("",$textA) . "</table><br />";
                        $infoText = str_replace("table class=\"info\"", "table class=\"info\"",$infoText);
                    }
                    
                    else $infoText = "";
                }
                
                if($datum == $stand) {
                    $doit = false;
                    for($z = 0; $z < sizeof($page); $z++) {
                        $page[$z] = ($page[$z]);
                        // if($doit && !strpos($page[$z], "Pausenaufsicht") && !strpos($page[$z], "Bereitschaft") ) {
                        
                        
                        if($doit && !strpos($page[$z], "Bereitschaft") ) {
                            
                            if($censor) {
                                // Lehrer zensieren
                                // vor drittem und siebtem </td> bis > vorher zensieren
                                // if($this->countTDPos($page[$z]) == 10) {
                                $page[$z] = str_replace("<strike>","",str_replace("</strike>","",$page[$z]));
                                
                                for($l = 0; $l < sizeof($allLehrer); $l++) {
                                    if($allLehrer[$l] != "") {
                                        $page[$z] = str_ireplace(">" . utf8_decode($allLehrer[$l]) . "<",">---*<", $page[$z]);
                                    }
                                }
                                // }
                            }
                            
                            if(str_replace("\n","", str_replace("\r","",$page[$z])) == "</table>") {
                                break;
                            }
                            
                            if(!$censor) {
                                if(strpos($page[$z], ">Vertreter</th>") > 0 && $i > 1) {
                                }
                                else {
                                    $content .= ($page[$z]);
                                }
                            }
                            else {
                                if(strpos($page[$z], ">Vertreter</th>") > 0 && $i > 1) {
                                }
                                else {
                                    $content .= ($page[$z]);
                                }
                            }
                            
                            if(str_replace("\n","", str_replace("\r","",$page[$z])) == "</table>") {
                                $doit = false;
                                break;
                            }
                        }
                        else {
                            if(strpos($page[$z], "mon_list") && strpos($page[$z], "class")) {
                                $doit = true;
                                if($i == 1) $content .= $page[$z];
                            }
                        }
                    }
                }
                

                // TODO: add html files to exte
                // extVplanModelUpload::addFile() ???
                
            }
            
            $content .= "</table>";
            
            $content = utf8_encode($content);
            $infoText = utf8_encode($infoText);
            
            
            DB::getDB()->query("UPDATE vplan SET vplanDate='" . $planDate . "', vplanUpdateTime=UNIX_TIMESTAMP(), $saveToField='" . (addslashes($content)) . "', vplanUpdate='$datum', vplanInfo='" . addslashes($infoText) . "' WHERE vplanName='" . $name . "'");
            echo("Update $name OK\n");
            
        }

        if(DB::getGlobalSettings()->stundenplanSoftware == "WILLI") {
            $data = file("vplan/$name/seite1.htm");

            $vplanUpdate = "n/a";

            // Suche Datum
            for($i = 0; $i < sizeof($data); $i++) {
                if(substr($data[$i], 0, strlen("<h2>")) == "<h2>") {
                    $planDate = substr($data[$i], strlen("<h2>Vertretungsplan f&uuml;r "), strpos($data[$i],"</h2>")-29);
                    $vplanUpdate = substr($data[$i], strpos($data[$i],"</h2>")+15, 12);
                }
            }


            // Suche Vertretungen

            $vertretungenTabelle = "";
            $isInVPlanTable = false;
            $isInHeader = false;

            for($i = 0; $i < sizeof($data); $i++) {
                if($data[$i] == "<h4>Vertretungen:</h4> <table class=\"k\" border-width=\"3\"><tr><th width=\"75\">\r\n") {
                    $isInVPlanTable = true;
                    $vertretungenTabelle = "<table class=\"table table-striped\">";
                    $vertretungenTabelle .= "<tr><th>Klasse</th><th>Lehrkraft</th><th>Stunde</th><th>Vertreten durch</th><th>Raum</th><th>Kommentar</th></tr>\r\n";
                    $isInHeader = true;
                    continue;
                }

                if($data[$i] == "<h4>Vertretungen:</h4> <table class=\"l\" border-width=\"3\"><tr><th width=\"200\">\r\n") {
                    $isInVPlanTable = true;
                    $vertretungenTabelle = "<table class=\"table table-striped\">";
                    $vertretungenTabelle .= "<tr><th>Lehrkraft</th><th>Stunde</th><th>Klasse</th><th>Raum</th><th>Kommentar</th></tr>\r\n";
                    $isInHeader = true;
                    continue;
                }

                if($isInHeader) {
                    if($data[$i] == "</tr>\r\n") {
                        $isInHeader = false;
                        continue;
                    }
                    else continue;
                }

                if($data[$i] == "</table>\r\n") {
                    $isInVPlanTable = false;
                    $vertretungenTabelle .= "</table>";
                    continue;
                }

                if($isInVPlanTable) {

                    $line = $data[$i];

                    $line = str_replace("\n","",$line);
                    $line = str_replace("\r","",$line);



                    if($censor) {
                        for($lk = 0; $lk < sizeof($alleLehrerObjekte); $lk++) {
                            $line = str_replace("(" . $alleLehrerObjekte[$lk]->getKuerzel() . ")", "(---*)", $line);
                            $line = str_replace($alleLehrerObjekte[$lk]->getName() . " " . $alleLehrerObjekte[$lk]->getRufname(), "---*", $line);
                            $line = str_replace("&nbsp;" . $alleLehrerObjekte[$lk]->getKuerzel() . "</td>", "---*</td>", $line);
                        }
                    }

                    if(strpos($line,"</tr>") !== false) {
                        $line .= "\r\n";
                    }

                    $vertretungenTabelle .= str_replace(" class=\"k\"","",
                        str_replace(" class=\"l\"","",
                            str_replace("<tbody>","",
                                str_replace("</tbody>","",$line))
                    ));
                }
            }




            $data = implode("",$data);

            echo($data);

            DB::getDB()->query("UPDATE vplan SET vplanDate='" . $planDate . "', $saveToField='" . (addslashes($vertretungenTabelle)) . "', vplanUpdateTime=UNIX_TIMESTAMP(), vplanUpdate='$vplanUpdate', vplanInfo='" . addslashes($infoText) . "' WHERE vplanName='" . $name . "'");


        }

        if(DB::getGlobalSettings()->stundenplanSoftware == "SPM++") {
            // Datum suchen
            // Es gibt nur eine XML Datei.
            // Read as XML Datei
            
            // header("Content-type: text/plain");
            
            
            $data = file("vplan/$name/seite1.htm");
            $data = implode("",$data);
            
            $data = str_replace("urspr.Fach", "Fach", $data);
            
            $xml = simplexml_load_string($data);
            
            $planDate = str_replace(("Vertretungsplan für "),"",$xml->Plan->Ueberschrift);
            
            $vplanUpdate = $xml->Plan->Erstellungsdatum;
            
            // 
            
            
            if($name == "lehrerheute" || $name == "lehrermorgen") {
               
                // Absenzen
                
                $infoText .= "<table class=\"table table-striped\"><tr><th>Absenz (Lehrer)</th><th>Betrifft (Stunden)</th></tr>\r\n</tr>";
                
                
                foreach($xml->Plan->Absenzen->Lehrer as $v) {
                    if($v['Sichtbar'] == "Ja") {
                    
                        $infoText .= "<tr><td>" . $v['Name'] . "</td><td>" . $v['Betrifft'] . "</td></tR>";
                    }
                }
                
                foreach($xml->Plan->Absenzen->Klasse as $v) {
                    if($v['Sichtbar'] == "Ja") {
                        
                        $infoText .= "<tr><td>" . $v['Name'] . "</td><td>" . $v['Betrifft'] . "</td></tR>";
                    }
                }
                
                $infoText .= "</table>";
                
                  
                $html .= "<table class=\"table table-striped\"><tr><th>Vertretung</th><th>Stunde</th><th>Klasse</th><th>Fach</th><th>Lehrer</th><th>Raum</th><th>Sonstiges</th>\r\n</tr>";
                
                
                
                // Aufsichten
                
                for($i = 0; $i < sizeof($xml->Plan->Pausenaufsichten->Aufsicht); $i++) {
                    if($xml->Plan->Pausenaufsichten->Aufsicht[$i]['Sichtbar'] == "Ja") {
                        $html .= "<tr>";
                        $html .= "<td>" . $xml->Plan->Pausenaufsichten->Aufsicht[$i]['Aufsicht'] . " (<i>Pausenaufsicht</i>)</td>";
                        $html .= "<td>" . $xml->Plan->Pausenaufsichten->Aufsicht[$i]['Zeit'] . "</td>";
                        $html .= "<td>&nbsp;</td>";
                        
                        $html .= "<td>&nbsp;</td>";
                        $html .= "<td>&nbsp;</td>";
                        $html .= "<td>" . $xml->Plan->Pausenaufsichten->Aufsicht[$i]['Ort'] . "</td>";
                        $html .= "<td>&nbsp;</td></tr>\r\n";
                    }

                }
                
                
                foreach($xml->Plan->Vertretungen->Vertretung as $v) {
                    
                    $html .= "<tr>";
                    $html .= "<td>" . $v->Vertretung . "</td>";
                    $html .= "<td>" . $v->Std . "</td>";
                    $html .= "<td>" . $v->Klasse . "</td>";
                    
                    $html .= "<td>" . $v->Fach . "</td>";
                    $html .= "<td>" . $v->Lehrer . "</td>";
                    $html .= "<td>" . $v->Raum . "</td>";
                    $html .= "<td>" . $v->Sonstiges . "</td></tr>\r\n";
                }
                

                $html .= "</table>\r\n";
                
            }
            
            if($name == "schuelerheute" || $name == "schuelermorgen") {
                $html = "<table class=\"table table-striped\"><tr><th>Klasse</th><th>Vertretung</th><th>Stunde</th><th>Fach</th><th>Lehrer</th><th>Raum</th><th>Sonstiges</th>\r\n</tr>";
                
                
                
                foreach($xml->Plan->Vertretungen->Vertretung as $v) {
                    $klasse = $v->Klasse;
                    
                    $html .= "<tr>";
                    $html .= "<td>" . $klasse . "</td>";
                    if(DB::getSettings()->getBoolean('vplan-censor-schuelerheute-vpm-vertreter')) $html .= "<td>--- *</td>";
                    else $html .= "<td>" . $v->Vertretung . "</td>";
                    $html .= "<td>" . $v->Std . "</td>";
                    
                    $html .= "<td>" . $v->Fach . "</td>";
                    if(DB::getSettings()->getBoolean('vplan-censor-schuelerheute-vpm-statt')) $html .= "<td>--- *</td>";
                    else $html .= "<td>" . $v->Lehrer . "</td>";
                    $html .= "<td>" . $v->Raum . "</td>";
                    $html .= "<td>" . $v->Sonstiges . "</td></tr>\r\n";
                }
                
                $html .= "</table>\r\n";
                
            }
            
            
            
            DB::getDB()->query("UPDATE vplan SET vplanDate='" . $planDate . "', $saveToField='" . (addslashes($html)) . "', vplanUpdateTime=UNIX_TIMESTAMP(), vplanUpdate='$vplanUpdate', vplanInfo='" . addslashes($infoText) . "' WHERE vplanName='" . $name . "'");
            
            
        }
    }
    
    private function countTDPos($s) {
        return substr_count($s, "</td>");
    }
    
    private function getTdPos($s) {
        $pss = array();
        $i = 0;
        $pos = 0;
        while(true) {
            $p = strpos($s,"</td>",$pos);
            if($p === FALSE) break;
            else {
                $pss[] = $p+1;
                $pos = $p + 1;
            }
        }
        
        return $pss;
    }
    
    
    public static function hasSettings() {
        return true;
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
        
        $settings = array(
            array(
                'name' => "vplan-schueleractive",
                'typ' => "BOOLEAN",
                'titel' => "Schüler Vertrtungsplan aktiv?",
                'text' => "Soll der Schüler Vertretungsplan aktiv sein? Wenn der Plan deaktiviert wird, dann ist er für Lehrer, Eltern und Schüler nicht mehr sichtbar!"
            ),

            array(
                'name' => "vplan-censor-lehrerheute",
                'typ' => "BOOLEAN",
                'titel' => "Lehrer Heute zensieren? (Lehrer Kürzel ausblenden)",
                'text' => ""
            )
            ,
            array(
                'name' => "vplan-censor-lehrermorgen",
                'typ' => "BOOLEAN",
                'titel' => "Lehrer Morgen zensieren? (Lehrer Kürzel ausblenden)",
                'text' => ""
            ),
        	array(
        		'name' => "vplan-only-own",
        		'typ' => "BOOLEAN",
        		'titel' => "Für Schüler nur eigene Vertretungen anzeigen?",
        		'text' => ""
        	),
        	array(
        		'name' => "vplan-dont-show-warning-school-plan",
        		'typ' => "BOOLEAN",
        		'titel' => "Warnung, dass nur der Plan in der Schule gültig ist NICHT anzeigen?",
        		'text' => ""
        	)
            
        );
        
        if(DB::getGlobalSettings()->stundenplanSoftware == "SPM++") {
            $settings[] =
            array(
                'name' => "vplan-censor-schuelerheute-vpm-vertreter",
                'typ' => "BOOLEAN",
                'titel' => "Schüler Heute zensieren (Vertreter)? (Lehrer Kürzel ausblenden)",
                'text' => ""
            );
            $settings[] =
            array(
                'name' => "vplan-censor-schuelerheute-vpm-statt",
                'typ' => "BOOLEAN",
                'titel' => "Schüler Heute zensieren (Statt Lehrer)? (Lehrer Kürzel ausblenden)",
                'text' => ""
            );
        }
        else {
            $settings[] = 
            array(
                'name' => "vplan-censor-schuelerheute",
                'typ' => "BOOLEAN",
                'titel' => "Schüler Heute zensieren? (Lehrer Kürzel ausblenden)",
                'text' => ""
            );
            
            
            $settings[] = 
            array(
                'name' => "vplan-censor-schuelermorgen",
                'typ' => "BOOLEAN",
                'titel' => "Schüler Morgen zensieren? (Lehrer Kürzel ausblenden)",
                'text' => ""
            );
        }
        
        return $settings;
    }
    
    
    public static function getSiteDisplayName() {
        return 'Vertretungsplan';
    }
    
    /**
     * Liest alle Nutzergruppen aus, die diese Seite verwendet. (Für die Benutzeradministration)
     * @return array(array('groupName' => '', 'beschreibung' => ''))
     */
    public static function getUserGroups() {
        return array();
        
    }
    
    public static function siteIsAlwaysActive() {
        return true;
    }
    
    public static function hasAdmin() {
        return true;
    }
    
    public static function getAdminMenuIcon() {
        return 'fa fas fa-sync-alt';
    }
    
    public static function getAdminMenuGroup() {
        return 'Vertretungsplan';
    }
    
    
    public static function getAdminMenuGroupIcon() {
        return 'fa fa-retweet';
    }
    
    public static function displayAdministration($selfURL) {
        
        // Webportal_VPLanSehen
        $userbox = administrationmodule::getUserListWithAddFunction($selfURL, 'vplansehen', 'addUser', 'deleteUser', 'Benutzer mit uneingeschränktem VPlan Zugriff', 'Hier eingetragene Benutzer haben vollen Zugriff auf den Stundenplan. (z.B. Sekretariatskräfte)', 'Webportal_VPLanSehen');
        
        $usergroup = usergroup::getGroupByName('Webportal_VPLanSehen');
        
        if($_REQUEST['action'] == 'addUser') {
            $usergroup->addUser($_REQUEST['userID']);
            header("Location: $selfURL");
            exit(0);
        }
        
        if($_REQUEST['action'] == 'deleteUser') {
            $usergroup->removeUser($_REQUEST['userID']);
            header("Location: $selfURL");
            exit(0);
        }
        
    	if(DB::getGlobalSettings()->stundenplanSoftware == 'TIME2007') {
    		if($_GET['action'] == 'updateVPlan') {
    			if(file_exists($_FILES['vplanFileLehrer']['tmp_name'])) {
    				TIMEUpdate::updateLehrerFromFile($_FILES['vplanFileLehrer']['tmp_name']);
    			};
    			if(file_exists($_FILES['vplanFileSchueler']['tmp_name'])) {
    				TIMEUpdate::updateSchuelerFromFile($_FILES['vplanFileSchueler']['tmp_name']);
    			};
    		}
    	}
    	
    	
        if(DB::getGlobalSettings()->stundenplanSoftware == "UNTIS") {
            if($_GET['action'] == 'updateVPlan') {
                $content = file($_FILES['vplanFile']['tmp_name']);
                $data = [];
                
                for($i = 0; $i < sizeof($content); $i++) {
                    $data[] = explode(",",str_replace("\"", "", str_replace("\r","",str_replace("\n","",utf8_encode($content[$i])))));
                }
                
                $today = DateFunctions::getTodayAsSQLDate();
                while(DateFunctions::isSQLDateWeekEnd($today) || Ferien::isFerien($today)) {
                    $today = DateFunctions::addOneDayToMySqlDate($today);
                }
                
                $morgen = DateFunctions::addOneDayToMySqlDate($today);
                while(DateFunctions::isSQLDateWeekEnd($morgen) || Ferien::isFerien($morgen)) {
                    $morgen= DateFunctions::addOneDayToMySqlDate($morgen);
                }
                
                $dataHeute = [];
                $dataMorgen = [];
                
                $dateHeute = str_replace("-","",$today);
                $dateMorgen = str_replace("-","",$morgen);

                for($i = 0; $i < sizeof($data); $i++) {
                    if($data[$i][1] == $dateHeute) $dataHeute[] = $data[$i];
                    if($data[$i][1] == $dateMorgen) $dataMorgen[] = $data[$i];
                }
                
                $htmlSchuelerHeute = '<table class="mon_list" >
<tr class="list"><th class="list" align="center">Klasse(n)</th><th class="list" align="center">Stunde</th><th class="list" align="center">Vertreter</th><th class="list" align="center">Fach</th><th class="list" align="center">Raum</th><th class="list" align="center">(Fach)</th><th class="list" align="center">(Lehrer)</th><th class="list" align="">Info</th></tr>
';
                $htmlLehrerHeute = '<table class="mon_list">
<tr class="list"><th class="list" align="center">Vertreter</th><th class="list" align="center">Stunde</th><th class="list" align="center">Klasse(n)</th><th class="list" align="center">Fach</th><th class="list" align="center">Raum</th><th class="list" align="center">(Fach)</th><th class="list" align="center">(Lehrer)</th><th class="list" align="">Info</th></tr>
';
                
                $htmlSchuelerHeuteUnCensored = '<table class="mon_list" >
<tr class="list"><th class="list" align="center">Klasse(n)</th><th class="list" align="center">Stunde</th><th class="list" align="center">Vertreter</th><th class="list" align="center">Fach</th><th class="list" align="center">Raum</th><th class="list" align="center">(Fach)</th><th class="list" align="center">(Lehrer)</th><th class="list" align="">Info</th></tr>
';
                $htmlLehrerHeuteUnCensored = '<table class="mon_list">
<tr class="list"><th class="list" align="center">Vertreter</th><th class="list" align="center">Stunde</th><th class="list" align="center">Klasse(n)</th><th class="list" align="center">Fach</th><th class="list" align="center">Raum</th><th class="list" align="center">(Fach)</th><th class="list" align="center">(Lehrer)</th><th class="list" align="">Info</th></tr>
';
                
                $htmlSchuelerMorgen = '<table class="mon_list" >
<tr class="list"><th class="list" align="center">Klasse(n)</th><th class="list" align="center">Stunde</th><th class="list" align="center">Vertreter</th><th class="list" align="center">Fach</th><th class="list" align="center">Raum</th><th class="list" align="center">(Fach)</th><th class="list" align="center">(Lehrer)</th><th class="list" align="">Info</th></tr>
';
                $htmlLehrerMorgen = '<table class="mon_list">
<tr class="list"><th class="list" align="center">Vertreter</th><th class="list" align="center">Stunde</th><th class="list" align="center">Klasse(n)</th><th class="list" align="center">Fach</th><th class="list" align="center">Raum</th><th class="list" align="center">(Fach)</th><th class="list" align="center">(Lehrer)</th><th class="list" align="">Info</th></tr>
';
                
                $htmlSchuelerMorgenUnCensored = '<table class="mon_list" >
<tr class="list"><th class="list" align="center">Klasse(n)</th><th class="list" align="center">Stunde</th><th class="list" align="center">Vertreter</th><th class="list" align="center">Fach</th><th class="list" align="center">Raum</th><th class="list" align="center">(Fach)</th><th class="list" align="center">(Lehrer)</th><th class="list" align="">Info</th></tr>
';
                $htmlLehrerMorgenUnCensored = '<table class="mon_list">
<tr class="list"><th class="list" align="center">Vertreter</th><th class="list" align="center">Stunde</th><th class="list" align="center">Klasse(n)</th><th class="list" align="center">Fach</th><th class="list" align="center">Raum</th><th class="list" align="center">(Fach)</th><th class="list" align="center">(Lehrer)</th><th class="list" align="">Info</th></tr>
';
                // Sort Schueler
                usort($dataHeute, function($a, $b) {
                    return [$a[14], $a[2]] <=> [$b[14], $b[2]];
                });
                usort($dataMorgen, function($a, $b) {
                    return [$a[14], $a[2]] <=> [$b[14], $b[2]];
                });

                for($i = 0; $i < sizeof($dataHeute); $i++) {
                    $htmlSchuelerHeute .= self::getPupilLineForUntisData($dataHeute[$i], DB::getSettings()->getBoolean("vplan-censor-schuelerheute"));
                    $htmlSchuelerHeuteUnCensored .= self::getPupilLineForUntisData($dataHeute[$i], false);
                }

                $htmlSchuelerHeute .= "</table>";
                $htmlSchuelerHeuteUnCensored .= "</table>";
                
                for($i = 0; $i < sizeof($dataMorgen); $i++) {
                    $htmlSchuelerMorgen .= self::getPupilLineForUntisData($dataMorgen[$i], DB::getSettings()->getBoolean("vplan-censor-schuelermorgen"));
                    $htmlSchuelerMorgenUnCensored .= self::getPupilLineForUntisData($dataMorgen[$i], false);
                }

                $htmlSchuelerMorgen .= "</table>";
                $htmlSchuelerMorgenUnCensored .= "</table>";

                // Sort Lehrer

                usort($dataHeute, function($a, $b) {
                    return [$a[6], $a[2]] <=> [$b[6], $b[2]];
                });
                usort($dataMorgen, function($a, $b) {
                    return [$a[6], $a[2], $a[14]] <=> [$b[6], $b[2], $b[14]];
                });

                for($i = 0; $i < sizeof($dataHeute); $i++) {
                    $htmlLehrerHeute .= self::getTeacherLineForUntisData($dataHeute[$i], DB::getSettings()->getBoolean("vplan-censor-lehrerheute"));
                    $htmlLehrerHeuteUnCensored .= self::getTeacherLineForUntisData($dataHeute[$i], false);
                }
                
                $htmlLehrerHeute.= "</table>";
                $htmlLehrerHeuteUnCensored .= "</table>";
                
                
                for($i = 0; $i < sizeof($dataMorgen); $i++) {
                    $htmlLehrerMorgen .= self::getTeacherLineForUntisData($dataMorgen[$i], DB::getSettings()->getBoolean("vplan-censor-lehrermorgen"));
                    $htmlLehrerMorgenUnCensored .= self::getTeacherLineForUntisData($dataMorgen[$i], false);
                }
                                
                $htmlLehrerMorgen.= "</table>";
                $htmlLehrerMorgenUnCensored .= "</table>";
                

                DB::getDB()->query("UPDATE vplan SET
                vplanContent='" . DB::getDB()->escapeString($htmlSchuelerHeute) . "',
                vplanContentUncensored = '" . DB::getDB()->escapeString($htmlSchuelerHeuteUnCensored) . "',
                vplanUpdate='" . date("d.m.Y H:i") . "',vplanInfo='',
                vplanDate='" . functions::getDayName(DateFunctions::getWeekDayFromSQLDate($today)-1) . ", " . DateFunctions::getNaturalDateFromMySQLDate($today) . "'
                     WHERE vplanName='schuelerheute'
                    ");
                
                DB::getDB()->query("UPDATE vplan SET
                vplanContent='" . DB::getDB()->escapeString($htmlLehrerHeute) . "',
                vplanContentUncensored = '" . DB::getDB()->escapeString($htmlLehrerHeuteUnCensored) . "',
                vplanUpdate='" . date("d.m.Y H:i") . "',vplanInfo='',
                vplanDate='" . functions::getDayName(DateFunctions::getWeekDayFromSQLDate($today)-1) . ", " . DateFunctions::getNaturalDateFromMySQLDate($today) . "'
                     WHERE vplanName='lehrerheute'
                    ");
                
                
                DB::getDB()->query("UPDATE vplan SET
                vplanContent='" . DB::getDB()->escapeString($htmlSchuelerMorgen) . "',
                vplanContentUncensored = '" . DB::getDB()->escapeString($htmlSchuelerMorgenUnCensored) . "',
                vplanUpdate='" . date("d.m.Y H:i") . "',vplanInfo='',
                vplanDate='" . functions::getDayName(DateFunctions::getWeekDayFromSQLDate($morgen)-1) . ", " . DateFunctions::getNaturalDateFromMySQLDate($morgen) . "'
                     WHERE vplanName='schuelermorgen'
                    ");
                
                DB::getDB()->query("UPDATE vplan SET
                vplanContent='" . DB::getDB()->escapeString($htmlLehrerMorgen) . "',
                vplanContentUncensored = '" . DB::getDB()->escapeString($htmlLehrerMorgenUnCensored) . "',
                vplanUpdate='" . date("d.m.Y H:i") . "',vplanInfo='',
                vplanDate='" . functions::getDayName(DateFunctions::getWeekDayFromSQLDate($morgen)-1) . ", " . DateFunctions::getNaturalDateFromMySQLDate($morgen) . "'
                     WHERE vplanName='lehrermorgen'
                    ");
                
                    
                if ( EXTENSION::isActive('ext.zwiebelgasse.vplan') ) {

                    $dir = PATH_WWW_TMP . 'ext_vplan';
                    if ( !is_dir($dir) ) {
                        mkdir($dir);
                    }
                    $uploadfile = $dir . DS . date('Y-m-d_H-i', time()).'.txt';
                    move_uploaded_file($_FILES['vplanFile']["tmp_name"], $uploadfile);
                    include_once PATH_EXTENSIONS . 'vplan' . DS . 'models' . DS . 'Upload.class.php';
                    extVplanModelUpload::addFile($uploadfile, true);

                }
                
                
                header("Location: " . $selfURL . "&updated=1");
                exit(0);
            }
        }
        
        $updateKey = "";
        if($_GET['doUpdateKey'] > 0) {
            DB::getSettings()->setValue("vplan-updateKey", $_POST['updateKey']);
            header("Location: $selfURL&keySaved=1");
            exit(0);
        }
        
        
        
        $html = "";
        eval("\$html = \"" . DB::getTPL()->get("vplan/admin/index") . "\";");
        
        return $html;
    }
    
    /**
     * @param unknown $data
     * @param unknown $censor
     */
    private static function getTeacherLineForUntisData($data,$censor) {

        // Vertreter	Stunde	Klasse(n)	Fach	Raum	Art	(Fach)	(Lehrer)	Vertr. von	(Le.) nach
      
        
        $line = "<tr class=\"list even\">";
        $line .= "<td class=\"list\" align=\"center\">" . $data[6] . "</td>";
        $line .= "<td class=\"list\" align=\"center\">" . $data[2] . "</td>";
        $line .= "<td class=\"list\" align=\"center\">" . $data[14] . "</td>";
        $line .= "<td class=\"list\" align=\"center\">" . $data[9] . "</td>";
        $line .= "<td class=\"list\" align=\"center\">" . $data[12] . "</td>";
        
        //$line .= "<td class=\"list\" align=\"center\">" . self::getUntisArt($data[17]) . "</td>";
        
        $line .= "<td class=\"list\" align=\"center\"><s>" . $data[7] . "</s></td>";
        $line .= "<td class=\"list\" align=\"center\"><s>" . $data[5] . "</s></td>";
        $line .= "<td class=\"list\" align=\"\">" . $data[16] . "</td>";
        //$line .= "<td class=\"list\" align=\"center\">&nbsp;</td>";
        //$line .= "<td class=\"list\" align=\"center\">&nbsp;</td>";
        
        $line .= "</tr>\r\n";
        
        return $line;
    }
    
    /**
     *
     */
    private static function getPupilLineForUntisData($data,$censor) {
        $line = "<tr class=\"list even\">";
        // <tr class="list even"><td class="list" align="center">
        // Klasse(n)	Stunde	Vertreter	Fach	Raum	Art	(Fach)	(Lehrer)	Vertr. von	(Le.) nach
        $line .= "<td class=\"list\" align=\"center\">" . $data[14] . "</td>"; // klasse
        $line .= "<td class=\"list\" align=\"center\">" . $data[2] . "</td>";  // stunde
        $line .= "<td class=\"list\" align=\"center\">" . utf8_decode($data[6]) . "</td>"; // vertreter
        $line .= "<td class=\"list\" align=\"center\">" . utf8_decode($data[9]) . "</td>"; // fach neu
        $line .= "<td class=\"list\" align=\"center\">" . utf8_decode($data[12]) . "</td>"; // raum neu
        
        //$line .= "<td class=\"list\" align=\"center\">" . self::getUntisArt($data[17]) . "</td>";
        
        $line .= "<td class=\"list\" align=\"center\"><s>" . utf8_decode($data[7]) . "</s></td>"; // fach alt
        $line .= "<td class=\"list\" align=\"center\"><s>" . utf8_decode($data[5]) . "</s></td>"; // lehrere alt
        $line .= "<td class=\"list\" align=\"\">" . utf8_decode($data[16]) . "</td>"; // info
        //$line .= "<td class=\"list\" align=\"center\">&nbsp;</td>";
        //$line .= "<td class=\"list\" align=\"center\">&nbsp;</td>";
        
        $line .= "</tr>\r\n";
        
        return $line;
    }
    
    /**
     *  T        verlegt
     F        verlegt von
     W        Tausch
     S        Betreuung
     A        Sondereinsatz
     C        Entfall
     L        Freisetzung
     P        Teil-Vertretung
     R        Raumvertretung
     B        Pausenaufsichtsvertretung
     ~        Lehrertausch
     E        Klausur
     */
    private static function getUntisVertretungArt($art) {
        switch($art) {
            case 'T': return 'Verlegt';
            case 'F': return 'Verlegt von';
            case 'W': return 'Tausch';
            case 'S': return 'Betreuung';
            case 'A': return 'Sondereinsatz';
            case 'C': return 'Entfall';
            case 'L': return 'Freisetzung';
            case 'P': return 'Teilvertretung';
            case 'R': return 'Raumvertretung';
            case 'B': return 'Pausenaufsichtsvertretung';
            case '~': return 'Lehrertausch';
            case 'E': return 'Klausur';
        }
        
        return '';
    }
    
    /**
     *  Bit 0        Entfall
     Bit 1        Betreuung
     Bit 2        Sondereinsatz
     Bit 3        Wegverlegung
     Bit 4        Freisetzung
     Bit 5        Plus als Vertreter
     Bit 6        Teilvertretung
     Bit 7        Hinverlegung
     Bit 16        Raumvertretung
     Bit 17        Pausenaufsichtsvertretung
     Bit 18        Stunde ist unterrichtsfrei
     Bit 20        Kennzeichen nicht drucken
     Bit 21        Kennzeichen neu
     */
    private static function getUntisArt($bit) {
        switch($bit) {
            case '0': return 'Vertretung';
            case '2': return 'Betreuung';
            case '4': return 'Sondereinsatz';
            case '8': return 'Wegverlegung';
            case '16': return 'Freisetzung';
            case '32': return 'Plus als Vertreter';
            case '64': return 'Teilvertretung';
            case '128': return 'Hinverlegung';
            
        }
        
        return $bit;
    }
    
    public static function getAdminGroup() {
        return "Webportal_VPlan_Update";
    }
}

