<?php

class schulinfo extends AbstractPage {

	private $schulleiterGroup = 'Webportal_Schulleiter';


	public function __construct() {
		parent::__construct(array("Schulinformationen"));

		$this->checkLogin();

	}

	public function execute() {
		// Zeit allgemeine Schulinformationen an (Adresse etc.)
		$html = "";

		$schulleitungHTML = "";


		for($i = 1; $i <= 10; $i++) {
			if(DB::getSettings()->getValue("schulinfo-schulleitung-" . $i . "-asvid") != "") {
				$lehrer = lehrer::getByASVId(DB::getSettings()->getValue("schulinfo-schulleitung-" . $i . "-asvid"));
				if($lehrer != null) {
					$schulleitungHTML .= "<tr><td>" . $lehrer->getRufname() . " " . $lehrer->getName() . ", " . $lehrer->getAmtsbezeichnung()->getKurzform() . "</td><td>";
					$schulleitungHTML .= DB::getSettings()->getValue("schulinfo-schulleitung-" . $i . "-funktion");
					$schulleitungHTML .= "</td></tr>";
				}
			}
		}

		$personalratHTML= "";
        if (DB::getSettings()->getValue("schulinfo-personalrat-show")) {
            for($i = 1; $i <= 20; $i++) {
                if(DB::getSettings()->getValue("schulinfo-personalrat-" . $i . "-userid") != "") {
                    $lehrer = lehrer::getByUserID(DB::getSettings()->getValue("schulinfo-personalrat-" . $i . "-userid"));
                    if($lehrer != null) {
                        $personalratHTML.= "<tr><td>" . $lehrer->getRufname() . " " . $lehrer->getName() . ", " . $lehrer->getAmtsbezeichnung()->getKurzform() . "</td><td>";
                        $personalratHTML.= DB::getSettings()->getValue("schulinfo-personalrat-" . $i . "-funktion");
                        $personalratHTML.= "</td></tr>";
                    }
                }
            }
        }




		// Fächer

		$facherHTML = "";

		$faecher = fach::getAll();

		// 		Debugger::debugObject($faecher,1);

		for($i = 0; $i < sizeof($faecher); $i++) {

			if(DB::getSettings()->getBoolean("schulinfo-fach-" . $faecher[$i]->getID() . "-unterrichtet")) {
				$facherHTML .= "<tr><td><b>" . $faecher[$i]->getLangform() . "</b><br />" . $faecher[$i]->getKurzform() . "</td>";

				if(DB::getSession()->isTeacher() || DB::getSession()->isAdmin() || DB::getSettings()->getBoolean("schulinfo-fachlehrer-show")) {
					$fachlehrer = $faecher[$i]->getFachLehrer();

					$facherHTML .= "<td>";

					for($f = 0; $f < sizeof($fachlehrer); $f++) {
						$facherHTML .= $fachlehrer[$f]->getDisplayNameMitAmtsbezeichnung() . "<br />";
					}

					$facherHTML .= "</td>";
				}

				if(DB::getSession()->isTeacher() || DB::getSession()->isAdmin() || DB::getSettings()->getBoolean("schulinfo-fachbetreuer-show")) {
					$fachlehrer = $faecher[$i]->getFachLehrer();

					$facherHTML .= "<td>";

					for($f = 1; $f <= 5; $f++) {
						if(DB::getSettings()->getValue("schulinfo-fachbetreuer-" . $faecher[$i]->getASDID() . "-" . $f) != "") {
						    $lehrer = lehrer::getByASVId(DB::getSettings()->getValue("schulinfo-fachbetreuer-" . $faecher[$i]->getASDID() . "-" . $f));
							if($lehrer != null) {
								$facherHTML .= $lehrer->getDisplayNameMitAmtsbezeichnung() . "<br />";
							}
						}
					}

					$facherHTML .= "</td>";
				}

			}




			$facherHTML .= "</td></tr>";
		}


		// /Fächer


		// Lehrer

		if(DB::getSession()->isAdmin() || self::isSchulleitung(DB::getSession()->getUser())) {
			$alleLehrer = lehrer::getAll(true);
		}
		else {
			$alleLehrer = lehrer::getAll();
		}


		$lehrerHTML = "";
		for($i = 0; $i < sizeof($alleLehrer); $i++) {

			$lehrerHTML .= "<tr><td>" . $alleLehrer[$i]->getKuerzel() . "</td><td>" . $alleLehrer[$i]->getDisplayNameMitAmtsbezeichnung() . "</td>";

			if(DB::getSession()->isAdmin() || self::isSchulleitung(DB::getSession()->getUser())) {
				$lehrerHTML .= "<td>";

				switch(DB::getSettings()->getValue("schulinfo-status-lehrer-" . $alleLehrer[$i]->getAsvID())) {
					case 'anwesendVZ':
					default:
						$lehrerHTML .= "Vollzeit";
					break;

					case 'anwesendTZ':
						$lehrerHTML .= "Teilzeit";
					break;

					case 'abwesend':
						$lehrerHTML .= "Derzeit abwesend";
					break;

					case 'keinlehrer':
						$lehrerHTML .= "Nicht mehr an der Schule";
					break;
				}

				$lehrerHTML .= "</td>";
			}

			$lehrerHTML .= "</tr>";

		}

		$schuelerAnzahl = schueler::getAnzahlSchueler() . "<br />davon " . schueler::getAnzahlWeiblich() . " weiblich und " . schueler::getAnzahlMaennlich() . " männlich";

		$klassenAnzahl = klasse::getAnzahlKlassen();

		$schuelerProKlasse = floor($schuelerAnzahl / $klassenAnzahl);

		eval("DB::getTPL()->out(\"" . DB::getTPL()->get("schulinfo/index") . "\");");
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
	    return [
	        [
	            'name' => 'schulinfo-keine-amtsbezeichungen',
	            'typ' => 'BOOLEAN',
	            'titel' => 'Keine Amtsbezeichungen bei Lehrern anzeigen',
	            'text' => 'Diese Einstellung blendet an den meisten Stellen die Amtsbezeichnung aus.'
	        ]
	    ];
	}



