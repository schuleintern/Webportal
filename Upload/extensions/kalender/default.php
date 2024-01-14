<?php



class extKalenderDefault extends AbstractPage {
	
	public static function getSiteDisplayName() {
		return '<i class="fas fa fa-calendar"></i> Kalender';
	}

	public function __construct($request = [], $extension = []) {
		parent::__construct(array( self::getSiteDisplayName() ), false, false, false, $request, $extension);
		$this->checkLogin();
	}


    public function execute() {

        //$_request = $this->getRequest();
        //print_r($_request);

        $acl = $this->getAcl();



        if ( !$this->canRead() ) {
            new errorPage('Kein Zugriff');
        }

        /*

        if ( DB::getSession()->isAdminOrGroupAdmin($this->extension['json']['adminGroupName']) === false ) {
            if ((int)$acl['rights']['read'] !== 1  ) {
                new errorPage('Kein Zugriff');
            }
        }
*/


        //print_r( $acl );

        //$user = DB::getSession()->getUser();

        $this->render([
            "tmpl" => "default",
            "scripts" => [
                PATH_EXTENSION . 'tmpl/scripts/kalender/dist/js/chunk-vendors.js',
                PATH_EXTENSION . 'tmpl/scripts/kalender/dist/js/app.js'
            ],
            "data" => [
                "apiURL" => "rest.php/kalender",
                "acl" => $acl['rights']
            ],
            "dropdown" => [
                [
                    "url" => "index.php?page=ext_kalender&view=default&task=icsFeed",
                    "title" => "ICS Feed",
                    "icon" => "fa fa-rss"
                ],
                [
                    "url" => "index.php?page=ext_kalender&view=default&task=printAll",
                    "title" => "Drucken",
                    "icon" => "fa fa-print",
                    "target" => true
                ]
            ]
        ]);


    }

    public function taskIcsFeed() {


        $userID = DB::getSession()->getUserID();
        if (!$userID) {
            return false;
        }

        $calendar = new Eluceo\iCal\Component\Calendar('schule-intern');
        $calendar->setPublishedTTL('PT15M');

        $tz  = 'Europe/Amsterdam';
        $dtz = new \DateTimeZone($tz);
        date_default_timezone_set($tz);

        include_once PATH_EXTENSION . 'models' . DS . 'Event.class.php';
        include_once PATH_EXTENSION . 'models' . DS . 'Kalender.class.php';

        $kalenders = extKalenderModelKalender::getAllAllowed(1, true);

        if ($kalenders) {

            foreach ($kalenders as $kalender) {

                $result = extKalenderModelEvent::getAllByKalenderID([$kalender['id']]);
                foreach($result as $row ) {
                    $event = (new Eluceo\iCal\Component\Event())
                        ->setUseTimezone(true)
                        ->setUseUtc(true)
                        ->setSummary(DB::getDB()->decodeString($row->getTitle()))
                        ->setCategories([$kalender['title']])
                        ->setLocation( DB::getDB()->decodeString($row->getPlace()) )
                        ->setDescription( DB::getDB()->decodeString($row->getComment()) );

                    if ( intval( $row->getTimeStart() ) <= 0) {
                        $event->setNoTime(true);
                        $event->setDtStart(new \DateTime($row->getDateStart(), $dtz));
                    } else {
                        $event->setDtStart(new \DateTime($row->getDateStart().' '.$row->getTimeStart(), $dtz));
                    }

                    if ( intval( $row->getDateEnd() ) <= 0) {
                        $event->setNoTime(true);
                        $event->setDtEnd(new \DateTime($row->getDateStart(), $dtz));
                    } else {
                        if ( intval( $row->getTimeEnd() ) <= 0) {
                            $event->setDtEnd(new \DateTime($row->getDateEnd().' 00:00:01', $dtz));
                        } else {
                            $event->setDtEnd(new \DateTime($row->getDateEnd().' '.$row->getTimeEnd(), $dtz));
                        }
                    }

                    $calendar->addComponent($event);
                }

            }
        }

        header('Access-Control-Allow-Origin: *');
        header('Content-Type: text/calendar; charset=utf-8');
        header('Content-Disposition: attachment; filename="feed.ics"');

        echo $calendar->render();
        exit;


    }




    public function taskPrintAll() {


        $userID = DB::getSession()->getUserID();
        if (!$userID) {
            return false;
        }

        include_once PATH_EXTENSION . 'models' . DS . 'Event.class.php';
        include_once PATH_EXTENSION . 'models' . DS . 'Kalender.class.php';

        $kalenders = extKalenderModelKalender::getAllAllowed(1);

        $today = time();

        $pdf = new PrintNormalPageA4WithHeader('Kalender');
		$pdf->setPrintedDateInFooter();
				
		$events = [];
				
        if (count($kalenders) > 0) {

            foreach ($kalenders as $kalender) {


                $result = extKalenderModelEvent::getAllByKalenderID([$kalender['id']]);
                foreach($result as $row ) {

                    $dateTimestamp = DateFunctions::getUnixTimeFromMySQLDate($row->getDateStart() );

                    if ( $dateTimestamp >= $today ) {

                        if (!is_array( $events[$dateTimestamp] )) {
                            $events[$dateTimestamp] = [];
                        }

                        $events[$dateTimestamp][] = $row;
                        
                    }
                        
                }

            }
        }

        ksort($events);

        $html = '<table width="100%">';

        foreach ($events as $day => $event) {

            $html .= '<tr><td colspan="2"><br><h2>'.date('d.m.Y',$day).'</h2><br></td></tr>';

            foreach ($event as $obj) {

                $item = $obj->getCollection();

                $html .= '<tr>';
                $ende = '';
                if ( (int)$item['dateEnd'] ) {
                    $ende = '<br><i>Bis: '.$item['dateEnd'].'</i>';
                }

                if ( (int)$item['timeEnd'] ) {
                    $html .= '<td width="30%"><b>'.$item['timeStart'].'</b> - '.$item['timeEnd'].$ende.'</td>';
                } else {
                    if ((int)$item['timeStart']) {
                        $html .= '<td width="30%"><b>'.$item['timeStart'].'</b>'.$ende.'</td>';
                    } else {
                        $html .= '<td width="30%"></td>';
                    }
                }
  
                $html .= '<td width="70%">'.$item['title'].'<br></td>';
                $html .= '</tr>';

                if ($item['eintragOrt']) {
                    $html .= '<tr><td></td><td><i>'.$item['place'].'</i></td></tr>';
                }
                if ($item['eintragKommentar']) {
                    $html .= '<tr><td></td><td><i>'.$item['comment'].'</i></td></tr>';
                }
                
            }
        }
        $html .= '</table>';

        $pdf->setHTMLContent($html);
        $pdf->send();
        exit;

    }

}
