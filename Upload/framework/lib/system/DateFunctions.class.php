<?php


/**
 * Stellt allgemein einige Datumsfunktionen zur Verfügung.
 * z.B. kann mit dieser Klasse mit natürlichenn Datumsangaben (TT.MM.JJJJ) umgegangen werden
 * Außerdem können SQL Datumsangaben verwndet werden. (YYYY-MM-DD)
 * @author Christian Spitschka (Spitschka IT Solutions)
 * @version 1.0
 */
class DateFunctions {
	private static $todaySQLDate = "";
	private static $todayNaturalDate = "";
	
	/**
	 * Überprüft, ob $date ein Datum in deutscher Schreibweise dd.mm.YYYY ist.
	 * @param unknown $date
	 */
	public static function isNaturalDate($date) {
		if(strlen($date) != 10) return false;
		$data = explode(".",$date);
		if(sizeof($data) != 3) return false;
		if($data[0] <= 31 && $data[0] >= 1) {
			if($data[1] >= 1 && $data[1] <= 12) {
				if($data[2] >= 1900) {
					return true;
				}
			}
		}

		return false;
	}
	
	/**
	 * Überprüft, ob ein Datum ein SQL Datum ist. (YYYY-MM-DD)
	 * @param String $date
	 * @return boolean
	 */
	public static function isSQLDate($date) {
		if(strlen($date) != 10) return false;
		$data = explode("-",$date);
		if(sizeof($data) != 3) return false;
		if($data[2] <= 31 && $data[2] >= 1) {
			if($data[1] >= 1 && $data[1] <= 12) {
				if($data[0] >= 1900) {
					return true;
				}
			}
		}
		
		return false;
	}
	
	public static function getMySQLDateFromUnixTimeStamp($time) {
	    return date("Y-m-d",$time);
	}

	/**
	 * Erzeugt ein MySQL Date (YYYY-MM-DD) aus einem deutschen Datum. (TT.MM.JJJJ)
	 * @param String $date naturalDate
	 * @return string MySQL Date aus @naturalDate
	 */
	public static function getMySQLDateFromNaturalDate($date) {
		$data = explode(".",$date);

		return $data[2] . "-" . $data[1] . "-" . $data[0];
	}

	/**
	 * Überprüft, ob das angegebene deutsche Datum heute oder später ist.
	 * @param String $date
	 * @return boolean true, falls das Datum heute oder später ist.
	 */
	public static function isNaturalDateTodayOrLater($date) {
		$data = explode(".",$date);

		$timeTest = mktime(0,0,0,$data[1],$data[0],$data[2]);

		$timeToday = mktime(0,0,0,date("m"),date("d"),date("Y"));

		return $timeTest >= $timeToday;
	}

	/**
	 * Überprüft, ob das angegebene MySQL Datum heute oder später ist.
	 * @param String $date
	 * @return boolean true, falls das Datum heute oder später ist.
	 */
	public static function isSQLDateTodayOrLater($date) {
		$data = explode("-",$date);

		$timeTest = mktime(0,0,0,$data[1],$data[2],$data[0]);

		$timeToday = mktime(0,0,0,date("m"),date("d"),date("Y"));

		return $timeTest >= $timeToday;
	}

	/**
	 * Überprüft, ob das angegebene MySQL Datum heute oder früher ist.
	 * @param String $date
	 * @return boolean true, falls das Datum heute oder früher ist.
	 */
	public static function isSQLDateTodayOrEarlier($date) {
		$data = explode("-",$date);

		$timeTest = mktime(0,0,0,$data[1],$data[2],$data[0]);

		$timeToday = mktime(0,0,0,date("m"),date("d"),date("Y"));

		return $timeTest <= $timeToday;
	}
	

