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

        // Updates durchführen
        if ($this->performUpdate($fromVersion, $toVersion)) {
            DB::getSettings()->setValue("current-release-id", $updateInfo['updateToReleaseID']);
            DB::getSettings()->setValue('currentVersion', DB::getVersion());
        } else {
            echo("Kein Update möglich.");
            exit(0);
        }

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

        if ($from == "1.2.0" && $to == "1.2.1") {
            $this->from120to121();
        }

        return true;
    }

    private function from111to120() {

    }
    
    private function from111to120() {

        DB::getDB()->query("ALTER TABLE `users` ADD `userAutoresponse` tinyint(1) NOT NULL DEFAULT '0';", true);
        DB::getDB()->query("ALTER TABLE `users` ADD `userAutoresponseText` longtext NOT NULL;", true);
        
        DB::getDB()->query("CREATE TABLE IF NOT EXISTS `ganztags_gruppen` ("
            ."`id` int(11) unsigned NOT NULL AUTO_INCREMENT,"
            ."`sortOrder` int(11) DEFAULT NULL,"
            ."`name` varchar(255) DEFAULT NULL,"
            ."PRIMARY KEY (`id`)"
            .") ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;", true);
        DB::getDB()->query("CREATE TABLE IF NOT EXISTS `ganztags_schueler` ("
            ."`asvid` varchar(200) NOT NULL DEFAULT '',"
            ."`info` varchar(255) DEFAULT NULL,"
            ."`gruppe` int(11) DEFAULT NULL,"
            ."`tag_mo` tinyint(1) DEFAULT NULL,"
            ."`tag_di` tinyint(1) DEFAULT NULL,"
            ."`tag_mi` tinyint(1) DEFAULT NULL,"
            ."`tag_do` tinyint(1) DEFAULT NULL,"
            ."`tag_fr` tinyint(1) DEFAULT NULL,"
            ."`tag_sa` tinyint(1) DEFAULT NULL,"
            ."`tag_so` tinyint(1) DEFAULT NULL,"
            ."PRIMARY KEY (`asvid`)"
            .") ENGINE=InnoDB DEFAULT CHARSET=utf8;", true);
        DB::getDB()->query("ALTER TABLE `schueler` ADD `schuelerGanztagBetreuung` int(11) NOT NULL DEFAULT '0';", true);

        $this->updateCssJSFolder(111);

        rename("./images", "../images_old_$oldVersion" . rand(1000,9999));
        rename("../data/update/Upload/www/images", "./images");
        
    }

    private function from111to112() {
        $this->updateCssJSFolder(111);
        $this->updateTextFileInWWWDir("update.php");

        DB::getDB()->query("UPDATE kalender_lnw SET eintragDatumEnde=eintragDatumStart");       // Bugfix Kalender Termine nach verschieben mit falschem Enddatum
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

    private function updateTextFileInWWWDir($fileName) {
        file_put_contents($fileName, file_get_contents("../data/update/Upload/www/" . $fileName));
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
