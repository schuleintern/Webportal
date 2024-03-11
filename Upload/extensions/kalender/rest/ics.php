<?php

use DateTimeImmutable;
use Eluceo\iCal\Domain\ValueObject\Date;
use Eluceo\iCal\Domain\ValueObject\DateTime;
use Eluceo\iCal\Domain\ValueObject\MultiDay;
use Eluceo\iCal\Domain\ValueObject\SingleDay;
use Eluceo\iCal\Domain\ValueObject\TimeSpan;


use DateInterval;
use Eluceo\iCal\Domain\Entity\Calendar;
use Eluceo\iCal\Domain\Entity\Event;
use Eluceo\iCal\Domain\ValueObject\Alarm;
use Eluceo\iCal\Domain\ValueObject\Attachment;
use Eluceo\iCal\Domain\ValueObject\EmailAddress;
use Eluceo\iCal\Domain\ValueObject\GeographicPosition;
use Eluceo\iCal\Domain\ValueObject\Location;
use Eluceo\iCal\Domain\ValueObject\Organizer;
use Eluceo\iCal\Domain\ValueObject\Uri;
use Eluceo\iCal\Presentation\Factory\CalendarFactory;
new Eluceo\iCal\Domain\ValueObject\Date;

class ics extends AbstractRest
{

    protected $statusCode = 200;


    public function execute($input, $request)
    {


        include_once PATH_EXTENSION . 'models' . DS . 'ICS.class.php';
        $Class = new extKalenderModelIcs();

        $data = $Class->getByCode($request[2]);
        if ($data) {
            $userID = $data->getData('user_id');
            //echo $userID;
        }

        //include_once(PATH_LIB . "composer/vendor/autoload.php");
        include_once PATH_EXTENSIONS . 'kalender' . DS . 'models' . DS . 'Event.class.php';
        include_once PATH_EXTENSIONS . 'kalender' . DS . 'models' . DS . 'Kalender.class.php';

        $events = [];
        $kalenders = extKalenderModelKalender::getAllAllowed(1, true, $userID);

        $tz = 'Europe/Amsterdam';
        $dtz = new \DateTimeZone($tz);
        date_default_timezone_set($tz);

        if ($kalenders) {

            foreach ($kalenders as $kalender) {

                $result = extKalenderModelEvent::getAllByKalenderID([$kalender['id']]);
                foreach ($result as $row) {

                    $sameDay = false;
                    $von = $row->getDateStart() . ' ' . $row->getTimeStart();
                    $bis_date = $row->getDateEnd();
                    if (!$bis_date || $row->getDateStart() == $row->getDateEnd() ) {
                        $bis_date = $row->getDateStart();
                        $sameDay = true;
                    }
                    $timeEnd = $row->getTimeEnd();
                    if ($sameDay && $timeEnd == '00:00') {
                        $timeEnd = $row->getTimeStart();
                    }
                    $bis = $bis_date . ' ' . $timeEnd;


                    if ($row->getDateStart() && $von) {
                        if ( $von != $bis ) {
                            $start = new DateTime(DateTimeImmutable::createFromFormat('Y-m-d H:i', $von), false);
                            $end = new DateTime(DateTimeImmutable::createFromFormat('Y-m-d H:i', $bis), false);
                            $occurrence = new TimeSpan($start, $end);

                        } elseif ( $sameDay == true && $row->getTimeStart() == $timeEnd ) {
                            $date = new Date(DateTimeImmutable::createFromFormat('Y-m-d', $row->getDateStart()));
                            $occurrence = new SingleDay($date);
                        } else {

                            $firstDay = new Date(DateTimeImmutable::createFromFormat('Y-m-d H:i', $von));
                            $lastDay = new Date(DateTimeImmutable::createFromFormat('Y-m-d H:i', $bis));
                            $occurrence = new MultiDay($firstDay, $lastDay);
                        }
                    }



                    $event = (new Eluceo\iCal\Domain\Entity\Event())
                        //->setUseTimezone(true)
                        //->setUseUtc(true)
                        ->setSummary( DB::getDB()->decodeString($row->getTitle()) )
                        ->setCategories([$kalender['title']])
                        ->setLocation(new Eluceo\iCal\Domain\ValueObject\Location(
                            DB::getDB()->decodeString($row->getPlace())
                        ))
                        ->setDescription(DB::getDB()->decodeString($row->getComment()))
                        ->setOccurrence($occurrence);


                    $events[] = $event;

                }
            }
        }


        // 2. Create Calendar domain entity
        $calendar = new Eluceo\iCal\Domain\Entity\Calendar($events);

        // 3. Transform domain entity into an iCalendar component
        $componentFactory = new Eluceo\iCal\Presentation\Factory\CalendarFactory();
        $calendarComponent = $componentFactory->createCalendar($calendar);

        // 4. Set headers
        header('Content-Type: text/calendar; charset=utf-8');
        header('Content-Disposition: attachment; filename="cal.ics"');

        // 5. Output
        echo $calendarComponent;
        exit;


    }


    /**
     * Set Allowed Request Method
     * (GET, POST, ...)
     *
     * @return String
     */
    public function getAllowedMethod()
    {
        return 'GET';
    }


    public function checkAuth($user, $pass)
    {
        return true;
    }

    /**
     * Muss der Benutzer eingeloggt sein?
     * Ist Eine Session vorhanden
     * @return Boolean
     */
    public function needsUserAuth()
    {
        return false;
    }

    /**
     * Ist eine Admin berechtigung nötig?
     * only if : needsUserAuth = true
     * @return Boolean
     */
    public function needsAdminAuth()
    {
        return false;
    }

    /**
     * Ist eine System Authentifizierung nötig? (mit API key)
     * only if : needsUserAuth = false
     * @return Boolean
     */
    public function needsSystemAuth()
    {
        return false;
    }

}

?>