	/**
	 * Überprüft, ob das angegebene MySQL Datum heute oder früher ist.
	 * @param String $date
	 * @return boolean true, falls das Datum heute oder früher ist.
	 */
	public static function isSQLDateBeforeToday($date) {
	    $data = explode("-",$date);
	    
	    $timeTest = mktime(0,0,0,$data[1],$data[2],$data[0]);
	    
	    $timeToday = mktime(0,0,0,date("m"),date("d"),date("Y"));
	    
	    return $timeTest < $timeToday;
	}

	/**
	 * Überprüft, ob das Datum $date nach dem $other liegt. (Oder gleich ist)
	 * @param unknown $date
	 * @param unknown $other
	 */
	public static function isNaturalDateAfterAnother($date,$other) {
		$data = explode(".",$date);
		$date = mktime(5,5,5,$data[1],$data[0],$data[2]);

		$data2 = explode(".",$other);
		$other = mktime(5,5,5,$data2[1],$data2[0],$data2[2]);

		return $other <= $date;
	}

	/**
	 * Überprüft, ob das Datum $date nach dem $other liegt. (Oder gleich ist)
	 * @param unknown $date
	 * @param unknown $other
	 */
	public static function isSQLDateAtOrAfterAnother($date,$other) {
		$data = explode("-",$date);
		$date = mktime(0,0,0,$data[1],$data[2],$data[0]);

		$data2 = explode("-",$other);
		$other = mktime(0,0,0,$data2[1],$data2[2],$data2[0]);

		return $other <= $date;
	}

	/**
	 * Überprüft, ob das Datum $date am oder vor dem $other liegt. (Oder gleich ist)
	 * @param unknown $date
	 * @param unknown $other
	 */
	public static function isSQLDateAtOrBeforeAnother($date,$other) {
		$data = explode("-",$date);
		$date = mktime(0,0,0,$data[1],$data[2],$data[0]);

		$data2 = explode("-",$other);
		$other = mktime(0,0,0,$data2[1],$data2[2],$data2[0]);

		return $other >= $date;
	}

	public static function getTodayAsNaturalDate() {
		if(self::$todayNaturalDate == "") self::$todayNaturalDate = date("d.m.Y");
		return self::$todayNaturalDate;
	}

	public static function getTodayAsSQLDate() {
		if(self::$todaySQLDate == "") self::$todaySQLDate = date("Y-m-d");
		return self::$todaySQLDate;
	}

	public static function getNaturalDateFromMySQLDate($date) {
		$data = explode("-",$date);

		return $data[2] . "." . $data[1] . "." . $data[0];
	}

	public static function isTime($time) {
		if(strlen($time) != 5) return false;
		$data = explode(":",$time);
		if(sizeof($data) != 2) return false;

		return ($data[0]*1) <= 24 && ($data[1]*1) <= 60;
	}

	public static function addOneDayToMySqlDate($date) {
	    
	    $time = self::getUnixTimeFromMySQLDate($date);
	    $time += 86400;        // add one Day
	    
	    return date("Y-m-d",$time);
	    
	}
	
	public static function addDaysToMySqlDate($date, $days) {
	    $time = self::getUnixTimeFromMySQLDate($date);
	    $time += 86400 * $days;
	    
	    return date("Y-m-d",$time);
	}
	
	public static function substractOneDayToMySqlDate($date) {
	    $time = self::getUnixTimeFromMySQLDate($date);
	    $time -= 86400;        // add one Day
	    
	    return date("Y-m-d",$time);
	}

    /**
     * @param $mysqlTimestamp
     * @return DateTime|false
     */
	public static function getDateTimeObjectFromMySQLTimestamp($mysqlTimestamp) {
        return DateTime::createFromFormat( "Y-m-d H:i:s", $mysqlTimestamp);
    }

    /**
     * @param DateTime $dateTime
     * @return mixed
     */
    public static function getTimeFromDateTimeObject($dateTime) {
	    return $dateTime->format("H:i");
    }


    /**
     * @param DateTime $dateTime
     * @return mixed
     */
    public static function getDateFromDateTimeObject($dateTime) {
        return $dateTime->format("d.m.Y");
    }

