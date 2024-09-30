<?php


class extAppDefault extends AbstractPage
{

    public static function getSiteDisplayName()
    {
        return '<i class="fa fas fa-mobile-alt"></i> App Connector - Default';
    }

    public function __construct($request = [], $extension = [])
    {
        parent::__construct(array(self::getSiteDisplayName()), false, false, false, $request, $extension);
        $this->checkLogin();
    }


    public function execute()
    {

        //$_request = $this->getRequest();
        //print_r($_request);

        $acl = $this->getAcl();
        if (!$this->canRead()) {
            new errorPage('Kein Zugriff');
        }

        //print_r( $acl );
        //$user = DB::getSession()->getUser();
        $resultObj = [];

        $schulnummer = implode('-', DB::getSchulnummern());

        if ($schulnummer) {
            $curl_session = curl_init();
            curl_setopt($curl_session, CURLOPT_URL, "https://auth.schule-intern.de/api/shool/" . $schulnummer . "/isActive");
            curl_setopt($curl_session, CURLOPT_HEADER, 0);
            curl_setopt($curl_session, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($curl_session);
            curl_close($curl_session);
            if ($result) {
                $resultObj = json_decode($result);
            }
        }

        $user = DB::getSession()->getUser();
        $username = $user->getUserName();

        $qr_str = "schule-intern:app-auth/".$schulnummer."/".$username;



        $this->render([
            "tmpl" => "default",
            "scripts" => [
                PATH_EXTENSION . 'tmpl/scripts/default/dist/js/chunk-vendors.js',
                PATH_EXTENSION . 'tmpl/scripts/default/dist/js/app.js'
            ],
            "data" => [
                "apiURL" => "rest.php/app",
                "acl" => $acl['rights'],
                "app" => $resultObj,
                "schulnummer" => $schulnummer,
                "username" => $username,
                "qr_str" => $qr_str
            ]
        ]);


    }


}