	public static function getSiteDisplayName() {
		return 'Schulinfo und -daten';
	}

	public static function siteIsAlwaysActive() {
		return true;
	}

	public static function hasAdmin() {
		return true;
	}


	public static function getAdminGroup() {
		return 'Webportal_Schulinfo_Admin';
	}

	public static function getAdminMenuGroup() {
		return 'Schulinformationen';
	}

	public static function getAdminMenuGroupIcon() {
		return 'fa fa-info-circle';
	}

	public static function getAdminMenuIcon() {
		return 'fa fa-info-circle';
	}

	public static function displayAdministration($selfURL) {

		if($_GET['action'] == 'setTeacherStatus') {
			$alleLehrer = lehrer::getAll(true);

			$grenzeTZ = intval($_POST['grenzeTZ']);
			$grenzeVZ = intval($_POST['grenzeVZ']);


			$lehrerHTML = "";

			//
			// anwesendVZ
			// anwesendTZ
			// abwesend
			// keinlehrer

			$stundenplan = stundenplandata::getCurrentStundenplan();
			if($stundenplan != null) {
				for($i = 0; $i < sizeof($alleLehrer); $i++) {
					$status = "";
					$stunden = $stundenplan->getStundenAnzahlForTeacher($alleLehrer[$i]->getKuerzel());

					// echo($alleLehrer[$i]->getKuerzel() . "->" . $stunden . "<br />");


					if($stunden >= $grenzeVZ) $status = 'anwesendVZ';
					else if($stunden >= $grenzeTZ) $status = 'anwesendTZ';
					else $status = 'abwesend';


					DB::getSettings()->setValue("schulinfo-status-lehrer-" . $alleLehrer[$i]->getAsvID(), $status);

				}
			}

			header("Location: $selfURL");
			exit(0);


		}

		$html = "";

		$saveMode = false;

		if($_GET['mode'] == 'save') $saveMode = true;

		$fieldsForSave = [
				'schulinfo-name',
				'schulinfo-name-zusatz',
				'schulinfo-adresse1',
				'schulinfo-adresse2',
				'schulinfo-plz',
				'schulinfo-ort',
				'schulinfo-telefon',
				'schulinfo-fax',
				'schulinfo-email',
				'schulinfo-homepage',
				'schulinfo-schulleiter-show',
				'schulinfo-schultyp',
				'schulinfo-lehrer-show',
				'schulinfo-fachbetreuer-show',
				'schulinfo-fachlehrer-show',
				'schulinfo-personalrat-show',
				'schulinfo-faecher-show'
		];



		$selectSchulleitung = "";



		for($i = 1; $i <= 10; $i++) {
			$selectSchulleitung .= "<tr><td><select name=\"schulleitung_" . $i . "_asvid\" class=\"form-control\">";
			$selectSchulleitung .= self::getTeacherSelect(DB::getSettings()->getValue("schulinfo-schulleitung-" . $i . "-asvid"));
			$selectSchulleitung .= "</select></td><td><input type=\"text\" name=\"schulleitung_" . $i . "_funktion\" class=\"form-control\" placeholder=\"z.B. Schulleitung, stellv. Schulleiter, erweitere Schulleitung\" value=\"" . DB::getSettings()->getValue("schulinfo-schulleitung-" . $i . "-funktion") . "\">";
			$selectSchulleitung .= "</td></tr>";

			if($saveMode) {
				DB::getSettings()->setValue("schulinfo-schulleitung-" . $i . "-asvid", $_POST["schulleitung_" . $i . "_asvid"]);
				DB::getSettings()->setValue("schulinfo-schulleitung-" . $i . "-funktion", $_POST["schulleitung_" . $i . "_funktion"]);
			}
		}



		// Fächer

		$facherHTML = "";

		$faecher = fach::getAll();


		for($i = 0; $i < sizeof($faecher); $i++) {

		    if($_REQUEST['mode'] == 'save') {
		        $faecher[$i]->setOrdnungszahl($_REQUEST["fach-" . $faecher[$i]->getID() ."-ordnung"]);
		    }

			$facherHTML .= "<tr><td><b>" . $faecher[$i]->getLangform() . "</b><br />" . $faecher[$i]->getKurzform();

			$fachlehrer = $faecher[$i]->getFachLehrer();

			$fachlehrerListe = [];
			for($f = 0; $f < sizeof($fachlehrer); $f++) $fachlehrerListe[] = $fachlehrer[$f]->getKuerzel();

			$facherHTML .= "<br/>" . implode(", ",$fachlehrerListe) . "</td>";

			$facherHTML .= "<td><div class=\"onoffswitch\">
				    	<input type=\"checkbox\" name=\"schulinfo-fach-" . $faecher[$i]->getID() . "-unterrichtet\" value=\"1\" class=\"onoffswitch-checkbox\" id=\"schulinfo-fach-" . $faecher[$i]->getID() . "-unterrichtet\"
				    		" . ((DB::getSettings()->getBoolean("schulinfo-fach-" . $faecher[$i]->getID() . "-unterrichtet")) ? " checked=\"checked\"" : ("")) . "
				    			>
				    	<label class=\"onoffswitch-label\" for=\"schulinfo-fach-" . $faecher[$i]->getID() . "-unterrichtet\">
				       		<span class=\"onoffswitch-inner\"></span>
				        	<span class=\"onoffswitch-switch\"></span>
				    	</label>
					</div>";
			$facherHTML .= "</td>";

			$facherHTML .= "<td><input type=\"number\" class=\"form-control\" name=\"fach-" . $faecher[$i]->getID() ."-ordnung\" value=\"" . $faecher[$i]->getOrdnungszahl() . "\"></td>";


			$facherHTML .= "<td>";

			$fieldsForSave[] = "schulinfo-fach-" . $faecher[$i]->getID() . "-unterrichtet";

			for($f = 1; $f <= 5; $f++) {
			    $facherHTML .= "<select name=\"schulinfo-fachbetreuer-" . $faecher[$i]->getASDID() . "-" . $f . "\" class=\"form-control\">" . self::getTeacherSelect(DB::getSettings()->getValue("schulinfo-fachbetreuer-" . $faecher[$i]->getASDID() . "-" . $f)) . "</select>\r\n";

			    $fieldsForSave[] = "schulinfo-fachbetreuer-" . $faecher[$i]->getASDID() . "-" . $f;

			}



			$facherHTML .= "</td></tr>";
		}


