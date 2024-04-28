<?php


class extFaecherAdminDefault extends AbstractPage
{

    public static function getSiteDisplayName()
    {
        return '<i class="fas fa fa-chalkboard-teacher"></i> Faecher - Übersicht';
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

        $acl = $this->getAcl();
        //$user = DB::getSession()->getUser();

        if (!$this->canAdmin()) {
            new errorPage('Kein Zugriff');
        }

        include_once PATH_EXTENSIONS.'faecher'.DS . 'models' . DS . 'Faecher.class.php';
        $faecher = new extFaecherModelFaecher();
        include_once PATH_EXTENSIONS. 'faecher'.DS . 'models' . DS . 'Unterricht.class.php';
        $unterricht = new extFaecherModelUnterricht();

        $this->render([
            "tmpl" => "default",
            "scripts" => [
                PATH_EXTENSION . 'tmpl/scripts/default/dist/js/chunk-vendors.js',
                PATH_EXTENSION . 'tmpl/scripts/default/dist/js/app.js'
            ],
            "data" => [
                "apiURL" => "rest.php/faecher",
                "acl" => $acl['rights'],
                "anzFaecher" => $faecher->getCount(),
                "anzUnterrichte" => $unterricht->getCount()

            ]
        ]);

    }


}
