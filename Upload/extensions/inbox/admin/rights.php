<?php



class extInboxAdminRights extends AbstractPage {
	
	public static function getSiteDisplayName() {
		return '<i class="fa fas fa-plug"></i> Inbox - Versandberechtigungen';
	}

	public function __construct($request = [], $extension = []) {
		parent::__construct(array( self::getSiteDisplayName() ), false, false, false, $request, $extension);
		$this->checkLogin();
	}

	public function execute() {

		//$this->getRequest();
		//$this->getAcl();

        if ( !$this->canWrite() ) {
            new errorPage('Kein Zugriff');
        }
		
		$this->render([
			"tmplHTML" => '<div class="box"><div class="box-body"><div id=app></div></div></div>',
			"scripts" => [
                PATH_COMPONENTS.'system/adminSettings2/dist/js/chunk-vendors.js',
                PATH_COMPONENTS.'system/adminSettings2/dist/js/app.js'
			],
			"data" => [
				"selfURL" => URL_SELF,
				"settings" => $this->getSettings()
			]

		]);

	}


    public static function getSettingsDescription() {

        $userGroups = usergroup::getAllOwnGroups();

        $options = [];

        for($i = 0; $i < sizeof($userGroups); $i++) {
            $options[] = [
                'value' => md5($userGroups[$i]->getName()),
                'name' => $userGroups[$i]->getName()
            ];
        }

        $settings =  [


            [
                'name' => "extInbox-acl-pupils-klassen",
                'typ' => 'ACL',
                'title' => "Wer darf allen Schüler*innen der Klassen schreiben?",
                'desc' => ""
            ],
            [
                'name' => "extInbox-acl-pupils-single",
                'typ' => 'ACL',
                'title' => "Wer darf allen Schüler*innen schreiben/suchen?",
                'desc' => ""
            ],
            [
                'name' => "extInbox-acl-pupils-own",
                'typ' => 'ACL',
                'title' => "Schüler*innen der eigenen Klassen dürfen von wem angeschrieben werden?",
                'desc' => "Eltern: Klassen der Kinder"
            ],

            [
                'name' => "extInbox-acl-parents-klassen",
                'typ' => 'ACL',
                'title' => "Wer darf allen Eltern der Klassen schreiben?",
                'desc' => ""
            ],
            [
                'name' => "extInbox-acl-parents-single",
                'typ' => 'ACL',
                'title' => "Wer darf allen Eltern schreiben/suchen?",
                'desc' => ""
            ],
            [
                'name' => "extInbox-acl-parents-own",
                'typ' => 'ACL',
                'title' => "Eltern der eigenen Klassen dürfen von wem angeschrieben werden?",
                'desc' => "Eltern: Klassen der Kinder"
            ],

            [
                'name' => "extInbox-acl-teachers-klassen",
                'typ' => 'ACL',
                'title' => "Wer darf allen Lehrer*innen der Klassen schreiben?",
                'desc' => ""
            ],
            [
                'name' => "extInbox-acl-teachers-single",
                'typ' => 'ACL',
                'title' => "Wer darf allen Lehrer*innen schreiben/suchen?",
                'desc' => ""
            ],
            [
                'name' => "extInbox-acl-teachers-leitung",
                'typ' => 'ACL',
                'title' => "Wer darf allen Klassenleitungen schreiben/suchen?",
                'desc' => ""
            ],
            [
                'name' => "extInbox-acl-teachers-fachschaft",
                'typ' => 'ACL',
                'title' => "Wer darf allen Fachschaften schreiben/suchen?",
                'desc' => ""
            ],
            [
                'name' => "extInbox-acl-teachers-own",
                'typ' => 'ACL',
                'title' => "Lehrer*innen der eigenen Klassen dürfen von wem angeschrieben werden?",
                'desc' => "Eltern: Klassen der Kinder; Nur für Schüler*innen und Eltern"
            ],

            [
                'name' => "extInbox-acl-inboxs-inboxs",
                'typ' => 'ACL',
                'title' => "Wer darf allen Postfächern schreiben?",
                'desc' => ""
            ],
            [
                'name' => "extInbox-acl-inboxs-groups",
                'typ' => 'ACL',
                'title' => "Wer darf allen Gruppen schreiben?",
                'desc' => ""
            ],
            [
                'name' => "extInbox-acl-inboxs-blocklist-pupils",
                'typ' => 'TEXT',
                'title' => "Welche Postfächer und Gruppen dürfen Schüler*innen nicht anschreiben?",
                'desc' => "Gruppen kommagetrennt"
            ],
            [
                'name' => "extInbox-acl-inboxs-blocklist-eltern",
                'typ' => 'TEXT',
                'title' => "Welche Postfächer und Gruppen dürfen Eltern nicht anschreiben?",
                'desc' => "Gruppen kommagetrennt"
            ],
            [
                'name' => "extInbox-acl-inboxs-blocklist-teachers",
                'typ' => 'TEXT',
                'title' => "Welche Postfächer und Gruppen dürfen Lehrer*innen nicht anschreiben?",
                'desc' => "Gruppen kommagetrennt"
            ],

            [
                'name' => "extInbox-acl-inboxs-confirm",
                'typ' => 'ACL',
                'title' => "Wer darf Lesebestätigungen anfordern?",
                'desc' => ""
            ]
        ];

        return $settings;

    }

    public function taskSave($postData) {

        $request = $this->getRequest();
        if ($request['page'] && $postData['settings']) {
            foreach($postData['settings'] as $item) {

                echo "INSERT INTO settings (settingName, settingValue, settingsExtension)
				values ('" .DB::getDB()->escapeString($item['name']) . "',
				'" . DB::getDB()->escapeString(($item['value'])) . "'
				,'" . DB::getDB()->escapeString(($request['page'])) . "')
				ON DUPLICATE KEY UPDATE settingValue='" . DB::getDB()->escapeString($item['value']) . "'";

                DB::getDB()->query("INSERT INTO settings (settingName, settingValue, settingsExtension)
				values ('" .DB::getDB()->escapeString($item['name']) . "',
				'" . DB::getDB()->escapeString(($item['value'])) . "'
				,'" . DB::getDB()->escapeString(($request['page'])) . "')
				ON DUPLICATE KEY UPDATE settingValue='" . DB::getDB()->escapeString($item['value']) . "'");
            }
            echo json_encode(['done' => 'true']);
        } else {
            echo json_encode(['error' => 'Fehler beim Speichern!']);
        }
    }



}