		// /Fächer


		// Lehrer

		$alleLehrer = lehrer::getAll(true);

		$lehrerHTML = "";
		for($i = 0; $i < sizeof($alleLehrer); $i++) {
			$lehrerHTML .= "<tr><td>" . $alleLehrer[$i]->getName() . ", " . $alleLehrer[$i]->getRufname() . ", " . $alleLehrer[$i]->getAmtsbezeichnung()->getKurzform() . "</td><td>";
			$lehrerHTML .= "<select name=\"schulinfo-status-lehrer-" . $alleLehrer[$i]->getAsvID() . "\" class=\"form-control\">";

			$lehrerHTML .= "<option value=\"anwesendVZ\"" . ((DB::getSettings()->getValue("schulinfo-status-lehrer-" . $alleLehrer[$i]->getAsvID()) == "anwesendVZ") ? " selected=\"seletected\"" : "") . ">Anwesend (Vollzeit)</option>";
			$lehrerHTML .= "<option value=\"anwesendTZ\"" . ((DB::getSettings()->getValue("schulinfo-status-lehrer-" . $alleLehrer[$i]->getAsvID()) == "anwesendTZ") ? " selected=\"seletected\"" : "") . ">Anwesend (Teilzeit)</option>";
			$lehrerHTML .= "<option value=\"abwesend\"" . ((DB::getSettings()->getValue("schulinfo-status-lehrer-" . $alleLehrer[$i]->getAsvID()) == "abwesend") ? " selected=\"seletected\"" : "") . ">Temporär abwesend (z.B. Elternzeit, Sabbatjahr)</option>";
			$lehrerHTML .= "<option value=\"abwesendActive\"" . ((DB::getSettings()->getValue("schulinfo-status-lehrer-" . $alleLehrer[$i]->getAsvID()) == "abwesendActive") ? " selected=\"seletected\"" : "") . ">Temporär abwesend aber aktive Benutzer*in</option>";
			$lehrerHTML .= "<option value=\"keinlehrer\"" . ((DB::getSettings()->getValue("schulinfo-status-lehrer-" . $alleLehrer[$i]->getAsvID()) == "keinlehrer") ? " selected=\"seletected\"" : "") . ">Nicht mehr an der Schule</option>";

			$lehrerHTML .= "</select></td></tr>";


			$fieldsForSave[] = "schulinfo-status-lehrer-" . $alleLehrer[$i]->getAsvID();

		}


