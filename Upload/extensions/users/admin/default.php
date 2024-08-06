<?php



class extUsersAdminDefault extends AbstractPage {

	public static function getSiteDisplayName() {
		return '<i class="fas fa-user-shield"></i> Benutzer - Übersicht';
	}

	public function __construct($request = [], $extension = []) {
		parent::__construct(array( self::getSiteDisplayName() ), false, false, false, $request, $extension);
		$this->checkLogin();
	}

	public function execute() {

		//$this->getRequest();
		//$this->getAcl();

        $acl = $this->getAcl();
        //$user = DB::getSession()->getUser();

        if ( !$this->canAdmin() ) {
            new errorPage('Kein Zugriff');
        }

        $unsetLehrer = 0;
        $lehrer = lehrer::getAll(true);
        if ($lehrer) {
            for($i = 0; $i < count($lehrer); $i++) {

                if( $lehrer[$i]->getUserID() == 0 && $lehrer[$i]->istActive() ) {
                    $unsetLehrer++;
                }
            }
        }

        $unsetSchueler = 0;
        $schueler = schueler::getAll();
        if ($schueler) {
            for($i = 0; $i < count($schueler); $i++) {

                if( $schueler[$i]->getUserID() == 0 ) {
                    $unsetSchueler++;
                }
            }
        }

        $unsetEltern = 0;
        $schuelerOhneCodeSQL = DB::getDB()->query("SELECT * FROM eltern_email WHERE elternUserID IS NULL ");
        while ($eltern = DB::getDB()->fetch_array($schuelerOhneCodeSQL)) {
            $unsetEltern++;
        }


        $this->render([
            "tmpl" => "default",
            "scripts" => [
                PATH_EXTENSION . 'tmpl/scripts/default/dist/js/chunk-vendors.js',
                PATH_EXTENSION . 'tmpl/scripts/default/dist/js/app.js'
            ],
            "data" => [
                "apiURL" => "rest.php/users",
                "acl" => $acl['rights'],
                "lehrerUserMode" => DB::getGlobalSettings()->lehrerUserMode,
                "schuelerUserMode" => DB::getGlobalSettings()->schuelerUserMode,
                "elternUserMode" => DB::getGlobalSettings()->elternUserMode,
                "lehrerAnzahl" => count($lehrer),
                "unsetLehrer" => $unsetLehrer,
                "schuelerAnzahl" => count($schueler),
                "unsetSchueler" => $unsetSchueler,
                //"elternAnzahl" => count($schueler),
                "unsetEltern" => $unsetEltern

            ]
        ]);

	}

    public function taskCreateTeacher() {

        include_once PATH_EXTENSIONS . 'users'.DS.'models' . DS .'Users.class.php';
        $class = new extUsersModelUsers();
        if ( $class->createTeachers() ) {
            $this->reloadWithoutParam('task');
        }
        new errorPage('Anlegen der Benutzer fehlgeschlagen');
        exit();

    }

    public function taskCreateSchueler() {

        include_once PATH_EXTENSIONS . 'users'.DS.'models' . DS .'Users.class.php';
        $class = new extUsersModelUsers();
        if ( $class->createSchueler() ) {
            $this->reloadWithoutParam('task');
        }
        new errorPage('Anlegen der Benutzer fehlgeschlagen');
        exit();

    }

    public function taskCreateEltern() {

        include_once PATH_EXTENSIONS . 'users'.DS.'models' . DS .'Users.class.php';
        $class = new extUsersModelUsers();
        if ( $class->createEltern() ) {
            $this->reloadWithoutParam('task');
        }
        new errorPage('Anlegen der Benutzer fehlgeschlagen');
        exit();

    }



}
