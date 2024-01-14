<?php



class extFilecollectAdminAcl extends AbstractPage {

	public static function getSiteDisplayName() {
		return '<i class="fas fa-user-shield"></i> Filecollect - Benutzerrechte';
	}

	public function __construct($request = [], $extension = []) {
		parent::__construct(array( self::getSiteDisplayName() ), false, false, false, $request, $extension);
		//$this->checkLogin();
	}

	public function execute() {

        $user = DB::getSession()->getUser();

        if ( !$user->isAnyAdmin() ) {
            new errorPage('Kein Zugriff');
        }

        $form = [
            "title" => "Meine Rechte",
            "desc" => "hier der desc text",
            "acl" => [
                "schueler" => [
                    "read" => 1,
                    "write" => 1,
                    "delete" => 1,
                ],
                "lehrer" => [
                    "read" => 1,
                    "write" => 1,
                    "delete" => 1,
                ],
                "eltern" => [
                    "read" => 1,
                    "write" => 1,
                    "delete" => 0,
                ],
                "none" => [
                    "read" => 0,
                    "write" => 0,
                    "delete" => 0,
                ]
            ]

        ];

		$this->render([
			"tmplHTML" => '<div class="box"><div class="box-body"><div id=app></div></div></div>',
			"scripts" => [
                PATH_COMPONENTS.'system/adminAcl2/dist/js/chunk-vendors.js',
                PATH_COMPONENTS.'system/adminAcl2/dist/js/app.js'
			],
			"data" => [
				"selfURL" => URL_SELF,
				"acl" => $this->getAclAll(),
                "form" => $form,
				"adminList" => self::getGroupMembers('Webportal_Administrator'),
				"adminExtension" => self::getGroupMembers(self::getAdminGroup())
			]
		]);
	}

}