		// /Lehrer


		// Personalrat

		$personalratHTML = "";

		for($i = 1; $i <= 20; $i++) {
		    $personalratHTML.= "<tr><td><select style=\"width:100%\" id=\"personalrat_" . $i . "\" name=\"personalrat_" . $i . "_userid\" class=\"form-control\">";
			$personalratHTML.= self::getUserSelect(DB::getSettings()->getValue("schulinfo-personalrat-" . $i . "-userid"));
			$personalratHTML.= "</select>";
			$personalratHTML .= '<script>
                    $(function () {
                      $("#personalrat_' . $i. '").select2();
                    });


                    </script>';


			$personalratHTML .= "</td><td><input type=\"text\" name=\"personalrat_" . $i . "_funktion\" class=\"form-control\" placeholder=\"z.B. Vorsitzende, Schriftführerin\" value=\"" . DB::getSettings()->getValue("schulinfo-personalrat-" . $i . "-funktion") . "\">";
			$personalratHTML.= "</td></tr>";

			if($saveMode) {
				DB::getSettings()->setValue("schulinfo-personalrat-" . $i . "-userid", $_POST["personalrat_" . $i . "_userid"]);
				DB::getSettings()->setValue("schulinfo-personalrat-" . $i . "-funktion", $_POST["personalrat_" . $i . "_funktion"]);
			}
		}


