<?php

/**
 * Aufruf nach einem Update
 */
class Update extends AbstractPage
{

    public function __construct()
    {
        parent::__construct(array(
            "Update"
        ));

        // Cache deaktivieren, damit das Update überhaupt laufen kann.
        DB::getCache()->disableCache();
    }

    public function execute()
    {
        $updateInfo = file_get_contents("../data/update.json");

        if ($updateInfo === false) {
            echo("update fail. (not availible)");
            exit(0);
        }

        $updateInfo = json_decode($updateInfo, true);

        $fromVersion = $updateInfo['updateFromVersion'];
        $toVersion = $updateInfo['updateToVersion'];

        
        // Compare Database with File and execute query
        // Deaktiviert, weil Buggy
        /** $dbFile = "../data/update/Upload/database.sql";
        if (file_exists($dbFile)) {
            $newStructure = file_get_contents("../framework/database.sql");
            $this->updateDatabase($newStructure);
        } **/       // Temporär entfernt für Update auf 1.3.0, Spi

        // Updates durchführen
        if ($this->performUpdate($fromVersion, $toVersion)) {
            DB::getSettings()->setValue("current-release-id", $updateInfo['updateToReleaseID']);
            DB::getSettings()->setValue('currentVersion', DB::getVersion());
        } else {
            echo("Kein Update möglich.");
            exit(0);
        }


        // Admins informieren

        // Release Info abholen

        $infoToReleaseID = file_get_contents(DB::getGlobalSettings()->updateServer . "/api/release/" . $updateInfo['updateToReleaseID']);

        $versionInfo = json_decode($infoToReleaseID, true);

        if(isset($versionInfo['changeLog'])) {
            $messageSender = new MessageSender();
            $messageSender->setSender(user::getSystemUser());

            $recipientHandler = new RecipientHandler("");

            $recipientHandler->addRecipient(new GroupRecipient("Webportal_Administrator"));

            // Jeden Modul Admin informieren

            $allPagesAdminGroups = requesthandler::getAllAdminGroups();
            for($i = 0; $i < sizeof($allPagesAdminGroups); $i++) {
                $recipientHandler->addRecipient(new GroupRecipient($allPagesAdminGroups[$i]));
            }


            $messageSender->setRecipients($recipientHandler);

            $messageSender->setSubject('Neue Version installiert');
            $messageSender->setText("Es wurde eine neue Version der Portalsoftware SchuleIntern installiert. <br><br><pre>Änderungen:\r\n" . $versionInfo['changeLog'] . "</pre>" . "<br><br><br><br><i>Dies ist eine automatisch erzeugte Nachricht.</i>");
            $messageSender->send();
        }

        // /Admins informieren



        // Template Cache leeren
        DB::getDB()->query("TRUNCATE `templates`");

        // CLI Scripte erneuern
        rename("../cli", "../cli_" . $fromVersion);
        rename("../data/update/Upload/cli", "../cli");

        // Abschluss
        unlink("../data/update.json");


        new infoPage("Update durchgeführt. Portal ist wieder in Betrieb.", "index.php");
    }

    private function performUpdate($from, $to)
    {

        if ($from == "1.0" && $to == "1.0.1") {
            $this->from100to101();
        }

        if ($from == "1.0.0" && $to == "1.0.1") {
            $this->from100to101();
        }

        if ($from == "1.0.1" && $to == "1.0.1") {
            $this->from100to101();
        }

        if ($from == "1.0.1" && $to == "1.1.0") {
            $this->from101to110();
        }

        if ($from == "1.1.0" && $to == "1.1.1") {
            $this->from110to111();
        }

        if ($from == "1.1.1" && $to == "1.1.2") {
            $this->from111to112();
        }

        if ($from == "1.1.1" && $to == "1.2.0") {
            $this->from111to120();
        }

        if($from == "1.1.2" && $to = "1.2.0") {
            $this->from111to120();
        }

        if ($from == "1.2.0" && $to == "1.2.1") {
            $this->from120to121();
        }

        if ($from == "1.2.1" && $to == "1.2.2") {
            $this->from121to122();
        }

        if ($from == "1.2.2" && $to == "1.3.0") {
            $this->from122to130();
        }

        if ($from == "1.3.0" && $to == "1.3.1") {
            $this->from130to131();
        }

        if ($from == "1.3.1" && $to == "1.3.2") {
            $this->from131to132();
        }

        // WIP
        if ($from == "1.3.2" && $to == "1.3.3") {
            $this->from132to133();
        }
        return true;
    }

