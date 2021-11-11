<?php


class ganztagsCalendar extends AbstractPage {
	
	private $isAdmin = false;
	private $isTeacher = false;
	


	public function __construct() {
		
		parent::__construct(array("Lehrertools", "Ganztags - Tagesansicht"));
				
		$this->checkLogin();
		
		// if(DB::getSession()->isTeacher()) {
		// 	$this->isTeacher = true;
		// }
		
		// if(DB::getSession()->isAdmin()) $this->isTeacher = true;
		
		// if(!$this->isTeacher) {
		// 	$this->isTeacher = DB::getSession()->isMember("Webportal_Klassenlisten_Sehen");
		// }
		
		// if(!$this->isTeacher) {
		//     $this->isTeacher = DB::getSession()->isMember("Schuelerinfo_Sehen");
		// }
		
		
	}

  public static function siteIsAlwaysActive() {
    return true;
  }
	public function execute() {
		
		if(AbstractPage::isActive('ganztags')) {

			$showDays = array(
				'Mo' => DB::getSettings()->getValue("ganztags-day-mo"),
				'Di' => DB::getSettings()->getValue("ganztags-day-di"),
				'Mi' => DB::getSettings()->getValue("ganztags-day-mi"),
				'Do' => DB::getSettings()->getValue("ganztags-day-do"),
				'Fr' => DB::getSettings()->getValue("ganztags-day-fr"),
				'Sa' => DB::getSettings()->getValue("ganztags-day-sa"),
				'So' => DB::getSettings()->getValue("ganztags-day-so")
			);



            if ( $_REQUEST['action'] == 'setEvent') {

                $data = json_decode($_REQUEST['data']);

                if ($data->id) {
                    if (DB::getDB()->escapeString($data->title) == '' && DB::getDB()->escapeString($data->room) == '') {
                        if ( DB::getDB()->query("DELETE FROM ganztags_events WHERE id=" . $data->id) ) {

                            echo json_encode([
                                'error' => false,
                                'done' => true
                            ]);
                            exit;
                        }
                    } else {
                        if ( DB::getDB()->query("UPDATE ganztags_events SET title='".DB::getDB()->escapeString($data->title)."', room='".DB::getDB()->escapeString($data->room)."' WHERE id= " . $data->id) ) {

                            echo json_encode([
                                'error' => false,
                                'done' => true
                            ]);
                            exit;
                        }
                    }
                } else {
                    if ( DB::getDB()->query("INSERT INTO ganztags_events (`date`, `gruppenID`,`title`,`room`)
                        values (
                            '" . $data->day . "',
                            " . (int)DB::getDB()->escapeString($data->gruppeID) . ",
                            '" . DB::getDB()->escapeString($data->title) . "',
                            '" . DB::getDB()->escapeString($data->room) . "'
                        ) ") ) {

                        echo json_encode([
                            'error' => false,
                            'done' => true
                        ]);
                        exit;
                    }
                }

                echo json_encode([
                    'error' => true,
                    'msg' => 'Fehler beim Speichern!'
                ]);
                exit;
            }


			if ( $_REQUEST['action'] == 'printToday') {


				setlocale(LC_TIME, 'de_DE', 'de_DE.UTF-8');
				$days = [ [ date('Y-m-d'), strftime("%a") ] ];

                //$days = [ [ '2021-09-27', 'Mo' ] ];

				$return = $this->getWeekSchuelerList($days);

				$pdf = new PrintNormalPageA4WithoutHeader('Ganztags');
				$pdf->setPrintedDateInFooter();

				$pdf->showImageErrors = true;
				
				foreach($return[0]['gruppen'] as $gruppe) {

                    $query_events = DB::getDB()->query("SELECT * FROM ganztags_events 
                        WHERE  gruppenID = ".$gruppe['gruppe']['id']."  AND date = '".$return[0][0]."'");
                    $events = [];
                    while($a = DB::getDB()->fetch_array($query_events)) {
                        $events[] = $a;
                    }

					$html = '';
					$html .= '<style>
					table {
						width: 100%;
					}
					td { padding: 0.3rem; }
					</style>';
					$html .= '<h3 style="text-align: right">'.$return[0][0].'</h3>';
					$html .= '<h1>'.$gruppe['gruppe']['name'].'</h1>';
					$html .= '<h4 style="color:#ccc">'.$gruppe['gruppe']['raum'].'</h4>';
					$html .= '<table cellspacing="0" cellpadding="5" border="0" style="border-color:white; border-collapse: collapse;" >
						<thead >
							<tr>
								<th width="5%"></th>
								<th width="15%" style="font-weight: bold;">Vorname</th>
								<th width="18%" style="font-weight: bold;">Name</th>
								<th width="5%" style="font-weight: bold;"></th>
								<th width="8%" style="font-weight: bold;"></th>
								<th width="6%" style="font-weight: bold;"><img src="./images/check-circle.svg" height="12px" width="12px"/></th>
								<th width="6%" style="font-weight: bold;"><img src="./images/times-circle.svg" height="12px" width="12px"/></th>
								<th width="" style="font-weight: bold;">Info</th>
							</tr>
						</thead>
						<tbody>';
					
					$num = 1;
					foreach($gruppe['schueler'] as $schueler) {
						$style = '';
						$boder = 'border-right: 0.01px solid #ccc;';
						if ($num%2) {
							$style = 'background-color: rgb(236, 240, 245); margin: 30px;';
							$boder = 'border-right: 0.01px solid white';
						}
						$html .= '<tr style="'.$style.'">';
						$html .= '<td width="5%" style="color:#ccc">'.$num.'</td>';

						if ( $schueler['absenz'] ) {
							$html .= '<td width="15%" style="text-decoration:line-through">'.$schueler['rufname'].'</td>';
						} else {
							$html .= '<td width="15%">'.$schueler['rufname'].'</td>';
						}
						if ( $schueler['absenz'] ) {
							$html .= '<td width="18%" style="text-decoration:line-through">'.$schueler['name'].'</td>';
						} else {
							$html .= '<td width="18%">'.$schueler['name'].'</td>';
						}

						$html .= '<td width="5%">';
						if ($schueler['geschlecht'] == 'm') {
							$html .= '<img src="./images/mars.svg" height="10px" width="10px"/>';
						} else if ($schueler['geschlecht'] == 'w') {
							$html .= '<img src="./images/venus.svg" height="10px" width="10px"/>';
						}
						$html .= '</td>';

						$html .= '<td width="8%" style="'.$boder.'">'.$schueler['klasse'].'</td>';
						$html .= '<td width="6%" style="'.$boder.'">';
						if ( $schueler['absenz'] ) {
							$html .= '<img src="./images/bed.svg" width="14px" height="17px"/>';
						}
						$html .= '</td>';
						$html .= '<td width="6%" style="'.$boder.'"></td>';

						$html .= '<td width="">';
							
							if ( $schueler['absenz'] ) {
								$html .= '<span style="font-size:85%"><i>Stunden:</i> '.$schueler['absenz_info']['stunden'].'</span>';
								if ($schueler['absenz_info']['notiz']) {
									$html .= '<br><i>Notiz:</i> '.$schueler['absenz_info']['notiz'];
								}
								if ( $schueler['info'] || $schueler['tag_info'] ) {
									$html .='<hr><br>';
								}
							}
                            $html .= $schueler['tag_info'].'<div style="font-size: 90%; text-align:right">'.$schueler['info'].'</div>';

						$html .= '</td>';

						$html .= '</tr>';
						$num++;
					}
					$html .= '</tbody></table>';

                    if (count($events) > 0) {

                        $html .= '<br><br>';
                        $html .= '<table cellspacing="0" cellpadding="5" border="0" style="border-color:white; border-collapse: collapse;" >
						<thead >
							<tr>
								<th width="80%" style="font-weight: bold;">Info</th>
								<th width="20%" style="font-weight: bold;"></th>
							</tr>
						</thead>
						<tbody>';

                        $i = 1;
                        foreach($events as $event) {

                            $style = '';
                            if ($i%2) {
                                $style = 'background-color: rgb(236, 240, 245); margin: 30px;';
                            }

                            $html .= '<tr style="'.$style.'">';
                            $html .= '<td width="80%">'.$event['title'].'</td>';
                            $html .= '<td width="20%">'.$event['room'].'</td>';
                            $html .= '</tr>';

                            $i++;
                        }
                        $html .= '</tbody></table>';
                    }


					$html .= '<br><br><br><br><br><hr><br>Aktivitäten<br><br><br><br><hr><br>Sonstiges';
					$pdf->setHTMLContent($html);
				}

				$pdf->send();

				exit;
			}

			if ( $_REQUEST['action'] == 'getWeek') {

				if ( !$_GET['days'] ) {
					die('missing data');
				}
				$days = json_decode($_GET['days']);
				$return = $this->getWeekSchuelerList($days);
				echo json_encode( $return );
				exit;
			}

			$acl = json_encode( $this->getAcl() );
			$showDays = json_encode($showDays);

			eval("echo(\"" . DB::getTPL()->get("ganztags/calendar"). "\");");
		}
	}
	
	private function getWeekSchuelerList($days) {

		if (!$days || !is_array($days)) {
			return false;
		}

		include_once("../framework/lib/data/absenzen/Absenz.class.php");
		$schueler = schueler::getGanztagsSchueler('schueler.schuelerRufname, schueler.schuelerName');
		$query = DB::getDB()->query("SELECT *  FROM ganztags_gruppen ORDER BY sortOrder, name ");
		$gruppen = [];
		while($group = DB::getDB()->fetch_array($query)) {
			$gruppen[] = $group;
		}

        $query_events = DB::getDB()->query("SELECT a.id, a.date, a.gruppenID, a.title, a.room FROM ganztags_events AS a 
            WHERE  a.date = '".$days[0][0]."'
            OR a.date = '".$days[1][0]."' 
            OR a.date = '".$days[2][0]."' 
            OR a.date = '".$days[3][0]."' 
            OR a.date = '".$days[4][0]."' 
            OR a.date = '".$days[5][0]."' 
            OR a.date = '".$days[6][0]."' 
            ");
        $events = [];
        while($a = DB::getDB()->fetch_array($query_events)) {
            $events[] = $a;
        }

		foreach($days as $key => $day) {
			if (empty($day)) {
				continue;
			}
			$absenzen = Absenz::getAbsenzenForDate($day[0], null);
			$day_db = 'tag_'.strtolower($day[1]);
			$list_gruppen = [];
			foreach($gruppen as $gruppe) {
				$arr = [];
                $gruppe_events = [];
				$found_absenz_anz = 0;
				foreach($schueler as $item) {
					if ($item->getGruppe() == $gruppe['id']) {
						$ganztags = $item->getGanztags();
						if ( $ganztags[$day_db] ) {
							$arr_schueler = [
								'rufname' => $item->getRufname(),
								'name' => $item->getName(),
								'geschlecht' => $item->getGeschlecht(),
								'klasse' => $item->getKlassenObjekt()->getKlassenName(),
								'absenz' => false,
								'absenz_info' => false,
								'info' => $ganztags['info'],
                                'tag_info' => $ganztags[$day_db.'_info']
							];
							$found_absenz = false;
							foreach($absenzen as $absenz) {
								if  ( $item->getAsvID() == $absenz->getSchueler()->getAsvID() ) {
									$found_absenz = [
										"stunden" => $absenz->getStundenAsString(),
										"notiz" => nl2br($absenz->getGanztagsNotiz())
									];
									$found_absenz_anz++;
								}
							}
							if ($found_absenz) {
								$arr_schueler['absenz'] = true;
								$arr_schueler['absenz_info'] = $found_absenz;
							}
							$arr[] = $arr_schueler;
						}
					}
				}

                // kalender gruppen loop?
                foreach($events as $event) {
                    if ($event['date'] == $day[0] && $event['gruppenID'] == $gruppe['id']) {
                        $gruppe_events[] = [
                            'title' => $event['title'],
                            'room' => $event['room'],
                            'id' => $event['id']
                        ];
                    }
                }

				$gruppe['absenz_anz'] = $found_absenz_anz;
				$list_gruppen[] = [
					'gruppe' => $gruppe,
					'schueler' => $arr,
                    'events' => $gruppe_events
				];
			}
			$day['gruppen'] = $list_gruppen;
			$days[$key] = $day;
		}
		return $days;
	}

	public static function hasSettings() {
		return true;
	}
	
	public static function getSettingsDescription() {
		$settings = array(
			array(
				'name' => "ganztags-day-mo",
				'typ' => "BOOLEAN",
				'titel' => "Montag anzeigen?",
				'text' => ""
			),
			array(
				'name' => "ganztags-day-di",
				'typ' => "BOOLEAN",
				'titel' => "Dienstag anzeigen?",
				'text' => ""
			),
			array(
				'name' => "ganztags-day-mi",
				'typ' => "BOOLEAN",
				'titel' => "Mittwoch anzeigen?",
				'text' => ""
			),
			array(
				'name' => "ganztags-day-do",
				'typ' => "BOOLEAN",
				'titel' => "Donnerstag anzeigen?",
				'text' => ""
			),
			array(
				'name' => "ganztags-day-fr",
				'typ' => "BOOLEAN",
				'titel' => "Freitag anzeigen?",
				'text' => ""
			),
			array(
				'name' => "ganztags-day-sa",
				'typ' => "BOOLEAN",
				'titel' => "Samstag anzeigen?",
				'text' => ""
			),
			array(
				'name' => "ganztags-day-so",
				'typ' => "BOOLEAN",
				'titel' => "Sonntag anzeigen?",
				'text' => ""
			)
		);
		return $settings;
	}
	
	
	public static function getSiteDisplayName() {
		return 'Ganztags';
	}
	
	/**
	 * Liest alle Nutzergruppen aus, die diese Seite verwendet. (Für die Benutzeradministration)
	 * @return array(array('groupName' => '', 'beschreibung' => ''))
	 */
	public static function getUserGroups() {
		return array();
	
	}
	
	public static function hasAdmin() {
		return true;
	}
	
	public static function getAdminGroup() {
		return false;
		//return 'Webportal_Klassenlisten_Admin';
	}
	
	public static function getAdminMenuGroup() {
		return 'Lehrertools';
	}
	
	public static function getAdminMenuGroupIcon() {
		return 'fa fa-wrench';
	}
	
	public static function getAdminMenuIcon() {
		return 'fa fa-table';
	}
	

	public static function displayAdministration($selfURL) {
		 
		if($_REQUEST['add'] > 0) {
			DB::getDB()->query("INSERT INTO ganztags_gruppen (`name`, `sortOrder`)
					values (
						'" . DB::getDB()->escapeString($_POST['ganztagsName']) . "',
						" . DB::getDB()->escapeString($_POST['ganztagsSortOrder']) . "
					) ");
		}

		if($_REQUEST['delete'] > 0) {
			DB::getDB()->query("DELETE FROM ganztags_gruppen WHERE id='" . $_REQUEST['delete'] . "'");
		}
		
		if($_REQUEST['save'] > 0) {
			$objekte = DB::getDB()->query("SELECT * FROM ganztags_gruppen ORDER BY sortOrder ASC");
			
			$objektData = array();
			while($o = DB::getDB()->fetch_array($objekte)) {
				DB::getDB()->query("UPDATE ganztags_gruppen SET 
						name='" . DB::getDB()->escapeString($_POST["name_".$o['id']]) . "',
						sortOrder='" . DB::getDB()->escapeString($_POST["sortOrder_".$o['id']]) . "'
						WHERE id='" . $o['id'] . "'");
			}
			
			
			header("Location: $selfURL");
			exit(0);
		}

		$html = '';

		$objekte = DB::getDB()->query("SELECT * FROM ganztags_gruppen ORDER BY sortOrder ASC");
		
		$objektData = array();
		while($o = DB::getDB()->fetch_array($objekte)) $objektData[] = $o;

		$objektHTML = "";
		for($i = 0; $i < sizeof($objektData); $i++) {
			$objektHTML .= "<tr>";
				$objektHTML .= "<td><input type=\"text\" name=\"name_" . $objektData[$i]['id'] . "\" class=\"form-control\" value=\"" . $objektData[$i]['name'] . "\"></td>";
				$objektHTML .= "<td><input type=\"number\" name=\"sortOrder_" . $objektData[$i]['id'] . "\" class=\"form-control\" value=\"" . @htmlspecialchars($objektData[$i]['sortOrder']) . "\"></td>";
				$objektHTML .= "<td><a href=\"#\" onclick=\"javascript:if(confirm('Soll das Objekt wirklisch gelöscht werden?')) window.location.href='$selfURL&delete=" . $objektData[$i]['id'] . "';\"><i class=\"fa fa-trash\"></i> Löschen</a></td>";
				
				$objektHTML .= "</tr>";
		}
	
		$html .= $objektHTML;
		eval("\$html = \"" . DB::getTPL()->get("ganztags/admin") . "\";");

		return $html;
	}
}


?>