<?php 

class stundenplandata {
	
	private static $currentPlanID = -1;
	private static $currentStundenplanObject = NULL;
	
	private $stundenplanID = 0;
	private $validFrom = "";
	private $validTill = "";
	private $stundenplanData = [];
	private $stundenplanName = "";
	
	private $aufsichten = [];
	
	public function __construct($id) {
		$data = DB::getDB()->query_first("SELECT * FROM stundenplan_plaene WHERE stundenplanID='" . $id . "'");
		$this->stundenplanID = $id;
		$this->stundenplanName = $data['stundenplanName'];
		
		$dateFrom = explode("-",$data['stundenplanAb']);
		$dateTill = explode("-",$data['stundenplanBis']);
		
		$this->validFrom = mktime(0,0,0,$dateFrom[1],$dateFrom[2],$dateFrom[0]);
		if($data['stundenplanBis'] != "") $this->validTill = mktime(23,59,59,$dateTill[1],$dateTill[2],$dateTill[0]);
		
		for($i = 0; $i < 7; $i++) {
			$this->stundenplanData[] = array();
			for($s = 0; $s < 11; $s++) {
				$this->stundenplanData[$i][] = array();
			}
		}
				
		$stundenData = DB::getDB()->query("SELECT * FROM stundenplan_stunden WHERE stundenplanID='" . $this->stundenplanID . "'");
		while($s = DB::getDB()->fetch_array($stundenData)) {
			$stunde = array(
					"teacher" => $s['stundeLehrer'],
					"room" => $s['stundeRaum'],
					"grade" => $s['stundeKlasse'],
					"subject" => $s['stundeFach']
			);
			
			$this->stundenplanData[$s['stundeTag']-1][$s['stundeStunde']-1][] = $stunde;
		}
		
		// $this->aufsichten = Aufsicht::getForStundenplan($this);
	}
	
	public function getID() {
	    return $this->stundenplanID;
	}
	
	public function getName() {
		return $this->stundenplanName;
	}
	
	/**
	 * Aktuellen Stundenplan auslesen
	 * @return stundenplandata
	 */
	public static function getCurrentStundenplan() {
		self::loadCurrentStundenplan();
		return self::$currentStundenplanObject;
	}
	
	public static function getCurrentStundenplanID() {
		self::loadCurrentStundenplan();
		return self::$currentPlanID;
	}
	
	
	public function getStundenAnzahlForTeacher($kuerzel) {
		$stundenData = DB::getDB()->query("SELECT * FROM stundenplan_stunden WHERE stundenplanID='" . $this->stundenplanID . "' AND stundeLehrer LIKE '" . $kuerzel . "'");
		
		return DB::getDB()->num_rows($stundenData);
	}
	
	private $planCache = [];
	
	/**
	 * Liest einen Tag aus.
	 * @param unknown $filter array("teacher","spi");
	 * @return array(array(stunden));
	 */
	public function getPlan($filter=array()) {
		$plan = array();
		
		for($i = 0; $i < 7; $i++) {
			$plan[] = array();
			for($s = 0; $s < stundenplandata::getMaxStunden(); $s++) {
				$plan[$i][] = array();
			}
		}
			
		if(sizeof($filter) > 0) {
			$sql = "";
			switch($filter[0]) {
				case "teacher":
					$sql = "stundeLehrer = '" . $filter[1] . "'";
				break;
				
				case "room":
					$sql = "stundeRaum LIKE '" . $filter[1] . "'";
				break;
					
				case "subject":
					$sql = "stundeFach LIKE '" . $filter[1] . "'";
				break;
				
				case 'gradeStrict':
				    $sql = "stundeKlasse = '" . $filter[1] . "'";
				break;
				
				case "grade":
					if(substr((string)$filter[1],-1) != "%") $filter[1] .= "%";
					$sql = "stundeKlasse LIKE '" . $filter[1] . "'";
				break;
			}
			
			if(is_array($this->planCache[$sql])) return $this->planCache[$sql];
			
			$stundenData = DB::getDB()->query("SELECT * FROM stundenplan_stunden WHERE stundenplanID='" . $this->stundenplanID . "' AND $sql");
			while($s = DB::getDB()->fetch_array($stundenData)) {
				$stunde = array(
						"teacher" => $s['stundeLehrer'],
						"room" => $s['stundeRaum'],
						"grade" => $s['stundeKlasse'],
						"subject" => $s['stundeFach']
				);
					
				$plan[$s['stundeTag']-1][$s['stundeStunde']-1][] = $stunde;
			}
			
			$this->planCache[$sql] = $plan;
			
			return $plan;
		}
		else return $plan;
	}
	
