<?php

class MessageSendRights extends AbstractPage {
    private static $isAdmin = false;

    public function __construct() {
        parent::__construct(array("Nachrichten"));

        new errorPage();
    }

    public function execute() {
        new errorPage();
    }

    public static function getSettingsDescription() {
        $settings = [];


        return $settings;
    }

    public static function getSiteDisplayName() {
        return "Versandberechtigungen";
    }

    public static function hasSettings() {
        return false;
    }

    /**
     * Liest alle Nutzergruppen aus, die diese Seite verwendet. (Für die Benutzeradministration)
     * @return array(array('groupName' => '', 'beschreibung' => ''))
     */
    public static function getUserGroups() {
        return array();

    }

    public static function siteIsAlwaysActive() {
        return true;
    }

    public static function hasAdmin() {
        return true;
    }

    public static function getAdminGroup() {
        return "Webportal_Message_Admin";
    }

    public static function displayAdministration($selfURL) {

        $saveStrings = [
            'message_send_rights_teacher_single_teachers',
            'message_send_rights_teacher_all_teachers',
            'message_send_rights_teacher_fachschaften',
            'message_send_rights_teacher_klassenteams',
            'message_send_rights_teacher_klassenleitung',
            'message_send_rights_teacher_jahrsgangsstufen',
            'message_send_rights_teacher_single_pupils',
            'message_send_rights_teacher_whole_grades',
            'message_send_rights_teacher_whole_grades_only_own',
            'message_send_rights_teacher_single_parents',
            'message_send_rights_teacher_whole_grades_parents',
            'message_send_rights_teacher_whole_grades_parents_only_own',

            'message_send_rights_teacher_allow_unterricht_own',
            'message_send_rights_teacher_allow_unterricht_all',


            'message_send_rights_teacher_allow_reading_check',
            'message_send_rights_teacher_allow_data_question',

            'message_send_rights_teacher_reply_all',

            'message_send_rights_teacher_allow_schulleitung',
            'message_send_rights_teacher_allow_personalrat',
            'message_send_rights_teacher_allow_hausmeister',
            'message_send_rights_teacher_allow_verwaltung',

            'message_send_rights_pupils_single_teachers',
            'message_send_rights_pupils_single_teachers_only_own',
            'message_send_rights_pupils_all_teachers',


            'message_send_rights_pupils_allow_unterricht_own',
            'message_send_rights_pupils_allow_unterricht_all',


            'message_send_rights_pupils_fachschaften',
            'message_send_rights_pupils_klassenteams',
            'message_send_rights_pupils_klassenleitung',
            'message_send_rights_pupils_klassenleitung_only_own',
            'message_send_rights_pupils_jahrsgangsstufen',
            'message_send_rights_pupils_single_pupils',
            'message_send_rights_pupils_single_pupils_only_own',
            'message_send_rights_pupils_whole_grades',
            'message_send_rights_pupils_whole_grades_only_own',
            'message_send_rights_pupils_single_parents',
            'message_send_rights_pupils_single_parents_only_own_grades',
            'message_send_rights_pupils_whole_grades_parents',
            'message_send_rights_pupils_whole_grades_parents_only_own',
            'message_send_rights_pupils_allow_reading_check',
            'message_send_rights_pupils_allow_data_question',

            'message_send_rights_pupils_reply_all',


            'message_send_rights_pupils_allow_schulleitung',
            'message_send_rights_pupils_allow_personalrat',
            'message_send_rights_pupils_allow_hausmeister',
            'message_send_rights_pupils_allow_verwaltung',

            'message_send_rights_parents_single_teachers',
            'message_send_rights_parents_single_teachers_only_own',
            'message_send_rights_parents_all_teachers',


            'message_send_rights_parents_allow_unterricht_own',
            'message_send_rights_parents_allow_unterricht_all',


            'message_send_rights_parents_fachschaften',
            'message_send_rights_parents_klassenteams',
            'message_send_rights_parents_klassenteams_only_own',
            'message_send_rights_parents_klassenleitung',
            'message_send_rights_parents_klassenleitung_only_own',
            'message_send_rights_parents_jahrsgangsstufen',
            'message_send_rights_parents_single_pupils',
            'message_send_rights_parents_single_pupils_only_own',
            'message_send_rights_parents_whole_grades',
            'message_send_rights_parents_whole_grades_only_own',
            'message_send_rights_parents_single_parents',
            'message_send_rights_parents_single_parents_only_own_grades',
            'message_send_rights_parents_whole_grades_parents',
            'message_send_rights_parents_whole_grades_parents_only_own',
            'message_send_rights_parents_allow_reading_check',
            'message_send_rights_parents_allow_data_question',

            'message_send_rights_parents_reply_all',

            'message_send_rights_parents_allow_schulleitung',
            'message_send_rights_parents_allow_personalrat',
            'message_send_rights_parents_allow_hausmeister',
            'message_send_rights_parents_allow_verwaltung',
        ];

        $currentSettings = [];

        for($i = 0; $i < sizeof($saveStrings); $i++) {
            $name = str_replace("message_send_rights_","",$saveStrings[$i]);
            $currentSettings[$name] = DB::getSettings()->getBoolean($saveStrings[$i]) ? ' checked="checked"' : '';
        }



        if($_REQUEST['action'] == 'SavePermissions') {
            for($i = 0; $i < sizeof($saveStrings); $i++) {
                $name = str_replace("message_send_rights_","",$saveStrings[$i]);
                DB::getSettings()->setValue($saveStrings[$i],$_POST[$name] == "1");
            }


            header("Location: $selfURL&saved=1");
            exit(0);

        }

        $html = "";

        $usergroup = usergroup::getGroupByName('Webportal_Elternmail');

        if($_REQUEST['action'] == 'addUser') {
            $usergroup->addUser($_POST['userID']);
            header("Location: $selfURL&userAdded=1");
            exit(0);

        }

        if($_REQUEST['action'] == 'removeUser') {
            $usergroup->removeUser($_REQUEST['userID']);
            header("Location: $selfURL&userDeleted=1");
            exit(0);
        }

        // Aktuelle Benutzer suchen, die Zugriff haben

        $currentUserBlock = administrationmodule::getUserListWithAddFunction($selfURL, "messageallrights", "addUser", "removeUser", "Benutzer, die an alle Nachrichten mit allen Rechten versenden können.", "Unabhängig von normalen Sendeberechtigungen können diese Nutzer an alle Benutzer mit allen Rechten (Lesebestätigung etc.) senden.", 'Webportal_Elternmail');



        eval("\$html = \"" . DB::getTPL()->get("messages/admin/sendrights/index") . "\";");
        return $html;
    }

