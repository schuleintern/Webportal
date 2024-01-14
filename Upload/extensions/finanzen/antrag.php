<?php



class extFinanzenAntrag extends AbstractPage
{

    public static function getSiteDisplayName()
    {
        return '<i class="fa fas fa-sun"></i> Finanzen - Antrag';
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

        $this->render([
            "tmpl" => "default",
            "scripts" => [
                PATH_EXTENSION . 'tmpl/scripts/antrag/dist/js/chunk-vendors.js',
                PATH_EXTENSION . 'tmpl/scripts/antrag/dist/js/app.js'
            ],
            "data" => [
                "apiURL" => "rest.php/finanzen",
                "acl" => $acl['rights']
            ]
        ]);
    }
}
