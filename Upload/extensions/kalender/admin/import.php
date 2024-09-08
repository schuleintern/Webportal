<?php

 

class extKalenderAdminImport extends AbstractPage
{

    public static function getSiteDisplayName()
    {
        return '<i class="fa fas fa-plug"></i> Kalender - Admin Import';
    }

    public function __construct($request = [], $extension = [])
    {
        parent::__construct(array(self::getSiteDisplayName()), false, false, false, $request, $extension);
        $this->checkLogin();
    }

    public function execute()
    {

        //$this->getRequest();
        //$this->getAcl();

        include_once PATH_EXTENSIONS . 'kalender' . DS . 'models' . DS . 'Kalender.class.php';
        include_once PATH_EXTENSIONS . 'kalender' . DS . 'models' . DS . 'Event.class.php';


        $this->render([
            "tmpl" => "import",
            "vars" => [
                'countKalender' => extKalenderModelKalender::countAll(),
                'countEvents' => extKalenderModelEvent::countAll()
            ],
            "scripts" => [
                //PATH_COMPONENTS.'system/adminSettings2/dist/js/chunk-vendors.js',
                //PATH_COMPONENTS.'system/adminSettings2/dist/js/app.js'
            ],
            "data" => [
                "selfURL" => URL_SELF,
                "settings" => $this->getSettings()
            ]

        ]);

    }

    public function taskDeleteAllItems($request)
    {
        if ((int)DB::getSession()->getUser()->isAnyAdmin() !== 1) {
            return [
                'error' => true,
                'msg' => 'Kein Zugriff'
            ];
        }
        include_once PATH_EXTENSIONS . 'kalender' . DS . 'models' . DS . 'Kalender.class.php';
        include_once PATH_EXTENSIONS . 'kalender' . DS . 'models' . DS . 'Event.class.php';

        extKalenderModelKalender::deleteALL();
        extKalenderModelEvent::deleteALL();

        $this->reloadWithoutParam('task');
    }


    public function taskImportFromKalender($request)
    {

        if ((int)DB::getSession()->getUser()->isAnyAdmin() !== 1) {
            return [
                'error' => true,
                'msg' => 'Kein Zugriff'
            ];
        }

        include_once PATH_EXTENSIONS . 'kalender' . DS . 'models' . DS . 'Kalender.class.php';
        include_once PATH_EXTENSIONS . 'kalender' . DS . 'models' . DS . 'Event.class.php';


        $result = DB::getDB()->query("SELECT * FROM andere_kalender ");
        while ($row = DB::getDB()->fetch_array($result, true)) {


            $iid = extKalenderModelKalender::submitData([
                "title" => $row['kalenderName'],
                "state" => 1,
                "color" => '',
                "sort" => 0,
                "preSelect" => 0,
                "acl" => '',
                "ferien" => 0,
                "public" => 0
            ]);

            $result2 = DB::getDB()->query("SELECT * FROM kalender_andere WHERE kalenderID = ".$row['kalenderID']);
            while ($row2 = DB::getDB()->fetch_array($result2, true)) {

                extKalenderModelEvent::submitData([
                    "id" => null,
                    "kalender_id" => $iid,
                    "title" => $row2['eintragTitel'],
                    "dateStart" => $row2['eintragDatumStart'],
                    "timeStart" => $row2['eintragUhrzeitStart'],
                    "dateEnd" => $row2['eintragDatumEnde'],
                    "timeEnd" => $row2['eintragUhrzeitEnde'],
                    "place" => $row2['eintragOrt'],
                    "comment" => $row2['eintragKommentar']
                ], $row2['eintragUser']);
            }
        }

        $this->reloadWithoutParam('task');
    }

    public function taskImportFromAllinone($request)
    {

        if ((int)DB::getSession()->getUser()->isAnyAdmin() !== 1) {
            return [
                'error' => true,
                'msg' => 'Kein Zugriff'
            ];
        }

        $kid = [];

        include_once PATH_EXTENSIONS . 'kalender' . DS . 'models' . DS . 'Kalender.class.php';

        $result = DB::getDB()->query("SELECT * FROM kalender_allInOne ");
        while ($row = DB::getDB()->fetch_array($result, true)) {

            $iid = extKalenderModelKalender::submitData([
                "title" => $row['kalenderName'],
                "state" => 1,
                "color" => $row['kalenderColor'],
                "sort" => $row['kalenderSort'],
                "preSelect" => $row['kalenderPreSelect'],
                "acl" => '',
                "ferien" => $row['kalenderFerien'],
                "public" => $row['kalenderPublic']
            ]);

            $kid[$row['kalenderID']] = $iid;
        }


        include_once PATH_EXTENSIONS . 'kalender' . DS . 'models' . DS . 'Event.class.php';

        $result = DB::getDB()->query("SELECT * FROM kalender_allInOne_eintrag ");
        while ($row = DB::getDB()->fetch_array($result)) {


            extKalenderModelEvent::submitData([
                "id" => null,
                "kalender_id" => $kid[$row['kalenderID']],
                "title" => $row['eintragTitel'],
                "dateStart" => $row['eintragDatumStart'],
                "timeStart" => $row['eintragTimeStart'],
                "dateEnd" => $row['eintragDatumEnde'],
                "timeEnd" => $row['eintragTimeEnde'],
                "place" => $row['eintragOrt'],
                "comment" => $row['eintragKommentar'],
                "repeat_type" => $row['eintragRepeat']
            ], $row['eintragUserID']);

        }

        $this->reloadWithoutParam('task');
    }


}
