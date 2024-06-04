<?php



class extSchulinfoDefault extends AbstractPage {
	
	public static function getSiteDisplayName() {
		return 'Schulinformationen';
	}

	public function __construct($request = [], $extension = []) {
		parent::__construct(array( self::getSiteDisplayName() ), false, false, false, $request, $extension);
		$this->checkLogin();
	}


	public function execute() {

		//$this->getRequest();
		//$this->getAcl();

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

        $schuelerAnzahl = schueler::getAnzahlSchueler() . "<br />davon " . schueler::getAnzahlWeiblich() . " weiblich und " . schueler::getAnzahlMaennlich() . " männlich";

        $klassenAnzahl = klasse::getAnzahlKlassen();

        $schuelerProKlasse = floor($schuelerAnzahl / $klassenAnzahl);



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

        // Personalrat
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
                    //$fachlehrer = $faecher[$i]->getFachLehrer();
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


        $this->render([
			"tmpl" => "default",
            "vars" => [
                "schulinfo_name" => DB::getSettings()->getValue("schulinfo-name"),
                "schulinfo_name_zusatz" => DB::getSettings()->getValue("schulinfo-name-zusatz"),
                "schulinfo_adresse1" => DB::getSettings()->getValue("schulinfo-adresse1"),
                "schulinfo_adresse2" => DB::getSettings()->getValue("schulinfo-adresse2"),
                "schulinfo_plz" => DB::getSettings()->getValue("schulinfo-plz"),
                "schulinfo_ort" => DB::getSettings()->getValue("schulinfo-ort"),
                "schulinfo_telefon" => DB::getSettings()->getValue("schulinfo-telefon"),
                "schulinfo_fax" => DB::getSettings()->getValue("schulinfo-fax"),
                "schulinfo_email" => DB::getSettings()->getValue("schulinfo-email"),
                "schulinfo_homepage" => DB::getSettings()->getValue("schulinfo-homepage"),

                "schulinfo_schulleiter_show" => DB::getSettings()->getValue("schulinfo-schulleiter-show"),
                "schulleitungHTML" => $schulleitungHTML,

                "schuelerAnzahl" => $schuelerAnzahl,
                "klassenAnzahl" => $klassenAnzahl,
                "schuelerProKlasse" => $schuelerProKlasse,

                "schulinfo_lehrer_show" => DB::getSettings()->getBoolean("schulinfo-lehrer-show"),
                "lehrerHTML" => $lehrerHTML,

                "schulinfo_personalrat_show" => DB::getSettings()->getBoolean("schulinfo-personalrat-show"),
                "personalratHTML" => $personalratHTML,

                "schulinfo_faecher_show" => DB::getSettings()->getBoolean("schulinfo-faecher-show"),
                "schulinfo_fachlehrer_show" => DB::getSettings()->getBoolean("schulinfo-fachlehrer-show"),
                "schulinfo_fachbetreuer_show" => DB::getSettings()->getBoolean("schulinfo-fachbetreuer-show"),
                "facherHTML" => $facherHTML
            ]
		]);

	}


}
