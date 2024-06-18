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
        $userFirstName = null;
        if ($data) {
            $userID = $data->getData('user_id');
            $userFirstName = user::getUserByID($userID)->getFirstName();
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
                    $evt = $row->transform();
                    $evt->setCategories([$kalender['title']]);
                    $events[] = $evt;
                }
            }
        }

        $calendar = new NamedCalendar($events);
        $calendar->setPublishedTTL(new DateInterval('PT1H'));
        if (!empty($userFirstName)) {
            if (in_array(strtolower(substr($userFirstName, -1)), array('s', 'ß', 'x', 'z'))) {
                // use the construct "Kalender von <name>" when name ends on an S sound
                $calendar->setName("Kalender von {$userFirstName}");
            } else {
                $calendar->setName("{$userFirstName}s Kalender");
            }
        }

        ICSFeed::sendICSFeed($calendar);
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