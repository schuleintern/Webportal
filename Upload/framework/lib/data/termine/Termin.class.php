<?php

/**
 * @deprecated
 * @author Christian
 *
 */
interface Termin {
	/**
	 * Titel des Termins
	 * @return String 
	 */
	public function getTitle();
	
	/**
	 * Datum des Termins
	 * @return String SQL Date des Termins
	 */
	public function getStartDate();
	
	/**
	 * Enddatum des Termins
	 * @return String SQL Date des Termins
	 */
	public function getEndDate();
	
	/**
	 * Ort des Termins
	 * Ort kann auch ein Raum in der Schule sein
	 * @return String Ort
	 */
	public function getLocation();
	
	
	/**
	 * Findet der Termin den ganzen Tag statt?
	 * @return booelan janein
	 */
	public function isWholeDay();
	
	/**
	 * Schulstunden an denen der Termin stattfindet
	 * @return int[] Stunden
	 */
	public function getStunden();
	
	/**
	 * Startzeit des Termins
	 * Bei eingegebenen Stunden sollte der Termin automatisch aus den Einstellungen die Uhrzeit berechnen.
	 * @return String Uhrzeit im Format HH:MM
	 */
	public function getStartTime();
	
	/**
	 * Endzeit des Termins
	 * Bei eingegebenen Stunden sollte der Termin automatisch aus den Einstellungen die Uhrzeit berechnen.
	 * @return String Uhrzeit im Format HH:MM
	 */
	public function getEndTime();
	
	/**
	 * Ist der Termin mehrtätig
	 * @return bool JaNein
	 */
	public function isMultiDay();
	

	/**
	 * Alle Termine an diesem Tag
	 * @param String $date SQL Date
	 * @return Termin[] Termine
	 */
	public static function getAllAtDate($date);
	
	/**
	 * Alle Termine zwischen Date1 und Date2
	 * @param String $date1 SQL Date
	 * @param String $date2 SQL Date
	 * @return Termin[] termine
	 */
	public static function getAllBetweenDays($date1, $date2);
	
	/**
	 * Ist die Instanz ein LNW?
	 * @return bool janein
	 */
	public function isLeistungsnachweis();
	
	/**
	 * Ist die Instanz ein Klassentermin?
	 * @return bool janein
	 */
	public function isKlassentermin();
	
	/**
	 * Ist die Instanz ein Elterntermin?
	 * @return bool janein
	 */
	public function isElterntermin();
	
	/**
	 * Ist die Instanz ein Lehrertermin?
	 * @return bool janein
	 */
	public function isLehrertermin();
	
	/**
	 * Soll der Termin auf der Homepage veröffentlicht werden
	 */
	public function publishToHomepage();
	
	/**
	 * Liest den Ersteller aus
	 * @return String Name des Erstellers
	 */
	public function getCreatorName();
	
	/**
	 * Liest den Zeitpunkt der Erstellung aus
	 * @return int Unix Timestamp der Erstellung
	 */
	public function getCreationTime();
}

