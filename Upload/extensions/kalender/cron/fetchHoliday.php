<?php

class extKalenderCronFetchHoliday extends AbstractCron
{

    public function __construct()
    {

    }

    public function execute()
    {

        $ferien_kalender = DB::getDB()->query_first("SELECT id FROM ext_kalender WHERE ferien = 1");

        if (!intval($ferien_kalender['id'])) {
            return false;
        }

        DB::getDB()->query("DELETE FROM ext_kalender_events WHERE kalender_id = " . intval($ferien_kalender['id']));


        $feriendata = file(DB::getGlobalSettings()->ferienURL);

        $now = new DateTime();
        $now = $now->format('Y-m-d H:i:s');

        if (sizeof($feriendata) > 0) {

            for ($i = 0; $i < sizeof($feriendata); $i++) {

                $ferien = explode(";", str_replace("\r\n", "", $feriendata[$i]));

                DB::getDB()->query("INSERT INTO ext_kalender_events (
					kalender_id,
					title,
					dateStart,
					dateEnd,
					timeStart,
					timeEnd,
					place,
					comment,
					user_id,
					createdTime,
                    status
					) values (
					" . intval($ferien_kalender['id']) . ",
					'" . DB::getDB()->escapeString($ferien[2]) . "',
					'" . DateFunctions::getMySQLDateFromNaturalDate($ferien[0]) . "',
					'" . DateFunctions::getMySQLDateFromNaturalDate($ferien[1]) . "',
					'00:00:00',
					'00:00:00',
					'',
					'',
					1,
					'" . $now . "',
					0
				);");

            }

        }
    }


    public function getName()
    {
        return "Ferien laden";
    }

    public function getDescription()
    {
        return "ext_kalender - fetch Holidays";
    }


    public function getCronResult()
    {
        return ['success' => 1, 'resultText' => 'Erfolgreich'];
    }

    public function informAdminIfFail()
    {
        return false;
    }

    public function executeEveryXSeconds()
    {
        return 86400;        // 1 mal am tag
    }


}