    public static function getAdminMenuIcon() {
        return 'fa fa-comments';
    }

    public static function getAdminMenuGroupIcon() {
        return 'fa fa-comments';
    }

    public static function getAdminMenuGroup() {
        return 'Nachrichten';
    }

    public static function init() {
        if (DB::getSession()) {
            self::$isAdmin = (DB::getSession()->isAdmin() || DB::getSession()->isMember('Webportal_Elternmail'));
        }
    }

    /**
     * Welche einzelnen Lehrer dürfen kontaktiert werden?
     * @return TeacherRecipient[]
     */
    public static function getAllowedTeachers() {
        if(self::$isAdmin) {
            return TeacherRecipient::getAllInstances();
        }

        if(DB::getSession()->isTeacher()) {
            if(DB::getSettings()->getBoolean('message_send_rights_teacher_single_teachers')) {
                return TeacherRecipient::getAllInstances();
            }
            else {
                return [];
            }
        }

        if(DB::getSession()->isPupil()) {
            if(DB::getSettings()->getBoolean('message_send_rights_pupils_single_teachers')) {
                if(DB::getSettings()->getBoolean('message_send_rights_pupils_single_teachers_only_own')) {
                    if(stundenplandata::getCurrentStundenplan() != null) {
                        $myTeacher = stundenplandata::getCurrentStundenplan()->getAllTeacherOfGrade(grade::getStundenplanGradeFromNormalGrade(DB::getSession()->getPupilObject()->getGrade()));
                        return TeacherRecipient::getAllInstancesForTeacherWithSujectList($myTeacher, DB::getSession()->getPupilObject()->getGrade());
                    }
                    else return [];

                }
                else return TeacherRecipient::getAllInstances();
            }
            else {
                return [];
            }
        }

        if(DB::getSession()->isEltern()) {
            if(DB::getSettings()->getBoolean('message_send_rights_parents_single_teachers')) {
                if(DB::getSettings()->getBoolean('message_send_rights_parents_single_teachers_only_own')) {
                    $klassen = DB::getSession()->getElternObject()->getKlassenObjectsAsArray();

                    /**
                     *
                     * @var lehrer[] $lehrer
                     */
                    $lehrer = [];

                    $recipients = [];

                    for($i = 0; $i < sizeof($klassen); $i++) {
                        $unterricht = SchuelerUnterricht::getUnterrichtForKlasse($klassen[$i]);



                        for($u = 0; $u < sizeof($unterricht); $u++) {
                            $lehrers = $unterricht[$u]->getLehrer();

                            if($lehrers && count((array)$lehrers) > 0) {

                                $found = false;
                                for($n = 0; $n < sizeof($lehrer); $n++) {
                                    if($lehrer[$n] != null && $lehrer[$n]->getAsvID() == $lehrers->getAsvID()) {
                                        $found = true;
                                    }
                                }

                                if(!$found) {
                                    $lehrer[] = $lehrers;
                                    $recipients[] = new TeacherRecipient($lehrers);

                                }
                            }

                        }

                    }





                    //Debugger::debugObject($recipients,1);

                    return $recipients;

                }
                else return TeacherRecipient::getAllInstances();
            }
            else {
                return [];
            }
        }

        return [];
    }

