<?php
/**
 *
 */
class extSprechstundeModelHelpers
{



    /**
     * @return String
     */
    public static function getCalenderHourStart()
    {
        $start = DB::getSettings()->getValue('extSprechstunde-time-start');
        if (!$start) { $start = '08:00'; }
        return $start;
    }


    /**
     * @return Integer
     */
    public static function getCalenderHours() {

        $start = extSprechstundeModelHelpers::getCalenderHourStart();
        $end = DB::getSettings()->getValue('extSprechstunde-time-end');
        if (!$end) { $end = '14:00'; }
        $start = DateTime::createFromFormat('H:i', $start);
        $start_int = $start->format('H');
        $end = DateTime::createFromFormat('H:i', $end);
        $end_int = $end->format('H');
        $hours = (int)$end_int - (int)$start_int;
        return $hours;

    }



}