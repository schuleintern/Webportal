<?php

class extKalenderCronFetchICS extends AbstractCron
{
    private $status = false;

    public function __construct()
    {

    }

    public function execute()
    {

        $state = [];

        $ferien_kalenders = DB::getDB()->query("SELECT id, icsfeed, title FROM ext_kalender WHERE icsfeed IS NOT NULL");

        while($ferien_kalender = DB::getDB()->fetch_array($ferien_kalenders)) {


            if (!(int)$ferien_kalender['id']) {
                continue;
            }

            if (strlen($ferien_kalender['icsfeed']) < 6) {
                continue;
            }

            DB::getDB()->query("DELETE FROM ext_kalender_events WHERE kalender_id = " . intval($ferien_kalender['id']));

            $client = new GuzzleHttp\Client();
            $res = $client->request('GET', $ferien_kalender['icsfeed'], ['verify' => false ]);


            if($res->getStatusCode() == 200) {

                $icalobj = new \ICalendarOrg\ZCiCal($res->getBody());
                //$icalobj = new ZCiCal($res->getBody());

                $calData = [];

                // read back icalendar data that was just parsed
                if (isset($icalobj->tree->child)) {
                    foreach ($icalobj->tree->child as $node) {
                        if ($node->getName() == "VEVENT") {
                            $event = [];

                            foreach ($node->data as $key => $value) {
                                switch ($key) {
                                    case 'DTSTART':
                                        // 20161221T140000Z


                                        $val = $value->getValues();


                                        $addHour = 0;

                                        if (strpos($val, 'Z') > 0) $addHour = 1;


                                        $event['dateStart'] = substr($val, 0, 4) . '-' . substr($val, 4, 2) . '-' . substr($val, 6, 2);
                                        if (strpos($val, 'T') == false) {
                                            $event['isWholeDay'] = 1;
                                            $event['timeStart'] = '';
                                        } else {
                                            $event['isWholeDay'] = 0;
                                            // Zeit suchen
                                            $time = substr($val, strpos($val, 'T') + 1);
                                            // Bugfix Issue#38
                                            // Eingaben mit dem Format x:xx bekommen eine 0 vorangestellt
                                            $hour = (substr($time, 0, 2) + $addHour);
                                            if (strlen($hour) < 2) {
                                                $hour = "0" . $hour;
                                            }
                                            $time = $hour . ":" . substr($time, 2, 2);
                                            $event['timeStart'] = $time;
                                        }
                                        break;

                                    case 'DTEND':
                                        // 20161221T140000Z
                                        $val = $value->getValues();
                                        $event['dateEnd'] = substr($val, 0, 4) . '-' . substr($val, 4, 2) . '-' . substr($val, 6, 2);

                                        if (strpos($val, 'Z') > 0) $addHour = 1;

                                        if (strpos($val, 'T') == false) {
                                            $event['isWholeDay'] = 1;
                                            $event['timeEnd'] = '';
                                            $event['dateEnd'] = DateFunctions::substractOneDayToMySqlDate($event['dateEnd']);
                                        } else {
                                            $event['isWholeDay'] = 0;
                                            // Zeit suchen
                                            $time = substr($val, strpos($val, 'T') + 1);
                                            $time = (substr($time, 0, 2) + $addHour) . ":" . substr($time, 2, 2);
                                            $event['timeEnd'] = $time;
                                        }

                                        break;

                                    case 'SUMMARY':
                                        $event['title'] = (string)$value->getValues();
                                        break;

                                    case 'LOCATION':
                                        $event['place'] = (string)$value->getValues();
                                        break;

                                    case 'DESCRIPTION':
                                        $event['comment'] = trim(htmlspecialchars((string)$value->getValues()));
                                        if ( $event['comment'] == '\n') {
                                            $event['comment'] = '';
                                        }
                                        break;

                                    case 'RRULE':
                                        $val = str_replace(';', '&', $value->getValues());
                                        parse_str($val, $output);
                                        $event['RRULE'] = $output;
                                        break;

                                }
                            }


                            $calData[] = $event;
                        }
                    }
                }



                foreach ($calData as $node) {
                    if ($node['RRULE']) {

                        if ($node['RRULE']['FREQ'] == 'YEARLY') {

                            $interval = (int)$node['RRULE']['INTERVAL'];
                            unset($node['RRULE']);

                            for ($z = 1; $z <= $interval; $z++) {

                                $clone = $node;
                                $clone['dateStart'] = (int)substr($clone['dateStart'], 0, 4) + $z . '-' . substr($clone['dateStart'], 5, 2) . '-' . substr($clone['dateStart'], 8, 2);
                                $clone['dateEnd'] = (int)substr($clone['dateEnd'], 0, 4) + $z . '-' . substr($clone['dateEnd'], 5, 2) . '-' . substr($clone['dateEnd'], 8, 2);

                                $calData[] = $clone;
                                $_debug[] = $clone;

                            }
                        }
                    }
                }



                include_once PATH_EXTENSIONS.'kalender'.DS . 'models' . DS . 'Event.class.php';

                foreach ($calData as $node) {
                    $node['kalender_id'] = $ferien_kalender['id'];

                    if ( extKalenderModelEvent::submitData($node) ) {

                    }
                }
                $state[] = $ferien_kalender['title'].' - '.count($calData).' Events importiert';

            } else {
                $state[] = $ferien_kalender['title'].' - No Connection, Code:'.$res->getStatusCode();
            }
        }
        $this->status .= implode(', ', $state);

    }


    public function getName()
    {
        return "Import vom ICS Feed";
    }

    public function getDescription()
    {
        return "Importiert Termine von externen Quellen - ICS";
    }


    public function getCronResult()
    {
        if (!$this->status) {
            return ['success' => 1, 'resultText' => 'Erfolgreich'];
        } else {
            return ['success' => 1, 'resultText' => $this->status];
        }

    }

    public function informAdminIfFail()
    {
        return false;
    }

    public function executeEveryXSeconds()
    {
        return 43200;        // 2 mal am tag
    }


}