    public static function isAllTeacherAllowed() {
        if(self::$isAdmin) return true;

        if(DB::getSession()->isTeacher()) {
            return DB::getSettings()->getBoolean('message_send_rights_teacher_all_teachers');
        }

        if(DB::getSession()->isPupil()) {
            return DB::getSettings()->getBoolean('message_send_rights_pupils_all_teachers');
        }

        if(DB::getSession()->isEltern()) {
            return DB::getSettings()->getBoolean('message_send_rights_parents_all_teachers');
        }

        return false;
    }

    public static function isSchulleitungAllowed() {
        if(self::$isAdmin) return true;

        if(DB::getSession()->isTeacher()) {
            return DB::getSettings()->getBoolean('message_send_rights_teacher_allow_schulleitung');
        }

        if(DB::getSession()->isPupil()) {
            return DB::getSettings()->getBoolean('message_send_rights_pupils_allow_schulleitung');
        }

        if(DB::getSession()->isEltern()) {
            return DB::getSettings()->getBoolean('message_send_rights_parents_allow_schulleitung');
        }

        return false;
    }


    public static function isOwnUnterrichtAllowed() {
        if(self::$isAdmin) return true;

        if(DB::getSession()->isTeacher()) {
            return DB::getSettings()->getBoolean('message_send_rights_teacher_allow_unterricht_own');
        }

        if(DB::getSession()->isPupil()) {
            return DB::getSettings()->getBoolean('message_send_rights_pupils_allow_unterricht_own');
        }

        if(DB::getSession()->isEltern()) {
            return DB::getSettings()->getBoolean('message_send_rights_parents_allow_unterricht_own');
        }

        return false;
    }

    public static function isAllUnterrichtAllowed() {
        if(self::$isAdmin) return true;

        if(DB::getSession()->isTeacher()) {
            return DB::getSettings()->getBoolean('message_send_rights_teacher_allow_unterricht_all');
        }

        if(DB::getSession()->isPupil()) {
            return DB::getSettings()->getBoolean('message_send_rights_pupils_allow_unterricht_all');
        }

        if(DB::getSession()->isEltern()) {
            return DB::getSettings()->getBoolean('message_send_rights_parents_allow_unterricht_all');
        }

        return false;
    }

    public static function isVerwaltungAllowed() {
        if(self::$isAdmin) return true;

        if(DB::getSession()->isTeacher()) {
            return DB::getSettings()->getBoolean('message_send_rights_teacher_allow_verwaltung');
        }

        if(DB::getSession()->isPupil()) {
            return DB::getSettings()->getBoolean('message_send_rights_pupils_allow_verwaltung');
        }

        if(DB::getSession()->isEltern()) {
            return DB::getSettings()->getBoolean('message_send_rights_parents_allow_verwaltung');
        }

        return false;
    }

