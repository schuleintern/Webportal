<?php

/**
 * Repräsentiert einen Schüler (ASV Datensatz)
 * @author Christian Spitschka, Spitschka IT Solutions
 */
class schueler {
	private static $all = array();
	private static $cachedAllElternUsers = [];

	private $data;

	private $adressen = array();
	private $telefonnummern = array();
	private $emailadressen = [];


	public function __construct($data) {
		$this->data = $data;
	}

	
	/**
	 * 
	 * @return string|unknown
	 */
	public function getKlasse() {
		if(substr($this->data['schuelerKlasse'],0,1) == "0") return substr($this->data['schuelerKlasse'],1);
		else return $this->data['schuelerKlasse'];
	}

	/**
	 * Klasse als String
	 * @return string
	 */
	public function getGrade() {
		return $this->getKlasse();
	}
	
	
	/**
	 * 
	 * @return NULL|klasse
	 */
	public function getKlassenObjekt() {
		return klasse::getByName($this->getKlasse());
	}

	public function getCompleteSchuelerName() {
	    $name = "";
	    if($this->getNamensbestandteilVorgestellt() != "") {
	        $name = $this->getNamensbestandteilVorgestellt() . " ";
        }
		$name .= $this->data['schuelerName'] . ", "  . $this->data['schuelerRufname'];

        if($this->getNamensbestandteilNachgestellt() != "") {
            $name .= " " . $this->getNamensbestandteilNachgestellt();
        }

        return $name;
	}
	
	public function getGeschlecht() {
		return $this->data['schuelerGeschlecht'];
	}

	public function getID() {
		return $this->data['schuelerAsvID'];
	}

	public function getAsvID() {
		return $this->data['schuelerAsvID'];
	}

	public function getGeburtstagAsNaturalDate() {
		return DateFunctions::getNaturalDateFromMySQLDate($this->data['schuelerGeburtsdatum']);
	}
	
	public function getGeburtstagAsSQLDate() {
		return $this->data['schuelerGeburtsdatum'];
	}

	public function getName() {
		return ($this->data['schuelerName']);
	}

	public function getRufname() {
		return ($this->data['schuelerRufname']);
	}
	
	public function getVornamen() {
		return ($this->data['schuelerVornamen']);
	}
	
	public function getBekenntnis() {
		return $this->data['schuelerBekenntnis'];
	}
	
	public function getAusbildungsrichtung() {
		return $this->data['schuelerAusbildungsrichtung'];
	}
	
	public function getGeburtsort() {
		return $this->data['schuelerGeburtsort'];
	}
	
	public function getGeburtsland() {
		return $this->data['schuelerGeburtsland'];
	}
	
	public function getEintrittJahrgangsstufe() {
		return $this->data['schulerEintrittJahrgangsstufe'];
	}

	public function getNamensbestandteilVorgestellt() {
		return $this->data['schuelerNameVorgestellt'];
	}

	public function getNamensbestandteilNachgestellt() {
		return $this->data['schuelerNameNachgestellt'];
	}

	public function getGruppe() {
		return ($this->data['gruppe']);
	}

    /**
     * @return SchuelerFremdsprache[]
     */
	public function getFremdsprachen() {
	    return SchuelerFremdsprache::getForSchueler($this);
    }
	
