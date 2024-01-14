<?php



class extAusweisDefault extends AbstractPage
{

    public static function getSiteDisplayName()
    {
        return '<i class="fa fa-address-card"></i> Ausweis';
    }

    public function __construct($request = [], $extension = [])
    {
        parent::__construct(array(self::getSiteDisplayName()), false, false, false, $request, $extension);
        $this->checkLogin();
    }

    public function execute()
    {

        $acl = $this->getAclAll();
        $user = DB::getSession()->getUser();

        if (!$this->canRead()) {
            new errorPage('Kein Zugriff');
        }


        if ($user) {
            switch ($user->getUserTyp(true)) {
                case 'isPupil':
                    $users = [DB::getSession()->getUser()->getCollection()];
                    break;
                case 'isEltern':
                    $tmp_data = DB::getSession()->getUser()->getElternObject()->getMySchueler();

                    $users = [];
                    foreach ($tmp_data as $item) {
                        $users[] = $item->getCollection(true);
                    }

                    break;
                case 'isTeacher':
                    $users = [DB::getSession()->getUser()->getCollection()];
                    break;
                case 'isNone':
                    $users = [DB::getSession()->getUser()->getCollection()];
                    break;
            }
        }
        $this->render([
            "tmpl" => "default",
            "scripts" => [
                PATH_EXTENSION . 'tmpl/scripts/default/dist/js/chunk-vendors.js',
                PATH_EXTENSION . 'tmpl/scripts/default/dist/js/app.js'
            ],
            "data" => [
                "selfURL" => URL_SELF,
                "apiURL" => "rest.php/ausweis",
                "acl" => $acl['rights'],
                "users" => $users,
                "apiKey" => DB::getGlobalSettings()->apiKey
            ]
        ]);
    }

    public static function taskGetAntragImage($request)
    {
        $path = PATH_DATA . 'ext_ausweis' . DS . 'ausweis' . DS . $request['path'];
        if (file_exists($path)) {
            return FILE::getFile($path);
        }
    }

    public static function taskGetFile($request)
    {

        if ($request['path']) {

            $user = DB::getSession()->getUser();
            if ($user) {
                switch ($user->getUserTyp(true)) {
                    case 'isPupil':
                        $users = [DB::getSession()->getUserID()];
                        break;
                    case 'isEltern':
                        $tmp_data = DB::getSession()->getUser()->getElternObject()->getMySchueler();
                        $users = [];
                        foreach ($tmp_data as $item) {
                            $users[] = $item->getUserID();
                        }
                        break;
                    case 'isTeacher':
                        $users = [DB::getSession()->getUserID()];
                        break;
                    case 'isNone':
                        $users = [DB::getSession()->getUserID()];
                        break;
                }
            }


            $str_userID = explode('-', explode('/', $request['path'])[0])[1];

            if (in_array($str_userID, $users)) {
                $path = PATH_DATA . 'ext_ausweis' . DS . 'ausweis' . DS . $request['path'];
                echo FILE::getFile($path);
            }


            exit;
        }
    }
}