    public static function isHausmeisterAllowed() {
        if(self::$isAdmin) return true;

        if(DB::getSession()->isTeacher()) {
            return DB::getSettings()->getBoolean('message_send_rights_teacher_allow_hausmeister');
        }

        if(DB::getSession()->isPupil()) {
            return DB::getSettings()->getBoolean('message_send_rights_pupils_allow_hausmeister');
        }

        if(DB::getSession()->isEltern()) {
            return DB::getSettings()->getBoolean('message_send_rights_parents_allow_hausmeister');
        }

        return false;
    }

    public static function isPersonalratAllowed() {
        if(self::$isAdmin) return true;

        if(DB::getSession()->isTeacher()) {
            return DB::getSettings()->getBoolean('message_send_rights_teacher_allow_personalrat');
        }

        if(DB::getSession()->isPupil()) {
            return DB::getSettings()->getBoolean('message_send_rights_pupils_allow_personalrat');
        }

        if(DB::getSession()->isEltern()) {
            return DB::getSettings()->getBoolean('message_send_rights_parents_allow_personalrat');
        }

        return false;
    }


    public static function isFachschaftenAllowed() {
        if(self::$isAdmin) return true;

        if(DB::getSession()->isTeacher()) {
            return DB::getSettings()->getBoolean('message_send_rights_teacher_fachschaften');
        }

        if(DB::getSession()->isPupil()) {
            return DB::getSettings()->getBoolean('message_send_rights_pupils_fachschaften');
        }

        if(DB::getSession()->isEltern()) {
            return DB::getSettings()->getBoolean('message_send_rights_parents_fachschaften');
        }

        return false;
    }

    public static function getAllowedKlassenteams() {
        if(self::$isAdmin) return KlassenteamRecipient::getAllInstances();

        if(DB::getSession()->isTeacher()) {
            if(DB::getSettings()->getBoolean('message_send_rights_teacher_klassenteams')) {
                return KlassenteamRecipient::getAllInstances();
            }
        }

        if(DB::getSession()->isPupil()) {
            if(DB::getSettings()->getBoolean('message_send_rights_pupils_klassenteams')) {
                if(DB::getSettings()->getBoolean('message_send_rights_pupils_klassenteams_only_own')) {

                    return KlassenteamRecipient::getAllInstancesForGrade(grade::getMyGradesFromStundenplan());
                }
                else {
                    return KlassenteamRecipient::getAllInstances();

                }
            }
        }

        if(DB::getSession()->isEltern()) {
            if(DB::getSettings()->getBoolean('message_send_rights_parents_klassenteams')) {
                if(DB::getSettings()->getBoolean('message_send_rights_pupils_parents_only_own')) {

                    return KlassenteamRecipient::getAllInstancesForGrade(grade::getMyGradesFromStundenplan());
                }
                else {
                    return KlassenteamRecipient::getAllInstances();

                }
            }
        }

        return [];
    }

    public static function getAllowedKlassenleitungen() {
        if(self::$isAdmin) return KlassenleitungRecipient::getAllInstances();

        if(DB::getSession()->isTeacher()) {
            if(DB::getSettings()->getBoolean('message_send_rights_teacher_klassenleitung')) {
                return KlassenleitungRecipient::getAllInstances();
            }
        }

        if(DB::getSession()->isPupil()) {
            if(DB::getSettings()->getBoolean('message_send_rights_pupils_klassenleitung')) {
                if(DB::getSettings()->getBoolean('message_send_rights_pupils_klassenleitung_only_own')) {

                    return KlassenleitungRecipient::getAllInstancesForGrade(grade::getMyGradesFromStundenplan());
                }
                else {
                    return KlassenleitungRecipient::getAllInstances();
                }
            }
        }

        if(DB::getSession()->isEltern()) {
            if(DB::getSettings()->getBoolean('message_send_rights_parents_klassenleitung')) {
                if(DB::getSettings()->getBoolean('message_send_rights_parents_klassenleitung_only_own')) {

                    return KlassenleitungRecipient::getAllInstancesForGrade(grade::getMyGradesFromStundenplan());
                }
                else {
                    return KlassenleitungRecipient::getAllInstances();
                }
            }
        }

        return [];
    }

