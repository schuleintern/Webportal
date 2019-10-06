<?php

$handle = fopen ("php://stdin","r");

echo("Installation (e.g. rsu-intern.de) eingeben.");

$installation = fgets($handle);
$installation = str_replace("\n","",$installation);
$installation = str_replace("\r","",$installation);


mkdir("data/backup",0777,true);
mkdir("data/beobachtungsboegen",0777,true);
mkdir("data/config",0777,true);

// copy($installation . "/mainsettings.php", "data/config/config.php");
copy($installation . "/userlib.class.php", "data/config/userlib.class.php");

copy($installation . "/imagesSchool/Briefkopf.jpg", "www/imagesSchool/Briefkopf.jpg");
copy($installation . "/imagesSchool/Icon.png", "www/imagesSchool/Icon.png");


rename($installation . "/imageUploads", "data/imageUploads");
rename($installation . "/dokumente", "data/dokumente");
rename($installation . "/uploads", "data/uploads");


mkdir("data/update",0777,true);
mkdir("data/wartungsmodus",0777,true);
mkdir("data/install",0777,true);
mkdir("data/temp", 0777, true);
mkdir("data/temp/asvsync", 0777, true);



file_put_contents("data/wartungsmodus/status.dat","");
file_put_contents("data/wartungsmodus/index.htm","
<!DOCTYPE html>
<html>
  
  <head>
     <meta charset=\"UTF-8\">
  
    <title>Wartungsmodus</title>
    <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
    <link href=\"cssjs/bootstrap/css/bootstrap.min.css\" rel=\"stylesheet\" type=\"text/css\" />
    <link href=\"cssjs/font/font-awesome/css/font-awesome.min.css\" rel=\"stylesheet\" type=\"text/css\" />
    <link href=\"cssjs/font/ionicons/css/ionicons.min.css\" rel=\"stylesheet\" type=\"text/css\" />
    <link href=\"cssjs/dist/css/AdminLTE.css\" rel=\"stylesheet\" type=\"text/css\" />
    <link href=\"cssjs/plugins/iCheck/square/blue.css\" rel=\"stylesheet\" type=\"text/css\" />

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src=\"https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js\"></script>
        <script src=\"https://oss.maxcdn.com/respond/1.4.2/respond.min.js\"></script>
    <![endif]-->
  </head>
  <body class=\"login-page\">
  	
    <div class=\"login-box\">
      <div class=\"login-logo\">
        <a href=\"index.php\"><img src=\"imagesSchool/Icon.png\" width=\"150\" border=\"0\"></a>
      </div><!-- /.login-logo -->
      <div class=\"login-box-body\">
      	<div class=\"callout callout-danger\"><strong><i class=\"fa fa-info\"></i> Das Portal befindet sich momentan im Wartungsmodus durch den Hersteller. Das Wartungsende ist für {ENDE} geplant.</strong></div>
      	</div>
      </div><!-- /.login-box-body -->
    </div><!-- /.login-box -->
    
    <script src=\"cssjs/plugins/jQuery/jQuery-2.1.4.min.js\"></script>
    <script src=\"cssjs/bootstrap/js/bootstrap.min.js\" type=\"text/javascript\"></script>
    <script src=\"cssjs/plugins/iCheck/icheck.min.js\" type=\"text/javascript\"></script>
    <script>
      $(function () {
        $('input').iCheck({
          checkboxClass: 'icheckbox_square-blue',
          radioClass: 'iradio_square-blue',
          increaseArea: '20%' // optional
        });
      });
    </script>
  </body>
</html>");


// Update Datenbank

$sqlCommand = "SET FOREIGN_KEY_CHECKS=0;
ALTER TABLE `absenzen_absenzen` ROW_FORMAT=Dynamic;
ALTER TABLE `absenzen_absenzen_stunden` ROW_FORMAT=Dynamic;
ALTER TABLE `absenzen_attestpflicht` ROW_FORMAT=Dynamic;
ALTER TABLE `absenzen_befreiungen` ROW_FORMAT=Dynamic;
ALTER TABLE `absenzen_beurlaubungen` ROW_FORMAT=Dynamic;
ALTER TABLE `absenzen_beurlaubung_antrag` DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci,
ROW_FORMAT=Dynamic;
ALTER TABLE `absenzen_beurlaubung_antrag` MODIFY COLUMN `antragSchuelerAsvID`  varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `antragUserID`;
ALTER TABLE `absenzen_beurlaubung_antrag` MODIFY COLUMN `antragBegruendung`  longtext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `antragDatumEnde`;
ALTER TABLE `absenzen_beurlaubung_antrag` MODIFY COLUMN `antragKLGenehmigtDate`  date NULL DEFAULT '' AFTER `antragKLGenehmigt`;
ALTER TABLE `absenzen_beurlaubung_antrag` MODIFY COLUMN `antragSLgenehmigtDate`  date NULL DEFAULT '' AFTER `antragSLgenehmigt`;
ALTER TABLE `absenzen_beurlaubung_antrag` MODIFY COLUMN `antragKLKommentar`  longtext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `antragIsVerarbeitet`;
ALTER TABLE `absenzen_beurlaubung_antrag` MODIFY COLUMN `antragSLKommentar`  longtext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `antragKLKommentar`;
ALTER TABLE `absenzen_beurlaubung_antrag` MODIFY COLUMN `antragStunden`  mediumtext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `antragSLKommentar`;
ALTER TABLE `absenzen_comments` ROW_FORMAT=Dynamic;
ALTER TABLE `absenzen_krankmeldungen` ROW_FORMAT=Dynamic;
ALTER TABLE `absenzen_meldung` ROW_FORMAT=Dynamic;
ALTER TABLE `absenzen_merker` ROW_FORMAT=Dynamic;
ALTER TABLE `absenzen_sanizimmer` ROW_FORMAT=Dynamic;
ALTER TABLE `absenzen_verspaetungen` ROW_FORMAT=Dynamic;
ALTER TABLE `andere_kalender` DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci,
ROW_FORMAT=Dynamic;
ALTER TABLE `andere_kalender` MODIFY COLUMN `kalenderName`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `kalenderID`;
ALTER TABLE `andere_kalender_kategorie` DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci,
ROW_FORMAT=Dynamic;
ALTER TABLE `andere_kalender_kategorie` MODIFY COLUMN `kategorieName`  mediumtext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `kategorieKalenderID`;
ALTER TABLE `andere_kalender_kategorie` MODIFY COLUMN `kategorieFarbe`  varchar(7) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `kategorieName`;
ALTER TABLE `andere_kalender_kategorie` MODIFY COLUMN `kategorieIcon`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `kategorieFarbe`;
ALTER TABLE `ausleihe_ausleihe` ROW_FORMAT=Dynamic;
ALTER TABLE `ausleihe_objekte` ROW_FORMAT=Dynamic;
ALTER TABLE `ausweise` DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci,
ROW_FORMAT=Dynamic;
ALTER TABLE `ausweise` MODIFY COLUMN `ausweisArt`  enum('SCHUELER','LEHRER','MITARBEITER','GAST') CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' AFTER `ausweisErsteller`;
ALTER TABLE `ausweise` MODIFY COLUMN `ausweisStatus`  enum('BEANTRAGT','GENEHMIGT','ERSTELLT','ABGEHOLT','NICHTGENEHMIGT') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `ausweisArt`;
ALTER TABLE `ausweise` MODIFY COLUMN `ausweisName`  mediumtext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `ausweisStatus`;
ALTER TABLE `ausweise` MODIFY COLUMN `ausweisBarcode`  mediumtext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `ausweisGeburtsdatum`;
ALTER TABLE `ausweise` MODIFY COLUMN `ausweisPLZ`  mediumtext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `ausweisBarcode`;
ALTER TABLE `ausweise` MODIFY COLUMN `ausweisOrt`  mediumtext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `ausweisPLZ`;
ALTER TABLE `ausweise` MODIFY COLUMN `ausweisEssenKundennummer`  mediumtext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `ausweisOrt`;
ALTER TABLE `ausweise` MODIFY COLUMN `ausweisKommentar`  longtext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `ausweisGueltigBis`;
ALTER TABLE `bad_mail` ROW_FORMAT=Dynamic;
ALTER TABLE `beurlaubungsantraege` DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci,
ROW_FORMAT=Dynamic;
ALTER TABLE `beurlaubungsantraege` MODIFY COLUMN `baSchuelerAsvID`  varchar(200) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `baUserID`;
ALTER TABLE `beurlaubungsantraege` MODIFY COLUMN `baSchuelerText`  longtext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `baSchuelerAsvID`;
ALTER TABLE `beurlaubung_antrag` DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci,
ROW_FORMAT=Dynamic;
ALTER TABLE `beurlaubung_antrag` MODIFY COLUMN `antragSchuelerAsvID`  varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `antragUserID`;
ALTER TABLE `beurlaubung_antrag` MODIFY COLUMN `antragStunden`  mediumtext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `antragEndDate`;
ALTER TABLE `beurlaubung_antrag` MODIFY COLUMN `antragBegruendung`  longtext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `antragStunden`;
ALTER TABLE `beurlaubung_antrag` MODIFY COLUMN `antragGenehmigungKommentar`  longtext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `antragGenehmigung`;
ALTER TABLE `cron_execution` ROW_FORMAT=Dynamic;
ALTER TABLE `dokumente_dateien` MODIFY COLUMN `dateiAvailibleDate`  date NULL DEFAULT '' AFTER `dateiName`;
ALTER TABLE `eltern_codes` ROW_FORMAT=Dynamic;
ALTER TABLE `eltern_register` ROW_FORMAT=Dynamic;
ALTER TABLE `email_addresses` ROW_FORMAT=Dynamic;
ALTER TABLE `externe_kalender` DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci,
ROW_FORMAT=Dynamic;
ALTER TABLE `externe_kalender` MODIFY COLUMN `kalenderName`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `kalenderID`;
ALTER TABLE `externe_kalender` MODIFY COLUMN `kalenderIcalFeed`  mediumtext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `kalenderAccessEltern`;
ALTER TABLE `externe_kalender` MODIFY COLUMN `office365Username`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `kalenderIcalFeed`;
ALTER TABLE `ffbumfrage` DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci,
ROW_FORMAT=Dynamic;
ALTER TABLE `ffbumfrage` MODIFY COLUMN `codeText`  varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `codeID`;
ALTER TABLE `ffbumfrage` MODIFY COLUMN `codeType`  enum('SCHUELER','ELTERN','','') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `codeUserID`;
ALTER TABLE `fremdlogin` DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci,
ROW_FORMAT=Dynamic;
ALTER TABLE `fremdlogin` MODIFY COLUMN `loginMessage`  longtext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `adminUserID`;
ALTER TABLE `icsfeeds` DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci,
ROW_FORMAT=Dynamic;
ALTER TABLE `icsfeeds` MODIFY COLUMN `feedType`  enum('KL','AK','EK') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `feedID`;
ALTER TABLE `icsfeeds` MODIFY COLUMN `feedData`  mediumtext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `feedType`;
ALTER TABLE `icsfeeds` MODIFY COLUMN `feedKey`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `feedData`;
ALTER TABLE `icsfeeds` MODIFY COLUMN `feedKey2`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `feedKey`;
ALTER TABLE `initialpasswords` ROW_FORMAT=Dynamic;
ALTER TABLE `kalender_andere` ROW_FORMAT=Dynamic;
ALTER TABLE `kalender_extern` ROW_FORMAT=Dynamic;
ALTER TABLE `kalender_ferien` ROW_FORMAT=Dynamic;
ALTER TABLE `kalender_klassentermin` ROW_FORMAT=Dynamic;
ALTER TABLE `klassentagebuch_fehl` DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci,
ROW_FORMAT=Dynamic;
ALTER TABLE `klassentagebuch_fehl` MODIFY COLUMN `fehlKlasse`  varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `fehlStunde`;
ALTER TABLE `klassentagebuch_fehl` MODIFY COLUMN `fehlFach`  varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `fehlKlasse`;
ALTER TABLE `klassentagebuch_fehl` MODIFY COLUMN `fehlLehrer`  varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `fehlFach`;
ALTER TABLE `klassentagebuch_klassen` ROW_FORMAT=Dynamic;
ALTER TABLE `klassentagebuch_klassen` MODIFY COLUMN `entryTeacher`  varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `entryGrade`;
ALTER TABLE `klassentagebuch_lehrer` ROW_FORMAT=Dynamic;
ALTER TABLE `klassentagebuch_pdf` DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci,
ROW_FORMAT=Dynamic;
ALTER TABLE `klassentagebuch_pdf` MODIFY COLUMN `pdfKlasse`  varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL FIRST ;
ALTER TABLE `kondolenzbuch` DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci,
ROW_FORMAT=Dynamic;
ALTER TABLE `kondolenzbuch` MODIFY COLUMN `eintragName`  mediumtext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `eintragID`;
ALTER TABLE `kondolenzbuch` MODIFY COLUMN `eintragText`  longtext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `eintragName`;
ALTER TABLE `laufzettel_stunden` MODIFY COLUMN `laufzettelZustimmungZeit`  int(11) NULL DEFAULT '' AFTER `laufzettelZustimmung`;
ALTER TABLE `laufzettel_stunden` MODIFY COLUMN `laufzettelZustimmungKommentar`  mediumtext CHARACTER SET utf8 COLLATE utf8_general_ci NULL AFTER `laufzettelZustimmungZeit`;
CREATE TABLE `mail_change_requests` (
`changeRequestID`  int(11) NOT NULL ,
`changeRequestUserID`  int(11) NOT NULL ,
`changeRequestTime`  int(11) NOT NULL ,
`changeRequestSecret`  mediumtext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
`changeRequestNewMail`  varchar(200) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL 
)
ENGINE=InnoDB
DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci
ROW_FORMAT=Dynamic
;
ALTER TABLE `mail_send` MODIFY COLUMN `replyTo`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' AFTER `mailCrawler`;
ALTER TABLE `mail_send` MODIFY COLUMN `mailCC`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' AFTER `replyTo`;
ALTER TABLE `messages_attachment` DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci,
ROW_FORMAT=Dynamic;
ALTER TABLE `messages_attachment` MODIFY COLUMN `attachmentAccessCode`  varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `attachmentFileUploadID`;
ALTER TABLE `messages_questions` DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci,
ROW_FORMAT=Dynamic;
ALTER TABLE `messages_questions` MODIFY COLUMN `questionText`  mediumtext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `questionID`;
ALTER TABLE `messages_questions` MODIFY COLUMN `questionType`  enum('BOOLEAN','TEXT','NUMBER','FILE') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'TEXT' AFTER `questionText`;
ALTER TABLE `messages_questions` MODIFY COLUMN `questionSecret`  varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `questionUserID`;
ALTER TABLE `messages_questions_answers` DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci,
ROW_FORMAT=Dynamic;
ALTER TABLE `messages_questions_answers` MODIFY COLUMN `answerData`  longtext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `answerMessageID`;
ALTER TABLE `modul_admin_notes` ROW_FORMAT=Dynamic;
CREATE TABLE `nextcloud_users` (
`userID`  int(11) NOT NULL AUTO_INCREMENT COMMENT 'Same UserID as in SI' ,
`nextcloudUsername`  mediumtext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
`userPasswordSet`  int(11) NOT NULL DEFAULT 0 ,
`userQuota`  varchar(200) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
`userGroups`  mediumtext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
PRIMARY KEY (`userID`)
)
ENGINE=InnoDB
DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci
ROW_FORMAT=Dynamic
;
ALTER TABLE `noten_arbeiten` DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci,
ROW_FORMAT=Dynamic;
ALTER TABLE `noten_arbeiten` MODIFY COLUMN `arbeitBereich`  enum('SA','KA','EX','MDL') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `arbeitID`;
ALTER TABLE `noten_arbeiten` MODIFY COLUMN `arbeitName`  mediumtext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `arbeitBereich`;
ALTER TABLE `noten_arbeiten` MODIFY COLUMN `arbeitLehrerKuerzel`  varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `arbeitName`;
ALTER TABLE `noten_arbeiten` MODIFY COLUMN `arbeitFachKurzform`  varchar(200) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'Kurzform, nicht ASD ID, da eigene Unterrichte erstellt sein könnten.' AFTER `arbeitGewicht`;
ALTER TABLE `noten_arbeiten` MODIFY COLUMN `arbeitDatum`  date NULL DEFAULT '' AFTER `arbeitFachKurzform`;
ALTER TABLE `noten_arbeiten` MODIFY COLUMN `arbeitUnterrichtName`  varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `arbeitDatum`;
ALTER TABLE `noten_bemerkung_textvorlagen` DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci,
ROW_FORMAT=Dynamic;
ALTER TABLE `noten_bemerkung_textvorlagen` MODIFY COLUMN `bemerkungTextWeiblich`  longtext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `bemerkungNote`;
ALTER TABLE `noten_bemerkung_textvorlagen` MODIFY COLUMN `bemerkungTextMaennlich`  longtext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `bemerkungTextWeiblich`;
ALTER TABLE `noten_bemerkung_textvorlagen_gruppen` DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci,
ROW_FORMAT=Dynamic;
ALTER TABLE `noten_bemerkung_textvorlagen_gruppen` MODIFY COLUMN `gruppeName`  mediumtext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `gruppeID`;
ALTER TABLE `noten_bemerkung_textvorlagen_gruppen` MODIFY COLUMN `koppelMVNote`  enum('M','V') CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' AFTER `gruppeName`;
ALTER TABLE `noten_fach_einstellungen` DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci,
ROW_FORMAT=Dynamic;
ALTER TABLE `noten_fach_einstellungen` MODIFY COLUMN `fachKurzform`  varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL FIRST ;
ALTER TABLE `noten_fach_einstellungen` MODIFY COLUMN `fachNoteZusammenMit`  mediumtext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'Fachkurzformen der Fächer, die mit diesem Fach zusammen verrechnet werden. Aktuelles Fach wird als Hauptfach angezeigt. Getrennt durch Komma.' AFTER `fachOrder`;
ALTER TABLE `noten_gewichtung` DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci,
ROW_FORMAT=Dynamic;
ALTER TABLE `noten_gewichtung` MODIFY COLUMN `fachKuerzel`  varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL FIRST ;
ALTER TABLE `noten_mv` DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci,
ROW_FORMAT=Dynamic;
ALTER TABLE `noten_mv` MODIFY COLUMN `mvFachKurzform`  varchar(200) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL FIRST ;
ALTER TABLE `noten_mv` MODIFY COLUMN `mvUnterrichtName`  varchar(200) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `mvFachKurzform`;
ALTER TABLE `noten_mv` MODIFY COLUMN `schuelerAsvID`  varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `vNote`;
ALTER TABLE `noten_mv` MODIFY COLUMN `noteKommentar`  mediumtext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `zeugnisID`;
ALTER TABLE `noten_noten` DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci,
ROW_FORMAT=Dynamic;
ALTER TABLE `noten_noten` MODIFY COLUMN `noteSchuelerAsvID`  varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL FIRST ;
ALTER TABLE `noten_noten` MODIFY COLUMN `noteDatum`  date NULL DEFAULT '' AFTER `noteArbeitID`;
ALTER TABLE `noten_noten` MODIFY COLUMN `noteKommentar`  longtext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `noteDatum`;
ALTER TABLE `noten_verrechnung` DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci,
ROW_FORMAT=Dynamic;
ALTER TABLE `noten_verrechnung` MODIFY COLUMN `verrechnungFach`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `verrechnungID`;
ALTER TABLE `noten_verrechnung` MODIFY COLUMN `verrechnungUnterricht1`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `verrechnungFach`;
ALTER TABLE `noten_verrechnung` MODIFY COLUMN `verrechnungUnterricht2`  varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `verrechnungUnterricht1`;
ALTER TABLE `noten_wahlfach_faecher` DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci,
ROW_FORMAT=Dynamic;
ALTER TABLE `noten_wahlfach_faecher` MODIFY COLUMN `fachKurzform`  varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `zeugnisID`;
ALTER TABLE `noten_wahlfach_faecher` MODIFY COLUMN `fachUnterrichtName`  varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `fachKurzform`;
ALTER TABLE `noten_wahlfach_faecher` MODIFY COLUMN `wahlfachBezeichnung`  mediumtext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `fachUnterrichtName`;
ALTER TABLE `noten_wahlfach_noten` DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci,
ROW_FORMAT=Dynamic;
ALTER TABLE `noten_wahlfach_noten` MODIFY COLUMN `schuelerAsvID`  varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `wahlfachID`;
ALTER TABLE `noten_zeugnisse` DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci,
ROW_FORMAT=Dynamic;
ALTER TABLE `noten_zeugnisse` MODIFY COLUMN `zeugnisArt`  enum('ZZ','JZ','NB','ABZ','SEMZ','ABIZ') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `zeugnisID`;
ALTER TABLE `noten_zeugnisse` MODIFY COLUMN `zeugnisName`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `zeugnisArt`;
ALTER TABLE `noten_zeugnisse_klassen` DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci,
ROW_FORMAT=Dynamic;
ALTER TABLE `noten_zeugnisse_klassen` MODIFY COLUMN `zeugnisKlasse`  varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `zeugnisID`;
ALTER TABLE `noten_zeugnisse_klassen` MODIFY COLUMN `zeugnisUnterschriftKlassenleitungAsvID`  varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `zeugnisNotenschluss`;
ALTER TABLE `noten_zeugnisse_klassen` MODIFY COLUMN `zeugnisUnterschriftSchulleitungAsvID`  varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `zeugnisUnterschriftKlassenleitungAsvID`;
ALTER TABLE `noten_zeugnisse_noten` DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci,
ROW_FORMAT=Dynamic;
ALTER TABLE `noten_zeugnisse_noten` MODIFY COLUMN `noteSchuelerAsvID`  varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL FIRST ;
ALTER TABLE `noten_zeugnisse_noten` MODIFY COLUMN `noteFachKurzform`  varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `noteZeugnisID`;
ALTER TABLE `noten_zeugnisse_noten` MODIFY COLUMN `notePaedBegruendung`  mediumtext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `noteIsPaed`;
ALTER TABLE `noten_zeugnis_bemerkung` DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci,
ROW_FORMAT=Dynamic;
ALTER TABLE `noten_zeugnis_bemerkung` MODIFY COLUMN `bemerkungSchuelerAsvID`  varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL FIRST ;
ALTER TABLE `noten_zeugnis_bemerkung` MODIFY COLUMN `bemerkungText1`  longtext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `bemerkungZeugnisID`;
ALTER TABLE `noten_zeugnis_bemerkung` MODIFY COLUMN `bemerkungText2`  longtext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `bemerkungText1`;
ALTER TABLE `noten_zeugnis_exemplar` DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci,
ROW_FORMAT=Dynamic;
ALTER TABLE `noten_zeugnis_exemplar` MODIFY COLUMN `schuelerAsvID`  varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `zeugnisID`;
ALTER TABLE `office365_accounts` DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci,
ROW_FORMAT=Dynamic;
ALTER TABLE `office365_accounts` MODIFY COLUMN `accountAsvID`  varchar(200) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL FIRST ;
ALTER TABLE `office365_accounts` MODIFY COLUMN `accountUsername`  varchar(2000) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `accountAsvID`;
ALTER TABLE `office365_accounts` MODIFY COLUMN `accountUserID`  mediumtext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `accountUsername`;
ALTER TABLE `office365_accounts` MODIFY COLUMN `accountInitialPassword`  varchar(200) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `accountUserID`;
ALTER TABLE `projekt_lehrer2grade` ROW_FORMAT=Dynamic;
ALTER TABLE `remote_usersync` ROW_FORMAT=Dynamic;
ALTER TABLE `respizienz` DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci,
ROW_FORMAT=Dynamic;
ALTER TABLE `respizienz` MODIFY COLUMN `respizienzFile`  mediumtext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `respizienzID`;
ALTER TABLE `respizienz` MODIFY COLUMN `respizienzFSLFile`  mediumtext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `respizienzFile`;
ALTER TABLE `respizienz` MODIFY COLUMN `respizientSLFile`  mediumtext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `respizienzFSLFile`;
ALTER TABLE `rsu_persons` ROW_FORMAT=Dynamic;
ALTER TABLE `rsu_print` ROW_FORMAT=Dynamic;
ALTER TABLE `rsu_sections` ROW_FORMAT=Dynamic;
ALTER TABLE `schaukasten_bildschirme` MODIFY COLUMN `schaukastenMode`  enum('L','P') CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' AFTER `schaukastenResolutionY`;
ALTER TABLE `schaukasten_bildschirme` MODIFY COLUMN `schaukastenScreenShot`  blob NULL AFTER `schaukastenIsActive`;
ALTER TABLE `schaukasten_powerpoint` DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci,
ROW_FORMAT=Dynamic;
ALTER TABLE `schaukasten_powerpoint` MODIFY COLUMN `powerpointName`  mediumtext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `lastUpdate`;
ALTER TABLE `schaukasten_website` DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci,
ROW_FORMAT=Dynamic;
ALTER TABLE `schaukasten_website` MODIFY COLUMN `websiteURL`  mediumtext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `websiteID`;
ALTER TABLE `schaukasten_website` MODIFY COLUMN `websiteName`  mediumtext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `websiteURL`;
ALTER TABLE `schueler` MODIFY COLUMN `schuelerAustrittDatum`  date NULL DEFAULT '' AFTER `schuelerJahrgangsstufe`;
ALTER TABLE `schuelerinfo_dokumente` DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci,
ROW_FORMAT=Dynamic;
ALTER TABLE `schuelerinfo_dokumente` MODIFY COLUMN `dokumentSchuelerAsvID`  varchar(200) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `dokumentID`;
ALTER TABLE `schuelerinfo_dokumente` MODIFY COLUMN `dokumentName`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `dokumentSchuelerAsvID`;
ALTER TABLE `schuelerinfo_dokumente` MODIFY COLUMN `dokumentKommentar`  mediumtext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `dokumentName`;
ALTER TABLE `schueler_briefe` DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci,
ROW_FORMAT=Dynamic;
ALTER TABLE `schueler_briefe` MODIFY COLUMN `briefAdresse`  mediumtext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `briefID`;
ALTER TABLE `schueler_briefe` MODIFY COLUMN `schuelerAsvID`  varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `briefAdresse`;
ALTER TABLE `schueler_briefe` MODIFY COLUMN `briefLehrerAsvID`  varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `schuelerAsvID`;
ALTER TABLE `schueler_briefe` MODIFY COLUMN `briefAnrede`  mediumtext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `briefLehrerAsvID`;
ALTER TABLE `schueler_briefe` MODIFY COLUMN `briefBetreff`  mediumtext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `briefAnrede`;
ALTER TABLE `schueler_briefe` MODIFY COLUMN `briefText`  longtext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `briefDatum`;
ALTER TABLE `schueler_briefe` MODIFY COLUMN `briefUnterschrift`  mediumtext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `briefText`;
ALTER TABLE `schueler_briefe` MODIFY COLUMN `briefErledigtKommentar`  mediumtext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `briefGedruckt`;
ALTER TABLE `schueler_briefe` MODIFY COLUMN `briefKommentar`  longtext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `briefErledigtKommentar`;
ALTER TABLE `schueler_fremdsprache` DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci,
ROW_FORMAT=Dynamic;
ALTER TABLE `schueler_fremdsprache` MODIFY COLUMN `schuelerAsvID`  varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL FIRST ;
ALTER TABLE `schueler_fremdsprache` MODIFY COLUMN `spracheAbJahrgangsstufe`  varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `spracheSortierung`;
ALTER TABLE `schueler_fremdsprache` MODIFY COLUMN `spracheFach`  mediumtext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `spracheAbJahrgangsstufe`;
ALTER TABLE `schueler_nachteilsausgleich` DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci,
ROW_FORMAT=Dynamic;
ALTER TABLE `schueler_nachteilsausgleich` MODIFY COLUMN `schuelerAsvID`  varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL FIRST ;
ALTER TABLE `schueler_nachteilsausgleich` MODIFY COLUMN `artStoerung`  enum('rs','lrs','ls') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `schuelerAsvID`;
ALTER TABLE `schueler_nachteilsausgleich` MODIFY COLUMN `arbeitszeitverlaengerung`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `artStoerung`;
ALTER TABLE `schueler_nachteilsausgleich` MODIFY COLUMN `kommentar`  mediumtext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `notenschutz`;
ALTER TABLE `schueler_nachteilsausgleich` MODIFY COLUMN `gueltigBis`  date NULL DEFAULT '' AFTER `kommentar`;
ALTER TABLE `schueler_nachteilsausgleich` MODIFY COLUMN `gewichtung`  enum('11','12','21') CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' AFTER `gueltigBis`;
ALTER TABLE `schulbuch_ausleihe` DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci,
ROW_FORMAT=Dynamic;
ALTER TABLE `schulbuch_ausleihe` MODIFY COLUMN `ausleiheEndDatum`  date NULL DEFAULT '' AFTER `ausleiheStartDatum`;
ALTER TABLE `schulbuch_ausleihe` MODIFY COLUMN `ausleiherNameUndKlasse`  mediumtext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `ausleiheEndDatum`;
ALTER TABLE `schulbuch_ausleihe` MODIFY COLUMN `ausleiherSchuelerAsvID`  varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `ausleiherNameUndKlasse`;
ALTER TABLE `schulbuch_ausleihe` MODIFY COLUMN `ausleiherLehrerAsvID`  varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `ausleiherSchuelerAsvID`;
ALTER TABLE `schulbuch_ausleihe` MODIFY COLUMN `ausleiheKommentar`  longtext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `rueckgeberUserID`;
ALTER TABLE `schulbuch_buecher` DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci,
ROW_FORMAT=Dynamic;
ALTER TABLE `schulbuch_buecher` MODIFY COLUMN `buchTitel`  mediumtext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `buchID`;
ALTER TABLE `schulbuch_buecher` MODIFY COLUMN `buchVerlag`  mediumtext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `buchTitel`;
ALTER TABLE `schulbuch_buecher` MODIFY COLUMN `buchISBN`  varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `buchVerlag`;
ALTER TABLE `schulbuch_buecher` MODIFY COLUMN `buchFach`  varchar(200) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `buchPreis`;
ALTER TABLE `schulbuch_exemplare` DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci,
ROW_FORMAT=Dynamic;
ALTER TABLE `schulbuch_exemplare` MODIFY COLUMN `exemplarBarcode`  varchar(200) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `exemplarBuchID`;
ALTER TABLE `schulbuch_exemplare` MODIFY COLUMN `exemplarAnschaffungsjahr`  varchar(5) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `exemplarZustand`;
ALTER TABLE `schulbuch_exemplare` MODIFY COLUMN `exemplarLagerort`  mediumtext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `exemplarIsBankbuch`;
ALTER TABLE `schulen` DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci,
ROW_FORMAT=Dynamic;
ALTER TABLE `schulen` MODIFY COLUMN `schuleNummer`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `schuleID`;
ALTER TABLE `schulen` MODIFY COLUMN `schuleArt`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `schuleNummer`;
ALTER TABLE `schulen` MODIFY COLUMN `schuleName`  mediumtext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `schuleArt`;
ALTER TABLE `sessions` ROW_FORMAT=Dynamic;
ALTER TABLE `site_activation` ROW_FORMAT=Dynamic;
ALTER TABLE `sprechtag` ROW_FORMAT=Dynamic;
ALTER TABLE `sprechtag_buchungen` ROW_FORMAT=Dynamic;
ALTER TABLE `sprechtag_raeume` DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci,
ROW_FORMAT=Dynamic;
ALTER TABLE `sprechtag_slots` ROW_FORMAT=Dynamic;
ALTER TABLE `stundenplan_aufsichten` DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci,
ROW_FORMAT=Dynamic;
ALTER TABLE `stundenplan_aufsichten` MODIFY COLUMN `aufsichtBereich`  mediumtext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `stundenplanID`;
ALTER TABLE `stundenplan_aufsichten` MODIFY COLUMN `aufsichtLehrerKuerzel`  varchar(200) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `aufsichtTag`;
ALTER TABLE `stundenplan_plaene` ROW_FORMAT=Dynamic;
ALTER TABLE `stundenplan_plaene` MODIFY COLUMN `stundenplanAb`  date NULL DEFAULT '' AFTER `stundenplanID`;
ALTER TABLE `stundenplan_plaene` MODIFY COLUMN `stundenplanBis`  date NULL DEFAULT '' AFTER `stundenplanAb`;
ALTER TABLE `stundenplan_stunden` ROW_FORMAT=Dynamic;
ALTER TABLE `templates` ROW_FORMAT=Dynamic;
ALTER TABLE `two_factor_trusted_devices` DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci,
ROW_FORMAT=Dynamic;
ALTER TABLE `two_factor_trusted_devices` MODIFY COLUMN `deviceCookieData`  mediumtext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `deviceID`;
ALTER TABLE `unknown_mails` ROW_FORMAT=Dynamic;
ALTER TABLE `unterricht` MODIFY COLUMN `unterrichtKoppelText`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' AFTER `unterrichtIsKlassenunterricht`;
ALTER TABLE `uploads` DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci,
ROW_FORMAT=Dynamic;
ALTER TABLE `uploads` MODIFY COLUMN `uploadFileName`  mediumtext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `uploadID`;
ALTER TABLE `uploads` MODIFY COLUMN `uploadFileExtension`  varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `uploadFileName`;
ALTER TABLE `uploads` MODIFY COLUMN `uploadFileMimeType`  varchar(200) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `uploadFileExtension`;
ALTER TABLE `uploads` MODIFY COLUMN `fileAccessCode`  varchar(222) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `uploaderUserID`;
ALTER TABLE `users` ROW_FORMAT=Dynamic;
ALTER TABLE `users` MODIFY COLUMN `userMobilePhoneNumber`  text CHARACTER SET utf8 COLLATE utf8_general_ci NULL AFTER `userFailedLoginCount`;
ALTER TABLE `users` MODIFY COLUMN `userTOTPSecret`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' AFTER `userCanChangePassword`;
ALTER TABLE `users` MODIFY COLUMN `userMailCreated`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' AFTER `userSignature`;
ALTER TABLE `users_groups` ROW_FORMAT=Dynamic;
ALTER TABLE `users_groups_own` DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci,
ROW_FORMAT=Dynamic;
ALTER TABLE `users_groups_own` MODIFY COLUMN `groupName`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL FIRST ;
ALTER TABLE `user_images` ROW_FORMAT=Dynamic;
ALTER TABLE `vplan` ROW_FORMAT=Dynamic;
ALTER TABLE `vplan_data` ROW_FORMAT=Dynamic;
ALTER TABLE `wlan_ticket` DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci,
ROW_FORMAT=Dynamic;
ALTER TABLE `wlan_ticket` MODIFY COLUMN `ticketType`  enum('GAST','SCHUELER') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `ticketID`;
ALTER TABLE `wlan_ticket` MODIFY COLUMN `ticketText`  mediumtext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `ticketType`;
ALTER TABLE `wlan_ticket` MODIFY COLUMN `ticketName`  mediumtext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `ticketAssignedBy`;
DROP TABLE `api_key`;
DROP TABLE `asv_ids`;
DROP TABLE `asvsync`;
DROP TABLE `contracts`;
DROP TABLE `cron_status`;
DROP TABLE `elternmail`;
DROP TABLE `elternmail_attachments`;
DROP TABLE `elternmail_formelements`;
DROP TABLE `elternmail_formelements_data`;
DROP TABLE `elternmail_groups`;
DROP TABLE `elternmail_mails`;
DROP TABLE `infomessages`;
DROP TABLE `kalender_kalender_daten`;
DROP TABLE `kalender_lehrer`;
DROP TABLE `kalender_schule`;
DROP TABLE `licenses`;
DROP TABLE `loginlogdebug`;
DROP TABLE `mappe1`;
DROP TABLE `puzzle`;
DROP TABLE `singlesignon_loginkeys`;
DROP TABLE `stundenplan`;
DROP TABLE `utf8_convert`;
TRUNCATE TABLE `templates`;
TRUNCATE TABLE `sessions`;
SET FOREIGN_KEY_CHECKS=1;";


include_once($installation . "/mainsettings.php");

$config = new GlobalSettings();


$configTemplateFile = "<?php

class GlobalSettings {
    /**
     * Ist die Anwendung im Debug modus?
     * @var bool
     */
    public \$debugMode = false;

    /**
     * Schulnummer als String (mit führender Null)
     * @var string
     */
    public \$schulnummer = \"" . $config->schulnummer . "\";

    /**
     * Datenbankeinstellungen für diese Installation
     * @var array
     */
    public \$dbSettigns = array(
        'host' => '" . $config->dbSettigns['host'] . "',
        'user' => '" . $config->dbSettigns['user'] . "',
        'password' => '" . $config->dbSettigns['password'] . "',
        'database' => '" . $config->dbSettigns['database'] . "'
    );

    /**
     * URL zur index.php für diese Installation (SSL!)
     * @var string
     */
    public \$urlToIndexPHP = \"" . $config->urlToIndexPHP . "\";

    /**
     * Schlüssel zum Ausführen des Cron Jobs.
     * @var string
     */
    public \$cronkey = \"" . $config->cronkey . "\";

    /**
     * Schlüssel für den Zugriff auf die API
     * @var string
     */
    public \$apiKey = \"" . getRandomString() . "\";

    /**
     * Seitenname zur Darstellung auf LoginSeite
     * @var string
     */
    public \$siteNameHTMLDisplay = \"" . $config->siteNameHTMLDisplay . "\";

    /**
     * Seitenname zur Darstellung auf LoginSeite
     * @var string
     */
    public \$siteNameHTMLDisplayShort = \"" . $config->siteNameHTMLDisplayShort . "\";

    /**
     * Einfacher Seitenname
     * @var string
     */
    public \$siteNamePlain = \"" . $config->siteNamePlain . "\";
    /**
     * Schulname
     * @var string
     */
    public \$schoolName = \"" . $config->schoolName . "\";

    /**
     * Modus der Schülerbenutzer:
     * SYNC:	Synchronisierung
     * ASV:		Benutzer kommen aus der ASV (werden automatisch erstellt.)
     * @var string
     */
    public \$schuelerUserMode = \"" . $config->schuelerUserMode . "\";
    /**
     * Modus der Lehrerbenutzer:
     * SYNC:	Synchronisierung
     * ASV:		Benutzer kommen aus der ASV (werden automatisch erstellt.)
     * @var string
     */
    public \$lehrerUserMode = \"" . $config->lehrerUserMode . "\";
    /**
     * Modus der Eltern:
     * ASV_MAIL:		E-Mailadressen kommen aus der ASV
     * ASV_CODE:		Eltern bekommen Elternbrief mit Code zur Selbstregistrierung^
     * KLASSENELTERN
     * @var string
     */
    public \$elternUserMode = \"" . $config->elternUserMode . "\";

    /**
     * Verwendete Stundenplan Software
     * UNTIS, SPM++, SPM++V2
     * @var string
     */
    public \$stundenplanSoftware = \"" . $config->stundenplanSoftware . "\";

    /**
     * Hat eine Notenverwaltung?
     * @var boolean
     */
    public \$hasNotenverwaltung = " . ($config->hasNotenverwaltung ? "true" : "false") . ";

    /**
     * URL zur Ferienliste für den Import in den Kalender.
     * @var string
     */
    public \$ferienURL = \"https://ferien.schule-intern.de/Ferien.txt\";
    
    /**
     * Update Server URL
     */
    public \$updateServer = \"https://update.schule-intern.de\";
}";

file_put_contents("data/config/config.php", $configTemplateFile);


$mysqli = new mysqli($config->dbSettigns['host'], $config->dbSettigns['user'], $config->dbSettigns['password'], $config->dbSettigns['database']);

if ($mysqli->connect_errno) {
    echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
    die();
}

$commands = explode(";", $sqlCommand);

for($i = 0; $i < sizeof($commands); $i++) {
    $command = trim($commands[$i]);
    if($command != "") {
        $mysqli->query($command);
    }
}

$office365Active = $mysqli->query("SELECT * FROM settings WHERE settingName='office365-active'");

if($office365Active->num_rows > 0) {
    $mysqli->query("INSERT INTO settings (settingName, settingValue) values
        ('office365-app-id','6b3c138b-d210-413c-ac43-0ad7d352c484'),
        ('office365-app-secret','ZbPqxoONQbPydYpPSfo9bNa')
    ");
}

$mysqli->query("INSERT INTO settings (settingName, settingValue) values
        ('current-release-id','1'),
        ('mail-server','" . $config->smtpSettings['host'] . "'),
        ('mail-server-port','25'),
        ('mail-server-auth','1'),
        ('mail-server-username','" . $config->smtpSettings['username'] . "'),
        ('mail-server-password','" . $config->smtpSettings['password'] . "'),
        ('mail-server-sender','" . $config->smtpSettings['sender'] . "')
    ");


function getRandomString($length = 10) {
    return substr(md5(rand()), 0, $length);
}