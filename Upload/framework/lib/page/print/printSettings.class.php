<?php


class printSettings extends AbstractPage {	
	public function __construct() {
		parent::__construct(array(""));
	}


	public function execute() {

		if($_REQUEST['action'] == 'GetPrintHeader') {
			$image = DB::getSettings()->getUpload('print-header');

			if($image != null) {
				$image->sendFile();
				exit(0);
			} else {
				header("Location: /cssjs/images/Briefkopf.jpg");
				exit(0);
			}
		}

	  new errorPage();
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
                'name' => 'print-header',
                'typ' => 'BILD',
                'titel' => 'Briefkopf',
                'text' => 'Briefkopf auf den Ausdrucken. Format: 1240x130px'
            ],
			[
				'name' => 'print-absender-kuvert',
				'typ' => 'ZEILE',
				'titel' => 'Absenderadresse im Fenster des Fensterkuverts',
				'text' => 'Geben Sie hier die Absenderadresse im Fensterkuvert an. Verwenden Sie einen senkrechten Strich (|), um Name Strasse und PLZ/Ort zu trennen.'
			],
			[
				'name' => 'print-fusszeile-kuvert',
				'typ' => 'HTML',
				'titel' => 'Fußzeile im Fensterkuvertausdruck',
				'text' => 'Geben Sie hier die Fußzeile im Fensterkuvertausdruck an. Der Text wird zentriert angezeigt. Der Seitenrand wird automatisch reduziert.'
			],
			[
				'name' => 'print-rechte-seite',
				'typ' => 'HTML',
				'titel' => 'Rechte Seite im Fensterkuvertausdruck',
				'text' => 'Geben Sie hier die rechte Seite (Kontaktinfos) im Fensterkuvertausdruck an. Der Text wird rechtsbündig angezeigt.'
			]
		];
	}
	
	
	public static function getSiteDisplayName() {
		return 'Druckeinstellungen';
	}
	
	/**
	 * Liest alle Nutzergruppen aus, die diese Seite verwendet. (Für die Benutzeradministration)
	 * @return array(array('groupName' => '', 'beschreibung' => ''))
	 */
	public static function getUserGroups() {
		return [];
	}
	
	public static function getAdminGroup() {
		return 'Webportal_PrintSettings_Admin';
	}
	
	public static function hasAdmin() {
		return true;
	}
	
	public static function getAdminMenuIcon() {
		return 'fa fa-print';
	}
	
	public static function getAdminMenuGroup() {
		return 'System';
	}
	
	public static function getAdminMenuGroupIcon() {
		return 'fa fa-gears';
	}
	
	public static function displayAdministration($selfURL) {
		if($_GET['action'] == 'testPrint') {
			$pdf = new PrintLetterWithWindowA4('testdruck');
			$pdf->setDatum(DateFunctions::getTodayAsNaturalDate());
			$pdf->setBetreff("Testbetreff");
			
			
			$pdf->addLetter("Test Empfänger\r\nTeststr. 1\r\n1234 Teststadt", "Sehr geehrte Damen und Herren, <br /><br />Lorem ipsum dolor sit amet, consetetur sadipscing elitr,  sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr,  sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr,  sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.
Duis autem vel eum iriure dolor in hendrerit in vulputate velit esse molestie consequat, vel illum dolore eu feugiat nulla facilisis at vero eros et accumsan et iusto odio dignissim qui blandit praesent luptatum zzril delenit augue duis dolore te feugait nulla facilisi. Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat.
Ut wisi enim ad minim veniam, quis nostrud exerci tation ullamcorper suscipit lobortis nisl ut aliquip ex ea commodo consequat. Duis autem vel eum iriure dolor in hendrerit in vulputate velit esse molestie consequat, vel illum dolore eu feugiat nulla facilisis at vero eros et accumsan et iusto odio dignissim qui blandit praesent luptatum zzril delenit augue duis dolore te feugait nulla facilisi.
Nam liber tempor cum soluta nobis eleifend option congue nihil imperdiet doming id quod mazim placerat facer possim assum. Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat. Ut wisi enim ad minim veniam, quis nostrud exerci tation ullamcorper suscipit lobortis nisl ut aliquip ex ea commodo consequat.
Duis autem vel eum iriure dolor in hendrerit in vulputate velit esse molestie consequat, vel illum dolore eu feugiat nulla facilisis.
At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr,  sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr,  At accusam aliquyam diam diam dolore dolores duo eirmod eos erat, et nonumy sed tempor et et invidunt justo labore Stet clita ea et gubergren, kasd magna no rebum. sanctus sea sed takimata ut vero voluptua. est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr,  sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat. 
Consetetur sadipscing elitr,  sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr,  sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr,  sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.<br /><br />Mit freundlichen Grüßen,<br />Das System");
			
			
			$pdf->send();
			exit();
		}
		
		return "<a href=\"" . $selfURL . "&action=testPrint\"><i class=\"fa fa-file-pdf-o\"></i> Testdruck mit den Einstellungen auf der zweiten Seite.</a><br />Wenn Sie den Briefkopf ändern möchten, schreiben Sie bitte eine E-Mail an info@spitschka.com";
	}
	
	public static function siteIsAlwaysActive() {
		return true;
	}
}


?>