    public static function canRequestReadingConfirmation() {
        if(self::$isAdmin) return true;

        if(DB::getSession()->isTeacher()) {
            return DB::getSettings()->getBoolean('message_send_rights_teacher_allow_reading_check');
        }

        if(DB::getSession()->isPupil()) {
            return DB::getSettings()->getBoolean('message_send_rights_pupils_allow_reading_check');

        }

        if(DB::getSession()->isEltern()) {
            return DB::getSettings()->getBoolean('message_send_rights_parents_allow_reading_check');

        }


        return false;

    }

    public static function canAskQuestions() {
        if(self::$isAdmin) return true;

        if(DB::getSession()->isTeacher()) {
            return DB::getSettings()->getBoolean('message_send_rights_teacher_allow_data_question');
        }

        if(DB::getSession()->isPupil()) {
            return DB::getSettings()->getBoolean('message_send_rights_pupils_allow_data_question');

        }

        if(DB::getSession()->isEltern()) {
            return DB::getSettings()->getBoolean('message_send_rights_parents_allow_data_question');

        }


        return false;

    }

    public static function canReplyAll() {
        if(self::$isAdmin) return true;

        if(DB::getSession()->isTeacher()) {
            return DB::getSettings()->getBoolean('message_send_rights_teacher_reply_all');
        }

        if(DB::getSession()->isPupil()) {
            return DB::getSettings()->getBoolean('message_send_rights_pupils_reply_all');

        }

        if(DB::getSession()->isEltern()) {
            return DB::getSettings()->getBoolean('message_send_rights_parents_reply_all');

        }


        return false;

    }

    public static function onlyForSchool() {
        return [];
    }

    /**
     * @return PupilRecipient[]
     */
    public static function getAllowedPupils() {
        if(self::$isAdmin) {
            return PupilRecipient::getAllInstances();
        }

        if(DB::getSession()->isTeacher()) {
            if(DB::getSettings()->getBoolean('message_send_rights_teacher_single_pupils')) {
                return PupilRecipient::getAllInstances();
            }
            else {
                return [];
            }
        }

        if(DB::getSession()->isPupil()) {
            if(DB::getSettings()->getBoolean('message_send_rights_pupils_single_pupils')) {
                if(DB::getSettings()->getBoolean('message_send_rights_pupils_single_pupils_only_own')) {
                    return PupilRecipient::getInstancesForGrades([DB::getSession()->getPupilObject()->getKlasse()]);
                }
                else return PupilRecipient::getAllInstances();
            }
            else {
                return [];
            }
        }

        if(DB::getSession()->isEltern()) {
            if(DB::getSettings()->getBoolean('message_send_rights_parents_single_pupils')) {
                if(DB::getSettings()->getBoolean('message_send_rights_parents_single_pupils_only_own')) {
                    return PupilRecipient::getInstancesForGrades(DB::getSession()->getElternObject()->getKlassenAsArray());

                }
                else return PupilRecipient::getAllInstances();
            }
            else {
                return [];
            }
        }

        return [];
    }

