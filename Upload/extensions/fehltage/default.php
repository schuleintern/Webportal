<?php



class extFehltageDefault extends AbstractPage
{

    public static function getSiteDisplayName()
    {
        return '<i class="fa fas fa-sun"></i> Fehltage';
    }

    public function __construct($request = [], $extension = [])
    {
        parent::__construct(array(self::getSiteDisplayName()), false, false, false, $request, $extension);
        $this->checkLogin();
    }

    public function execute()
    {

        $acl = $this->getAcl();
        if (!$this->canRead()) {
            new errorPage('Kein Zugriff');
        }


        //$klassen = klasse::getAllKlassen();

        /*
                foreach ($klassen as $klasse) {
                    $collection = $klasse->getCollection(true);
                    $ret[] = $collection;
                }
        */

        /*
        $alleSchueler = schueler::getAll();
        foreach ($alleSchueler as $schueler) {
            //$schueler = schueler::getByAsvID($_REQUEST['schuelerAsvID']);

            if ($schueler) {


                $collection = $schueler->getCollection();

                $absenzen = Absenz::getAbsenzenForSchueler($schueler);

                $absenzenCalculator = new AbsenzenCalculator($absenzen);
                $absenzenCalculator->calculate();

                //$absenzenStat = $absenzenCalculator->getDayStat();
                //$total = $absenzenCalculator->getTotal();

                //$collection['start'] = $absenzenCalculator->getDayStat();
                $collection['total'] = $absenzenCalculator->getTotal();

                $ret[] = $collection;
            }
        }
        */


        /*
        if ( !$this->canRead() ) {
            return [
                'error' => true,
                'msg' => 'Kein Zugriff'
            ];
        }
        */


        /*
        include_once PATH_EXTENSION . 'models' . DS . 'Inbox.class.php';
        if ($tmp_data = extInboxModelInbox::getByUserID($userID)) {
            foreach ($tmp_data as $item) {
                $ret[] = $item->getCollection(true, true);
            }
        }
*/


        $this->render([
            "tmpl" => "default",
            "scripts" => [
                PATH_EXTENSION . 'tmpl/scripts/default/dist/js/chunk-vendors.js',
                PATH_EXTENSION . 'tmpl/scripts/default/dist/js/app.js'
            ],
            "data" => [
                "apiURL" => "rest.php/fehltage",
                "acl" => $acl['rights']
            ]
        ]);
    }
}