		// /personalrat

		// Verwaltungsmitarbeiter

		$verwaltungsmitarbeiterHTML = "";

		for($i = 1; $i <= 20; $i++) {
		    $verwaltungsmitarbeiterHTML.= "<tr><td><select style=\"width:100%\" id=\"verwaltung_" . $i . "\" name=\"verwaltung_" . $i . "_userid\" class=\"form-control\">";
		    $verwaltungsmitarbeiterHTML.= self::getUserSelect(DB::getSettings()->getValue("schulinfo-verwaltungsmitarbeiter-" . $i . "-userid"));
		    $verwaltungsmitarbeiterHTML.= "</select>";
		    $verwaltungsmitarbeiterHTML .= '<script>
                    $(function () {
                      $("#verwaltung_' . $i. '").select2();
                    });


                    </script>';


		    $verwaltungsmitarbeiterHTML .= "</td></tr>";

		    if($saveMode) {
		        DB::getSettings()->setValue("schulinfo-verwaltungsmitarbeiter-" . $i . "-userid", $_POST["verwaltung_" . $i . "_userid"]);
		    }
		}


		// /Verwaltungsmitarbeiter

		// Hausmeister

		$hausmeisterHTML = "";

		for($i = 1; $i <= 5; $i++) {
		    $hausmeisterHTML.= "<tr><td><select style=\"width:100%\" id=\"hausmeister_" . $i . "\" name=\"hausmeister_" . $i . "_userid\" class=\"form-control\">";
		    $hausmeisterHTML.= self::getUserSelect(DB::getSettings()->getValue("schulinfo-hausmeister-" . $i . "-userid"));
		    $hausmeisterHTML.= "</select>";
		    $hausmeisterHTML .= '<script>
                    $(function () {
                      $("#hausmeister_' . $i. '").select2();
                    });


                    </script>';


		    $hausmeisterHTML .= "</td></tr>";

		    if($saveMode) {
		        DB::getSettings()->setValue("schulinfo-hausmeister-" . $i . "-userid", $_POST["hausmeister_" . $i . "_userid"]);
		    }
		}


		// /Hausmeister

		if($saveMode) {
			//Debugger::debugObject($_POST,1);


			for($i = 0; $i < sizeof($fieldsForSave); $i++) {
				DB::getSettings()->setValue($fieldsForSave[$i], $_POST[$fieldsForSave[$i]]);
			}

		}

		if($saveMode) {
			header("Location: $selfURL&saved=1");
			exit(0);
		}

		eval("\$html = \"" . DB::getTPL()->get("schulinfo/admin/index") . "\";");