	public function isGanztags() {
		if ($this->data['schuelerGanztagBetreuung']) {
			return true;
		}
		return false;
	}
	public function getGanztags($action = false) {

		$tage = [];
		if ($this->data['tag_mo']) { $tage[] = 'Mo'; }
		if ($this->data['tag_di']) { $tage[] = 'Di'; }
		if ($this->data['tag_mi']) { $tage[] = 'Mi'; }
		if ($this->data['tag_do']) { $tage[] = 'Do'; }
		if ($this->data['tag_fr']) { $tage[] = 'Fr'; }
		if ($this->data['tag_sa']) { $tage[] = 'Sa'; }
		if ($this->data['tag_so']) { $tage[] = 'So'; }

		$gruppe = [];

		if ( $this->data['gruppe'] /*&& $action == false */) {
			$gruppen_query = DB::getDB()->query("SELECT `name` AS `gruppe_name` FROM ganztags_gruppen WHERE id = ".$this->data['gruppe']." ");
			while($row = mysqli_fetch_array($gruppen_query)) { $gruppe = $row; }
		}


		if ($action == 'print') {
			if ($this->data['tag_mo']) { $this->data['tag_mo'] = 'x'; } else { $this->data['tag_mo'] = ''; }
			if ($this->data['tag_di']) { $this->data['tag_di'] = 'x'; } else { $this->data['tag_di'] = ''; }
			if ($this->data['tag_mi']) { $this->data['tag_mi'] = 'x'; } else { $this->data['tag_mi'] = ''; }
			if ($this->data['tag_do']) { $this->data['tag_do'] = 'x'; } else { $this->data['tag_do'] = ''; }
			if ($this->data['tag_fr']) { $this->data['tag_fr'] = 'x'; } else { $this->data['tag_fr'] = ''; }
			if ($this->data['tag_sa']) { $this->data['tag_sa'] = 'x'; } else { $this->data['tag_sa'] = ''; }
			if ($this->data['tag_so']) { $this->data['tag_so'] = 'x'; } else { $this->data['tag_so'] = ''; }
		} else if ($action == 'html') {
			if ($this->data['tag_mo']) { $this->data['tag_mo'] = '<i class="fa fa-check-circle" style="color:green"></i>'; } else { $this->data['tag_mo'] = ''; }
			if ($this->data['tag_di']) { $this->data['tag_di'] = '<i class="fa fa-check-circle" style="color:green"></i>'; } else { $this->data['tag_di'] = ''; }
			if ($this->data['tag_mi']) { $this->data['tag_mi'] = '<i class="fa fa-check-circle" style="color:green"></i>'; } else { $this->data['tag_mi'] = ''; }
			if ($this->data['tag_do']) { $this->data['tag_do'] = '<i class="fa fa-check-circle" style="color:green"></i>'; } else { $this->data['tag_do'] = ''; }
			if ($this->data['tag_fr']) { $this->data['tag_fr'] = '<i class="fa fa-check-circle" style="color:green"></i>'; } else { $this->data['tag_fr'] = ''; }
			if ($this->data['tag_sa']) { $this->data['tag_sa'] = '<i class="fa fa-check-circle" style="color:green"></i>'; } else { $this->data['tag_sa'] = ''; }
			if ($this->data['tag_so']) { $this->data['tag_so'] = '<i class="fa fa-check-circle" style="color:green"></i>'; } else { $this->data['tag_so'] = ''; }
		}


		return [
			'info' => $this->data['info'],
			'gruppe_id' => $this->data['gruppe'],
			'tage_anz' => count($tage),
			'tage' => implode(', ', $tage),
			'tag_mo' => $this->data['tag_mo'],
            'tag_mo_info' => $this->data['tag_mo_info'],
            'tag_di' => $this->data['tag_di'],
            'tag_di_info' => $this->data['tag_di_info'],
            'tag_mi' => $this->data['tag_mi'],
            'tag_mi_info' => $this->data['tag_mi_info'],
            'tag_do' => $this->data['tag_do'],
            'tag_do_info' => $this->data['tag_do_info'],
            'tag_fr' => $this->data['tag_fr'],
            'tag_fr_info' => $this->data['tag_fr_info'],
            'tag_sa' => $this->data['tag_sa'],
            'tag_sa_info' => $this->data['tag_sa_info'],
            'tag_so' => $this->data['tag_so'],
            'tag_so_info' => $this->data['tag_so_info'],
            'gruppe_name' => $gruppe['gruppe_name']
		];
	}

	public function getNachteilsausgleich() {
	    return SchuelerNachteilsausgleich::getNachteilsausgleichForSchueler($this);
	}
	
	
	public function delete() {
		DB::getDB()->query("DELETE FROM schueler WHERE schuelerAsvID='" . $this->getAsvID() . "'");
		DB::getDB()->query("DELETE FROM eltern_adressen WHERE adresseSchuelerAsvID='" . $this->getAsvID() . "'");	
		DB::getDB()->query("DELETE FROM eltern_email WHERE elternSchuelerAsvID='" . $this->getAsvID() . "'");
	}
	
	public function setFoto($fileUpload) {
		DB::getDB()->query("UPDATE schueler SET schuelerFoto='" . $fileUpload->getID() . "' WHERE schuelerAsvID='" . $this->getAsvID() . "'");
	}