	/**
	 * Ermittelt die Stunden, die zu einem Zeitpunkt für einen Lehrer stattfinden.
	 * @param int $day Tag (1-7)
	 * @param int $stunde Stunde (1-X)
	 * @param String $teacher Lehrerkürzel
	 */
	public function getStundenAtDayAndStundeForTeacher($day, $stunde, $teacher) {
		$stunden = [];
		$stundenData = DB::getDB()->query("SELECT * FROM stundenplan_stunden WHERE stundenplanID='" . $this->stundenplanID . "' AND stundeLehrer LIKE '" . $teacher . "%' AND stundeTag='" . $day . "' AND stundeStunde='" . $stunde . "'");;
		while($s = DB::getDB()->fetch_array($stundenData)) {
			$stunden[] = array(
					"teacher" => $s['stundeLehrer'],
					"room" => $s['stundeRaum'],
					"grade" => $s['stundeKlasse'],
					"subject" => $s['stundeFach']
			);
		}
		
		return $stunden;
	}
	
	public function getAll($filter) {
		$plan = $this->stundenplanData;
		
		$result = array();
		
		
		$addOrder = "";
		
		switch($filter) {
			case "room":
				$sql = "stundeRaum";
			break;
			
			case "subject":
				$sql = "stundeFach";
			break;
			
			case "teacher":
				$sql = "stundeLehrer";
			break;
			
			case "grade":
				$sql = "stundeKlasse";
				$addOrder = "LENGTH($sql), ";
			break;
		}
		
		$all = DB::getDB()->query("SELECT DISTINCT $sql FROM stundenplan_stunden WHERE stundenplanID='" . $this->stundenplanID . "' ORDER BY $addOrder $sql ASC");
		
		while($a = DB::getDB()->fetch_array($all)) {
			if($a[$sql] != "") $result[] = $a[$sql];
		}
		
		return $result;
		
	}	

	/**
	 * Liest die Lehrer einer Klasse aus.
	 * @param String $grade Klasse
	 * @return String[] String[Fach] = Lehrer
	 */
	public function getAllTeacherOfGrade($grade) {
	
		$result = array();
	
	
		$addOrder = "";
	
		$all = DB::getDB()->query("SELECT DISTINCT stundeLehrer, stundeFach FROM stundenplan_stunden WHERE stundenplanID='" . $this->stundenplanID . "' AND stundeKlasse LIKE '" . DB::getDB()->escapeString($grade) . "' ORDER BY stundeFach ASC");
	
		while($a = DB::getDB()->fetch_array($all)) {
			if($a['stundeLehrer'] != "") $result[$a['stundeFach']] = $a['stundeLehrer'];
		}
	
		return $result;
	
	}
	
	/**
	 * 
	 * @param String $fach
	 * @return String[]
	 */
	public function getAllTeachersOfSubject($fach) {
		
		$result = array();
		
			
		$all = DB::getDB()->query("SELECT DISTINCT stundeLehrer FROM stundenplan_stunden WHERE stundenplanID='" . $this->stundenplanID . "' AND stundeFach LIKE '" . DB::getDB()->escapeString($fach) . "' ORDER BY stundeLehrer ASC");
		
		while($a = DB::getDB()->fetch_array($all)) {
			if($a['stundeLehrer'] != "") $result[] = $a['stundeLehrer'];
		}
		
		return $result;
		
	}
	
