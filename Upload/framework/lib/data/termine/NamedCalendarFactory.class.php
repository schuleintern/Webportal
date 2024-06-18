<?php
use \Eluceo\iCal\Presentation\Component\Property;
use \Eluceo\iCal\Presentation\Component\Property\Value\TextValue;

/**
 * Custom CalendarFactory to support the iCalendar NAME and X-WR-CALNAME properties
 */
class NamedCalendarFactory extends \Eluceo\iCal\Presentation\Factory\CalendarFactory {
    protected function getProperties(\Eluceo\iCal\Domain\Entity\Calendar $calendar): Generator {
        yield from parent::getProperties($calendar);

        if ($calendar instanceof NamedCalendar) {
            $name = $calendar->getName();
            if ($name) {
                // One property for the de-facto standard (RFC 7968)...
                yield new Property("NAME", new TextValue($name));
                // ...and one for support (see https://learn.microsoft.com/en-us/openspecs/exchange_server_protocols/ms-oxcical/1da58449-b97e-46bd-b018-a1ce576f3e6d)
                yield new Property("X-WR-CALNAME", new TextValue($name));
            }
        }
    }
}