	public function setUserID($userID) {
        DB::getDB()->query("UPDATE schueler SET schuelerUserID='" . $userID . "' WHERE schuelerAsvID='" . $this->getAsvID() . "'");
    }
	
	public function removeFoto() {
		DB::getDB()->query("UPDATE schueler SET schuelerFoto='0' WHERE schuelerAsvID='" . $this->getAsvID() . "'");
	}
	
	/**
	 * 
	 * @return FileUpload|NULL
	 */
	public function getFoto() {
		return FileUpload::getByID($this->data['schuelerFoto']);
	}


	public function getKlassenleitungAsText() {
		$klassen = klasse::getAllKlassen();
		for($i = 0; $i < sizeof($klassen); $i++) {
			if($klassen[$i]->getKlassenName() == $this->getKlasse()) {
				$kl = $klassen[$i]->getKlassenleitung();

				$text =  "";

				for($k = 0; $k < sizeof($kl); $k++) {
					$text .= (($k > 0) ? ", " : "") . $kl[$k]->getKuerzel();
				}

				return $text;
			}
		}


		return "";
	}

	public function isKlassenleitung($lehrerObjekt) {
		$klassen = klasse::getAllKlassen();

		for($i = 0; $i < sizeof($klassen); $i++) {
			if($klassen[$i]->getKlassenName() == $this->getKlasse()) {
				$kl = $klassen[$i]->getKlassenleitung();

				for($k = 0; $k < sizeof($kl); $k++) {
					if($lehrerObjekt->getKuerzel() == $kl[$k]->getKuerzel()) return true;
				}
			}
		}


		return false;
	}


	public function getAlter() {
		$currentDate = DateFunctions::getTodayAsSQLDate();
		list($cJahr, $cMonat, $cTag) = explode("-",$currentDate);
		list($sJahr, $sMonat, $sTag) = explode("-",$this->data['schuelerGeburtsdatum']);

		$alter = $cJahr - $sJahr;

		if($cMonat < $sMonat) return $alter-1;
		else if($cMonat > $sMonat) return $alter;
		else {
			if($cTag < $sTag) return $alter-1;
			else return $alter;
		}

		// UNreachable COde:
		return "Alter unbekannt";
	}

	public function getWohnort() {
		$this->initAdressen();

		for($i = 0; $i < sizeof($this->adressen); $i++) {
			if($this->adressen[$i]->isSchueler()) {
				return $this->adressen[$i]->getOrt();
			}
		}

		return "Wohnort unbekannt";
	}

	/**
	 * @return SchuelerAdresse[]
	 */
	public function getAdressen() {
		$this->initAdressen();

		return $this->adressen;
	}
	
	public function getSchuelerUserID() {
		return $this->data['schuelerUserID'];
	}

	/**
	 * 
	 * @return SchuelerTelefonnummer[]
	 */
	public function getTelefonnummer() {
		$this->initAdressen();

		return $this->telefonnummern;
	}
	
	/**
	 * 
	 * @return SchuelerElternEmail[]
	 */
	public function getElternEMail() {
		$this->initAdressen();
		
		return $this->emailadressen;
	}

	private function initAdressen() {
		include_once("../framework/lib/data/SchuelerAdresse.class.php");
		include_once("../framework/lib/data/SchuelerTelefonnummer.class.php");
		if(sizeof($this->adressen) == 0) {
			$adressen = DB::getDB()->query("SELECT * FROM eltern_adressen WHERE adresseSchuelerAsvID='" . $this->data['schuelerAsvID'] . "' ORDER BY adresseIsHauptansprechpartner DESC");
			while($a = DB::getDB()->fetch_array($adressen)) {
				$this->adressen[] = new SchuelerAdresse($a);
			}

			$telefonnummern = DB::getDB()->query("SELECT * FROM eltern_telefon WHERE schuelerAsvID='" . $this->data['schuelerAsvID'] . "'");
			while($t = DB::getDB()->fetch_array($telefonnummern)) {
				$this->telefonnummern[] = new SchuelerTelefonnummer($t);
			}
			
			$telefonnummern = DB::getDB()->query("SELECT * FROM eltern_email WHERE elternSchuelerAsvID='" . $this->data['schuelerAsvID'] . "'");
			while($t = DB::getDB()->fetch_array($telefonnummern)) {
				$this->emailadressen[] = new SchuelerElternEmail($t);
			}
		}
	}