	public function getAllGradesForTeacher($teacherkuerzel) {
		
		$teacher = $teacherkuerzel;
		
		$all = DB::getDB()->query("SELECT DISTINCT stundeKlasse FROM stundenplan_stunden WHERE stundenplanID='" . $this->stundenplanID . "' AND stundeLehrer LIKE '"  . $teacher . "%' ORDER BY length(stundeKlasse), stundeKlasse ASC");
	
		$result = array();
	
		while($a = DB::getDB()->fetch_array($all)) {
			if($a['stundeKlasse'] != "")
			$result[] = $a['stundeKlasse'];
		}

		
		return $result;
	}
	
	
	public function getAllSubjectsForTeacher($teacherkuerzel) {
	    
	    $teacher = $teacherkuerzel;
	    
	    $all = DB::getDB()->query("SELECT DISTINCT stundeFach FROM stundenplan_stunden WHERE stundenplanID='" . $this->stundenplanID . "' AND stundeLehrer LIKE '"  . $teacher . "%' ORDER BY stundeFach ASC");
	    
	    $result = array();
	    
	    while($a = DB::getDB()->fetch_array($all)) {
	        if($a['stundeFach'] != "")
	            $result[] = $a['stundeFach'];
	    }
	    
	    
	    return $result;
	}
	
	

	public function getAllGradesAtLevel($level) {
		$all = DB::getDB()->query("SELECT DISTINCT stundeKlasse FROM stundenplan_stunden WHERE stundenplanID='" . $this->stundenplanID . "' AND stundeKlasse LIKE '%"  . $level . "%' ORDER BY length(stundeKlasse), stundeKlasse ASC");
	
		$result = array();
	
		while($a = DB::getDB()->fetch_array($all)) {
			if($a['stundeKlasse'] != "")
				$result[] = $a['stundeKlasse'];
		}
	
		return $result;
	}
	
	public function getAllMyPossibleGrades($grade) {
		$all = DB::getDB()->query("SELECT DISTINCT stundeKlasse FROM stundenplan_stunden WHERE stundenplanID='" . $this->stundenplanID . "' AND stundeKlasse LIKE '%"  . $grade . "%' ORDER BY length(stundeKlasse), stundeKlasse ASC");
		
		$result = array();
		
		while($a = DB::getDB()->fetch_array($all)) {
			$result[] = $a['stundeKlasse'];
		}
				
		return $result;
	}
	
	/**
	 * 
	 * @param String $lehrerkuerzel
	 * @param scehueler $schuelerklasse
	 * @return String[]
	 */
	public function getFachForTeacherAndSchueler($lehrerkuerzel, $schuelerklasse) {
		$faecher = DB::getDB()->query("SELECT DISTINCT stundeFach FROM stundenplan_stunden WHERE stundenplanID='" . $this->stundenplanID . "' AND stundeLehrer LIKE '" . $lehrerkuerzel . "' AND stundeKlasse LIKE '%" . $schuelerklasse . "%'");
		
		$ergebnis = array();
		while($f = DB::getDB()->fetch_array($faecher)) $ergebnis[] = $f['stundeFach'];
		
		return $ergebnis;
	}
	
	private static function loadCurrentStundenplan() {
		if(self::$currentPlanID < 0) {
			$currentPlanID = DB::getDB()->query_first("SELECT stundenplanID FROM stundenplan_plaene WHERE stundenplanIsDeleted=0 AND stundenplanAb <= CURDATE() AND (stundenplanBis = '0000-00-00' OR stundenplanBis IS NULL OR stundenplanBis >= CURDATE()) ORDER BY stundenplanID DESC");
			if($currentPlanID['stundenplanID'] > 0) {
				self::$currentPlanID = $currentPlanID['stundenplanID'];
				
				self::$currentStundenplanObject = new stundenplandata(self::$currentPlanID);
			}
		}
	}
	
	// Prüft, ob die StundenplanID gültig ist.
	public static function isValidStundenplanID($id) {
		if($id == self::getCurrentStundenplanID()) return true;
		
		$plan = DB::getDB()->query_first("SELECT * FROM stundenplan_plaene WHERE stundenplanID='" . addslashes($id) . "' AND stundenplanAb >= CURDATE() AND stundenplanIsDeleted=0");
		
		return ($plan['stundenplanID'] > 0) ? true : false;
	}
	
	public static function getAllCurrentPlans() {
		// Alle Pläne, die ab sofort gültig sind.
		
		
		$plaene = DB::getDB()->query("SELECT * FROM stundenplan_plaene WHERE stundenplanIsDeleted=0 AND (stundenplanAb >= CURDATE() OR stundenplanID='" . self::getCurrentStundenplanID() . "')");
		$planArray = array();
		
		while($p = DB::getDB()->fetch_array($plaene)) {
			$planArray[] = $p;
		}
		
		return $planArray;
	}
	
