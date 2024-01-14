<?php



class extFehltageAdminSlots extends AbstractPage
{

    public static function getSiteDisplayName()
    {
        return '<i class="fa fa-user-md"></i> Fehltage - Slots';
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



        include_once PATH_EXTENSIONS . 'fehltage' . DS . 'models' . DS . 'Slots.class.php';
        $data = extFehltageModelSlots::getAll();

        $ret = [];
        foreach($data as $item) {
            $ret[] = $item->getCollection();
        }


        if (!$this->canWrite()) {
            new errorPage('Kein Zugriff');
        }


        $this->render([
            "tmpl" => "default",
            "scripts" => [
                PATH_EXTENSION . 'tmpl/scripts/slots/dist/js/chunk-vendors.js',
                PATH_EXTENSION . 'tmpl/scripts/slots/dist/js/app.js'
            ],
            "data" => [
                "slots" => $ret,
                "selfURL" => URL_SELF,
                "apiURL" => "rest.php/fehltage"
            ]

        ]);

    }


}
