<?php

/**
 *
 */
class extVplanModelImport
{


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
                
            }
            
            $content .= "</table>";
            
            $content = utf8_encode($content);
            $infoText = utf8_encode($infoText);
            
            
            DB::getDB()->query("UPDATE vplan SET vplanDate='" . $planDate . "', vplanUpdateTime=UNIX_TIMESTAMP(), $saveToField='" . (addslashes($content)) . "', vplanUpdate='$datum', vplanInfo='" . addslashes($infoText) . "' WHERE vplanName='" . $name . "'");
            echo("Update $name OK\n");
            
        }
    }

    
}