	public static function getFuturePlaene() {
		$plaene = DB::getDB()->query("SELECT * FROM stundenplan_plaene WHERE stundenplanIsDeleted=0 AND (stundenplanAb > CURDATE())");
		$planArray = array();
		
		while($p = DB::getDB()->fetch_array($plaene)) {
			$planArray[] = $p;
		}
		
		return $planArray;
	}
	
	public static function getStundenplanAtDate($sqldate, $ignoreFail = false) {
		$date = $sqldate;
		$planID = DB::getDB()->query_first("SELECT stundenplanID FROM stundenplan_plaene WHERE stundenplanIsDeleted=0 AND (stundenplanAb <= '" . $date . "' AND (stundenplanBis >= '" . $date . "' OR stundenplanBis IS NULL))");
		if($planID['stundenplanID'] > 0) {
			return new stundenplandata($planID['stundenplanID']);
		}
		
		if(!$ignoreFail) {
			DB::showError("Es kann kein Stundenplan für das Datum \"" . functions::getFormatedDateFromSQLDate($date) . "\" geladen werden.");
			die();
		}
		
		return null;
	}
	
	private static $planCacheStundenplan = [];
	
	/**
	 * 
	 * @param unknown $sqldate
	 * @return stundenplandata|NULL
	 */
	public static function getStundenplanAtDateCached($sqldate) {
	    // if(is_object(self::$planCacheStundenplan[$sqldate])) return self::$planCacheStundenplan[$sqldate];
	    
	    $date = $sqldate;
	    die("SELECT stundenplanID FROM stundenplan_plaene WHERE stundenplanIsDeleted=0 AND (stundenplanAb <= '" . $date . "' AND (stundenplanBis >= '" . $date . "' OR stundenplanBis IS NULL)))");
	    $planID = DB::getDB()->query_first("SELECT stundenplanID FROM stundenplan_plaene WHERE stundenplanIsDeleted=0 AND (stundenplanAb <= '" . $date . "' AND (stundenplanBis >= '" . $date . "' OR stundenplanBis IS NULL))");
	    if($planID['stundenplanID'] > 0) {
	        self::$planCacheStundenplan[$sqldate] = new stundenplandata($planID['stundenplanID']);
	        return self::$planCacheStundenplan[$sqldate];
	    }
	    else {
	        
	        self::$planCacheStundenplan[$sqldate] = null;
	        return null;
	    }
	}
	
	
	public function getStundenplanGradesFromNormalGrades($grades) {		
		for($i = 0; $i < sizeof($grades); $i++) {
			$where .= (($i > 0) ? ("OR ") : ("")) . "stundeKlasse LIKE '" . $grades[$i] . "%' ";
		}
				
		if($where == "") $where = "1";
				
		$grades = DB::getDB()->query("SELECT DISTINCT stundeKlasse FROM stundenplan_stunden WHERE stundenplanID='" . $this->getStundenplanID() . "' AND $where ORDER BY length(stundeKlasse) ASC, stundeKlasse ASC");
		
		$gradeArray = array();
		while($g = DB::getDB()->fetch_array($grades)) {
			if($g['stundeKlasse'] != "") $gradeArray[] = $g['stundeKlasse'];
		}
		
		
		return $gradeArray;
	}

	
	public function getStundenplanID() {
		return $this->stundenplanID;
	}
	
	public static function isNormalFach($fach) {
		$alleNormalenFaecher = array(
				"B",
				"C",
				"D",
				"E",
				"BwR",
				"Ek",
				"Eth",
				"Ev",
				"F",
				"G",
				"HE",
				"IT",
				"IT_1",
				"IT_T",
				"IT_2",
				"K",
				"Ku",
				"M",
				"Mu",
				"Ph",
				"Sk",
				"Ske",
				"Sm",
				"Sw",
				"Tv",
				"We_1",
				"We_2",
				"WR"
		);
		
		return in_array($fach, $alleNormalenFaecher);
	}
	
