<?php



class extAdmintoolsAdminChangeuser extends AbstractPage
{

    public static function getSiteDisplayName()
    {
        return '<i class="fas fa-tools"></i> Admintools - Changeuser';
    }

    public function __construct($request = [], $extension = [])
    {
        parent::__construct(array(self::getSiteDisplayName()), false, false, false, $request, $extension);
        $this->checkLogin();
    }

    public function execute()
    {

        //$this->getRequest();
        $acl = $this->getAcl();

        $user = DB::getSession()->getUser();
        if (!$user->isAdmin()) {
            new errorPage('Kein Zugriff');
        }



        $this->render([
            "tmplHTML" => '<div class="box"><div class="box-body"><div id=app></div></div></div>',
            "scripts" => [
                PATH_EXTENSION . 'tmpl/scripts/changeuser/dist/js/chunk-vendors.js',
                PATH_EXTENSION . 'tmpl/scripts/changeuser/dist/js/app.js'
            ],
            "data" => [
                "apiURL" => "rest.php/admintools",
                "restURL" => "rest.php/GetUser/",
                "acl" => $acl['rights']
            ]

        ]);

    }



    public function taskLoginUser($postData)
    {

        if ( $this->canAdmin()) {

            $request = $this->getRequest();

            if ($request['uid']) {

                $user = user::getUserByID(intval($request['uid']));
                if ($user != null) {
                    Fremdlogin::createFremdlogin($user, 'Adminlogin.');

                    DB::getDB()->query("UPDATE sessions SET sessionIsDebug=1, sessionUserID='" . intval($request['uid']) . "' WHERE sessionID='" . DB::getSession()->getSessionID() . "'");
                    header("Location: index.php");
                    exit(0);
                }
            }
        }

        header("Location: index.php");
        exit(0);

    }


}
