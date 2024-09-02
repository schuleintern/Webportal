<?php



class extAkteDefault extends AbstractPage
{

    public static function getSiteDisplayName()
    {
        return '<i class="fa fas fa-id-card"></i> Umfragen';
    }

    public function __construct($request = [], $extension = [])
    {
        parent::__construct(array(self::getSiteDisplayName()), false, false, false, $request, $extension);
        $this->checkLogin();
    }

    public function execute()
    {

        $klassen = klasse::getAllKlassen();
        $ret = [];
        foreach ($klassen as $klasse) {
            $foo = [
                'name' => $klasse->getKlassenName(),
                'pupils' => []
            ];
            $pupils = $klasse->getSchueler();
            foreach ($pupils as $pupil) {
                $foo['pupils'][] = $pupil->getCollection(true);
            }
            $ret[] = $foo;
        }


        include_once PATH_EXTENSION . 'models' . DS .'Tags.class.php';
        $class = new extAkteModelTags();
        $tmp_data = $class->getAll();
        $tags = [];
        if ($tmp_data) {
            foreach ($tmp_data as $item) {
                $tags[] = $item->getCollection();
            }
        }



        $acl = $this->getAcl();
        if (!$this->canRead()) {
            new errorPage('Kein Zugriff');
        }

        $this->render([
            "tmpl" => "default",
            "scripts" => [
                PATH_EXTENSION . 'tmpl/scripts/default/dist/js/chunk-vendors.js',
                PATH_EXTENSION . 'tmpl/scripts/default/dist/js/app.js'
            ],
            "data" => [
                "apiURL" => "rest.php/akte",
                "acl" => $acl['rights'],
                "klassen" => $ret,
                "tags" => $tags
            ]
        ]);
    }

}