		return $html;
	}

	private static function getTeacherSelect($selected="") {
		$alleLehrer = lehrer::getAll();

		$select = "<option value=\"\">&nbsp;</option>";
		for($i = 0; $i < sizeof($alleLehrer); $i++) {
			$select .= "<option value=\"" . $alleLehrer[$i]->getAsvID() . "\"" . (($selected == $alleLehrer[$i]->getAsvID() && $alleLehrer[$i]->getAsvID() != "") ? " selected=\"selected\"" : "") . ">" . $alleLehrer[$i]->getName() . ", " . $alleLehrer[$i]->getRufname() . ", " . $alleLehrer[$i]->getAmtsbezeichnung()->getKurzform() . "</option>";
		}

		return $select;
	}

	private static $allUserCache = [];

	private static function getUserSelect($selected = '') {
	    if(sizeof(self::$allUserCache) == 0) self::$allUserCache = user::getAll();

	    $user = self::$allUserCache;

	    $select = "<option value=\"\">&nbsp;</option>";
	    for($i = 0; $i < sizeof($user); $i++) {
	        $select .= "<option value=\"" . $user[$i]->getUserID() . "\"" . (($selected == $user[$i]->getUserID() && $user[$i]->getUserID() != "") ? " selected=\"selected\"" : "") . ">" . $user[$i]->getDisplayNameWithFunction() . "</option>";
	    }

	    return $select;

	}

	/**
	 *
	 * @param user $user
	 */
	public static function isSchulleitung($user) {

	    if($user == null) return false;

		if(!$user->isTeacher()) return false;

		$teacher = $user->getTeacherObject();

		for($i = 1; $i <= 10; $i++) {
			if(DB::getSettings()->getValue("schulinfo-schulleitung-" . $i . "-asvid") == $teacher->getAsvID()) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @return lehrer[]
	 */
	public static function getSchulleitungLehrerObjects() {
	    $lehrer = [];

	    for($i = 1; $i <= 10; $i++) {
	        if(DB::getSettings()->getValue("schulinfo-schulleitung-" . $i . "-asvid") != '') {
	            $l = lehrer::getByASVId(DB::getSettings()->getValue("schulinfo-schulleitung-" . $i . "-asvid"));
	            if($l != null) $lehrer[] = $l;
	        }
	    }

	    return $lehrer;
	}

	/**
	 *
	 * @return user[]
	 */
	public static function getVerwaltungsmitarbeiter() {

	    $lehrer = [];

	    for($i = 1; $i <= 10; $i++) {
	        if(DB::getSettings()->getValue("schulinfo-verwaltungsmitarbeiter-" . $i . "-userid") != '') {
	            $l = user::getUserByID(DB::getSettings()->getValue("schulinfo-verwaltungsmitarbeiter-" . $i . "-userid"));
	            if($l != null) $lehrer[] = $l;
	        }
	    }

	    return $lehrer;
	}

	/**
	 *
	 * @param user $user
	 * @return boolean
	 */
	public static function isVerwaltung($user) {
	    $vws = self::getVerwaltungsmitarbeiter();

	    for($i = 0; $i < sizeof($vws); $i++) {
	        if($vws[$i]->getUserID() == $user->getUserID()) {
	            return true;
	        }
	    }

	    return false;
	}

	/**
	 *
	 * @return user[]
	 */
	public static function getHausmeister() {
	    $lehrer = [];

	    for($i = 1; $i <= 10; $i++) {
	        if(DB::getSettings()->getValue("schulinfo-hausmeister-" . $i . "-userid") != '') {
	            $l = user::getUserByID(DB::getSettings()->getValue("schulinfo-hausmeister-" . $i . "-userid"));
	            if($l != null) $lehrer[] = $l;
	        }
	    }

	    return $lehrer;
	}

	public static function getPersonalratMitarbeiter() {
	    $lehrer = [];

	    for($i = 1; $i <= 10; $i++) {
	        if(DB::getSettings()->getValue("schulinfo-personalrat-" . $i . "-userid") != '') {
	            $l = user::getUserByID(DB::getSettings()->getValue("schulinfo-personalrat-" . $i . "-userid"));
	            if($l != null) $lehrer[] = $l;
	        }
	    }

	    return $lehrer;
	}

	public static function isGymnasium() {
	    return DB::getSettings()->getValue('schulinfo-schultyp') == 'gy';
	}


    public static function isMittelschule() {
        return DB::getSettings()->getValue('schulinfo-schultyp') == 'ms';
    }

	public static function isRealschule() {
        return DB::getSettings()->getValue('schulinfo-schultyp') == 'rs';
    }

    public static function isGrundschule() {
        return DB::getSettings()->getValue('schulinfo-schultyp') == 'rs';
    }


}


?>