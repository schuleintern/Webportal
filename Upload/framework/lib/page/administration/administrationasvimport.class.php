<?php


class administrationasvimport extends AbstractPage {

	private $info;

	const ADMINGROUP_ASV_SYNC = 'Webportal_ASVSync';

	private static $html;

	public function __construct() {
		parent::__construct(array("Administration", "ASV Import"), false, true);

		new errorPage();	// Adminmodul
	}

	public function execute() {
        // Nothing to declare. (green exit)
	}

	private static function get7ZIP() {
        if(PHP_OS != "WINNT") {
            if(DB::getSettings()->getValue("7zip-linux") != "") return DB::getSettings()->getValue("7zip-linux");
            else return null;
        }
        else {
            if(DB::getSettings()->getValue("7zip-windows") != "") return DB::getSettings()->getValue("7zip-windows");
            else return null;
        }
    }

	public static function displayAdministration($selfURL) {
		switch($_GET['action']) {
			default:
			    
			    $lastImport = DB::getAsvStand();
			    
				eval("\$html =  \"" . DB::getTPL()->get("administration/usersync/asv") . "\";");
				return $html;
			break;

			case 'syncasv':
			    if(DB::checkDemoAccess()) self::syncasv($selfURL);

				return self::$html;
			break;

			case 'syncasvstep2':
			    if(DB::checkDemoAccess()) self::syncasv2();

			    return self::$html;
			break;
		}

	}

	private static function syncasv($selfURL) {
	    $z7 = DB::getGlobalSettings()->zip7Linux;
	    $z7Windows = DB::getGlobalSettings()->zip7Windows;

	    $uploadfileZip = "../data/temp/asvsync/export.zip";

	    $uploadfile = "../data/temp/asvsync/export.xml";

	    ob_start();


	    @unlink($uploadfile);
	    @unlink($uploadfileZip);

	    $result = "";

	    if (self::get7ZIP() != null && move_uploaded_file($_FILES['asvexportfile']['tmp_name'], $uploadfileZip)) {
	        // try to unzip

            $z7 = self::get7ZIP();

	        if(PHP_OS != "WINNT") {
	            @exec("$z7 e ../data/temp/asvsync/export.zip -p\"" . addslashes($_POST['asvexportfilepassword']) . "\" -o\"../data/temp/asvsync/\"", $result);
	            @exec("chmod 777 ../data/temp/asvsync/export.zip");

	        }
	        else {
	            @exec("\"$z7\" e temp/asvsync/export.zip -p\"" . addslashes($_POST['asvexportfilepassword']) . "\" -otemp/asvsync/", $result);
	        }
	    }

        if (move_uploaded_file($_FILES['asvexportfilexml']['tmp_name'], $uploadfile)) {
            // goood :-)
        }



        if(file_exists($uploadfile)) {
            // Suche Klassen
            
	        $simpleXML = simplexml_load_file($uploadfile, null, LIBXML_NOCDATA);
	        
	        $klassen = self::searchGrades($simpleXML);
	        
	        $klassenHTML = "";


	        
	        for($i = 0; $i < sizeof($klassen); $i++) {
	            $klassenHTML .= '<div class="checkbox icheck">
                  <label>
                    <input type="checkbox" name="' . md5($klassen[$i]) . '" value="1" checked="checked" id="' . md5($klassen[$i]) . '" class="tableselectfield"> Klasse ' . $klassen[$i] . '
                  </label>
                </div>' . "\r\n";


	        }
	        
	        
	        eval("self::\$html = \"" . DB::getTPL()->get("administration/usersync/asvselectgrades") . "\";");
	        
	    }
	    else {
            self::$html = "Der Upload der ASV Export Datei war leider nicht erfolgreich.";
	    }
	}
	
	private static function searchGrades($simpleXML) {
	    $grades = [];
	    
	    $schulnummer = $simpleXML->schulen[0]->schule->schulnummer;
	    
	    if($schulnummer != DB::getGlobalSettings()->schulnummer) {
	        new errorPage("Die Schulnummer in der Exportdatei (" . $schulnummer . ") stimmt nicht mit der Schulnummer der Installation (" . DB::getGlobalSettings()->schulnummer . ") überein!");
	        exit(0);
	    }
	    	    
	    foreach($simpleXML->schulen[0]->schule->klassen->klasse as $klasse) {

	        $grades[] = strval($klasse->klassenname);

	    }
	    
	    return $grades;
	    
	}
	
	private static function syncasv2() {

		$uploadfileZip = "../data/temp/asvsync/export.zip";

		$uploadfile = "../data/temp/asvsync/export.xml";


		if(file_exists($uploadfile)) {

			$simpleXML = simplexml_load_file($uploadfile, null, LIBXML_NOCDATA);

			self::handleXML($simpleXML);
			
			DB::getSettings()->setValue("last-asv-import", DateFunctions::getTodayAsNaturalDate());

			@unlink($uploadfile); // Datenschutz! Löschen!
			@unlink($uploadfileZip);
		}
		else {
			new errorPage("Die hochgeladene Datei ist ungültig! (Die Datei konnte nicht entpackt werden!");
			die();
		}

	}

	private static $klassen = array();
	private static $faecher = array();
	private static $unterricht = array();
	private static $lehrer = array();

