<?php


class getRecipients extends AbstractRest
{

    protected $statusCode = 200;


    public function execute($input, $request)
    {


        $userID = DB::getSession()->getUser()->getUserID();
        if (!$userID) {
            return [
                'error' => true,
                'msg' => 'Missing User ID'
            ];
        }
        $userType = DB::getSession()->getUser()->getUserTyp(true);


        $acl = $this->getAcl();
        if (!$this->canRead()) {
            return [
                'error' => true,
                'msg' => 'Kein Zugriff'
            ];
        }


        /*
            Fachschaften
        */
        $fachschaften = [];
        $faecher = fach::getAll();
        foreach ($faecher as $fach) {
            /*
            $users = [];
            $teachers = $fach->getFachLehrer();
            foreach($teachers as $teacher) {
                $foo = $teacher->getUser()->getCollection();
                $users[] = $foo['id'];
            }
            */
            $fachschaften[] = [
                'id' => $fach->getID(),
                'title' => $fach->getKurzform()
                //,'users' => $users
            ];

        }




        /*
            Klassen
        */
        $klassen = [];
        $klassenData = klasse::getAllKlassen();
        foreach ($klassenData as $klasse) {
            $stufe = $klasse->getKlassenstufe() ? $klasse->getKlassenstufe() : (int)$klasse->getKlassenName();
            if ($stufe) {
                if (!is_array($klassen[$stufe])) {
                    $klassen[$stufe] = [];
                }
                $klassen[$stufe][] = $klasse->getKlassenName();
            }
        }


        /*
            Gruppen
        */

        $groups = [];
        if (EXTENSION::isActive('ext.zwiebelgasse.users')) {
            include_once PATH_EXTENSIONS . 'users' . DS . 'models' . DS . 'Groups.class.php';
            $class = new extUsersModelGroups();
            $tmp_data = $class->getAll();
            foreach ($tmp_data as $item) {
                $groups[] = $item->getCollection();
            }
        }

        /*
            Postfach Gruppe
        */
        $inboxs = [];
        $dataSQL = DB::getDB()->query("SELECT id, title FROM ext_inboxs WHERE type = 'group'");
        while ($data = DB::getDB()->fetch_array($dataSQL, true)) {
            $inboxs[] = $data;
        }




        /*
            Einzelnen Accounts
        */
        $inboxUsers = [];
        $dataSQL = DB::getDB()->query("SELECT inbox_id, user_id, isPublic FROM ext_inbox_user WHERE isPublic IS NOT NULL");
        while ($data = DB::getDB()->fetch_array($dataSQL, true)) {
            if ($userType && $data['isPublic']) {
                $isPublic = json_decode($data['isPublic']);
                if ($isPublic->$userType || DB::getSession()->getUser()->isAdmin() ) {
                    $tempUser = user::getUserByID($data['user_id']);
                    $inboxUsers[] = [
                        'id' => $data['inbox_id'],
                        'title' => $tempUser->getDisplayName()
                    ];
                }
            }
        }


        $acl = [
            'pupils' => [
                'klassen' => $this->getUserAcl('extInbox-acl-pupils-klassen', $userType),
                'single' => $this->getUserAcl('extInbox-acl-pupils-single', $userType),
                'own' => $this->getUserAcl('extInbox-acl-pupils-own', $userType),
                'all' => $this->getUserAcl('extInbox-acl-pupils-all', $userType)
            ],
            'parents' => [
                'klassen' => $this->getUserAcl('extInbox-acl-parents-klassen', $userType),
                'single' => $this->getUserAcl('extInbox-acl-parents-single', $userType),
                'own' => $this->getUserAcl('extInbox-acl-parents-own', $userType),
                'all' => $this->getUserAcl('extInbox-acl-parents-all', $userType)
            ],
            'teachers' => [
                'klassen' => $this->getUserAcl('extInbox-acl-teachers-klassen', $userType),
                'single' => $this->getUserAcl('extInbox-acl-teachers-single', $userType),
                'leitung' => $this->getUserAcl('extInbox-acl-teachers-leitung', $userType),
                'fachschaft' => $this->getUserAcl('extInbox-acl-teachers-fachschaft', $userType),
                'own' => $this->getUserAcl('extInbox-acl-teachers-own', $userType),
                'all' => $this->getUserAcl('extInbox-acl-teachers-all', $userType)
            ],
            'inboxs' => [
                'inboxs' => $this->getUserAcl('extInbox-acl-inboxs-inboxs', $userType),
                'groups' => $this->getUserAcl('extInbox-acl-inboxs-groups', $userType)
            ],
            'confirm' => $this->getUserAcl('extInbox-acl-inboxs-confirm', $userType)
        ];

        $userData = DB::getSession()->getUser()->getCollection(true);
        $own = [
            'klassen' => $userData['klassen'],
            'pupils' => [],
            'parents' => [],
            'teachers' => []
        ];
        if ($userData['klassen']) {
            include_once PATH_EXTENSIONS . 'inbox' . DS . 'models' . DS . 'Inbox2.class.php';
            $Inbox = new extInboxModelInbox2();

            foreach ($userData['klassen'] as $foo) {
                $klassenObj = klasse::getByName((string)$foo);
                if ($klassenObj) {
                    $schuelers = $klassenObj->getSchueler();
                    if ($schuelers) {
                        $retPupil = [
                            'title' => $foo,
                            'inboxs' => []
                        ];
                        $retParents = [
                            'title' => $foo,
                            'inboxs' => []
                        ];
                        foreach ($schuelers as $schueler) {
                            if ($schueler->getUserID()) {
                                $inbox = $Inbox->getByUserIDFirst($schueler->getUserID());
                                if ($inbox) {
                                    $retPupil['inboxs'][] = $inbox->getCollection();
                                }
                            }
                            $elterns = $schueler->getParentsUsers();
                            foreach ($elterns as $eltern) {
                                if ($eltern->getUserID()) {
                                    $inbox = $Inbox->getByUserIDFirst($schueler->getUserID());
                                    if ($inbox) {
                                        $retParents['inboxs'][] = $inbox->getCollection();
                                    }
                                }
                            }
                        }
                        $own['pupils'][] = $retPupil;
                        $own['parents'][] = $retParents;
                    }
                    $retTeacher = [
                        'title' => $foo,
                        'inboxs' => []
                    ];
                    $teachers = $klassenObj->getKlassenlehrer();
                    if ($teachers) {
                        foreach ($teachers as $teacher) {
                            if ($teacher->getUserID()) {
                                $inbox = $Inbox->getByUserIDFirst($teacher->getUserID());
                                if ($inbox) {
                                    $retTeacher['inboxs'][] = $inbox->getCollection();
                                }
                            }
                        }
                    }
                    $own['teachers'][] = $retTeacher;

                }
            }
        }

        if ($userType == 'isPupil') {
            $blocklist = DB::getSettings()->getValue('extInbox-acl-inboxs-blocklist-pupils');
            $groups = $this->onBlocklist($groups, $blocklist);
            $inboxs = $this->onBlocklist($inboxs, $blocklist);
        }
        if ($userType == 'isEltern') {
            $blocklist = DB::getSettings()->getValue('extInbox-acl-inboxs-blocklist-eltern');
            $groups = $this->onBlocklist($groups, $blocklist);
            $inboxs = $this->onBlocklist($inboxs, $blocklist);
        }
        if ($userType == 'isTeacher') {
            $blocklist = DB::getSettings()->getValue('extInbox-acl-inboxs-blocklist-teachers');
            $groups = $this->onBlocklist($groups, $blocklist);
            $inboxs = $this->onBlocklist($inboxs, $blocklist);
            $acl['teachers']['own'] = false;
        }
        if ($userType == 'isNone') {
            $acl['teachers']['own'] = false;
        }

        return [
            'acl' => $acl,
            'own' => $own,
            'klassen' => $klassen,
            'group' => $groups,
            'inboxUsers' => $inboxUsers,
            'fachschaft' => $fachschaften,
            'inboxs' => $inboxs
        ];


    }