    /**
     * @param DateTime $dateTime
     * @return mixed
     */
    public static function getDateAndTimeFromDateTimeObject($dateTime) {
        return $dateTime->format("d.m.Y H:i");
    }

	/**
	 * Bestimmt den Wochentag anhand eines SQL Datums
	 * @param String $date
	 * @return int Tag der Woche (date("w"))
	 */
	public static function getDayFromMySqlDate($date) {
		$data = explode("-",$date);
		
		$date = mktime(0,0,0,$data[1],$data[2],$data[0]);

		return date("w",$date);
	}

	public static function getMonthFromMySqlDate($date) {
		$data = explode("-",$date);
		$date = mktime(20,20,20,$data[1],$data[2],$data[0]);

		return date("n",$date);
	}

	public static function getWeekDayFromNaturalDate($date) {
        $data = explode(".",$date);

        $time = mktime(10,10,10,$data[1],$data[0],$data[2]);
        
        return date("w",$time);
	}
	
	public static function getWeekDayFromNaturalDateISO($date) {
	    $data = explode(".",$date);
	    
	    $time = mktime(10,10,10,$data[1],$data[0],$data[2]);
	    
	    return date("N",$time);
	}
	
	public static function getWeekDayFromSQLDate($date) {
		
		$date = self::getNaturalDateFromMySQLDate($date);
		
		return self::getWeekDayFromNaturalDate($date);
	}
	
	public static function getWeekDayFromSQLDateISO($date) {
	    
	    $date = self::getNaturalDateFromMySQLDate($date);
	    
	    return self::getWeekDayFromNaturalDateISO($date);
	}
	
	
	public static function isSQLDateWeekEnd($date) {
		$day = self::getWeekDayFromSQLDate($date);
				
		return $day == 0 || $day == 6;
	}
	
	public static function getWeekDayNameFromNaturalDate($date) {
		return functions::getDayName(self::getWeekDayFromNaturalDate($date)-1);
	}
	
	public static function getIcalDateFromSQLDate($sqlDate) {
		$data = explode("-",$sqlDate);
		
		return $data[0] . $data[1] .  $data[2];
	}
	
	public static function getUnixTimeFromMySQLDate($sqlDate) {
		$date = explode("-",$sqlDate);
		return mktime(12,0,0,$date[1],$date[2],$date[0]);
	}

	public static function getDifferenceInDays($firstDateSQL, $secondDateSQL = -1) {
	    if($secondDateSQL < 0) $secondDateSQL = self::getTodayAsSQLDate();
	    
	    list($yearFirstDate, $monthFirstDate, $dayFirstDate) = explode("-",$firstDateSQL);
	    
	    $timeFirstDate = mktime(12,0,0,$monthFirstDate,$dayFirstDate,$yearFirstDate);
	    
	    list($yearSecondDate, $monthSecondDate, $daySecondDate) = explode("-",$secondDateSQL);
	    
	    $timeSecondDate = mktime(12,0,0,$monthSecondDate,$daySecondDate,$yearSecondDate);
	    
	    return round(($timeFirstDate - $timeSecondDate) / 86400);      // Aufrunden
	}

	public static function getMySQLTimeStamp($day = -1, $month = -1, $year = -1, $hour = -1, $minute = -1) {
	    if($day == -1) $day = date("d");
	    else if($day < 10) $day = "0" . ($day*1);

        if($month == -1) $month = date("m");
        else if($month < 10) $month = "0" . ($month*1);

        if($year == -1) $year = date("Y");
        else if($year < 10) $year = "0" . ($year*1);

        if($hour == -1) $hour = date("H");
        else if($hour < 10) $hour = "0" . ($hour*1);

        if($minute == -1) $minute = date("i");
        else if($minute < 10) $minute = "0" . ($minute*1);

        return $year . "-" . $month . "-" . $day . " " . $hour . ":" . $minute . ":00";
    }

}


?>