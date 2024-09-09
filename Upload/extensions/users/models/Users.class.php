<?php

class extUsersModelUsers extends ExtensionModel
{

    static $table = 'users';

    static $fields = [
        'userID'
    ];


    static $defaults = [
        /*
        'userRemoteUserID' => '',
        'userAsvID' => '',
        'userAutoresponseText' => '',
        'userLastPasswordChangeRemote' => 0,
        'userSignature' => '',
        'userMailInitialPassword' => '',
        'userPush' => ''
        */
    ];


    /**
     * Constructor
     * @param $data
     */
    public function __construct($data = false)
    {
        parent::__construct($data, self::$table ? self::$table : false,
            [
                'table_id' => 'userID'
            ]);
        self::setModelFields(self::$fields, self::$defaults);
    }


    public function getCollection($full = false, $url = false)
    {

        $collection = parent::getCollection();

        /*
        if ($full) {
            if ($this->getData('lehrerUserID')) {
                $collection['user'] = user::getCollectionByID($this->getData('lehrerUserID'));
            }
        }
        */

        return $collection;
    }

    public function createEltern()
    {
        if (DB::getGlobalSettings()->elternUserMode == "ASV_CODE") {
            $schuelerOhneCodeSQL = DB::getDB()->query("SELECT * FROM schueler WHERE schuelerAsvID NOT IN (SELECT codeSchuelerAsvID FROM eltern_codes)");
            while ($schueler = DB::getDB()->fetch_array($schuelerOhneCodeSQL)) {
                $code = substr(md5($schueler['schuelerAsvID']), 0, 5) . "-" . substr(md5(rand()), 0, 10);
                DB::getDB()->query("INSERT INTO eltern_codes (codeSchuelerAsvID, codeText, codeUserID) values('" . $schueler['schuelerAsvID'] . "','" . $code . "',0)");
            }
            return true;
        }
        if (DB::getGlobalSettings()->elternUserMode == "ASV_MAIL") {
            return true;
        }
        return false;
    }

    public function createSchueler()
    {
        if (DB::getGlobalSettings()->schuelerUserMode == "ASV") {

            $schueler_create = [];
            $dataSQL = DB::getDB()->query("SELECT * FROM schueler WHERE schuelerUserID=0 LIMIT 0, 50");
            while ($data = DB::getDB()->fetch_array($dataSQL, true)) {
                $schueler_create[] = $data;
            }


            foreach ($schueler_create as $data) {
                $newPassword = substr(md5(rand(0, 3000)), 1, 10);

                // Benutzeranlegen
                DB::getDB()->query("INSERT INTO users
                (
                  userName,
                  userFirstName,
                  userLastName,
                  userCachedPasswordHash,
                  userCachedPasswordHashTime,
                  userNetwork,
                  userAsvID,
                 userAutoresponseText
                ) values(
                  '_PLACEHOLDER_',
                  '" . DB::getDB()->escapeString($data['schuelerRufname']) . "',
                  '" . DB::getDB()->escapeString($data['schuelerName']) . "',
                  '" . login::hash($newPassword) . "',
                  UNIX_TIMESTAMP(),
                  'SCHULEINTERN_SCHUELER',
                  '" . DB::getDB()->escapeString($data['schuelerAsvID']) . "',
                  ''
                ) ON DUPLICATE KEY UPDATE userAsvID='" . DB::getDB()->escapeString($data['schuelerAsvID']) . "'
              ");
                $newUserID = DB::getDB()->insert_id();
                DB::getDB()->query("UPDATE schueler SET schuelerUserID='" . $newUserID . "' WHERE schuelerAsvID='" . $data['schuelerAsvID'] . "'");
                DB::getDB()->query("INSERT INTO initialpasswords (initialPasswordUserID, initialPassword) values('" . $newUserID . "','" . $newPassword . "')");
            }

            DB::getDB()->query("UPDATE users SET userName = CONCAT('S', userID) WHERE userName='_PLACEHOLDER_'");

            return true;
        }
        return false;
    }

    public function createTeachers()
    {

        if (DB::getGlobalSettings()->lehrerUserMode == "ASV") {

            $lehrer = lehrer::getAll(true);

            for ($i = 0; $i < sizeof($lehrer); $i++) {

                if ($lehrer[$i]->getUserID() == 0 && $lehrer[$i]->istActive()) {

                    $newPassword = substr(md5(rand()), 1, 10);

                    // Benutzeranlegen
                    DB::getDB()->query("INSERT INTO users
                        (
                          userName,
                          userFirstName,
                          userLastName,
                          userCachedPasswordHash,
                          userCachedPasswordHashTime,
                          userNetwork,
                          userAsvID,
                            userAutoresponseText
                        ) values(
                          '" . DB::getDB()->escapeString($lehrer[$i]->getKuerzel()) . "',
                          '" . DB::getDB()->escapeString($lehrer[$i]->getRufname()) . "',
                          '" . DB::getDB()->escapeString($lehrer[$i]->getName()) . "',
                          '" . login::hash($newPassword) . "',
                          UNIX_TIMESTAMP(),
                          'SCHULEINTERN_LEHRER',
                          '" . DB::getDB()->escapeString($lehrer[$i]->getAsvID()) . "',
                          ''
                        ) ON DUPLICATE KEY UPDATE userAsvID='" . DB::getDB()->escapeString($lehrer[$i]->getAsvID()) . "'
                      ");

                    $userID = DB::getDB()->insert_id();

                    DB::getDB()->query("UPDATE lehrer SET lehrerUserID='" . $userID . "' WHERE lehrerID='" . $lehrer[$i]->getXMLID() . "'");
                    DB::getDB()->query("INSERT INTO initialpasswords (initialPasswordUserID, initialPassword) values('" . $userID . "','" . $newPassword . "')");
                } else if ($lehrer[$i]->getUser() != null && !$lehrer[$i]->istActive()) {
                    $lehrer[$i]->getUser()->deleteUser();
                } else if ($lehrer[$i]->getUserID() > 0 && !$lehrer[$i]->istActive()) {            // UserID vorhanden, aber kein Benutzer
                    $lehrer[$i]->setUserID();
                }
            }
            return true;

        }


        return false;

    }


}