    public static function getAllowedPupilGrades() {
        if(self::$isAdmin) {
            return PupilsOfGrade::getAllInstances();
        }

        if(DB::getSession()->isTeacher()) {
            if(DB::getSettings()->getBoolean('message_send_rights_teacher_whole_grades')) {
                if(DB::getSettings()->getBoolean('message_send_rights_teacher_whole_grades_only_own')) {
                    $stundenplan = stundenplandata::getCurrentStundenplan();

                    if($stundenplan != null) {
                        return PupilsOfGrade::getOnly($stundenplan->getAllGradesForTeacher(DB::getSession()->getTeacherObject()->getKuerzel()));
                    }
                    else return [];
                }
                else {
                    return PupilsOfGrade::getAllInstances();
                }

            }
            else {
                return [];
            }
        }

        if(DB::getSession()->isPupil()) {
            if(DB::getSettings()->getBoolean('message_send_rights_pupils_whole_grades')) {
                if(DB::getSettings()->getBoolean('message_send_rights_pupils_whole_grades_only_own')) {
                    return PupilsOfGrade::getInstancesForGrades([DB::getSession()->getPupilObject()->getKlasse()]);
                }
                else return PupilsOfGrade::getAllInstances();
            }
            else {
                return [];
            }
        }

        if(DB::getSession()->isEltern()) {
            if(DB::getSettings()->getBoolean('message_send_rights_parents_whole_grades')) {
                if(DB::getSettings()->getBoolean('message_send_rights_parents_whole_grades_only_own')) {
                    return PupilsOfGrade::getInstancesForGrades(DB::getSession()->getElternObject()->getKlassenAsArray());
                }
                else return PupilsOfGrade::getAllInstances();
            }
            else {
                return [];
            }
        }

        return [];
    }

    public static function getAllowedParentsOfPupilsGrades() {
        if(self::$isAdmin) {
            return ParentsOfGrade::getAllInstances();
        }

        if(DB::getSession()->isTeacher()) {
            if(DB::getSettings()->getBoolean('message_send_rights_teacher_whole_grades_parents')) {
                if(DB::getSettings()->getBoolean('message_send_rights_teacher_whole_grades_parents_only_own')) {
                    $stundenplan = stundenplandata::getCurrentStundenplan();

                    if($stundenplan != null) {
                        return ParentsOfGrade::getOnly($stundenplan->getAllGradesForTeacher(DB::getSession()->getTeacherObject()->getKuerzel()));
                    }
                    else return [];
                }
                else {
                    return ParentsOfGrade::getAllInstances();
                }

            }
            else {
                return [];
            }
        }

        if(DB::getSession()->isPupil()) {
            if(DB::getSettings()->getBoolean('message_send_rights_pupils_whole_grades_parents')) {
                if(DB::getSettings()->getBoolean('message_send_rights_pupils_whole_grades_parents_only_own')) {
                    return ParentsOfGrade::getInstancesForGrades([DB::getSession()->getPupilObject()->getKlasse()]);
                }
                else return ParentsOfGrade::getAllInstances();
            }
            else {
                return [];
            }
        }

        if(DB::getSession()->isEltern()) {
            if(DB::getSettings()->getBoolean('message_send_rights_parents_whole_grades_parents')) {
                if(DB::getSettings()->getBoolean('message_send_rights_parents_whole_grades_parents_only_own')) {
                    return ParentsOfGrade::getInstancesForGrades(DB::getSession()->getElternObject()->getKlassenAsArray());
                }
                else return ParentsOfGrade::getAllInstances();
            }
            else {
                return [];
            }
        }

        return [];
    }

    /**
     * @return ParentRecipient[]
     */
    public static function getAllowedParents() {
        if(self::$isAdmin) {
            return ParentRecipient::getAllInstances();
        }

        if(DB::getSession()->isTeacher()) {
            if(DB::getSettings()->getBoolean('message_send_rights_teacher_single_parents')) {
                return ParentRecipient::getAllInstances();
            }
            else {
                return [];
            }
        }

        if(DB::getSession()->isPupil()) {
            if(DB::getSettings()->getBoolean('message_send_rights_pupils_single_parents')) {
                if(DB::getSettings()->getBoolean('message_send_rights_pupils_single_parents_only_own_grades')) {
                    return ParentRecipient::getInstancesForGrades([DB::getSession()->getPupilObject()->getKlasse()]);
                }
                else return ParentRecipient::getAllInstances();
            }
            else {
                return [];
            }
        }

        if(DB::getSession()->isEltern()) {
            if(DB::getSettings()->getBoolean('message_send_rights_parents_single_parents')) {
                if(DB::getSettings()->getBoolean('message_send_rights_parents_single_parents_only_own')) {
                    return ParentRecipient::getInstancesForGrades(DB::getSession()->getElternObject()->getKlassenAsArray());

                }
                else return ParentRecipient::getAllInstances();
            }
            else {
                return [];
            }
        }

        return [];
    }

}