    private  function from132to133() {

        // TODO: edit config.php TEST !!!!
        $config = '
        /**
         * Domain des Extension Servers
         * @var string
         */
        public $extensionsServer = "https://store.zwiebel-intern.de/";';
        file_put_contents(PATH_ROOT.'config'.DS.'config.php',$config,FILE_APPEND);


        DB::getDB()->query("CREATE TABLE `extensions` (
              `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
              `name` varchar(255) DEFAULT NULL,
              `uniqid` varchar(255) DEFAULT NULL,
              `version` int(11) DEFAULT NULL,
              `active` tinyint(11) DEFAULT NULL,
              `folder` varchar(255) DEFAULT NULL,
              `menuCat` varchar(25) DEFAULT NULL,
              PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

        DB::getDB()->query("CREATE TABLE `raumplan_stunden` (
                `stundeID` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `stundenplanID` int(11) DEFAULT NULL,
                `stundeKlasse` varchar(20) DEFAULT NULL,
                `stundeLehrer` varchar(20) DEFAULT NULL,
                `stundeFach` varchar(20) DEFAULT NULL,
                `stundeRaum` varchar(20) DEFAULT NULL,
                `stundeDatum` date DEFAULT NULL,
                `stundeStunde` int(2) DEFAULT NULL,
                PRIMARY KEY (`stundeID`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

        DB::getDB()->query("CREATE TABLE `kalender_allInOne` (
            `kalenderID`        int(11) NOT NULL AUTO_INCREMENT,
            `kalenderName`      varchar(255) NOT NULL,
            `kalenderColor`     varchar(7) DEFAULT NULL,
            `kalenderSort`      tinyint(1) DEFAULT NULL,
            `kalenderPreSelect` tinyint(1) DEFAULT NULL,
            `kalenderAcl`       int(11) DEFAULT NULL,
            `kalenderFerien`    tinyint(1) DEFAULT '0',
            `kalenderPublic`    tinyint(1) DEFAULT NULL,
            PRIMARY KEY (`kalenderID`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;");

        DB::getDB()->query("INSERT INTO `kalender_allInOne` (`kalenderID`, `kalenderName`, `kalenderColor`, `kalenderSort`, `kalenderPreSelect`,
                                 `kalenderAcl`, `kalenderFerien`, `kalenderPublic`)
        VALUES (1, 'Schulkalender', '#2E64FE', 1, 1, 4, 0, 1),
               (2, 'Interner Kalender', '#FE2E64', 2, 1, 5, 0, 0),
               (3, 'Ferien', '#00a65a', 3, 1, 6, 1, 1);");

        DB::getDB()->query("CREATE TABLE `kalender_allInOne_eintrag`
        (
            `eintragID`           int(11) NOT NULL AUTO_INCREMENT,
            `kalenderID`          int(11) NOT NULL,
            `eintragKategorieID`  int(11) NOT NULL DEFAULT '0',
            `eintragTitel`        varchar(255) NOT NULL,
            `eintragDatumStart`   date         NOT NULL,
            `eintragTimeStart`    time         NOT NULL,
            `eintragDatumEnde`    date         NOT NULL,
            `eintragTimeEnde`     time         NOT NULL,
            `eintragOrt`          varchar(255) NOT NULL,
            `eintragKommentar`    tinytext     NOT NULL,
            `eintragUserID`       int(11) NOT NULL,
            `eintragCreatedTime`  datetime     NOT NULL,
            `eintragModifiedTime` datetime     NOT NULL,
            PRIMARY KEY (`eintragID`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;");

        DB::getDB()->query("CREATE TABLE `kalender_allInOne_kategorie` (
           `kategorieID` int(11) NOT NULL AUTO_INCREMENT,
           `kategorieKalenderID` int(11) NOT NULL,
           `kategorieName` varchar(255) NOT NULL,
           `kategorieFarbe` varchar(7) NOT NULL,
           `kategorieIcon` varchar(255) NOT NULL,
               PRIMARY KEY (`kategorieID`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;");



        $this->updateComponentsFolder(131);
        $this->updateCssJSFolder(131);
        $this->updateImagesFolder(131);

    }

    private  function from131to132() {

        // Keine DB Änderungen

        $this->updateComponentsFolder(131);
        $this->updateCssJSFolder(131);
        $this->updateImagesFolder(131);
    }

    private  function from130to131() {

        // Keine DB Änderungen

        $this->updateComponentsFolder(130);
        $this->updateCssJSFolder(130);
        $this->updateImagesFolder(130);
    }

    private function from122to130() {

        // sql changes:
        // absenzen_absenzen => `absenzGanztagsNotiz` tinytext NOT NULL,
        // ganztags_gruppen => `raum` varchar(30) DEFAULT NULL,
        // ganztags_gruppen => `farbe` varchar(8) DEFAULT NULL,


        $sql = "ALTER TABLE `ganztags_gruppen` ADD `raum` VARCHAR(30) NULL DEFAULT NULL AFTER `name`, ADD `farbe` VARCHAR(8) NULL DEFAULT NULL AFTER `raum`;
ALTER TABLE `absenzen_absenzen` ADD COLUMN `absenzGanztagsNotiz` tinytext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `absenzKommtSpaeter`;
ALTER TABLE `absenzen_absenzen` ADD INDEX `absenzDatum`(`absenzDatum`) USING BTREE;
ALTER TABLE `absenzen_absenzen` ADD INDEX `absenzSchuelerAsvID`(`absenzSchuelerAsvID`) USING BTREE;
ALTER TABLE `absenzen_attestpflicht` ADD INDEX `attestpflichtStart`(`attestpflichtStart`, `attestpflichtEnde`) USING BTREE;
ALTER TABLE `absenzen_attestpflicht` ADD INDEX `schuelerAsvID`(`schuelerAsvID`) USING BTREE;
ALTER TABLE `absenzen_krankmeldungen` ADD INDEX `krankmeldungAbsenzID`(`krankmeldungAbsenzID`) USING BTREE;
ALTER TABLE `absenzen_krankmeldungen` ADD INDEX `krankmeldungDate`(`krankmeldungDate`) USING BTREE;
ALTER TABLE `absenzen_krankmeldungen` ADD INDEX `krankmeldungElternID`(`krankmeldungElternID`) USING BTREE;
ALTER TABLE `absenzen_krankmeldungen` ADD INDEX `krankmeldungSchuelerASVID`(`krankmeldungSchuelerASVID`) USING BTREE;
ALTER TABLE `absenzen_sanizimmer` ADD INDEX `sanizimmerSchuelerAsvID`(`sanizimmerSchuelerAsvID`) USING BTREE;
ALTER TABLE `acl` MODIFY COLUMN `id` int(10) UNSIGNED NOT NULL FIRST;
ALTER TABLE `acl` MODIFY COLUMN `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `acl` ADD INDEX `moduleClass`(`moduleClass`) USING BTREE;
ALTER TABLE `acl` ADD INDEX `moduleClassParent`(`moduleClassParent`) USING BTREE;
ALTER TABLE `aufeinenblick_settings` ADD INDEX `aufeinenblickUserID`(`aufeinenblickUserID`) USING BTREE;
ALTER TABLE `ausleihe_ausleihe` ADD INDEX `ausleiheObjektID`(`ausleiheObjektID`) USING BTREE;
ALTER TABLE `ausweise` ADD INDEX `ausweisArt`(`ausweisArt`) USING BTREE;
ALTER TABLE `ausweise` ADD INDEX `ausweisBezahlt`(`ausweisBezahlt`) USING BTREE;
ALTER TABLE `ausweise` ADD INDEX `ausweisErsteller`(`ausweisErsteller`) USING BTREE;
ALTER TABLE `ausweise` ADD INDEX `ausweisStatus`(`ausweisStatus`) USING BTREE;
ALTER TABLE `bad_mail` ADD INDEX `badMailDone`(`badMailDone`) USING BTREE;
ALTER TABLE `beurlaubung_antrag` ADD INDEX `antragAbsenzID`(`antragAbsenzID`) USING BTREE;
ALTER TABLE `beurlaubung_antrag` ADD INDEX `antragSchuelerAsvID`(`antragSchuelerAsvID`) USING BTREE;
ALTER TABLE `beurlaubung_antrag` ADD INDEX `antragUserID`(`antragUserID`) USING BTREE;
CREATE TABLE `cache`  (
  `cacheKey` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `cacheTTL` int(11) NOT NULL,
  `cacheType` enum('object','text','base64') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'text',
  `cacheData` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`cacheKey`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;
ALTER TABLE `cron_execution` ADD INDEX `cronName`(`cronName`) USING BTREE;
ALTER TABLE `dokumente_dateien` ADD INDEX `gruppenID`(`gruppenID`) USING BTREE;
ALTER TABLE `eltern_adressen` ADD INDEX `adresseSchuelerAsvID`(`adresseSchuelerAsvID`) USING BTREE;
ALTER TABLE `eltern_codes` ADD INDEX `codeSchuelerAsvID`(`codeSchuelerAsvID`) USING BTREE;
ALTER TABLE `eltern_email` ADD INDEX `elternUserID`(`elternUserID`) USING BTREE;
ALTER TABLE `fremdlogin` ADD INDEX `userID`(`userID`) USING BTREE;
ALTER TABLE `ganztags_gruppen` MODIFY COLUMN `id` int(10) UNSIGNED NOT NULL FIRST;
ALTER TABLE `ganztags_gruppen` MODIFY COLUMN `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `ganztags_schueler` ADD INDEX `gruppe`(`gruppe`) USING BTREE;
ALTER TABLE `icsfeeds` ADD INDEX `feedKey2`(`feedKey2`) USING BTREE;
ALTER TABLE `icsfeeds` ADD INDEX `feedKey`(`feedKey`) USING BTREE;
ALTER TABLE `icsfeeds` ADD INDEX `feedType`(`feedType`) USING BTREE;
ALTER TABLE `icsfeeds` ADD INDEX `feedUserID`(`feedUserID`) USING BTREE;
ALTER TABLE `image_uploads` ADD INDEX `uploadUserName`(`uploadUserName`) USING BTREE;
ALTER TABLE `initialpasswords` ADD INDEX `initialPasswordUserID`(`initialPasswordUserID`) USING BTREE;
ALTER TABLE `initialpasswords` ADD INDEX `passwordPrinted`(`passwordPrinted`) USING BTREE;
ALTER TABLE `kalender_andere` ADD INDEX `eintragDatumStart`(`eintragDatumStart`, `eintragDatumEnde`) USING BTREE;
ALTER TABLE `kalender_andere` ADD INDEX `kalenderID`(`kalenderID`) USING BTREE;
ALTER TABLE `kalender_extern` ADD INDEX `eintragDatumStart`(`eintragDatumStart`, `eintragDatumEnde`) USING BTREE;
ALTER TABLE `kalender_extern` ADD INDEX `kalenderID`(`kalenderID`) USING BTREE;
ALTER TABLE `kalender_ferien` ADD INDEX `ferienStart`(`ferienStart`, `ferienEnde`) USING BTREE;
ALTER TABLE `kalender_klassentermin` ADD INDEX `eintragDatumStart`(`eintragDatumStart`, `eintragDatumEnde`) USING BTREE;
ALTER TABLE `kalender_lnw` ADD INDEX `eintragArt`(`eintragArt`) USING BTREE;
ALTER TABLE `kalender_lnw` ADD INDEX `eintragDatumStart`(`eintragDatumStart`, `eintragDatumEnde`) USING BTREE;
ALTER TABLE `kalender_lnw` ADD INDEX `eintragKlasse`(`eintragKlasse`) USING BTREE;
ALTER TABLE `kalender_lnw` ADD INDEX `eintragLehrer`(`eintragLehrer`) USING BTREE;
ALTER TABLE `klassentagebuch_fehl` ADD INDEX `fehlDatum`(`fehlDatum`) USING BTREE;
ALTER TABLE `klassentagebuch_fehl` ADD INDEX `fehlKlasse`(`fehlKlasse`) USING BTREE;
ALTER TABLE `klassentagebuch_fehl` ADD INDEX `fehlLehrer`(`fehlLehrer`) USING BTREE;
ALTER TABLE `klassentagebuch_klassen` ADD INDEX `entryGrade`(`entryGrade`) USING BTREE;
ALTER TABLE `klassentagebuch_klassen` ADD INDEX `entryTeacher`(`entryTeacher`) USING BTREE;
CREATE TABLE `kms`  (
  `kmsID` int(11) NOT NULL AUTO_INCREMENT,
  `kmsAktenzeichen` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `kmsTitel` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
  `kmsSchularten` int(11) NULL DEFAULT NULL,
  `kmsUploadID` int(11) NOT NULL,
  PRIMARY KEY (`kmsID`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;
ALTER TABLE `laufzettel` ADD INDEX `laufzettelDatum`(`laufzettelDatum`) USING BTREE;
ALTER TABLE `laufzettel` ADD INDEX `laufzettelErsteller`(`laufzettelErsteller`) USING BTREE;
ALTER TABLE `laufzettel_stunden` ADD INDEX `laufzettelID`(`laufzettelID`) USING BTREE;
ALTER TABLE `laufzettel_stunden` ADD INDEX `laufzettelLehrer`(`laufzettelLehrer`) USING BTREE;
ALTER TABLE `lehrer` ADD INDEX `lehrerID`(`lehrerID`) USING BTREE;
ALTER TABLE `lehrer` ADD INDEX `lehrerKuerzel`(`lehrerKuerzel`) USING BTREE;
ALTER TABLE `lehrer` ADD INDEX `lehrerUserID`(`lehrerUserID`) USING BTREE;
ALTER TABLE `lerntutoren` ADD INDEX `lerntutorSchuelerAsvID`(`lerntutorSchuelerAsvID`) USING BTREE;
CREATE TABLE `loginstat`  (
  `statTimestamp` timestamp NOT NULL DEFAULT current_timestamp,
  `statLoggedInTeachers` int(11) NULL DEFAULT NULL,
  `statLoggedInStudents` int(11) NULL DEFAULT NULL,
  `statLoggedInParents` int(11) NULL DEFAULT NULL,
  PRIMARY KEY (`statTimestamp`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;
ALTER TABLE `mail_send` ADD INDEX `mailSent`(`mailSent`, `mailCrawler`) USING BTREE;
ALTER TABLE `mebis_accounts` ADD INDEX `mebisAccountNachname`(`mebisAccountNachname`) USING BTREE;
ALTER TABLE `mebis_accounts` ADD INDEX `mebisAccountVorname`(`mebisAccountVorname`) USING BTREE;
ALTER TABLE `mensa_speiseplan` MODIFY COLUMN `id` int(10) UNSIGNED NOT NULL FIRST;
ALTER TABLE `mensa_speiseplan` MODIFY COLUMN `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `messages_folders` ADD INDEX `folderUserID`(`folderUserID`) USING BTREE;
ALTER TABLE `messages_messages` DROP INDEX `messageUserID`;
ALTER TABLE `messages_messages` ADD COLUMN `messageMyRecipientSaveString` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'In welchem Empfänger ist der Empfänger dieser Nachricht enthalten. (Wen betrifft es.)' AFTER `messageIsForwardFrom`;
ALTER TABLE `messages_messages` ADD COLUMN `messageIsConfidential` tinyint(1) NOT NULL DEFAULT 0 AFTER `messageMyRecipientSaveString`;
ALTER TABLE `messages_messages` ADD INDEX `messageIsSentViaEMail`(`messageIsSentViaEMail`) USING BTREE;
ALTER TABLE `messages_messages` ADD INDEX `messageTime`(`messageTime`) USING BTREE;
ALTER TABLE `messages_questions` ADD INDEX `questionUserID`(`questionUserID`) USING BTREE;
ALTER TABLE `messages_questions_answers` ADD INDEX `answerMessageID`(`answerMessageID`) USING BTREE;
ALTER TABLE `messages_questions_answers` ADD INDEX `answerQuestionID`(`answerQuestionID`) USING BTREE;
ALTER TABLE `modul_admin_notes` ADD INDEX `noteModuleName`(`noteModuleName`) USING BTREE;
ALTER TABLE `resetpassword` ADD INDEX `resetUserID`(`resetUserID`) USING BTREE;
ALTER TABLE `schueler` ADD INDEX `schuelerEintrittDatum`(`schuelerEintrittDatum`) USING BTREE;
ALTER TABLE `schueler` ADD INDEX `schuelerKlasse`(`schuelerKlasse`) USING BTREE;
ALTER TABLE `schueler` ADD INDEX `schuelerUserID`(`schuelerUserID`) USING BTREE;
CREATE TABLE `schueler_quarantaene`  (
  `quarantaeneID` int(11) NOT NULL AUTO_INCREMENT,
  `quarantaeneSchuelerAsvID` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `quarantaeneStart` date NULL DEFAULT NULL,
  `quarantaeneEnde` date NULL DEFAULT NULL,
  `quarantaeneArt` enum('I','K1','S') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'S',
  `quarantaeneKommentar` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `quarantaeneCreatedByUserID` int(11) NOT NULL,
  `quarantaeneFileUpload` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`quarantaeneID`) USING BTREE,
  INDEX `quarantaeneSchuelerAsvID`(`quarantaeneSchuelerAsvID`) USING BTREE,
  INDEX `quarantaeneStart`(`quarantaeneStart`, `quarantaeneEnde`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;
ALTER TABLE `schuelerinfo_dokumente` ADD INDEX `dokumentSchuelerAsvID`(`dokumentSchuelerAsvID`) USING BTREE;
ALTER TABLE `schulbuch_ausleihe` ADD INDEX `ausleiheExemplarID`(`ausleiheExemplarID`) USING BTREE;
ALTER TABLE `schulbuch_ausleihe` ADD INDEX `ausleiherLehrerAsvID`(`ausleiherLehrerAsvID`) USING BTREE;
ALTER TABLE `schulbuch_ausleihe` ADD INDEX `ausleiherSchuelerAsvID`(`ausleiherSchuelerAsvID`) USING BTREE;
ALTER TABLE `schulbuch_ausleihe` ADD INDEX `ausleiherUserID`(`ausleiherUserID`) USING BTREE;
ALTER TABLE `schulbuch_ausleihe` ADD INDEX `ausleiheStartDatum`(`ausleiheStartDatum`, `ausleiheEndDatum`) USING BTREE;
ALTER TABLE `schulbuch_ausleihe` ADD INDEX `rueckgeberUserID`(`rueckgeberUserID`) USING BTREE;
ALTER TABLE `schulbuch_exemplare` ADD INDEX `exemplarBuchID`(`exemplarBuchID`) USING BTREE;
ALTER TABLE `schulen` ADD INDEX `schuleNummer`(`schuleNummer`) USING BTREE;
ALTER TABLE `sessions` ADD INDEX `sessionLastActivity`(`sessionLastActivity`) USING BTREE;
ALTER TABLE `sessions` ADD INDEX `sessionType`(`sessionType`) USING BTREE;
ALTER TABLE `sessions` ADD INDEX `sessionUserID`(`sessionUserID`) USING BTREE;
CREATE TABLE `settings_history`  (
  `settingHistoryID` int(11) NOT NULL AUTO_INCREMENT,
  `settingHistoryName` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `settingHistoryChangeTime` int(11) NOT NULL,
  `settingHistoryOldValue` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `settingHistoryNewValue` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `settingHistoryUserID` int(11) NULL DEFAULT NULL,
  PRIMARY KEY (`settingHistoryID`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;
ALTER TABLE `site_activation` ADD INDEX `siteIsActive`(`siteIsActive`) USING BTREE;
ALTER TABLE `sprechtag` ADD COLUMN `sprechtagInfotext` longtext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `sprechtagBeginTime`;
ALTER TABLE `sprechtag` ADD COLUMN `sprechtagIsOnline` tinyint(1) NOT NULL AFTER `sprechtagInfotext`;
ALTER TABLE `sprechtag` ADD INDEX `sprechtagDate`(`sprechtagDate`) USING BTREE;
ALTER TABLE `sprechtag` ADD INDEX `sprechtagIsActive`(`sprechtagIsActive`) USING BTREE;
ALTER TABLE `sprechtag_buchungen` ADD COLUMN `meetingURL` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL AFTER `elternUserID`;
ALTER TABLE `sprechtag_buchungen` ADD INDEX `lehrerKuerzel`(`lehrerKuerzel`) USING BTREE;
ALTER TABLE `sprechtag_buchungen` ADD INDEX `schuelerAsvID`(`schuelerAsvID`) USING BTREE;
ALTER TABLE `sprechtag_buchungen` ADD INDEX `sprechtagID`(`sprechtagID`) USING BTREE;
ALTER TABLE `sprechtag_slots` ADD INDEX `sprechtagID`(`sprechtagID`) USING BTREE;
ALTER TABLE `stundenplan_aufsichten` ADD INDEX `stundenplanID`(`stundenplanID`) USING BTREE;
ALTER TABLE `stundenplan_plaene` ADD INDEX `stundenplanAb`(`stundenplanAb`, `stundenplanBis`) USING BTREE;
ALTER TABLE `stundenplan_stunden` ADD INDEX `stundenplanID`(`stundenplanID`) USING BTREE;
ALTER TABLE `unterricht` ADD INDEX `unterrichtFachID`(`unterrichtFachID`) USING BTREE;
ALTER TABLE `unterricht` ADD INDEX `unterrichtLehrerID`(`unterrichtLehrerID`) USING BTREE;
ALTER TABLE `unterricht_besuch` ADD INDEX `unterrichtID`(`unterrichtID`, `schuelerAsvID`) USING BTREE;
ALTER TABLE `uploads` ADD INDEX `fileAccessCode`(`fileAccessCode`) USING BTREE;
ALTER TABLE `users` ADD INDEX `userName`(`userName`(1024)) USING BTREE;
ALTER TABLE `users_groups` ADD INDEX `groupName`(`groupName`) USING BTREE;
ALTER TABLE `users_groups` ADD INDEX `userID`(`userID`) USING BTREE;
ALTER TABLE `users_groups_own` ADD INDEX `groupIsMessageRecipient`(`groupIsMessageRecipient`) USING BTREE;
DROP TABLE `absenzen_absenzen_stunden`;
DROP TABLE `beurlaubungsantraege`;
DROP TABLE `database_database`;
DROP TABLE `database_user2database`;
DROP TABLE `database_users`;
DROP TABLE `elternbriefe`;
DROP TABLE `ffbumfrage`;
DROP TABLE `klassenkalender`;
DROP TABLE `klassentagebuch_lehrer`;
DROP TABLE `pupil_grade`;
DROP TABLE `rsu_persons`;
DROP TABLE `rsu_print`;
DROP TABLE `rsu_sections`;
DROP TABLE `user_images`;
DROP TABLE `wlan_schueler`;";

        $sql = explode(";", $sql);

        // Datenbank Update
        for($i = 0; $i < sizeof($sql); $i++) {
            DB::getDB()->query($sql[$i],true);        // Datenbank Update
        }


        $this->updateComponentsFolder(122);
        $this->updateCssJSFolder(122);
        $this->updateImagesFolder(122);
    }

    private function from121to122() {
        
        $this->updateComponentsFolder(121);
        $this->updateCssJSFolder(121);

        DB::getDB()->query("CREATE TABLE IF NOT EXISTS `acl` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `moduleClass` varchar(50) DEFAULT NULL,
            `moduleClassParent` varchar(50) DEFAULT NULL,
            `schuelerRead` tinyint(1) DEFAULT '0',
            `schuelerWrite` tinyint(1) DEFAULT '0',
            `schuelerDelete` tinyint(1) DEFAULT '0',
            `elternRead` tinyint(1) DEFAULT '0',
            `elternWrite` tinyint(1) DEFAULT '0',
            `elternDelete` tinyint(1) DEFAULT '0',
            `lehrerRead` tinyint(1) DEFAULT '0',
            `lehrerWrite` tinyint(1) DEFAULT '0',
            `lehrerDelete` tinyint(1) DEFAULT '0',
            `noneRead` tinyint(1) DEFAULT '0',
            `noneWrite` tinyint(1) DEFAULT '0',
            `noneDelete` tinyint(1) DEFAULT '0',
            `owneRead` tinyint(1) DEFAULT '0',
            `owneWrite` tinyint(1) DEFAULT '0',
            `owneDelete` tinyint(1) DEFAULT '0',
            PRIMARY KEY (`id`)
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

        DB::getDB()->query("CREATE TABLE IF NOT EXISTS `mensa_order` (
            `userID` int(11) DEFAULT NULL,
            `speiseplanID` int(11) DEFAULT NULL,
            `time` datetime DEFAULT NULL
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

        DB::getDB()->query("CREATE TABLE IF NOT EXISTS `mensa_speiseplan` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `date` date DEFAULT NULL,
            `title` varchar(255) DEFAULT NULL,
            `preis_schueler` float DEFAULT NULL,
            `preis_default` float DEFAULT NULL,
            `desc` text,
            `vegetarisch` tinyint(1) DEFAULT NULL,
            `vegan` tinyint(1) DEFAULT NULL,
            `laktosefrei` tinyint(1) DEFAULT NULL,
            `glutenfrei` tinyint(1) DEFAULT NULL,
            `bio` tinyint(1) DEFAULT NULL,
            `regional` tinyint(1) DEFAULT NULL,
            PRIMARY KEY (`id`)
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

 
    }


    private function from120to121() {
        $this->updateComponentsFolder(120);
        $this->updateCssJSFolder(120);
    }
    
    private function from111to120() {

        DB::getDB()->query("ALTER TABLE `database_user2database` MODIFY COLUMN `rights` int(11) NOT NULL AFTER `databaseID`;", true);

        DB::getDB()->query("ALTER TABLE `database_users` MODIFY COLUMN `userUserID` int(11) NOT NULL AFTER `userPassword`;", true);

        DB::getDB()->query("CREATE TABLE `ganztags_gruppen`  (
          `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
          `sortOrder` int(11) NULL DEFAULT NULL,
          `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
          PRIMARY KEY (`id`) USING BTREE
        ) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;", true);

        DB::getDB()->query("CREATE TABLE `ganztags_schueler`  (
          `asvid` varchar(200) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
          `info` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
          `gruppe` int(11) NULL DEFAULT NULL,
          `tag_mo` tinyint(1) NULL DEFAULT NULL,
          `tag_di` tinyint(1) NULL DEFAULT NULL,
          `tag_mi` tinyint(1) NULL DEFAULT NULL,
          `tag_do` tinyint(1) NULL DEFAULT NULL,
          `tag_fr` tinyint(1) NULL DEFAULT NULL,
          `tag_sa` tinyint(1) NULL DEFAULT NULL,
          `tag_so` tinyint(1) NULL DEFAULT NULL,
          PRIMARY KEY (`asvid`) USING BTREE
        ) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;", true);

        DB::getDB()->query("ALTER TABLE `klassentagebuch_pdf` ADD PRIMARY KEY (`pdfKlasse`, `pdfJahr`, `pdfMonat`) USING BTREE;", true);

        DB::getDB()->query("ALTER TABLE `lehrer` ADD COLUMN `lehrerNameVorgestellt` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL AFTER `lehrerName`;", true);

        DB::getDB()->query("ALTER TABLE `lehrer` ADD COLUMN `lehrerNameNachgestellt` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL AFTER `lehrerNameVorgestellt`;", true);

        DB::getDB()->query("ALTER TABLE `mail_change_requests` CHARACTER SET = latin1, COLLATE = latin1_swedish_ci;", true);

        DB::getDB()->query("ALTER TABLE `mail_change_requests` MODIFY COLUMN `changeRequestSecret` text CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL AFTER `changeRequestTime`;", true);

        DB::getDB()->query("ALTER TABLE `mail_change_requests` MODIFY COLUMN `changeRequestNewMail` varchar(200) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL AFTER `changeRequestSecret`;", true);

        DB::getDB()->query("ALTER TABLE `messages_messages` ADD INDEX `messagesKey`(`messageUserID`, `messageSender`, `messageFolder`, `messageFolderID`, `messageIsRead`, `messageIsDeleted`) USING BTREE;", true);

        DB::getDB()->query("ALTER TABLE `nextcloud_users` CHARACTER SET = latin1, COLLATE = latin1_swedish_ci;", true);

        DB::getDB()->query("ALTER TABLE `nextcloud_users` MODIFY COLUMN `nextcloudUsername` text CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL AFTER `userID`;", true);

        DB::getDB()->query("ALTER TABLE `nextcloud_users` MODIFY COLUMN `userQuota` varchar(200) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL AFTER `userPasswordSet`;", true);

        DB::getDB()->query("ALTER TABLE `nextcloud_users` MODIFY COLUMN `userGroups` text CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL AFTER `userQuota`;", true);

        DB::getDB()->query("ALTER TABLE `schueler` ADD COLUMN `schuelerNameVorgestellt` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL AFTER `schuelerName`;", true);

        DB::getDB()->query("ALTER TABLE `schueler` ADD COLUMN `schuelerNameNachgestellt` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL AFTER `schuelerNameVorgestellt`;", true);

        DB::getDB()->query("ALTER TABLE `schueler` ADD COLUMN `schuelerGanztagBetreuung` int(11) NOT NULL DEFAULT 0 AFTER `schuelerFoto`;", true);

        DB::getDB()->query("ALTER TABLE `sprechtag_slots` MODIFY COLUMN `slotIsOnlineBuchbar` int(11) NOT NULL DEFAULT 1 AFTER `slotIsPause`;", true);

        DB::getDB()->query("ALTER TABLE `stundenplan_stunden` MODIFY COLUMN `stundeLehrer` varchar(20) CHARACTER SET utf8 COLLATE utf8_german2_ci NOT NULL AFTER `stundeKlasse`;", true);

        DB::getDB()->query("ALTER TABLE `users` ADD COLUMN `userAutoresponse` tinyint(1) NOT NULL DEFAULT 0 AFTER `userMailInitialPassword`", true);

        DB::getDB()->query("ALTER TABLE `users` ADD COLUMN `userAutoresponseText` longtext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `userAutoresponse`", true);

        $this->updateCssJSFolder(111);
        $this->updateComponentsFolder(111);
        $this->updateImagesFolder(111);

        DB::getDB()->query("UPDATE kalender_lnw SET eintragDatumEnde=eintragDatumStart");       // Bugfix Kalender Termine nach verschieben mit falschem Enddatum

        $this->updateTextFileInWWWDir("update.php");        // Update update.php
        $this->updateTextFileInWWWDir("startup.php");        // Update update.php

        return true;
    }

    /**
     *  Version 112 nicht veröffentlicht
     */
    private function from111to112() {
        $this->updateCssJSFolder(111);
        $this->updateTextFileInWWWDir("update.php");

    }

    private function from110to111() {
        // Nichts zu erlegigen
        return true;
    }

    private function from101to110()
    {
        // Druckheader kopieren
        $upload = FileUpload::uploadPictureFromFile("./imagesSchool/Briefkopf.jpg", "Briefkopf.jpg");
        DB::getSettings()->setValue("print-header", $upload['uploadobject']->getID());

        // Logo kopieren
        $upload = FileUpload::uploadPictureFromFile("./imagesSchool/Icon.png", "SeitenLogo.png");
        DB::getSettings()->setValue("global-logo", $upload['uploadobject']->getID());


        // Falls Digitale Respizienz aktiv, diese migrieren
        if(AbstractPage::isActive('respizienz')) {
            DB::getSettings()->setValue('resp-mode','RESP');
            DB::getSettings()->setValue('resp-name','Digitale Respizienz');
            DB::getSettings()->setValue('resp-activate-fb',true);
            DB::getSettings()->setValue('resp-activate-sl',true);

            $faecher = fach::getAll();


            for ($i = 0; $i < sizeof($faecher); $i++) {
                $asdID = $faecher[$i]->getASDID();

                DB::getSettings()->setValue('resp-' . $asdID . '-SA', true);
                DB::getSettings()->setValue('resp-' . $asdID . '-EX', true);
                DB::getSettings()->setValue('resp-' . $asdID . '-PLNW', true);
                DB::getSettings()->setValue('resp-' . $asdID . '-KA', true);
                DB::getSettings()->setValue('resp-' . $asdID . '-MODUS', true);
            }

        }


        $sql = ["ALTER TABLE `absenzen_beurlaubung_antrag` MODIFY COLUMN `antragKLGenehmigtDate`  date NULL DEFAULT null AFTER `antragKLGenehmigt`", "
                ALTER TABLE `absenzen_beurlaubung_antrag` MODIFY COLUMN `antragSLgenehmigtDate`  date NULL DEFAULT null AFTER `antragSLgenehmigt`", "
                ALTER TABLE `ausweise` MODIFY COLUMN `ausweisArt`  enum('SCHUELER','LEHRER','MITARBEITER','GAST') CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL AFTER `ausweisErsteller`", "
                ALTER TABLE `dokumente_dateien` MODIFY COLUMN `dateiAvailibleDate`  date NULL DEFAULT '' AFTER `dateiName`", "
                CREATE TABLE `externe_kalender_kategorien` (
                `kalenderID`  int(11) NOT NULL ,
                `kategorieName`  varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL ,
                `kategorieText`  text CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL ,
                `kategorieFarbe`  varchar(7) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT '#000000' ,
                `kategorieIcon`  varchar(200) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT 'fa fa-calendar' ,
                PRIMARY KEY (`kalenderID`, `kategorieName`)
                )
                ENGINE=InnoDB
                DEFAULT CHARACTER SET=latin1 COLLATE=latin1_swedish_ci
                ROW_FORMAT=Dynamic
                ", "
                ALTER TABLE `kalender_extern` ADD COLUMN `eintragExternalID`  text CHARACTER SET utf8 COLLATE utf8_general_ci NULL AFTER `eintragKommentar`", "
                ALTER TABLE `kalender_extern` ADD COLUMN `eintragExternalChangeKey`  text CHARACTER SET utf8 COLLATE utf8_general_ci NULL AFTER `eintragExternalID`", "
                ALTER TABLE `kalender_extern` ADD COLUMN `eintragKategorieName`  varchar(200) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' AFTER `eintragExternalChangeKey`", "
                ALTER TABLE `laufzettel_stunden` MODIFY COLUMN `laufzettelZustimmungZeit`  int(11) NULL DEFAULT '' AFTER `laufzettelZustimmung`", "
                ALTER TABLE `laufzettel_stunden` MODIFY COLUMN `laufzettelZustimmungKommentar`  mediumtext CHARACTER SET utf8 COLLATE utf8_general_ci NULL AFTER `laufzettelZustimmungZeit`", "
                ALTER TABLE `mail_change_requests` ROW_FORMAT=Dynamic", "
                ALTER TABLE `messages_messages` ADD COLUMN `messageRecipientsPreview`  longtext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `messageRecipients`", "
                ALTER TABLE `messages_messages` ADD COLUMN `messageIsForwardFrom`  int(11) NOT NULL DEFAULT 0 AFTER `messageIsDeleted`", "
                ALTER TABLE `nextcloud_users` ROW_FORMAT=Dynamic", "
                ALTER TABLE `noten_arbeiten` MODIFY COLUMN `arbeitDatum`  date NULL DEFAULT '' AFTER `arbeitFachKurzform`", "
                ALTER TABLE `noten_bemerkung_textvorlagen_gruppen` MODIFY COLUMN `koppelMVNote`  enum('M','V') CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT '' AFTER `gruppeName`", "
                ALTER TABLE `noten_noten` MODIFY COLUMN `noteDatum`  date NULL DEFAULT '' AFTER `noteArbeitID`", "
                ALTER TABLE `schaukasten_bildschirme` MODIFY COLUMN `schaukastenMode`  enum('L','P') CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' AFTER `schaukastenResolutionY`", "
                ALTER TABLE `schaukasten_bildschirme` MODIFY COLUMN `schaukastenScreenShot`  blob NULL AFTER `schaukastenIsActive`", "
                ALTER TABLE `schueler` MODIFY COLUMN `schuelerAustrittDatum`  date NULL DEFAULT '' AFTER `schuelerJahrgangsstufe`", "
                ALTER TABLE `schueler_nachteilsausgleich` MODIFY COLUMN `gueltigBis`  date NULL DEFAULT '' AFTER `kommentar`", "
                ALTER TABLE `schueler_nachteilsausgleich` MODIFY COLUMN `gewichtung`  enum('11','12','21') CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT '' AFTER `gueltigBis`", "
                ALTER TABLE `schulbuch_ausleihe` MODIFY COLUMN `ausleiheEndDatum`  date NULL DEFAULT '' AFTER `ausleiheStartDatum`", "
                ALTER TABLE `stundenplan_plaene` MODIFY COLUMN `stundenplanAb`  date NULL DEFAULT '' AFTER `stundenplanID`", "
                ALTER TABLE `stundenplan_plaene` MODIFY COLUMN `stundenplanBis`  date NULL DEFAULT '' AFTER `stundenplanAb`", "
                ALTER TABLE `users` MODIFY COLUMN `userMobilePhoneNumber`  text CHARACTER SET utf8 COLLATE utf8_general_ci NULL AFTER `userFailedLoginCount`"
        ];

        // Datenbank Update
        for($i = 0; $i < sizeof($sql); $i++) {
            DB::getDB()->query($sql[$i],true);        // Datenbank Update
        }


        // Update der CSS / JS Dateien
        rename("./cssjs", "../cssjs_old_101");
        rename("../data/update/Upload/www/cssjs", "./cssjs");

        return true;
    }

    private function from100to101()
    {
        // Änderungen an Datenbank:
        $sql = "CREATE TABLE `lerntutoren` (
            `lerntutorID`  int(11) NOT NULL AUTO_INCREMENT ,
            `lerntutorSchuelerAsvID`  varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL ,
            PRIMARY KEY (`lerntutorID`)
            )
            ENGINE=InnoDB
            DEFAULT CHARACTER SET=latin1 COLLATE=latin1_swedish_ci
            ROW_FORMAT=Dynamic
            ;";

        $sql2 = "
            CREATE TABLE `lerntutoren_slots` (
            `slotID`  int(11) NOT NULL AUTO_INCREMENT ,
            `slotLerntutorID`  int(11) NOT NULL ,
            `slotFach`  varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL ,
            `slotJahrgangsstufe`  varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL ,
            `slotSchuelerBelegt`  varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT '' ,
            PRIMARY KEY (`slotID`)
            )
            ENGINE=InnoDB
            DEFAULT CHARACTER SET=latin1 COLLATE=latin1_swedish_ci
            ROW_FORMAT=Dynamic
            ;";


        DB::getDB()->query($sql, true);
        DB::getDB()->query($sql2, true);

        return true;

    }


    private function updateCssJSFolder($oldVersion) {
        rename("./cssjs", "../cssjs_old_$oldVersion" . rand(1000,9999));
        rename("../data/update/Upload/www/cssjs", "./cssjs");
    }

    private function updateImagesFolder($oldVersion) {
        rename("./images", "../images_$oldVersion" . rand(1000,9999));
        rename("../data/update/Upload/www/images", "./images");
    }

    private function updateComponentsFolder($oldVersion) {
        if(!rename("./components", "../components_old_$oldVersion" . rand(1000,9999))) {
            // TODO: Log Error
        }
        if(!rename("../data/update/Upload/www/components", "./components")) {
            // TODO: Log Error
        }
    }

    private function updateTextFileInWWWDir($fileName) {
        $newContents = file("../data/update/Upload/www/" . $fileName);

        if($newContents === false) return;  // Update Fail TODO: Log

        $newContents = implode("", $newContents);

        $result = file_put_contents($fileName, $newContents);     // Schreiben
        if($result === false) return;   // Fail TODO: Log
    }

    private static function deleteAll($dir)
    {
        if (is_file($dir)) unlink($dir);
        else if (is_dir($dir)) {
            $dirContent = opendir($dir);

            while ($content = readdir($dirContent)) {
                if ($content != '.' && $content != "..") {
                    self::deleteAll($content);
                }
            }

            return @rmdir($dir);
        }
    }

    private function updateDatabase ($newStructure) {

        if ($newStructure == '') {
            return false;
        }
        $source = DB::getDbStructure();
        
        if ($source == '') {
            return false;
        }

        $dbStruct = new dbStruct();

        $sqlUpdates = $dbStruct->getUpdates( $source, $newStructure);

        if ( !empty($sqlUpdates) ) {
            foreach($sqlUpdates as $sql) {
                DB::getDB()->query($sql);
            }
        }
        
    }

    public static function getSettingsDescription()
    {
        return [];
    }

    public static function getSiteDisplayName()
    {
        return "Update";
    }

    public static function hasSettings()
    {
        return false;
    }

    public static function getUserGroups()
    {
        return array();

    }

    public static function siteIsAlwaysActive()
    {
        return true;
    }

    public static function hasAdmin()
    {
        return false;
    }

    public static function displayAdministration($selfURL)
    {
        return '';
    }
}

?>