	/**
	 * @return schueler[] alle Schüler
	 */
	public static function getAll($orderBy='schuelerName, schuelerRufname') {
		if(sizeof(self::$all) == 0) {
			$alle = DB::getDB()->query("SELECT * FROM schueler ORDER BY $orderBy");
			while($s = DB::getDB()->fetch_array($alle)) {
				self::$all[] = new schueler($s);
			}
		}

		return self::$all;
	}
	
	public static function getAnzahlSchueler() {
		$a = DB::getDB()->query_first("SELECT COUNT(schuelerAsvID) AS a FROM schueler WHERE schuelerAustrittDatum IS NULL OR schuelerAustrittDatum > CURDATE()");
		return $a['a'];
	}
	
	public static function getAnzahlWeiblich() {
		$a = DB::getDB()->query_first("SELECT COUNT(schuelerAsvID) AS a FROM schueler WHERE (schuelerAustrittDatum IS NULL OR schuelerAustrittDatum > CURDATE()) AND schuelerGeschlecht='w'");
		return $a['a'];
	}
	
	public static function getAnzahlMaennlich() {
		$a = DB::getDB()->query_first("SELECT COUNT(schuelerAsvID) AS a FROM schueler WHERE (schuelerAustrittDatum IS NULL OR schuelerAustrittDatum > CURDATE()) AND schuelerGeschlecht='m'");
		return $a['a'];
	}

	public function isAusgetreten() {
		if($this->data['schuelerAustrittDatum'] != "") {
			$data = explode("-",$this->data['schuelerAustrittDatum']);
			$timeAustritt = mktime(1,1,1,$data[1],$data[2],$data[0]);
			return $timeAustritt < time();
		}
		else return false;
	}

	public function getAustrittDatumAsMySQLDate() {
		return $this->data['schuelerAustrittDatum'];
	}

	public static function getByAsvID($asvID) {
		
		$all = self::getAll();
		
		for($i = 0; $i < sizeof($all); $i++) {
			if(strtolower($all[$i]->getAsvID()) == strtolower($asvID)) return $all[$i];
		}
		
		return null;

	}
	
	public function getUserName() {
		if($this->data['schuelerUserID'] > 0) {
			$user = DB::getDB()->query_first("SELECT userName FROM users WHERE userID='" . $this->data['schuelerUserID'] . "'");
			if($user['userName'] != "") return $user['userName'];
		}
		
		return null;
	}
	
	public function getUserID() {
		return $this->data['schuelerUserID'];
	}
	
	public function getUser() {
	    return user::getUserByID($this->getUserID());
	}
	
	/**
	 * @return user[] Elternbenutzer zu diesem Schüler
	 */
	public function getParentsUsers() {
		
		if(sizeof(self::$cachedAllElternUsers) == 0) {
			$parents = DB::getDB()->query("SELECT * FROM eltern_email JOIN users ON eltern_email.elternEMail LIKE users.userName");
			
			while($p = DB::getDB()->fetch_array($parents)) {
				if(!is_array(self::$cachedAllElternUsers[$p['elternSchuelerAsvID']])) {
					self::$cachedAllElternUsers[$p['elternSchuelerAsvID']] = array();
				}
				self::$cachedAllElternUsers[$p['elternSchuelerAsvID']][] = new user($p);
			}
		}
		
		if(is_array(self::$cachedAllElternUsers[$this->getAsvID()])) {
			return self::$cachedAllElternUsers[$this->getAsvID()];
		}
		
		return [];
	}

	/**
	 * @return schueler[] alle Ganztags Schüler
	 */
	public function getGanztagsSchueler($orderBy='schueler.schuelerName, schueler.schuelerRufname') {
		if(sizeof(self::$all) == 0) {
			$alle = DB::getDB()->query("SELECT schueler.* , ganztags.* , gruppen.name as gruppenname   FROM schueler
			LEFT JOIN ganztags_schueler AS `ganztags` ON schueler.schuelerAsvID LIKE ganztags.asvid 
			LEFT JOIN ganztags_gruppen AS `gruppen` ON ganztags.gruppe LIKE gruppen.id 

			WHERE schueler.schuelerGanztagBetreuung != 0 ORDER BY $orderBy");
			while($s = DB::getDB()->fetch_array($alle)) {
				self::$all[] = new schueler($s);
			}
		}

		return self::$all;
	}
}

?>