    private function onBlocklist( $data = false, $strBlocklist = false) {
        if ($data && is_array($data) && $strBlocklist) {
            $arr = explode(',', $strBlocklist);
            if ($arr) {
                foreach ($arr as $foo) {
                    foreach ($data as $key => $item) {
                        if ($item['title'] == $foo) {
                            array_splice($data, $key, 1);
                        }
                    }
                }
            }
        }
        return $data;
    }

    private function getUserAcl($val, $userType) {

        if (!$val || !$userType) {
            return false;
        }
        if (DB::getSession()->getUser()->isAdmin()) {
            return true;
        }
        $data = DB::getSettings()->getValue($val);
        if ($data) {
            $data = json_decode($data);
            if ($data) {
                if ($data->$userType) {
                    return true;
                }
            }
        }
        return false;
    }
    /*
    private function isAllowed($val)
    {
        $content = DB::getSettings()->getValue($val);
        if ($content) {
            $aclSetting = json_decode($content);
            if ($aclSetting) {
                return $aclSetting->{DB::getSession()->getUser()->getUserTyp(true)};

            }
        }
        return false;
    }
    */

    /**
     * Set Allowed Request Method
     * (GET, POST, ...)
     *
     * @return String
     */
    public function getAllowedMethod()
    {
        return 'GET';
    }


    public function needsAppAuth()
    {
        return true;
    }
    /**
     * Muss der Benutzer eingeloggt sein?
     * Ist Eine Session vorhanden
     * @return Boolean
     */
    public function needsUserAuth()
    {
        return false;
    }

    /**
     * Ist eine Admin berechtigung nötig?
     * only if : needsUserAuth = true
     * @return Boolean
     */
    public function needsAdminAuth()
    {
        return false;
    }

    /**
     * Ist eine System Authentifizierung nötig? (mit API key)
     * only if : needsUserAuth = false
     * @return Boolean
     */
    public function needsSystemAuth()
    {
        return false;
    }

}

?>