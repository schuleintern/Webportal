<?php 


/**
 * Sendet die Elternmailnachrichten
 * @author Christian
 *
 */

class LehrerkalenderIcalDownloader extends AbstractCron {
	
	private $termine = 0;
	private $success = true;
	private $message = '';
	
	public function __construct() {
		
	}
	
	public function execute() {
		if(DB::getSettings()->getValue('lehrerkalender_ical') != '') {
			$feedData = file_get_contents(DB::getSettings()->getValue('lehrerkalender_ical'));
			
			if($feedData === false) {
				$this->success = false;
				$this->message = 'iCal feed nicht verfügbar oder falsch: ' . DB::getSettings()->getValue('lehrerkalender_ical');
			}
			
					
			$icalobj = new ZCiCal($feedData);
			
			
			/**
			 *
			 *
			 Event 214:
			 DESCRIPTION: Zeiteinteilung “Kurzstunden”
			 1.              7:50 – 8:20
			 2.              8:20 – 8:50
			 3.              8:50 – 9:20
			 Pause   9:20 – 9:40
			 4.              9:40 – 10:10
			 5.              10:10 – 10:40
			 6.              10:40 – 11:10
			 Nachmittagsunterricht entfällt
			 
			 
			 
			 UID: 040000008200E00074C5B7101A82E00800000000D0A1445B4EEED2010000000000000000100000007CA17A483D26C44EB358B2A6AC300C0D
			 SUMMARY: Kurzstunden
			 DTSTART: 20171222
			 DTEND: 20171223
			 CLASS: PUBLIC
			 PRIORITY: 5
			 DTSTAMP: 20170803T195602Z
			 TRANSP: TRANSPARENT
			 STATUS: CONFIRMED
			 SEQUENCE: 0
			 LOCATION:
			 X-MICROSOFT-CDO-APPT-SEQUENCE: 0
			 X-MICROSOFT-CDO-BUSYSTATUS: FREE
			 X-MICROSOFT-CDO-INTENDEDSTATUS: BUSY
			 X-MICROSOFT-CDO-ALLDAYEVENT: TRUE
			 X-MICROSOFT-CDO-IMPORTANCE: 1
			 X-MICROSOFT-CDO-INSTTYPE: 0
			 X-MICROSOFT-DISALLOW-COUNTER: FALSE
			 
			 
			 */
			
			if(isset($icalobj->tree->child))
			{
				foreach($icalobj->tree->child as $node)
				{
					if($node->getName() == "VEVENT")		// Nur Events importieren.
					{
						$ecount++;
						echo "Event $ecount:\n";
						foreach($node->data as $key => $value)
						{
							if(is_array($value))
							{
								for($i = 0; $i < count($value); $i++)
								{
									$p = $value[$i]->getParameters();
									echo "  $key: " . $value[$i]->getValues() . "\n";
								}
							}
							else
							{
								echo "  $key: " . $value->getValues() . "\n";
							}
						}
					}
				}
			}
		}
	}
	
	public function getName() {
		return "Lerherkalender iCAL Download";
	}
	
	public function getDescription() {
		return "Lädt das iCal Feed für den Lehrerkalender herunter und importiert die Lehrertermine.";
	}
	
	/**
	 *
	 *
	 * @return ['success' => 'true/false', 'resultText' => 'Text, der in der Administration angezeigt wird.']
	 */
	public function getCronResult() {
		if(DB::getSettings()->getValue('lehrerkalender_ical') == '') {
			return ['success' => true, 'resultText' => 'Keine Aktion durchgeführt, da kein iCal Download aktiviert ist.'];
		}
		else {
			return ['success' => true, 'resultText' => $this->termine . ' importiert.'];
		}
	}
	
	public function informAdminIfFail() {
		return false;
	}
	
	public function executeEveryXSeconds() {
		return 900;		// Alle 15 Minuten
	}
}



?>