	public static function getMaxStunden() {
		return DB::getSettings()->getValue("stundenplan-anzahlstunden");
	}
	
    /**
     * @param int $day_of_week The number of the weekday to query (1: Monday, 5: Friday)
     * @param int[] $periods The numbers of the periods to query (1: first period)
     * @param bool $continuous Whether periods that directly follow a previous period should be merged (true: do merge)
     * @return array<array{start: DateInterval, end: DateInterval}>|null
     */
    public static function getTimesForWeekdayAndPeriods($day_of_week, $periods, $continuous=false) {
        if (empty($periods)) { return null; }

        // Ensure that $periods is sorted in an ascending order.
        // Since the array isn't passed by reference, we don't need to create a copy first.
        sort($periods, SORT_NUMERIC);

        $times = array();
        $lastPeriod = null;
        foreach ($periods as $period) {
            if (!$continuous) {
                $times[] = static::getTimesForWeekdayAndPeriod($day_of_week, $period);
            } elseif ($lastPeriod === null) {
                $times[] = array("start" => static::getTimesForWeekdayAndPeriod($day_of_week, $period)["start"]);
            } elseif ($period != $lastPeriod + 1) {
                end($times)["end"] = stundenplandata::getTimesForWeekdayAndPeriod($day_of_week, $lastPeriod)["end"];
                $times[] = array("start" => stundenplandata::getTimesForWeekdayAndPeriod($day_of_week, $period)["start"]);
            }
            $lastPeriod = $period;
        }

        if ($continuous && $lastPeriod !== null) {
            end($times)["end"] = stundenplandata::getTimesForWeekdayAndPeriod($day_of_week, $lastPeriod)["end"];
        }

        return $times;
    }

    /**
     * Returns the start and end time of a specific period on the specified day of week.
     *
     * The array contains two array keys, "start" and "end", which map to the start and end times of the queried period, respectively.
     *
     * Returns null if no data is available for the specified combination of weekday and period.
     *
     * @param int $day_of_week The number of the weekday to query (1: Monday, 5: Friday)
     * @param int $period The number of the period to query (1: first period)
     * @return array{start: DateInterval, end: DateInterval}|null
     */
    public static function getTimesForWeekdayAndPeriod($day_of_week, $period) {
        $settings = DB::getSettings();
        $start = $settings->getValue("stundenplan-stunde-$day_of_week-$period-start");
        $end = $settings->getValue("stundenplan-stunde-$day_of_week-$period-ende");
        if ($start === null || $end === null) { return null; }

        $today = new DateTimeImmutable("today");  # This is the current date time at 00:00h.
        return array(
            "start" => (new DateTimeImmutable($start))->diff($today),
            "end" => (new DateTimeImmutable($end))->diff($today)
        );
    }

    /**
     * Returns the start and end times of all periods on the specified day of week.
     *
     * The top-level array maps the number of the period (1: first period) to another array containing the start and end times of that period.
     *
     * @param int $day_of_week The number of the week day to query (1: Monday, 5: Friday)
     * @return array<array{start: DateInterval, end: DateInterval}|null>
     */
    public static function getTimesForWeekday($day_of_week) {
        $settings = DB::getSettings();
        $periodsCount = $settings->getValue("stundenplan-anzahlstunden");

        if ($settings->getValue("stundenplan-everydayothertimes") <= 0) {
            // If the timetable uses the same times every day, just query the times for Monday.
            $day_of_week = 1;
        }

        $times_weekday = array();
        for ($period = 1; $period < $periodsCount; $period++) {
            $times_weekday[$period] = static::getTimesForWeekdayAndPeriod($day_of_week, $period);
        }
        return $times_weekday;
    }

    /**
     * Returns the start and end times of all periods on the timetable.
     *
     * The top-level array maps the number of the week day (1: Monday, 5: Friday) to another array, which maps the number of the period (1: first period) to a third array containing the start and end times of that period.
     *
     * @return array<array<array{start: DateInterval, end: DateInterval}|null>>
     */
    public static function getTimes() {
        $times = array();
        for ($day_of_week = 1; $day_of_week < 6; $day_of_week++) {
            $times[$day_of_week] = static::getTimesForWeekday($day_of_week);
        }
        return $times;
    }
}


?>