	private static function handleXML($simpleXML) {
	    
		// header("Content-type: text/plain");

		$doneActions = "";

		$schulnummer = $simpleXML->schulen[0]->schule->schulnummer;

		if($schulnummer != DB::getGlobalSettings()->schulnummer) {
			new errorPage("Die Schulnummer in der Exportdatei (" . $schulnummer . ") stimmt nicht mit der Schulnummer der Installation (" . DB::getGlobalSettings()->schulnummer . ") überein!");
			exit(0);
		}

		// Fï¿½cher laden
		foreach($simpleXML->schulen[0]->schule->faecher->fach as $fach) {
			self::$faecher[] = array(
					"id" => strval($fach->xml_id),
			        'asdid' => strval($fach->asd_fach),
					"kurzform" => strval($fach->kurzform),
					"langform" => strval($fach->langform),
			        'istselbsterstellt' => strval($fach->ist_selbst_erstellt)
			);
		}


		// Ordnungszahlen retten
		$ordnungszahlen = [];
		
		$faecher = DB::getDB()->query("SELECT * FROM faecher");
		while($f = DB::getDB()->escapeString($faecher)) $ordnungszahlen[] = [
		    'asdID' => $f['fachASDID'],
		    'ordnung' => $f['fachOrdnung']
		];
		
		$doneActions .= "Fächer eingelesen\r\n";

		// Fï¿½cher in DB Schreiben
		DB::getDB()->query("DELETE FROM faecher");

		for($i = 0; $i < sizeof(self::$faecher); $i++) {
			DB::getDB()->query("INSERT INTo faecher (fachID, fachKurzform, fachLangform, fachASDID, fachIstSelbstErstellt)
					values (
						'" . self::$faecher[$i]['id'] . "',
						'" . DB::getDB()->escapeString(self::$faecher[$i]['kurzform']) . "',
						'" . DB::getDB()->escapeString(self::$faecher[$i]['langform']) . "',
                        '" . DB::getDB()->escapeString(self::$faecher[$i]['asdid']) . "',
                        '" . DB::getDB()->escapeString(self::$faecher[$i]['istselbsterstellt']) . "'
					)");
		}

		$doneActions .= "Fächer gespeichert\r\n";
		
		for($i = 0; $i < sizeof($ordnungszahlen); $i++) {
		    DB::getDB()->query("UPDATE faecher SET fachOrdnung='" . $ordnungszahlen[$i]['ordnung'] . "' WHERE fachASDID='" . $ordnungszahlen[$i]['asdID'] . "'");
		}
		$ausbSXML = simplexml_load_file('../framework/asvwerte/Unterrichtsart_(2129).xml', null, LIBXML_NOCDATA);


		$unterrichtsartKurz = [];
		$unterrichtsartLang = [];
		foreach($ausbSXML->eintrag as $bg) {
			// print_r($bg);
			if(strval($bg->schulart_kurzform) == strtoupper(DB::getSettings()->getValue("schulinfo-schultyp"))) $unterrichtsart[strval($bg->schluessel)] = strval($bg->kurzform);
			if(strval($bg->schulart_kurzform) == strtoupper(DB::getSettings()->getValue("schulinfo-schultyp"))) $unterrichtsartLang[strval($bg->schluessel)] = strval($bg->langform);
		}


		// Unterricht laden
		foreach($simpleXML->schulen[0]->schule->unterrichtselemente->unterrichtselement as $unterricht) {

			$koppelText = '';
			$isPseudoKoppel = 0;

			if(is_object($unterricht->koppel)) {
				$koppelText = strval($unterricht->koppel->kurzform);
				if(strval($unterricht->koppel->is_pseudokoppel) == 'true') $isPseudoKoppel = 1;
			}



			self::$unterricht[] = array(
					"id" => strval($unterricht->xml_id),
					"lehrer" => strval($unterricht->lehrkraft_id),
					"fachid" => strval($unterricht->fach_id),
					"bezeichnung" => (strval($unterricht->bezeichnung)),
					"unterrichtsart" => $unterrichtsartLang[strval($unterricht->unterrichtsart)],
					"stunden" => strval($unterricht->stunden),
					"wissenschaftlich" => strval($unterricht->ist_wissenschaftlich) == 'true',
					"startdatum" => DateFunctions::getMySQLDateFromNaturalDate(strval($unterricht->von)),
					"enddatum" => DateFunctions::getMySQLDateFromNaturalDate(strval($unterricht->bis)),
					'klassenunterricht' => strval($unterricht->in_matrix) == 'true',
					'koppeltext' => $koppelText,
					'pseudokoppel' => $isPseudoKoppel
			);
		}

		$doneActions .= "Unterricht eingelesen\r\n";

		// Unterricht synchronisieren
		$currentUnterricht = DB::getDB()->query("SELECT * FROM unterricht");

		while($u = DB::getDB()->fetch_array($currentUnterricht)) {
			$found = false;
			for($i = 0; $i < sizeof(self::$unterricht); $i++) {
				if(self::$unterricht[$i]['id'] == $u['unterrichtID']) {
					// self::$unterricht[$i] = array();
					$found = true;
					break;
				}
			}

			if(!$found) {
				DB::getDB()->query("DELETE FROM unterricht WHERE unterrichtID='" . $u['unterrichtID'] . "'");
			}
		}

		for($i = 0; $i < sizeof(self::$unterricht); $i++) {
			if(sizeof(self::$unterricht[$i]) > 0) {
				DB::getDB()->query("INSERT INTO unterricht
					(
						unterrichtID,
						unterrichtLehrerID,
						unterrichtFachID,
						unterrichtBezeichnung,
						unterrichtArt,
						unterrichtStunden,
						unterrichtIsWissenschaftlich,
						unterrichtStart,
						unterrichtEnde,
						unterrichtIsKlassenunterricht,
						unterrichtKoppelText,
						unterrichtKoppelIsPseudo
					)
						values
					(
						'" . self::$unterricht[$i]['id'] . "',
						'" . self::$unterricht[$i]['lehrer'] . "',
						'" . self::$unterricht[$i]['fachid'] . "',
						'" . self::$unterricht[$i]['bezeichnung'] . "',
						'" . self::$unterricht[$i]['unterrichtsart'] . "',
						'" . self::$unterricht[$i]['stunden'] . "',
						'" . self::$unterricht[$i]['wissenschaftlich'] . "',
						'" . self::$unterricht[$i]['startdatum'] . "',
						'" . self::$unterricht[$i]['enddatum'] . "',
						'" . self::$unterricht[$i]['klassenunterricht'] . "',
						" . ((self::$unterricht[$i]['koppeltext'] != '') ? "'" . self::$unterricht[$i]['koppeltext']  . "'" : 'null') . ",
						'" . self::$unterricht[$i]['pseudokoppel'] . "'
					) ON DUPLICATE KEY UPDATE
						unterrichtID='" . self::$unterricht[$i]['id'] . "',
						unterrichtLehrerID='" . self::$unterricht[$i]['lehrer'] . "',
						unterrichtFachID='" . self::$unterricht[$i]['fachid'] . "',
						unterrichtBezeichnung='" . self::$unterricht[$i]['bezeichnung'] . "',
						unterrichtArt='" . self::$unterricht[$i]['unterrichtsart'] . "',
						unterrichtStunden='" . self::$unterricht[$i]['stunden'] . "',
						unterrichtIsWissenschaftlich='" . self::$unterricht[$i]['wissenschaftlich'] . "',
						unterrichtStart='" . self::$unterricht[$i]['startdatum'] . "',
						unterrichtEnde='" . self::$unterricht[$i]['enddatum'] . "',
						unterrichtIsKlassenunterricht='" . self::$unterricht[$i]['klassenunterricht'] . "',
						unterrichtKoppelText = " . ((self::$unterricht[$i]['koppeltext'] != '') ? "'" . self::$unterricht[$i]['koppeltext']  . "'" : 'null') . ",
						unterrichtKoppelIsPseudo='" . self::$unterricht[$i]['pseudokoppel'] . "'
				");
			}
		}
		// Unterricht Ende

		$doneActions .= "Unterricht synchronisiert\r\n";



		// Schulen einlesen
		$ausbSXML = simplexml_load_file('../framework/asvwerte/Schulart_rechtlich_(2104).xml', null, LIBXML_NOCDATA);


		$schularten = [];
		foreach($ausbSXML->eintrag as $bg) {
			// print_r($bg);
			$schularten[strval($bg->schluessel)*1] = strval($bg->kurzform);
		}


		DB::getDB()->query("DELETE FROM schulen");

		foreach($simpleXML->schulverzeichnis_liste->schulverzeichniseintrag as $schule) {
			DB::getDB()->query("INSERT INTO schulen (schuleID, schuleNummer, schuleArt, schuleName) values(

				'" . DB::getDB()->escapeString(strval($schule->xml_id)*1) . "',
				'" . DB::getDB()->escapeString(strval($schule->schulnummer)) . "',
				'" . DB::getDB()->escapeString($schularten[strval($schule->schulart)*1]) . "',
				'" . DB::getDB()->escapeString(strval($schule->dienststellenname)) . "'
			)");
		}

		// /Schulen einlesen



		// Besuchten Unterricht lï¿½schen
		DB::getDB()->query("DELETE FROM unterricht_besuch");

		// Klassen laden


		$ausbSXML = simplexml_load_file('../framework/asvwerte/Bildungsgang_(1010).xml', null, LIBXML_NOCDATA);


		$ausbs = [];
		foreach($ausbSXML->eintrag as $bg) {
			// print_r($bg);
			$ausbs[strval($bg->schluessel)] = strval($bg->kurzform);
		}



		$relSXML = simplexml_load_file('../framework/asvwerte/Religionszugehoerigkeit.xml', null, LIBXML_NOCDATA);


		$religion = [];
		foreach($relSXML->eintrag as $bg) {
			$religion[strval($bg->schluessel)] = strval($bg->kurzform);
		}

		$relSXML = simplexml_load_file('../framework/asvwerte/Staat_(2118).xml', null, LIBXML_NOCDATA);


		$staaten = [];
		foreach($relSXML->eintrag as $bg) {
			$staaten[strval($bg->schluessel)] = strval($bg->langform);
		}

		$relSXML = simplexml_load_file('../framework/asvwerte/Jahrgangsstufe_(1015).xml', null, LIBXML_NOCDATA);


		$jahrgangsstufen = [];
		foreach($relSXML->eintrag as $bg) {
			if(strval($bg->schulart_kurzform) == strtoupper(DB::getSettings()->getValue("schulinfo-schultyp"))) $jahrgangsstufen[strval($bg->schluessel)*1] = strval($bg->kurzform);
		}

		$relSXML = simplexml_load_file('../framework/asvwerte/Unterrichtsfach_(1041).xml', null, LIBXML_NOCDATA);


		$unterrichtsfaecher = [];
		foreach($relSXML->eintrag as $bg) {
			if(strval($bg->schulart_kurzform) == strtoupper(DB::getSettings()->getValue("schulinfo-schultyp"))) $unterrichtsfaecher[strval($bg->schluessel)*1] = strval($bg->anzeigeform);
		}

		$anzahlSchueler = 0;
		
	
		foreach($simpleXML->schulen[0]->schule->klassen->klasse as $klasse) {
		    
		    $klassenName = strval($klasse->klassenname);

		    $fieldCheck = str_replace(" ", "_", $klassenName);
		    $fieldCheck2 = str_replace(".", "_", $klassenName);
		    
		    if($_REQUEST[md5($fieldCheck)] > 0) {
		        // Import starten
		    }
		    elseif($_REQUEST[md5($fieldCheck2)] > 0) {
		        // :-)
		    }
		    else {
		        $doneActions .= $klassenName . " übersprungen.\r\n";
		        continue;
		    }
		    
			$kls = array();
			foreach($klasse->klassenleitungen->klassenleitung as $klassenleitung) {
				if(strval($klassenleitung->lehrkraft_id) != "") $kls[] = array(
						'lehrerID' => strval($klassenleitung->lehrkraft_id),
						'art' => (strval($klassenleitung->klassenleitung_art) == "K") ? 1 : 2
				);
			}

			$schuelerAnzahl = 0;

			$schuelerListe = array();

			// Jede Klassengruppe einzeln.
			foreach($klasse->klassengruppen->klassengruppe as $klassengruppe) {

				$ausbildungsrichtung = strval($klassengruppe->bildungsgang);
				$ausbildungsrichtung = $ausbs[$ausbildungsrichtung];
				$jahrgangsstufe = $jahrgangsstufen[strval($klassengruppe->jahrgangsstufe)*1];

				foreach($klassengruppe->schuelerliste->schuelerin as $schueler) {

					$adressen = array();
					$sprachen = [];

					foreach($schueler->schueleranschriften->schueleranschrift as $schueleranschrift) {

					    $kontakt = [];
						for($i = 0; $i < sizeof($schueleranschrift->kommunikationsdaten->kommunikation); $i++) {

							$kontakt[] = array(
									"typ" => strval($schueleranschrift->kommunikationsdaten->kommunikation[$i]->typ),
									"wert" =>  (strval($schueleranschrift->kommunikationsdaten->kommunikation[$i]->nummer_adresse)),
									"anschrifttyp" => strval($schueleranschrift->anschriftstyp)
							);
						}

						$adressen[] = array(
								"wessen" => (strval($schueleranschrift->anschrift_wessen)),
								"anschriftstyp" => (strval($schueleranschrift->anschriftstyp)),
								"auskunftsberechtigt" => (strval($schueleranschrift->auskunftsberechtigt)),
								"hauptansprechpartner" => (strval($schueleranschrift->hauptansprechpartner)),
								"strasse" => (strval($schueleranschrift->anschrift->strasse)),
								"nummer" => (strval($schueleranschrift->anschrift->nummer)),
								"ortsbezeichnung" => (strval($schueleranschrift->anschrift->ortsbezeichnung)),
								"postleitzahl" => (strval($schueleranschrift->anschrift->postleitzahl)),
								"anredetext" => (strval($schueleranschrift->anschrift->anredetext)),
								"anschrifttext" => (strval($schueleranschrift->anschrift->anschrifttext)),
								"familienname" => (strval($schueleranschrift->person->familienname)),
								"vornamen" =>( strval($schueleranschrift->person->vornamen)),
								"anrede" => (strval($schueleranschrift->person->anrede)),
								"personentyp" => (strval($schueleranschrift->person->personentyp)),
								"kontakt" => $kontakt
						);

					}

					$unterrichtListe = array();

					for($f = 0; $f < sizeof($schueler->besuchte_faecher->besuchtes_fach); $f++) {
						$fach = $schueler->besuchte_faecher->besuchtes_fach[$f];
						for($i = 0; $i < sizeof($fach->unterrichtselemente->unterrichtselement_id); $i++) {
							$unterrichtListe[] = strval($fach->unterrichtselemente->unterrichtselement_id[$i]);
						}
					}

					$import = true;

					/* if(strval($schueler->austrittsdatum) != "") {

						$austrittsdatum = explode(".",strval($schueler->austrittsdatum));
						$timeAustritt = mktime(10,10,10,$austrittsdatum[1],$austrittsdatum[0],$austrittsdatum[2]);

						if($timeAustritt < time()) $import = false;
					} */

					if(strval($schueler->eintrittsdatum) != "") {
						$eintrittsdatum = explode(".",strval($schueler->eintrittsdatum));
					}

					foreach($schueler->fremdsprachen->fremdsprache as $sprache) {
						$sprachen[] = [
							'jahrgangsstufe' => $jahrgangsstufen[strval($sprache->von_jahrgangsstufe)*1],
							'feststellungspruefung' => ((strval($sprache->feststellungspruefung) == 'true') ? 1 : 0),
							'unterrichtsfach' => $unterrichtsfaecher[strval($sprache->unterrichtsfach)*1],
							'sortierung' => strval($sprache->sortierung)
						];
					}

					if($import) {
						$anzahlSchueler++;
						$schuelerListe[] = array(

							"asvid" => strval($schueler->lokales_differenzierungsmerkmal),
							"name" => (strval($schueler->familienname)),
							"vornamen" => (strval($schueler->vornamen)),
							"rufname" => (strval($schueler->rufname)),
							"geschlecht" => (strval($schueler->geschlecht)) == "1" ? "m" : "w",
							"geburtsdatum" => (strval($schueler->geburtsdatum)),
							"austrittsdatum" => strval($schueler->austrittsdatum),
							"bekenntnis" => $religion[strval($schueler->religionszugehoerigkeit)],
							'geburtsort' => strval($schueler->geburtsort),
							'geburtsland' => $staaten[strval($schueler->Geburtsland)],
							'jahrgangsstufe' => $jahrgangsstufe,
							'jahrgangsstufeeintritt' => $jahrgangsstufen[strval($schueler->eintritt_jahrgangsstufe)*1],
							'eintrittsdatum' => DateFunctions::getMySQLDateFromNaturalDate(strval($schueler->eintrittsdatum)),
							// "kontakt" => $kontakt,
							"adressen" => $adressen,
							"unterricht" => $unterrichtListe,
							"ausbildungsrichtung" => $ausbildungsrichtung,
							'sprachen' => $sprachen,
							"ganztag_betreuung" => (strval($schueler->ganztag_betreuung)),
							);


						$schuelerAnzahl++;
					}
				}
			}

			$klassenName = strval($klasse->klassenname);

			for($k = 0; $k < 10; $k++) $klassenName = str_replace("0".$k, $k, $klassenName);

			self::$klassen[] = array(
				"name" => $klassenName,
				"klassenleitung" => $kls,
				"schueler" => $schuelerListe
			);
		}

		// Lehrer laden

		$doneActions .= "Klassen mit Schülern eingelesen.\r\n";

		foreach($simpleXML->schulen[0]->schule->lehrkraefte->lehrkraft as $lehrer) {

			$datenID = intval($lehrer->lehrkraftdaten_nicht_schulbezogen_id);

			$name = "";
			$vornamen = "";
			$rufname = "";
			$geschlecht = "";
			$zeugnisname = "";
			$amtsbezeichnungID = 0;
			$asvID = "";

			foreach($simpleXML->lehrkraftdaten_nicht_schulbezogen_liste->lehrkraftdaten_nicht_schulbezogen as $daten) {
				if(intval($daten->xml_id) == $datenID) {
					$name = strval($daten->familienname);
					$vornamen = strval($daten->vornamen);
					$rufname = strval($daten->rufname);
					$geschlecht = ((strval($daten->geschlecht) == "2") ? "w" : "m");
					$zeugnisname = strval($daten->zeugnisname_1);
					$amtsbezeichnungID = intval($daten->amtsbezeichnung);
					$asvID = strval($daten->lokales_differenzierungsmerkmal);
					break;
				}
			}
			

			if(strval($lehrer->namenskuerzel) != "") 
    			self::$lehrer[] = array(
    				"xmlid" => intval($lehrer->xml_id),
    				"datenid" => intval($lehrer->lehrkraftdaten_nicht_schulbezogen_id),
    				"kuerzel" => (strval($lehrer->namenskuerzel)),
    				"name" => ($name),
    				"vornamen" => ($vornamen),
    				"rufname" => ($rufname),
    				"geschlecht" => $geschlecht,
    				"zeugnisname" => ($zeugnisname),
    				"amtsbezeichnung" => $amtsbezeichnungID,
    				"asvid" => $asvID
    			);
		}

		$doneActions .= "Lehrer eingelesen\r\n";

		// Sync Lehrer

		// Welche Lehrer löschen?
		$lehrer = DB::getDB()->query("SELECT * FROM lehrer");
		while($l = DB::getDB()->fetch_array($lehrer)) {
			$found = false;
			for($i = 0; $i < sizeof(self::$lehrer); $i++) {
				if(self::$lehrer[$i]['asvid'] == $l['lehrerAsvID']) {
					$found = true;
					break;
				}
			}
			if(!$found) {
				$ll = DB::getDB()->query_first("SELECT * FROM lehrer WHERE lehrerAsvID='" . $l['lehrerAsvID'] . "'");

				DB::getDB()->query("DELETE FROM lehrer WHERE lehrerAsvID='" . $ll['lehrerAsvID'] . "'");

				if(DB::getGlobalSettings()->lehrerUserMode == "ASV" && $ll['lehrerUserID'] > 0) {
					DB::getDB()->query("DELETE FROM users WHERE userID='" . $ll['lehrerUserID'] . "'");
					DB::getDB()->query("DELETE FROM users_groups WHERE userID='" . $ll['lehrerUserID'] . "'");
				}
			}
		}
		// Lehrer anlegen

		$lehrer = self::$lehrer;

		for($i = 0; $i < sizeof($lehrer); $i++) {
			DB::getDB()->query("
				INSERT INTO lehrer
					(
						lehrerID,
						lehrerAsvID,
						lehrerKuerzel,
						lehrerName,
						lehrerVornamen,
						lehrerRufname,
						lehrerGeschlecht,
						lehrerZeugnisunterschrift,
						lehrerAmtsbezeichnung
					) values(
						'" . DB::getDB()->escapeString($lehrer[$i]['xmlid']) . "',
						'" . DB::getDB()->escapeString($lehrer[$i]['asvid']) . "',
						'" . DB::getDB()->escapeString($lehrer[$i]['kuerzel']) . "',
						'" . DB::getDB()->escapeString($lehrer[$i]['name']) . "',
						'" . DB::getDB()->escapeString($lehrer[$i]['vornamen']) . "',
						'" . DB::getDB()->escapeString($lehrer[$i]['rufname']) . "',
						'" . DB::getDB()->escapeString($lehrer[$i]['geschlecht']) . "',
						'" . DB::getDB()->escapeString($lehrer[$i]['zeugnisname']) . "',
						'" . DB::getDB()->escapeString($lehrer[$i]['amtsbezeichnung']) . "'
					) ON DUPLICATE KEY UPDATE
						lehrerID='" . DB::getDB()->escapeString($lehrer[$i]['xmlid']) . "',
						lehrerKuerzel='" . DB::getDB()->escapeString($lehrer[$i]['kuerzel']) . "',
						lehrerName='" . DB::getDB()->escapeString($lehrer[$i]['name']) . "',
						lehrerVornamen='" . DB::getDB()->escapeString($lehrer[$i]['vornamen']) . "',
						lehrerRufname='" . DB::getDB()->escapeString($lehrer[$i]['rufname']) . "',
						lehrerGeschlecht='" . DB::getDB()->escapeString($lehrer[$i]['geschlecht']) . "',
						lehrerZeugnisunterschrift='" . DB::getDB()->escapeString($lehrer[$i]['zeugnisname']) . "',
						lehrerAmtsbezeichnung='" . DB::getDB()->escapeString($lehrer[$i]['amtsbezeichnung']) . "',
						lehrerUserID=lehrerUserID
			");
		}


		// /Lehrer


		$doneActions .= "Lehrer synchronisiert.\r\n";

		// Klassenleitung

		DB::getDB()->query("DELETE FROM klassenleitung");
		for($i = 0; $i < sizeof(self::$klassen); $i++) {
			for($k = 0; $k < sizeof(self::$klassen[$i]['klassenleitung']); $k++) {
				DB::getDB()->query("INSERT INTO klassenleitung (klasseName, lehrerID, klassenleitungArt) values('" . DB::getDB()->escapeString(self::$klassen[$i]['name']) . "','" . self::$klassen[$i]['klassenleitung'][$k]['lehrerID'] . "','" . self::$klassen[$i]['klassenleitung'][$k]['art'] . "') ON DUPLICATE KEY UPDATE lehrerID=lehrerID");
			}
		}

		$doneActions .= "Klassenleitungen synchronisiert.\r\n";

		//

		// Lï¿½schen?
		$alleSchueler = DB::getDB()->query("SELECT * FROM schueler");
		while($schueler = DB::getDB()->fetch_array($alleSchueler)) {
			$found = false;

			for($i = 0; $i < sizeof(self::$klassen); $i++) {
				for($s = 0; $s < sizeof(self::$klassen[$i]['schueler']); $s++) {
					if(self::$klassen[$i]['schueler'][$s]['asvid'] == $schueler['schuelerAsvID']) {
						$found = true;
					}
				}
			}

			if(!$found) {
				$ss = DB::getDB()->query_first("SELECT * FROM schueler WHERE schuelerAsvID='" .  $schueler['schuelerAsvID'] . "'");

				DB::getDB()->query("DELETE FROM schueler WHERE schuelerAsvID='" . $ss['schuelerAsvID'] . "'");

				if(DB::getGlobalSettings()->schuelerUserMode == "ASV" && $ss['schuelerUserID'] > 0) {
					DB::getDB()->query("DELETE FROM users WHERE userID='" . $ss['schuelerUserID'] . "'");
					DB::getDB()->query("DELETE FROM users_groups WHERE userID='" . $ss['schuelerUserID'] . "'");
				}
			}
		}
		//

		for($i = 0; $i < sizeof(self::$klassen); $i++) {
			for($s = 0; $s < sizeof(self::$klassen[$i]['schueler']); $s++) {
				// DB::getDB()->query("DELETE FROM schueler WHERE schuelerAsvID='" . self::$klassen[$i]['schueler'][$s]['asvid'] . "'");

				$data = explode(".",self::$klassen[$i]['schueler'][$s]['geburtsdatum']);

				$gebdatum = $data[2] . '-' . $data[1] . '-' . $data[0];

				if(self::$klassen[$i]['schueler'][$s]['austrittsdatum'] != "") {
					$data = explode(".",self::$klassen[$i]['schueler'][$s]['austrittsdatum']);
					$austrittsdatum = "'" . $data[2] . '-' . $data[1] . '-' . $data[0] . "'";
				}
				else $austrittsdatum = "NULL";

				DB::getDB()->query("INSERT INTO schueler
						(
							schuelerAsvID,
							schuelerName,
							schuelerVornamen,
							schuelerRufname,
							schuelerGeschlecht,
							schuelerGeburtsdatum,
							schuelerKlasse,
							schuelerAustrittDatum,
							schuelerBekenntnis,
							schuelerAusbildungsrichtung,
							schuelerGeburtsort,
							schuelerGeburtsland,
							schuelerJahrgangsstufe,
							schulerEintrittJahrgangsstufe,
							schuelerEintrittDatum,
							schuelerGanztagBetreuung
						) values (
							'" . DB::getDB()->escapeString(self::$klassen[$i]['schueler'][$s]['asvid']) . "',
							'" . DB::getDB()->escapeString(self::$klassen[$i]['schueler'][$s]['name']) . "',
							'" . DB::getDB()->escapeString(self::$klassen[$i]['schueler'][$s]['vornamen']) . "',
							'" . DB::getDB()->escapeString(self::$klassen[$i]['schueler'][$s]['rufname']) . "',
							'" . DB::getDB()->escapeString(self::$klassen[$i]['schueler'][$s]['geschlecht']) . "',
							'" . $gebdatum . "',
							'" . self::$klassen[$i]['name'] . "',
						    " . $austrittsdatum . ",
							'" . DB::getDB()->escapeString(self::$klassen[$i]['schueler'][$s]['bekenntnis']) . "',
							'" . DB::getDB()->escapeString(self::$klassen[$i]['schueler'][$s]['ausbildungsrichtung']) . "',
							'" . DB::getDB()->escapeString(self::$klassen[$i]['schueler'][$s]['geburtsort']) . "',
							'" . DB::getDB()->escapeString(self::$klassen[$i]['schueler'][$s]['geburtsland']) . "',
							'" . DB::getDB()->escapeString(self::$klassen[$i]['schueler'][$s]['jahrgangsstufe']) . "',
							'" . DB::getDB()->escapeString(self::$klassen[$i]['schueler'][$s]['jahrgangsstufeeintritt']) . "',
							'" . DB::getDB()->escapeString(self::$klassen[$i]['schueler'][$s]['eintrittsdatum']) . "',
							'" . DB::getDB()->escapeString(self::$klassen[$i]['schueler'][$s]['ganztag_betreuung']) . "'
						) ON DUPLICATE KEY UPDATE
							schuelerAsvID='" . self::$klassen[$i]['schueler'][$s]['asvid'] . "',
							schuelerName='" . DB::getDB()->escapeString(self::$klassen[$i]['schueler'][$s]['name']) . "',
							schuelerVornamen='" . DB::getDB()->escapeString(self::$klassen[$i]['schueler'][$s]['vornamen']) . "',
							schuelerRufname='" . DB::getDB()->escapeString(self::$klassen[$i]['schueler'][$s]['rufname']) . "',
							schuelerGeschlecht='" . DB::getDB()->escapeString(self::$klassen[$i]['schueler'][$s]['geschlecht']) . "',
							schuelerGeburtsdatum='" . $gebdatum . "',
							schuelerKlasse='" . self::$klassen[$i]['name'] . "',
							schuelerAustrittDatum=" . $austrittsdatum . ",
							schuelerBekenntnis = '" . DB::getDB()->escapeString(self::$klassen[$i]['schueler'][$s]['bekenntnis']) . "',
							schuelerAusbildungsrichtung = '" . DB::getDB()->escapeString(self::$klassen[$i]['schueler'][$s]['ausbildungsrichtung']) . "',
							schuelerGeburtsort='" . DB::getDB()->escapeString(self::$klassen[$i]['schueler'][$s]['geburtsort']) . "',
							schuelerGeburtsland='" . DB::getDB()->escapeString(self::$klassen[$i]['schueler'][$s]['geburtsland']) . "',
							schuelerJahrgangsstufe = '" . DB::getDB()->escapeString(self::$klassen[$i]['schueler'][$s]['jahrgangsstufe']) . "',
							schulerEintrittJahrgangsstufe='" . DB::getDB()->escapeString(self::$klassen[$i]['schueler'][$s]['jahrgangsstufeeintritt']) . "',
							schuelerEintrittDatum='" . DB::getDB()->escapeString(self::$klassen[$i]['schueler'][$s]['eintrittsdatum']) . "',
							schuelerGanztagBetreuung='" . DB::getDB()->escapeString(self::$klassen[$i]['schueler'][$s]['ganztag_betreuung']) . "'
						");

				$values = "";

				for($j = 0; $j < sizeof(self::$klassen[$i]['schueler'][$s]['sprachen']); $j++) {
					$sprache = self::$klassen[$i]['schueler'][$s]['sprachen'][$j];
					$insertsFremdsprachen[] = "(
				'" . DB::getDB()->escapeString(self::$klassen[$i]['schueler'][$s]['asvid']) . "',
				'" . DB::getDB()->escapeString($sprache['sortierung']) . "',
				'" . DB::getDB()->escapeString($sprache['jahrgangsstufe']) . "',
				'" . DB::getDB()->escapeString($sprache['unterrichtsfach']) . "',
				'" . DB::getDB()->escapeString($sprache['feststellungspruefung']) . "')";
				}


				for($u = 0; $u < sizeof(self::$klassen[$i]['schueler'][$s]['unterricht']); $u++) {

					if($u > 0) $values .= ",";
					$values .= "('" . self::$klassen[$i]['schueler'][$s]['unterricht'][$u] . "','" . self::$klassen[$i]['schueler'][$s]['asvid'] . "')";
				}

				if($values != "") DB::getDB()->query("INSERT INTO unterricht_besuch (unterrichtID, schuelerAsvID) values " . $values);
			}
		}

		DB::getDB()->query("DELETE FROM schueler_fremdsprache");
		if(sizeof($insertsFremdsprachen) > 0) {
			DB::getDB()->query("INSERT INTO schueler_fremdsprache (schuelerAsvID, spracheSortierung, spracheAbJahrgangsstufe, spracheFach, spracheFeststellungspruefung) values " . implode(",",$insertsFremdsprachen));
		}

		$doneActions .= "Schüler synchronisiert.\r\n";

		// Kontaktdaten der Eltern

		// Syncen, nicht löschen, da UserIDs gespeichert.
		// DB::getDB()->query("DELETE FROM eltern_email");

		if(DB::getGlobalSettings()->elternUserMode == "ASV_MAIL") {
    		$elternMails = DB::getDB()->query("SELECT * FROM eltern_email");
    		
    		// Debugger::debugObject(self::$klassen,1);
    		
    		while($elternMail = DB::getDB()->fetch_array($elternMails)) {
    			$found = false;
    			
    			

    			for($i = 0; $i < sizeof(self::$klassen); $i++) {
    				for($s = 0; $s < sizeof(self::$klassen[$i]['schueler']); $s++) {
    				    for($a = 0; $a < sizeof(self::$klassen[$i]['schueler'][$s]['adressen']); $a++) {
        					for($k = 0; $k < sizeof(self::$klassen[$i]['schueler'][$s]['adressen'][$a]['kontakt']); $k++) {
    
        					    
        					    if((self::$klassen[$i]['schueler'][$s]['adressen'][$a]['wessen'] == 'eb' || self::$klassen[$i]['schueler'][$s]['adressen'][$a]['wessen'] == 'web')  && filter_var(trim(self::$klassen[$i]['schueler'][$s]['adressen'][$a]['kontakt'][$k]['wert']), FILTER_VALIDATE_EMAIL)) {
        					        if(strtolower(trim(self::$klassen[$i]['schueler'][$s]['adressen'][$a]['kontakt'][$k]['wert'])) == strtolower($elternMail['elternEMail'])) {
        								if(self::$klassen[$i]['schueler'][$s]['asvid'] == $elternMail['elternSchuelerAsvID']) {
        									$found = true;
        									break;
        								}
        							}
    
        						}
        						if($found) break;
        					}
    				    }
    					if($found) break;
    				}
    				if($found) break;
    			}

    			if(!$found) {
    				DB::getDB()->query("DELETE FROM eltern_email WHERE elternEMail LIKE '" . $elternMail['elternEMail'] . "' AND elternSchuelerAsvID LIKE '" . $elternMail['elternSchuelerAsvID'] . "'");
    			}
    		}
		}
		

		$doneActions .= "Kontaktdaten synchronisiert.\r\n";

		DB::getDB()->query("DELETE FROM eltern_telefon");

		for($i = 0; $i < sizeof(self::$klassen); $i++) {
			for($s = 0; $s < sizeof(self::$klassen[$i]['schueler']); $s++) {
				for($k = 0; $k < sizeof(self::$klassen[$i]['schueler'][$s]['kontakt']); $k++) {



				}

			}
		}

		$doneActions .= "Telefonnummern synchronisiert.\r\n";

		DB::getDB()->query("TRUNCATE TABLE eltern_adressen");

		$insertsAdressen = array();
		$id = 0;

		for($i = 0; $i < sizeof(self::$klassen); $i++) {
			for($s = 0; $s < sizeof(self::$klassen[$i]['schueler']); $s++) {
				for($a = 0; $a < sizeof(self::$klassen[$i]['schueler'][$s]['adressen']); $a++) {

					$wessen = "";
					switch(self::$klassen[$i]['schueler'][$s]['adressen'][$a]['wessen']) {
						case '1':
						default:
							$wessen = 'eb';
						break;

						case '2':
							$wessen = 'web';
						break;

						case '3':
							$wessen = 's';
						break;

						case '4':
							$wessen = 'w';
						break;
					}

					$id++;

					$insertsAdressen[] = "(
								NULL,
								'" . self::$klassen[$i]['schueler'][$s]['asvid'] . "',
								'$wessen',
								'" . ((self::$klassen[$i]['schueler'][$s]['adressen'][$a]['auskunftsberechtigt'] == "true") ? 1 : 0) . "',
								'" . ((self::$klassen[$i]['schueler'][$s]['adressen'][$a]['hauptansprechpartner'] == "true") ? 1 : 0) . "',
								'" . addslashes(self::$klassen[$i]['schueler'][$s]['adressen'][$a]['strasse']) . "',
								'" . addslashes(self::$klassen[$i]['schueler'][$s]['adressen'][$a]['nummer']) . "',
								'" . addslashes(self::$klassen[$i]['schueler'][$s]['adressen'][$a]['ortsbezeichnung']) . "',
								'" . addslashes(self::$klassen[$i]['schueler'][$s]['adressen'][$a]['postleitzahl']) . "',
								'" . addslashes(self::$klassen[$i]['schueler'][$s]['adressen'][$a]['anredetext']) . "',
								'" . addslashes(self::$klassen[$i]['schueler'][$s]['adressen'][$a]['anschrifttext']) . "',
								'" . addslashes(self::$klassen[$i]['schueler'][$s]['adressen'][$a]['familienname']) . "',
								'" . addslashes(self::$klassen[$i]['schueler'][$s]['adressen'][$a]['vornamen']) . "',
								'" . addslashes(self::$klassen[$i]['schueler'][$s]['adressen'][$a]['anrede']) . "',
								'" . addslashes(self::$klassen[$i]['schueler'][$s]['adressen'][$a]['personentyp']) . "'
							)";


					for($k = 0; $k < sizeof(self::$klassen[$i]['schueler'][$s]['adressen'][$a]['kontakt']); $k++) {






					if(filter_var(trim(self::$klassen[$i]['schueler'][$s]['adressen'][$a]['kontakt'][$k]['wert']), FILTER_VALIDATE_EMAIL) !== false) {
						if(DB::getGlobalSettings()->elternUserMode == "ASV_MAIL") {

							DB::getDB()->query("INSERT INTO eltern_email (elternEMail, elternSchuelerAsvID, elternAdresseID) values(
									'" . DB::getDB()->escapeString(strtolower(trim(self::$klassen[$i]['schueler'][$s]['adressen'][$a]['kontakt'][$k]['wert']))) . "',
									'" . self::$klassen[$i]['schueler'][$s]['asvid'] . "'
							,$id) ON DUPLICATE KEY UPDATE elternSchuelerAsvID=elternSchuelerAsvID, elternAdresseID=$id");
						}
					}
						else {
							$art = "";

							if(self::$klassen[$i]['schueler'][$s]['adressen'][$a]['kontakt'][$k]['typ']*1 == 3) {
								// Fax
								$art = "fax";
							}

							if(self::$klassen[$i]['schueler'][$s]['adressen'][$a]['kontakt'][$k]['typ']*1 == 2) {
								// Handy
								$art = "mobiltelefon";
							}

							if(self::$klassen[$i]['schueler'][$s]['adressen'][$a]['kontakt'][$k]['typ']*1 == 1) {
								// Handy
								$art = "telefon";
							}

							if($art != "") {
								DB::getDB()->query("INSERT INTO eltern_telefon (telefonNummer, schuelerAsvID, telefonTyp, kontaktTyp, adresseID) values
								(
								'" . trim(self::$klassen[$i]['schueler'][$s]['adressen'][$a]['kontakt'][$k]['wert']) . "',
								'" . self::$klassen[$i]['schueler'][$s]['asvid'] . "',
								'" . $art . "',
								'" . trim(self::$klassen[$i]['schueler'][$s]['adressen'][$a]['kontakt'][$k]['anschrifttyp']) . "',$id

								) ON DUPLICATE KEY UPDATE telefonNummer=telefonNummer");
							}
						}



					}




				}
			}

		}

		if(sizeof($insertsAdressen) > 0) {
			DB::getDB()->query("
						INSERT INTO eltern_adressen
							(
								`adresseID`,
								`adresseSchuelerAsvID`,
								`adresseWessen`,
								`adresseIsAuskunftsberechtigt`,
								`adresseIsHauptansprechpartner`,
								`adresseStrasse`,
								`adresseNummer`,
								`adresseOrt`,
								`adressePostleitzahl`,
								`adresseAnredetext`,
								`adresseAnschrifttext`,
								`adresseFamilienname`,
								`adresseVorname`,
								`adresseAnrede`,
								`adressePersonentyp`
							) VALUES
					" . implode(", ", $insertsAdressen));
		}

		$doneActions .= "Adressen der Eltern synchronisiert.\r\n";

		$message = "Es wurden " . sizeof(self::$klassen) . " Klassen mit " . $anzahlSchueler . " Schülern importiert.";

		$matcher = new MatchUserFunctions();

		if(DB::getGlobalSettings()->lehrerUserMode == "SYNC") $doneActions .= $matcher->matchLehrer();
		if(DB::getGlobalSettings()->schuelerUserMode == "SYNC") $doneActions .= $matcher->matchSchueler();

		// $doneActions .= self::$matchUsers();

		eval("self::\$html = \"" . DB::getTPL()->get("administration/usersync/asvimportok") . "\";");

	}

	public static function getAdminGroup() {
		return self::ADMINGROUP_ASV_SYNC;
	}

	public static function hasSettings() {
		return true;
	}

	public static function getSiteDisplayName() {
		return "ASV Import";
	}

	public static function getSettingsDescription() {
		return [
		    [
		        'name' => '7zip-linux',
                'typ' => 'ZEILE',
                'titel' => 'Pfad zu 7ZIP (Linux)',
                'text' => ''
            ],
            [
                'name' => '7zip-windows',
                'typ' => 'ZEILE',
                'titel' => 'Pfad zu 7ZIP (Windows)',
                'text' => ''
            ],
        ];
	}

	public static function siteIsAlwaysActive() {
		return true;
	}

	public static function hasAdmin() {
		return true;
	}

	public static function getAdminMenuGroup() {
		return 'Im-/Export';
	}

	public static function getAdminMenuGroupIcon() {
		return 'fa fa-download';
	}

	public static function need2Factor() {
	    return true;
	}

}


?>