<?php


/**
 * Stellt Funktionen für Klassen zur Verfügung.
 * NICHT die Repräsentation einer Klasse aus Schülern
 * @author Christian
 *
 */
class grade {

	// Liest alle Klassen in einem Level aus.
	
	public static function getAllGradesAtLevel($level) {
		$grades = DB::getDB()->query("SELECT DISTINCT schuelerKlasse FROM schueler WHERE schuelerKlasse LIKE '" . $level . "%' ORDER BY schuelerKlasse");

		$gradeArray = array();
		while($g = DB::getDB()->fetch_array($grades)) {
			$gradeArray[] = $g['schuelerKlasse'];
		}
		
		return $gradeArray;
	}
	
	public static function getAllGrades() {
		$grades = DB::getDB()->query("SELECT DISTINCT schuelerKlasse FROM schueler				
				ORDER BY LENGTH(schuelerKlasse), schuelerKlasse");
		
		$gradeArray = array();
		while($g = DB::getDB()->fetch_array($grades)) {
			 $gradeArray[] = $g['schuelerKlasse'];
		}
		
		return $gradeArray;
		
	}
	
	/**
	 * Bestimmt die höchste Klassenstufe
	 */
	public static function getMaxGrade() {
		$all = self::getAllGrades();
		
		
		$maxGrade = 0;
		
		for($i = 0; $i < sizeof($all); $i++) {
			$number = [];
			for($c = 0; $c < strlen($all[$i]); $c++) {
				if(functions::isNumber($all[$i][$c])) {
					$number[] = $all[$i][$c];
				}
			}
			
			$number = implode("",$number);
			
			if($number > $maxGrade) $maxGrade = $number;
		}
		
		return $maxGrade;
	}
	
	/**
	 * Bestimmt die niedrigste Klassenstufe
	 */
	public static function getMinGrade() {
		$all = self::getAllGrades();
	
	
		$minGrade = 9999;
	
			for($i = 0; $i < sizeof($all); $i++) {
			$number = [];
			for($c = 0; $c < strlen($all[$i]); $c++) {
				if(functions::isNumber($all[$i][$c])) {
					$number[] = $all[$i][$c];
				}
			}
			
			$number = implode("",$number);
			
			if($number < $minGrade) $minGrade = $number;
		}
	
		return $minGrade;
	}
	
	/**
	 * @deprecated Umstellung stundenplandata
	 * TODO: umstellen auf neuen stundenplan
	 */
	public static function getAllGradesStundenplan() {
		$grades = DB::getDB()->query("SELECT DISTINCT stundeKlasse FROM stundenplan	ORDER BY length(stundeKlasse), stundeKlasse");
	
		$gradeArray = array();
		while($g = DB::getDB()->fetch_array($grades)) {
			if(substr_count($g['stundeKlasse'],"_") == 1 || substr_count($g['stundeKlasse'],"_") == 0) $gradeArray[] = $g['stundeKlasse'];
		}
	
		return $gradeArray;
	
	}

	public static function getMyGradesFromStundenplan() {
	
		$currentStundenplanID = stundenplandata::getCurrentStundenplanID();
		
		if(DB::getSession()->isTeacher()) {
			$lehrer = DB::getSession()->getTeacherObject()->getKuerzel();
			
			$grades = DB::getDB()->query("SELECT DISTINCT stundeKlasse FROM stundenplan_stunden WHERE stundenplanID='$currentStundenplanID' AND stundeLehrer LIKE '" . $lehrer . "%' ORDER BY LENGTH(stundeKlasse), stundeKlasse");
		}
		else {
			$where = "";
			
			if(DB::getSession()->isEltern()) $grades = DB::getSession()->getElternObject()->getKlassenAsArray();
			else if(DB::getSession()->isPupil()) $grades = array(DB::getSession()->getPupilObject()->getKlasse());
			else $grades = array();
			
			for($i = 0; $i < sizeof($grades); $i++) {
				$where .= (($i > 0) ? ("OR ") : ("")) . "stundeKlasse LIKE '" . $grades[$i] . "%' ";
			}
			
			if($where == "") $where = "1";
			
			$grades = DB::getDB()->query("SELECT DISTINCT stundeKlasse FROM stundenplan_stunden WHERE stundenplanID='$currentStundenplanID' AND $where ORDER BY stundeKlasse");
		}
		$gradeArray = array();
		while($g = DB::getDB()->fetch_array($grades)) {
			if($g['stundeKlasse'] != "") $gradeArray[] = $g['stundeKlasse'];
		}
		
		
		return $gradeArray;
	}
	
	public static function getMyGradesWithSubject() {
	
		$currentStundenplanID = stundenplandata::getCurrentStundenplanID();
	
		if(DB::getSession()->isTeacher()) {
			$lehrer = DB::getSession()->getTeacherObject()->getKuerzel();
				
			$grades = DB::getDB()->query("SELECT DISTINCT stundeKlasse, stundeFach FROM stundenplan_stunden WHERE stundenplanID='$currentStundenplanID' AND stundeLehrer LIKE '" . $lehrer . "%' ORDER BY LENGTH(stundeKlasse), stundeKlasse");
		}
		
		$gradeArray = array();
		while($g = DB::getDB()->fetch_array($grades)) {
			if($g['stundeKlasse'] != "") $gradeArray[] = $g;
		}
	
	
		return $gradeArray;
	}
	
	public static function getStundenplanGradeFromNormalGrade($klasse) {

		$currentStundenplanID = stundenplandata::getCurrentStundenplanID();
	
		$grade = DB::getDB()->query_first("SELECT DISTINCT stundeKlasse FROM stundenplan_stunden WHERE stundenplanID='$currentStundenplanID' AND stundeKlasse LIKE '%" . $klasse . "%' ORDER BY stundeKlasse");
		
		if($grade['stundeKlasse'] != "") return $grade['stundeKlasse'];
		else return null;
	}
}


