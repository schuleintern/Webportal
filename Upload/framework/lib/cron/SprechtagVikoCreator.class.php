<?php 


class SprechtagVikoCreator extends AbstractCron {

    private $createdVikos = 0;
	
	public function __construct() {
		
	}
	
	public function execute() {
	    // Benutzername != Kürzel --> FQDN

        if(!Office365Meetings::isActiveForTeacher()) return;


        $sprechtage = [];

        $sprechtageSQL = DB::getDB()->query("SELECT * FROM sprechtag WHERE sprechtagIsActive=1 AND sprechtagIsOnline=1 AND sprechTagBuchbarBis < CURRENT_DATE()");

        while($s = DB::getDB()->fetch_array($sprechtageSQL)) {
            $sprechtage[] = $s;
        }


        for($s = 0; $s < sizeof($sprechtage); $s++) {

            $datum = $sprechtage[$s]['sprechtagDate'];

            $slotsOhneViko = [];
            $slotsOhneVikoSQL = DB::getDB()->query("SELECT * FROM sprechtag_buchungen NATURAL JOIN sprechtag_slots WHERE sprechtagID='" . $sprechtage[$s]['sprechtagID'] . "' AND schuelerAsvID!='' AND meetingURL IS null");

            while($sl = DB::getDB()->fetch_array($slotsOhneVikoSQL)) $slotsOhneViko[] = $sl;

            $created = 0;

            for($i = 0; $i < sizeof($slotsOhneViko); $i++) {

                $schueler = schueler::getByAsvID($slotsOhneViko[$i]['schuelerAsvID']);
                if($schueler != null) {
                    $lehrer = lehrer::getByKuerzel($slotsOhneViko[$i]['lehrerKuerzel']);
                    if($lehrer != null) {
                        $user = $lehrer->getUser();
                        if($user != null) {
                            // 2017-04-15T12:00:00

                            $dateTimeStart = $datum . "T" . date("H:i", $slotsOhneViko[$i]['slotStart']);
                            $dateTimeEnd = $datum . "T" . date("H:i", $slotsOhneViko[$i]['slotEnde']);

                            $subject = "Sprechtag: " . $schueler->getCompleteSchuelerName() . " (Klasse " . $schueler->getKlasse() . ")";

                            $url = Office365Api::createMeeting(
                                $lehrer->getUser()->getUserName(),
                                $dateTimeStart,
                                $dateTimeEnd,
                                $subject,
                                ""
                            );

                            if($url !== null) {
                                DB::getDB()->query("UPDATE sprechtag_buchungen SET meetingUrl='" . DB::getDB()->escapeString($url) . "' WHERE buchungID='" . $slotsOhneViko[$i]['buchungID'] . "'");
                            }

                            $created++;
                            $this->createdVikos++;

                            if($created == 50) break;

                        }
                    }
                }



            }



        }



	}
	
	public function getName() {
		return "Videokonferenztermine für den Elternsprechtag anlegen";
	}
	
	public function getDescription() {
		return "";
	}
	
	/**
	 *
	 *
	 * @return ['success' => 'true/false', 'resultText' => 'Text, der in der Administration angezeigt wird.']
	 */
	public function getCronResult() {
		return ['success' => 1, 'resultText' => 'Erstellt: ' . $this->createdVikos];
	}
	
	public function informAdminIfFail() {
		return false;
	}
	
	public function executeEveryXSeconds() {
	    return 180;		// Alle 2 Wochen ausführen.
	}
}



?>