/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

CREATE TABLE IF NOT EXISTS `absenzen_absenzen` (
`absenzID` int(11) NOT NULL AUTO_INCREMENT,
`absenzSchuelerAsvID` varchar(50) NOT NULL,
`absenzDatum` date NOT NULL,
`absenzDatumEnde` date NOT NULL,
`absenzQuelle` enum('TELEFON','WEBPORTAL','LEHRER','PERSOENLICH','FAX') NOT NULL,
`absenzBemerkung` mediumtext NOT NULL,
`absenzErfasstTime` int(11) NOT NULL,
`absenzErfasstUserID` int(11) NOT NULL,
`absenzBefreiungID` int(11) NOT NULL DEFAULT 0,
`absenzBeurlaubungID` int(11) NOT NULL DEFAULT 0,
`absenzStunden` mediumtext NOT NULL,
`absenzisEntschuldigt` tinyint(1) NOT NULL,
`absenzIsSchriftlichEntschuldigt` tinyint(1) NOT NULL,
`absenzKommtSpaeter` tinyint(1) NOT NULL DEFAULT 0,
PRIMARY KEY (`absenzID`) USING BTREE,
KEY `absenzSchuelerAsvID` (`absenzSchuelerAsvID`),
KEY `absenzDatum` (`absenzDatum`)
) ENGINE=InnoDB AUTO_INCREMENT=43813 DEFAULT CHARSET=utf8 COMMENT='Absenzen der Schüler';

CREATE TABLE IF NOT EXISTS `absenzen_attestpflicht` (
`attestpflichtID` int(11) NOT NULL AUTO_INCREMENT,
`schuelerAsvID` varchar(100) NOT NULL,
`attestpflichtStart` date NOT NULL,
`attestpflichtEnde` date NOT NULL,
`attestpflichtUserID` int(11) NOT NULL,
PRIMARY KEY (`attestpflichtID`) USING BTREE,
KEY `schuelerAsvID` (`schuelerAsvID`),
KEY `attestpflichtStart` (`attestpflichtStart`,`attestpflichtEnde`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `absenzen_befreiungen` (
`befreiungID` int(11) NOT NULL AUTO_INCREMENT,
`befreiungUhrzeit` varchar(100) NOT NULL,
`befreiungLehrer` varchar(100) NOT NULL,
`befreiungPrinted` tinyint(1) NOT NULL DEFAULT 0,
PRIMARY KEY (`befreiungID`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=2300 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `absenzen_beurlaubung_antrag` (
`antragID` int(11) NOT NULL AUTO_INCREMENT,
`antragUserID` int(11) NOT NULL,
`antragSchuelerAsvID` varchar(100) NOT NULL,
`antragDatumStart` date NOT NULL,
`antragDatumEnde` date NOT NULL,
`antragBegruendung` longtext NOT NULL,
`antragTime` int(11) NOT NULL,
`antragKLGenehmigt` tinyint(1) NOT NULL DEFAULT -1,
`antragKLGenehmigtDate` date DEFAULT NULL,
`antragSLgenehmigt` tinyint(1) NOT NULL DEFAULT -1,
`antragSLgenehmigtDate` date DEFAULT NULL,
`antragIsVerarbeitet` tinyint(1) NOT NULL DEFAULT 0,
`antragKLKommentar` longtext NOT NULL,
`antragSLKommentar` longtext NOT NULL,
`antragStunden` mediumtext NOT NULL,
PRIMARY KEY (`antragID`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `absenzen_beurlaubungen` (
`beurlaubungID` int(11) NOT NULL AUTO_INCREMENT,
`beurlaubungCreatorID` int(11) NOT NULL,
`beurlaubungPrinted` tinyint(1) NOT NULL DEFAULT 0,
`beurlaubungIsInternAbwesend` tinyint(1) NOT NULL DEFAULT 0,
PRIMARY KEY (`beurlaubungID`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=5906 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `absenzen_comments` (
`schuelerAsvID` varchar(100) NOT NULL,
`commentText` longtext NOT NULL,
PRIMARY KEY (`schuelerAsvID`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `absenzen_krankmeldungen` (
`krankmeldungID` int(11) NOT NULL AUTO_INCREMENT,
`krankmeldungSchuelerASVID` varchar(50) NOT NULL,
`krankmeldungDate` date NOT NULL,
`krankmeldungUntilDate` date NOT NULL,
`krankmeldungElternID` int(11) NOT NULL,
`krankmeldungDurch` enum('m','v','s','schueleru18','schuelerue18') NOT NULL,
`krankmeldungKommentar` mediumtext NOT NULL,
`krankmeldungAbsenzID` tinyint(1) NOT NULL DEFAULT 0,
`krankmeldungTime` int(11) NOT NULL,
PRIMARY KEY (`krankmeldungID`) USING BTREE,
KEY `krankmeldungSchuelerASVID` (`krankmeldungSchuelerASVID`),
KEY `krankmeldungDate` (`krankmeldungDate`),
KEY `krankmeldungElternID` (`krankmeldungElternID`),
KEY `krankmeldungAbsenzID` (`krankmeldungAbsenzID`)
) ENGINE=InnoDB AUTO_INCREMENT=2032 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `absenzen_meldung` (
`meldungDatum` date NOT NULL,
`meldungKlasse` varchar(100) NOT NULL,
`meldungUserID` int(11) NOT NULL,
`meldungTime` int(11) NOT NULL,
PRIMARY KEY (`meldungDatum`,`meldungKlasse`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `absenzen_merker` (
`merkerID` int(11) NOT NULL AUTO_INCREMENT,
`merkerSchuelerAsvID` varchar(100) NOT NULL,
`merkerDate` date NOT NULL,
`merkerText` text NOT NULL,
PRIMARY KEY (`merkerID`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=146 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `absenzen_sanizimmer` (
`sanizimmerID` int(11) NOT NULL AUTO_INCREMENT,
`sanizimmerSchuelerAsvID` varchar(20) NOT NULL,
`sanizimmerTimeStart` int(11) NOT NULL DEFAULT 0,
`sanizimmerTimeEnde` int(11) NOT NULL DEFAULT 0,
`sanizimmerErfasserUserID` int(11) NOT NULL,
`sanizimmerResult` enum('ZURUECK','BEFREIUNG','RETTUNGSDIENST') NOT NULL,
`sanizimmerGrund` mediumtext NOT NULL,
PRIMARY KEY (`sanizimmerID`) USING BTREE,
KEY `sanizimmerSchuelerAsvID` (`sanizimmerSchuelerAsvID`)
) ENGINE=InnoDB AUTO_INCREMENT=252 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `absenzen_verspaetungen` (
`verspaetungID` int(11) NOT NULL AUTO_INCREMENT,
`verspaetungSchuelerAsvID` varchar(50) NOT NULL,
`verspaetungDate` date NOT NULL,
`verspaetungMinuten` int(11) NOT NULL,
`verspaetungKommentar` mediumtext NOT NULL,
`verspaetungStunde` int(11) NOT NULL DEFAULT 1,
`verspaetungBearbeitet` int(11) NOT NULL DEFAULT 0,
`verspaetungBearbeitetKommentar` text NOT NULL,
`verspaetungBenachrichtigt` int(11) NOT NULL DEFAULT 0,
PRIMARY KEY (`verspaetungID`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `acl` (
`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
`moduleClass` varchar(50) DEFAULT NULL,
`moduleClassParent` varchar(50) DEFAULT NULL,
`schuelerRead` tinyint(1) DEFAULT 0,
`schuelerWrite` tinyint(1) DEFAULT 0,
`schuelerDelete` tinyint(1) DEFAULT 0,
`elternRead` tinyint(1) DEFAULT 0,
`elternWrite` tinyint(1) DEFAULT 0,
`elternDelete` tinyint(1) DEFAULT 0,
`lehrerRead` tinyint(1) DEFAULT 0,
`lehrerWrite` tinyint(1) DEFAULT 0,
`lehrerDelete` tinyint(1) DEFAULT 0,
`noneRead` tinyint(1) DEFAULT 0,
`noneWrite` tinyint(1) DEFAULT 0,
`noneDelete` tinyint(1) DEFAULT 0,
`owneRead` tinyint(1) DEFAULT 0,
`owneWrite` tinyint(1) DEFAULT 0,
`owneDelete` tinyint(1) DEFAULT 0,
PRIMARY KEY (`id`) USING BTREE,
KEY `moduleClass` (`moduleClass`),
KEY `moduleClassParent` (`moduleClassParent`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `amtsbezeichnungen` (
`amtsbezeichnungID` int(11) NOT NULL,
`amtsbezeichnungKurzform` mediumtext NOT NULL,
`amtsbezeichnungAnzeigeform` mediumtext NOT NULL,
`amtsbezeichnungKurzformW` mediumtext NOT NULL,
`amtsbezeichnungAnzeigeformW` mediumtext NOT NULL,
PRIMARY KEY (`amtsbezeichnungID`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `andere_kalender` (
`kalenderID` int(11) NOT NULL AUTO_INCREMENT,
`kalenderName` varchar(255) NOT NULL,
`kalenderAccessSchueler` tinyint(1) NOT NULL DEFAULT 0,
`kalenderAccessLehrer` int(11) NOT NULL DEFAULT 0,
`kalenderAccessEltern` int(11) NOT NULL DEFAULT 0,
`kalenderAccessLehrerSchreiben` tinyint(1) NOT NULL,
`kalenderAccessSchuelerSchreiben` tinyint(1) NOT NULL,
`kalenderAccessElternSchreiben` tinyint(1) NOT NULL,
`kalenderDeleteOnlyOwn` tinyint(1) NOT NULL DEFAULT 0,
PRIMARY KEY (`kalenderID`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `andere_kalender_kategorie` (
`kategorieID` int(11) NOT NULL AUTO_INCREMENT,
`kategorieKalenderID` int(11) NOT NULL,
`kategorieName` mediumtext NOT NULL,
`kategorieFarbe` varchar(7) NOT NULL,
`kategorieIcon` varchar(255) NOT NULL,
PRIMARY KEY (`kategorieID`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `anschrifttyp` (
`anschriftTypID` varchar(10) NOT NULL,
`anschriftTypKurzform` varchar(255) NOT NULL,
`anschriftTypAnzeigeform` mediumtext NOT NULL,
`anschriftTypLangform` mediumtext NOT NULL,
PRIMARY KEY (`anschriftTypID`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `aufeinenblick_settings` (
`aufeinenblickSettingsID` int(11) NOT NULL AUTO_INCREMENT,
`aufeinenblickUserID` int(11) NOT NULL,
`aufeinenblickHourCanceltoday` int(11) NOT NULL,
`aufeinenblickShowVplan` int(11) NOT NULL,
`aufeinenblickShowCalendar` int(11) NOT NULL,
`aufeinenblickShowStundenplan` int(11) NOT NULL,
PRIMARY KEY (`aufeinenblickSettingsID`) USING BTREE,
KEY `aufeinenblickUserID` (`aufeinenblickUserID`)
) ENGINE=MyISAM AUTO_INCREMENT=485 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `ausleihe_ausleihe` (
`ausleiheID` int(11) NOT NULL AUTO_INCREMENT,
`ausleiheObjektID` int(11) NOT NULL,
`ausleiheObjektIndex` int(11) NOT NULL,
`ausleiheDatum` date NOT NULL,
`ausleiheAusleiherUserID` int(11) NOT NULL,
`ausleiheStunde` int(11) NOT NULL,
`ausleiheKlasse` varchar(10) NOT NULL,
`ausleiheLehrer` varchar(100) NOT NULL,
PRIMARY KEY (`ausleiheID`) USING BTREE,
KEY `ausleiheObjektID` (`ausleiheObjektID`)
) ENGINE=InnoDB AUTO_INCREMENT=133 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `ausleihe_objekte` (
`objektID` int(11) NOT NULL AUTO_INCREMENT,
`objektName` mediumtext NOT NULL,
`objektAnzahl` int(11) NOT NULL,
`isActive` tinyint(1) NOT NULL,
`sortOrder` int(11) NOT NULL,
`sumItems` int(11) NOT NULL DEFAULT 1,
PRIMARY KEY (`objektID`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COMMENT='Auszuleihende Gegenstände';

CREATE TABLE IF NOT EXISTS `ausweise` (
`ausweisID` int(11) NOT NULL AUTO_INCREMENT,
`ausweisErsteller` int(11) NOT NULL,
`ausweisArt` enum('SCHUELER','LEHRER','MITARBEITER','GAST') CHARACTER SET latin1 DEFAULT NULL,
`ausweisStatus` enum('BEANTRAGT','GENEHMIGT','ERSTELLT','ABGEHOLT','NICHTGENEHMIGT') NOT NULL,
`ausweisName` mediumtext NOT NULL,
`ausweisGeburtsdatum` date NOT NULL,
`ausweisBarcode` mediumtext NOT NULL,
`ausweisPLZ` mediumtext NOT NULL,
`ausweisOrt` mediumtext NOT NULL,
`ausweisEssenKundennummer` mediumtext NOT NULL,
`ausweisPreis` int(11) NOT NULL COMMENT 'Preis in Cent',
`ausweisBezahlt` tinyint(1) NOT NULL DEFAULT 0,
`ausweisFoto` int(11) NOT NULL,
`ausweisGueltigBis` date NOT NULL,
`ausweisKommentar` longtext NOT NULL,
PRIMARY KEY (`ausweisID`) USING BTREE,
KEY `ausweisErsteller` (`ausweisErsteller`),
KEY `ausweisBezahlt` (`ausweisBezahlt`),
KEY `ausweisStatus` (`ausweisStatus`),
KEY `ausweisArt` (`ausweisArt`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `bad_mail` (
`badMailID` int(11) NOT NULL AUTO_INCREMENT,
`badMail` mediumtext NOT NULL,
`badMailDone` int(11) NOT NULL,
PRIMARY KEY (`badMailID`) USING BTREE,
KEY `badMailDone` (`badMailDone`)
) ENGINE=InnoDB AUTO_INCREMENT=1722 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `beobachtungsbogen_boegen` (
`beobachtungsbogenID` int(11) NOT NULL AUTO_INCREMENT,
`beobachtungsbogenName` varchar(200) NOT NULL,
`beobachtungsbogenDatum` date NOT NULL,
`beobachtungsbogenStartDate` date NOT NULL,
`beobachtungsbogenDeadline` date NOT NULL,
`beobachtungsbogenText` mediumtext NOT NULL,
`beobachtungsbogenTitel` mediumtext NOT NULL,
PRIMARY KEY (`beobachtungsbogenID`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `beobachtungsbogen_eintragungsfrist` (
`beobachtungsbogenID` int(11) NOT NULL,
`userID` int(11) NOT NULL,
`frist` date NOT NULL,
PRIMARY KEY (`beobachtungsbogenID`,`userID`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `beobachtungsbogen_fragen` (
`frageID` int(11) NOT NULL AUTO_INCREMENT,
`beobachtungsbogenID` int(11) NOT NULL,
`frageText` mediumtext NOT NULL,
`frageTyp` enum('1','2') NOT NULL COMMENT '#1: 2 bis -2 ( :-) :-) bis :-( :-( ) #2: 2-0 ( :-) :-) bis :-| )',
`frageZugriff` enum('LEHRER','KLASSENLEITUNG') NOT NULL,
PRIMARY KEY (`frageID`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=50 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `beobachtungsbogen_fragen_daten` (
`frageID` int(11) NOT NULL,
`schuelerID` int(11) NOT NULL,
`bewertung` int(11) NOT NULL,
`lehrerKuerzel` varchar(100) NOT NULL,
`fachName` varchar(100) NOT NULL,
PRIMARY KEY (`frageID`,`schuelerID`,`lehrerKuerzel`,`fachName`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `beobachtungsbogen_klasse_fach_lehrer` (
`beobachtungsbogenID` int(11) NOT NULL,
`klasseName` varchar(100) NOT NULL,
`fachName` varchar(100) NOT NULL,
`lehrerKuerzel` varchar(100) NOT NULL,
`isOK` tinyint(1) NOT NULL,
PRIMARY KEY (`beobachtungsbogenID`,`klasseName`,`fachName`,`lehrerKuerzel`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `beobachtungsbogen_klassenleitung` (
`beobachtungsbogenID` int(11) NOT NULL,
`klassenName` varchar(100) NOT NULL,
`klassenleitungUserID` int(11) NOT NULL,
`klassenleitungTyp` int(11) NOT NULL DEFAULT 1,
PRIMARY KEY (`beobachtungsbogenID`,`klassenName`,`klassenleitungTyp`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `beobachtungsbogen_schueler_namen` (
`beobachtungsbogenID` int(11) NOT NULL,
`schuelerID` int(11) NOT NULL,
`schuelerFirstName` varchar(255) NOT NULL,
`schulerLastName` varchar(255) NOT NULL,
PRIMARY KEY (`beobachtungsbogenID`,`schuelerID`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `beurlaubung_antrag` (
`antragID` int(11) NOT NULL AUTO_INCREMENT,
`antragUserID` int(11) NOT NULL,
`antragSchuelerAsvID` varchar(100) NOT NULL,
`antragStartDate` date NOT NULL,
`antragEndDate` date NOT NULL,
`antragStunden` mediumtext NOT NULL,
`antragBegruendung` longtext NOT NULL,
`antragGenehmigung` int(11) NOT NULL DEFAULT -1,
`antragGenehmigungKommentar` longtext NOT NULL,
`antragAbsenzID` int(11) NOT NULL,
PRIMARY KEY (`antragID`) USING BTREE,
KEY `antragUserID` (`antragUserID`),
KEY `antragSchuelerAsvID` (`antragSchuelerAsvID`),
KEY `antragAbsenzID` (`antragAbsenzID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `cache` (
`cacheKey` varchar(255) NOT NULL,
`cacheTTL` int(11) NOT NULL,
`cacheType` enum('object','text','base64') NOT NULL DEFAULT 'text',
`cacheData` longtext NOT NULL,
PRIMARY KEY (`cacheKey`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `cron_execution` (
`cronRunID` int(11) NOT NULL AUTO_INCREMENT,
`cronName` varchar(255) NOT NULL,
`cronStartTime` int(11) NOT NULL,
`cronEndTime` int(11) NOT NULL,
`cronSuccess` tinyint(1) NOT NULL,
`cronResult` longtext NOT NULL,
PRIMARY KEY (`cronRunID`) USING BTREE,
KEY `cronName` (`cronName`)
) ENGINE=InnoDB AUTO_INCREMENT=3337941 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `datenschutz_erklaerung` (
`userID` int(11) NOT NULL,
`userConfirmed` int(11) NOT NULL,
PRIMARY KEY (`userID`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `dokumente_dateien` (
`dateiID` int(11) NOT NULL AUTO_INCREMENT,
`gruppenID` int(11) NOT NULL,
`dateiName` varchar(255) NOT NULL,
`dateiAvailibleDate` date DEFAULT NULL,
`dateiUploadTime` int(11) NOT NULL,
`dateiDownloads` int(11) NOT NULL DEFAULT 0,
`dateiKommentar` mediumtext NOT NULL,
`dateiMimeType` varchar(200) NOT NULL,
`dateiExtension` varchar(20) NOT NULL,
PRIMARY KEY (`dateiID`) USING BTREE,
KEY `gruppenID` (`gruppenID`)
) ENGINE=MyISAM AUTO_INCREMENT=422 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `dokumente_gruppen` (
`gruppenID` int(11) NOT NULL AUTO_INCREMENT,
`gruppenName` varchar(255) NOT NULL,
`kategorieID` int(11) NOT NULL,
PRIMARY KEY (`gruppenID`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `dokumente_kategorien` (
`kategorieID` int(11) NOT NULL AUTO_INCREMENT,
`kategorieName` varchar(255) NOT NULL,
`kategorieAccessSchueler` tinyint(1) NOT NULL,
`kategorieAccessLehrer` tinyint(1) NOT NULL,
`kategorieAccessEltern` tinyint(1) NOT NULL,
PRIMARY KEY (`kategorieID`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `eltern_adressen` (
`adresseID` int(11) NOT NULL AUTO_INCREMENT,
`adresseSchuelerAsvID` varchar(100) NOT NULL,
`adresseWessen` enum('eb','web','s','w') NOT NULL COMMENT 'eb=Erziehungsberechtiger, web=weiterer Erziehungsberechtigter; s=Schüler; w=weitere Anschrift',
`adresseIsAuskunftsberechtigt` tinyint(1) NOT NULL,
`adresseIsHauptansprechpartner` tinyint(1) NOT NULL,
`adresseStrasse` mediumtext NOT NULL,
`adresseNummer` mediumtext NOT NULL,
`adresseOrt` mediumtext NOT NULL,
`adressePostleitzahl` varchar(10) NOT NULL,
`adresseAnredetext` mediumtext NOT NULL,
`adresseAnschrifttext` mediumtext NOT NULL,
`adresseFamilienname` mediumtext NOT NULL,
`adresseVorname` mediumtext NOT NULL,
`adresseAnrede` mediumtext NOT NULL,
`adressePersonentyp` varchar(20) NOT NULL,
PRIMARY KEY (`adresseID`) USING BTREE,
KEY `adresseSchuelerAsvID` (`adresseSchuelerAsvID`)
) ENGINE=MyISAM AUTO_INCREMENT=3313 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `eltern_codes` (
`codeID` int(11) NOT NULL AUTO_INCREMENT,
`codeSchuelerAsvID` varchar(100) NOT NULL,
`codeText` varchar(50) NOT NULL,
`codeUserID` text NOT NULL,
`codePrinted` tinyint(1) NOT NULL DEFAULT 0,
PRIMARY KEY (`codeID`) USING BTREE,
KEY `codeSchuelerAsvID` (`codeSchuelerAsvID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `eltern_email` (
`elternEMail` varchar(255) NOT NULL,
`elternSchuelerAsvID` varchar(20) NOT NULL,
`elternUserID` int(11) NOT NULL DEFAULT 0,
`elternAdresseID` int(11) NOT NULL,
PRIMARY KEY (`elternEMail`,`elternSchuelerAsvID`) USING BTREE,
KEY `elternUserID` (`elternUserID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `eltern_register` (
`registerID` int(11) NOT NULL AUTO_INCREMENT,
`registerCheckKey` varchar(200) NOT NULL,
`registerSchuelerKey` varchar(200) NOT NULL,
`registerTime` int(11) NOT NULL,
`registerPassword` varchar(200) NOT NULL,
`registerMail` varchar(255) NOT NULL,
`firstName` text NOT NULL,
`lastName` text NOT NULL,
PRIMARY KEY (`registerID`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `eltern_telefon` (
`telefonNummer` varchar(255) NOT NULL,
`schuelerAsvID` varchar(50) NOT NULL,
`telefonTyp` enum('telefon','mobiltelefon','fax') NOT NULL DEFAULT 'telefon',
`kontaktTyp` varchar(10) NOT NULL,
`adresseID` int(11) NOT NULL,
PRIMARY KEY (`telefonNummer`,`schuelerAsvID`,`adresseID`) USING BTREE,
KEY `telefonNummer` (`telefonNummer`,`schuelerAsvID`,`telefonTyp`) USING BTREE,
KEY `telefonNummer_2` (`telefonNummer`,`schuelerAsvID`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `eltern_to_schueler` (
`elternUserID` int(11) NOT NULL,
`schuelerUserID` int(11) NOT NULL,
PRIMARY KEY (`elternUserID`,`schuelerUserID`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `email_addresses` (
`userID` int(11) NOT NULL,
`userEMail` mediumtext NOT NULL,
`userConfirmCode` mediumtext NOT NULL,
PRIMARY KEY (`userID`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `externe_kalender` (
`kalenderID` int(11) NOT NULL AUTO_INCREMENT,
`kalenderName` varchar(255) NOT NULL,
`kalenderAccessSchueler` tinyint(1) NOT NULL DEFAULT 0,
`kalenderAccessLehrer` int(11) NOT NULL DEFAULT 0,
`kalenderAccessEltern` int(11) NOT NULL DEFAULT 0,
`kalenderIcalFeed` mediumtext NOT NULL,
`office365Username` varchar(255) NOT NULL,
PRIMARY KEY (`kalenderID`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `externe_kalender_kategorien` (
`kalenderID` int(11) NOT NULL,
`kategorieName` varchar(255) NOT NULL,
`kategorieText` text NOT NULL,
`kategorieFarbe` varchar(7) NOT NULL DEFAULT '#000000',
`kategorieIcon` varchar(200) NOT NULL DEFAULT 'fa fa-calendar',
PRIMARY KEY (`kalenderID`,`kategorieName`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `faecher` (
`fachID` int(11) NOT NULL COMMENT 'Aus XML File',
`fachKurzform` mediumtext NOT NULL,
`fachLangform` mediumtext NOT NULL,
`fachIstSelbstErstellt` tinyint(1) NOT NULL DEFAULT 0,
`fachASDID` varchar(100) NOT NULL,
`fachOrdnung` int(11) NOT NULL DEFAULT 10,
PRIMARY KEY (`fachID`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `fremdlogin` (
`fremdloginID` int(11) NOT NULL AUTO_INCREMENT,
`userID` int(11) NOT NULL,
`adminUserID` int(11) NOT NULL,
`loginMessage` longtext NOT NULL,
`loginTime` int(11) NOT NULL,
PRIMARY KEY (`fremdloginID`) USING BTREE,
KEY `userID` (`userID`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `ganztags_gruppen` (
`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
`sortOrder` int(11) DEFAULT NULL,
`name` varchar(255) DEFAULT NULL,
PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `ganztags_schueler` (
`asvid` varchar(200) NOT NULL DEFAULT '',
`info` varchar(255) DEFAULT NULL,
`gruppe` int(11) DEFAULT NULL,
`tag_mo` tinyint(1) DEFAULT NULL,
`tag_di` tinyint(1) DEFAULT NULL,
`tag_mi` tinyint(1) DEFAULT NULL,
`tag_do` tinyint(1) DEFAULT NULL,
`tag_fr` tinyint(1) DEFAULT NULL,
`tag_sa` tinyint(1) DEFAULT NULL,
`tag_so` tinyint(1) DEFAULT NULL,
PRIMARY KEY (`asvid`) USING BTREE,
KEY `gruppe` (`gruppe`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `icsfeeds` (
`feedID` int(11) NOT NULL AUTO_INCREMENT,
`feedType` enum('KL','AK','EK') NOT NULL,
`feedData` mediumtext NOT NULL,
`feedKey` varchar(255) NOT NULL,
`feedKey2` varchar(255) NOT NULL,
`feedUserID` int(11) NOT NULL,
PRIMARY KEY (`feedID`) USING BTREE,
KEY `feedKey` (`feedKey`),
KEY `feedKey2` (`feedKey2`),
KEY `feedUserID` (`feedUserID`),
KEY `feedType` (`feedType`)
) ENGINE=InnoDB AUTO_INCREMENT=524 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `image_uploads` (
`uploadID` int(11) NOT NULL AUTO_INCREMENT,
`uploadTime` int(11) NOT NULL,
`uploadUserName` varchar(20) NOT NULL,
PRIMARY KEY (`uploadID`) USING BTREE,
KEY `uploadUserName` (`uploadUserName`)
) ENGINE=MyISAM AUTO_INCREMENT=1367 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `initialpasswords` (
`initialPasswordID` int(11) NOT NULL AUTO_INCREMENT,
`initialPasswordUserID` int(11) NOT NULL,
`initialPassword` varchar(200) NOT NULL,
`passwordPrinted` int(11) NOT NULL DEFAULT 0,
PRIMARY KEY (`initialPasswordID`) USING BTREE,
KEY `initialPasswordUserID` (`initialPasswordUserID`),
KEY `passwordPrinted` (`passwordPrinted`)
) ENGINE=InnoDB AUTO_INCREMENT=391 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `kalender_andere` (
`eintragID` int(11) NOT NULL AUTO_INCREMENT,
`kalenderID` int(11) NOT NULL,
`eintragTitel` text NOT NULL,
`eintragDatumStart` date NOT NULL,
`eintragDatumEnde` date NOT NULL,
`eintragUser` int(11) NOT NULL,
`eintragIsWholeDay` tinyint(4) NOT NULL DEFAULT 1,
`eintragUhrzeitStart` text NOT NULL,
`eintragUhrzeitEnde` text NOT NULL,
`eintragEintragZeitpunkt` int(11) NOT NULL,
`eintragOrt` text NOT NULL,
`eintragKommentar` text NOT NULL,
`eintragKategorie` int(11) NOT NULL DEFAULT 0,
PRIMARY KEY (`eintragID`) USING BTREE,
KEY `eintragDatumStart` (`eintragDatumStart`,`eintragDatumEnde`),
KEY `kalenderID` (`kalenderID`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `kalender_extern` (
`eintragID` int(11) NOT NULL AUTO_INCREMENT,
`kalenderID` int(11) NOT NULL,
`eintragTitel` text NOT NULL,
`eintragDatumStart` date NOT NULL,
`eintragDatumEnde` date NOT NULL,
`eintragUser` int(11) NOT NULL,
`eintragIsWholeDay` tinyint(4) NOT NULL DEFAULT 1,
`eintragUhrzeitStart` text NOT NULL,
`eintragUhrzeitEnde` text NOT NULL,
`eintragEintragZeitpunkt` int(11) NOT NULL,
`eintragOrt` text NOT NULL,
`eintragKommentar` text NOT NULL,
`eintragExternalID` text DEFAULT NULL,
`eintragExternalChangeKey` text DEFAULT NULL,
`eintragKategorieName` varchar(200) DEFAULT '',
PRIMARY KEY (`eintragID`) USING BTREE,
KEY `kalenderID` (`kalenderID`),
KEY `eintragDatumStart` (`eintragDatumStart`,`eintragDatumEnde`)
) ENGINE=InnoDB AUTO_INCREMENT=32327224 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `kalender_ferien` (
`ferienID` int(11) NOT NULL AUTO_INCREMENT,
`ferienName` mediumtext NOT NULL,
`ferienStart` date NOT NULL,
`ferienEnde` date NOT NULL,
`ferienSchuljahr` varchar(7) NOT NULL,
PRIMARY KEY (`ferienID`) USING BTREE,
KEY `ferienStart` (`ferienStart`,`ferienEnde`)
) ENGINE=InnoDB AUTO_INCREMENT=2880 DEFAULT CHARSET=utf8 COMMENT='Alle Ferien in den nächsten Jahren';

CREATE TABLE IF NOT EXISTS `kalender_klassentermin` (
`eintragID` int(11) NOT NULL AUTO_INCREMENT,
`eintragTitel` text NOT NULL,
`eintragDatumStart` date NOT NULL,
`eintragDatumEnde` date NOT NULL,
`eintragKlassen` text NOT NULL,
`eintragBetrifft` text NOT NULL,
`eintragLehrer` text NOT NULL,
`eintragStunden` text NOT NULL,
`eintragEintragZeitpunkt` int(11) NOT NULL,
`eintragOrt` text NOT NULL,
PRIMARY KEY (`eintragID`) USING BTREE,
KEY `eintragDatumStart` (`eintragDatumStart`,`eintragDatumEnde`)
) ENGINE=InnoDB AUTO_INCREMENT=65 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `kalender_lnw` (
`eintragID` int(11) NOT NULL AUTO_INCREMENT,
`eintragTitel` mediumtext NOT NULL,
`eintragArt` enum('SCHULAUFGABE','KURZARBEIT','STEGREIFAUFGABE','KLASSENTERMIN','PLNW','MODUSTEST','NACHHOLSCHULAUFGABE') NOT NULL,
`eintragDatumStart` date NOT NULL,
`eintragDatumEnde` date NOT NULL,
`eintragKlasse` varchar(200) NOT NULL,
`eintragBetrifft` varchar(255) NOT NULL,
`eintragLehrer` varchar(20) NOT NULL,
`eintragFach` varchar(100) NOT NULL,
`eintragEintragZeitpunkt` int(11) NOT NULL DEFAULT 0,
`eintragStunden` varchar(255) NOT NULL,
`eintragAlwaysShow` tinyint(1) NOT NULL DEFAULT 0,
PRIMARY KEY (`eintragID`) USING BTREE,
KEY `eintragArt` (`eintragArt`),
KEY `eintragDatumStart` (`eintragDatumStart`,`eintragDatumEnde`),
KEY `eintragKlasse` (`eintragKlasse`),
KEY `eintragLehrer` (`eintragLehrer`)
) ENGINE=MyISAM AUTO_INCREMENT=11452 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `klassenleitung` (
`klasseName` varchar(200) NOT NULL,
`lehrerID` int(11) NOT NULL,
`klassenleitungArt` int(11) NOT NULL DEFAULT 1,
PRIMARY KEY (`klasseName`,`lehrerID`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `klassentagebuch_fehl` (
`fehlID` int(11) NOT NULL AUTO_INCREMENT,
`fehlDatum` date NOT NULL,
`fehlStunde` int(11) NOT NULL,
`fehlKlasse` varchar(100) NOT NULL,
`fehlFach` varchar(100) NOT NULL,
`fehlLehrer` varchar(100) NOT NULL,
PRIMARY KEY (`fehlID`) USING BTREE,
KEY `fehlDatum` (`fehlDatum`),
KEY `fehlLehrer` (`fehlLehrer`),
KEY `fehlKlasse` (`fehlKlasse`)
) ENGINE=InnoDB AUTO_INCREMENT=121080 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `klassentagebuch_klassen` (
`entryID` int(11) NOT NULL AUTO_INCREMENT,
`entryGrade` varchar(100) NOT NULL,
`entryTeacher` varchar(100) NOT NULL,
`entryFach` varchar(100) NOT NULL,
`entryDate` date NOT NULL,
`entryStunde` int(11) NOT NULL,
`entryStoff` text NOT NULL,
`entryFileID` int(11) NOT NULL COMMENT 'Angehängte Datei',
`entryHausaufgabe` text NOT NULL,
`entryIsAusfall` tinyint(1) NOT NULL,
`entryIsVertretung` tinyint(1) NOT NULL DEFAULT 0,
`entryNotizen` longtext NOT NULL,
`entryFilesPrivate` text NOT NULL,
`entryFilesPublic` text NOT NULL,
PRIMARY KEY (`entryID`) USING BTREE,
KEY `entryGrade` (`entryGrade`),
KEY `entryTeacher` (`entryTeacher`)
) ENGINE=InnoDB AUTO_INCREMENT=346 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `klassentagebuch_pdf` (
`pdfKlasse` varchar(100) NOT NULL,
`pdfJahr` int(11) NOT NULL,
`pdfMonat` int(11) NOT NULL,
`pdfUploadID` int(11) NOT NULL,
PRIMARY KEY (`pdfKlasse`,`pdfJahr`,`pdfMonat`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `kms` (
`kmsID` int(11) NOT NULL AUTO_INCREMENT,
`kmsAktenzeichen` varchar(255) DEFAULT NULL,
`kmsTitel` text DEFAULT NULL,
`kmsSchularten` int(11) DEFAULT NULL,
`kmsUploadID` int(11) NOT NULL,
PRIMARY KEY (`kmsID`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `kondolenzbuch` (
`eintragID` int(11) NOT NULL AUTO_INCREMENT,
`eintragName` mediumtext NOT NULL,
`eintragText` longtext NOT NULL,
`eintragTime` int(11) NOT NULL,
PRIMARY KEY (`eintragID`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `laufzettel` (
`laufzettelID` int(11) NOT NULL AUTO_INCREMENT,
`laufzettelArt` enum('SA','UG') NOT NULL,
`laufzettelErsteller` int(11) NOT NULL,
`laufzettelDatum` date NOT NULL,
`laufzettelTitel` mediumtext NOT NULL,
`laufzettelNachricht` mediumtext NOT NULL,
`laufzettelMittagsbetreuung` int(11) NOT NULL DEFAULT 0,
`laufzettelMittagessen` int(11) NOT NULL,
`laufzettelHausmeister` int(11) NOT NULL DEFAULT 0,
`laufzettelZustimmungSchulleitung` tinyint(1) NOT NULL,
`laufzettelZustimmungSchulleitungTime` int(11) NOT NULL,
`laufzettelZustimmungSchulleitungPerson` text NOT NULL,
PRIMARY KEY (`laufzettelID`) USING BTREE,
KEY `laufzettelDatum` (`laufzettelDatum`),
KEY `laufzettelErsteller` (`laufzettelErsteller`)
) ENGINE=MyISAM AUTO_INCREMENT=366 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `laufzettel_stunden` (
`laufzettelStundeID` int(11) NOT NULL AUTO_INCREMENT,
`laufzettelID` int(11) NOT NULL,
`laufzettelKlasse` varchar(50) NOT NULL,
`laufzettelLehrer` varchar(50) NOT NULL,
`laufzettelFach` varchar(50) NOT NULL,
`laufzettelStunde` int(11) NOT NULL,
`laufzettelZustimmung` tinyint(1) NOT NULL DEFAULT 0,
`laufzettelZustimmungZeit` int(11) DEFAULT NULL,
`laufzettelZustimmungKommentar` mediumtext DEFAULT NULL,
PRIMARY KEY (`laufzettelStundeID`) USING BTREE,
KEY `laufzettelLehrer` (`laufzettelLehrer`),
KEY `laufzettelID` (`laufzettelID`)
) ENGINE=MyISAM AUTO_INCREMENT=2925 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `lehrer` (
`lehrerID` int(11) NOT NULL COMMENT 'XML ID aus ASV',
`lehrerAsvID` varchar(100) NOT NULL,
`lehrerKuerzel` varchar(100) NOT NULL,
`lehrerName` mediumtext NOT NULL,
`lehrerNameVorgestellt` text DEFAULT NULL,
`lehrerNameNachgestellt` text DEFAULT NULL,
`lehrerVornamen` mediumtext NOT NULL,
`lehrerRufname` mediumtext NOT NULL,
`lehrerGeschlecht` enum('w','m') NOT NULL,
`lehrerZeugnisunterschrift` mediumtext NOT NULL,
`lehrerAmtsbezeichnung` int(11) NOT NULL,
`lehrerUserID` int(11) NOT NULL DEFAULT 0,
PRIMARY KEY (`lehrerAsvID`) USING BTREE,
KEY `lehrerKuerzel` (`lehrerKuerzel`),
KEY `lehrerID` (`lehrerID`),
KEY `lehrerUserID` (`lehrerUserID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `lerntutoren` (
`lerntutorID` int(11) NOT NULL AUTO_INCREMENT,
`lerntutorSchuelerAsvID` varchar(100) NOT NULL,
PRIMARY KEY (`lerntutorID`) USING BTREE,
KEY `lerntutorSchuelerAsvID` (`lerntutorSchuelerAsvID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `lerntutoren_slots` (
`slotID` int(11) NOT NULL AUTO_INCREMENT,
`slotLerntutorID` int(11) NOT NULL,
`slotFach` varchar(255) NOT NULL,
`slotJahrgangsstufe` varchar(255) NOT NULL,
`slotSchuelerBelegt` varchar(255) DEFAULT '',
PRIMARY KEY (`slotID`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `loginstat` (
`statTimestamp` timestamp NOT NULL DEFAULT current_timestamp(),
`statLoggedInTeachers` int(11) DEFAULT NULL,
`statLoggedInStudents` int(11) DEFAULT NULL,
`statLoggedInParents` int(11) DEFAULT NULL,
PRIMARY KEY (`statTimestamp`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `mail_change_requests` (
`changeRequestID` int(11) NOT NULL,
`changeRequestUserID` int(11) NOT NULL,
`changeRequestTime` int(11) NOT NULL,
`changeRequestSecret` text NOT NULL,
`changeRequestNewMail` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `mail_send` (
`mailID` int(11) NOT NULL AUTO_INCREMENT,
`mailRecipient` mediumtext NOT NULL,
`mailSubject` mediumtext NOT NULL,
`mailText` mediumtext NOT NULL,
`mailSent` int(11) NOT NULL DEFAULT 0,
`mailCrawler` int(11) NOT NULL DEFAULT 1,
`replyTo` varchar(255) DEFAULT '',
`mailCC` varchar(255) DEFAULT '',
`mailLesebestaetigung` tinyint(1) NOT NULL DEFAULT 0,
`mailIsHTML` tinyint(1) NOT NULL DEFAULT 0,
`mailAttachments` text NOT NULL,
PRIMARY KEY (`mailID`) USING BTREE,
KEY `mailSent` (`mailSent`,`mailCrawler`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `math_captcha` (
`captchaID` int(11) NOT NULL AUTO_INCREMENT,
`captchaQuestion` varchar(100) NOT NULL,
`captchaSolution` int(11) NOT NULL,
`captchaSecret` varchar(5) NOT NULL,
PRIMARY KEY (`captchaID`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=18584 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `mebis_accounts` (
`mebisAccountID` int(11) NOT NULL AUTO_INCREMENT,
`mebisAccountVorname` varchar(200) NOT NULL,
`mebisAccountNachname` varchar(200) NOT NULL,
`mebisAccountBenutzername` varchar(200) NOT NULL,
`mebisAccountPasswort` varchar(200) NOT NULL,
PRIMARY KEY (`mebisAccountID`) USING BTREE,
KEY `mebisAccountVorname` (`mebisAccountVorname`),
KEY `mebisAccountNachname` (`mebisAccountNachname`)
) ENGINE=MyISAM AUTO_INCREMENT=1591 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `mensa_order` (
`userID` int(11) DEFAULT NULL,
`speiseplanID` int(11) DEFAULT NULL,
`time` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `mensa_speiseplan` (
`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
`date` date DEFAULT NULL,
`title` varchar(255) DEFAULT NULL,
`preis_schueler` float DEFAULT NULL,
`preis_default` float DEFAULT NULL,
`desc` text DEFAULT NULL,
`vegetarisch` tinyint(1) DEFAULT NULL,
`vegan` tinyint(1) DEFAULT NULL,
`laktosefrei` tinyint(1) DEFAULT NULL,
`glutenfrei` tinyint(1) DEFAULT NULL,
`bio` tinyint(1) DEFAULT NULL,
`regional` tinyint(1) DEFAULT NULL,
PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `messages_attachment` (
`attachmentID` int(11) NOT NULL AUTO_INCREMENT,
`attachmentFileUploadID` int(11) NOT NULL,
`attachmentAccessCode` varchar(20) NOT NULL,
PRIMARY KEY (`attachmentID`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=2894 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `messages_folders` (
`folderID` int(11) NOT NULL AUTO_INCREMENT,
`folderName` text NOT NULL,
`folderUserID` int(11) NOT NULL,
PRIMARY KEY (`folderID`) USING BTREE,
KEY `folderUserID` (`folderUserID`)
) ENGINE=MyISAM AUTO_INCREMENT=584 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `messages_messages` (
`messageID` int(11) NOT NULL AUTO_INCREMENT,
`messageUserID` int(11) NOT NULL,
`messageSubject` text NOT NULL,
`messageText` longtext NOT NULL,
`messageSender` int(11) NOT NULL,
`messageFolder` enum('POSTEINGANG','GESENDETE','PAPIERKORB','ANDERER','ARCHIV') NOT NULL DEFAULT 'POSTEINGANG',
`messageFolderID` int(11) NOT NULL DEFAULT 0,
`messageRecipients` longtext NOT NULL,
`messageRecipientsPreview` longtext NOT NULL,
`messageCCRecipients` longtext NOT NULL,
`messageBCCRecipients` longtext NOT NULL,
`messageIsRead` tinyint(1) NOT NULL DEFAULT 0,
`messagePriority` enum('NORMAL','HIGH','LOW','') NOT NULL DEFAULT 'NORMAL',
`messageTime` int(11) NOT NULL DEFAULT 0,
`messageAttachments` text NOT NULL,
`messageNeedConfirmation` tinyint(1) NOT NULL DEFAULT 0,
`messageIsConfirmed` tinyint(1) NOT NULL,
`messageConfirmTime` int(11) NOT NULL,
`messageConfirmChannel` enum('PORTAL','MAIL') NOT NULL,
`messageHasQuestions` tinyint(1) NOT NULL DEFAULT 0,
`messageAllowAnswer` int(11) NOT NULL DEFAULT 1,
`messageIsReplyTo` int(11) NOT NULL DEFAULT 0,
`messageConfirmSecret` varchar(20) NOT NULL,
`messageIsSentViaEMail` tinyint(1) NOT NULL DEFAULT 0,
`messageQuestionIDs` text NOT NULL,
`messageIsDeleted` tinyint(1) NOT NULL DEFAULT 0,
`messageIsForwardFrom` int(11) NOT NULL DEFAULT 0,
`messageMyRecipientSaveString` varchar(255) DEFAULT NULL COMMENT 'In welchem Empfänger ist der Empfänger dieser Nachricht enthalten. (Wen betrifft es.)',
PRIMARY KEY (`messageID`) USING BTREE,
KEY `messagesKey` (`messageUserID`,`messageSender`,`messageFolder`,`messageFolderID`,`messageIsRead`,`messageIsDeleted`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=463728 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `messages_questions` (
`questionID` int(11) NOT NULL AUTO_INCREMENT,
`questionText` mediumtext NOT NULL,
`questionType` enum('BOOLEAN','TEXT','NUMBER','FILE') NOT NULL DEFAULT 'TEXT',
`questionUserID` int(11) NOT NULL,
`questionSecret` varchar(10) NOT NULL,
PRIMARY KEY (`questionID`) USING BTREE,
KEY `questionUserID` (`questionUserID`)
) ENGINE=InnoDB AUTO_INCREMENT=110 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `messages_questions_answers` (
`answerID` int(11) NOT NULL AUTO_INCREMENT,
`answerQuestionID` int(11) NOT NULL,
`answerMessageID` int(11) NOT NULL,
`answerData` longtext NOT NULL,
PRIMARY KEY (`answerID`) USING BTREE,
KEY `answerQuestionID` (`answerQuestionID`),
KEY `answerMessageID` (`answerMessageID`)
) ENGINE=InnoDB AUTO_INCREMENT=16323 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `modul_admin_notes` (
`noteID` int(11) NOT NULL AUTO_INCREMENT,
`noteModuleName` varchar(255) NOT NULL,
`noteText` text NOT NULL,
`noteUserID` int(11) NOT NULL,
`noteTime` int(11) NOT NULL,
PRIMARY KEY (`noteID`) USING BTREE,
KEY `noteModuleName` (`noteModuleName`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `nextcloud_users` (
`userID` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Same UserID as in SI',
`nextcloudUsername` text NOT NULL,
`userPasswordSet` int(11) NOT NULL DEFAULT 0,
`userQuota` varchar(200) NOT NULL,
`userGroups` text NOT NULL,
PRIMARY KEY (`userID`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `noten_arbeiten` (
`arbeitID` int(11) NOT NULL AUTO_INCREMENT,
`arbeitBereich` enum('SA','KA','EX','MDL') NOT NULL,
`arbeitName` mediumtext NOT NULL,
`arbeitLehrerKuerzel` varchar(10) NOT NULL,
`arbeitIsMuendlich` tinyint(1) NOT NULL,
`arbeitGewicht` decimal(4,2) NOT NULL DEFAULT 1.00,
`arbeitFachKurzform` varchar(200) NOT NULL COMMENT 'Kurzform, nicht ASD ID, da eigene Unterrichte erstellt sein kÃ¶nnten.',
`arbeitDatum` date DEFAULT NULL,
`arbeitUnterrichtName` varchar(100) NOT NULL,
PRIMARY KEY (`arbeitID`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `noten_bemerkung_textvorlagen` (
`bemerkungID` int(11) NOT NULL AUTO_INCREMENT,
`bemerkungGruppeID` int(11) NOT NULL,
`bemerkungNote` int(11) NOT NULL DEFAULT 0,
`bemerkungTextWeiblich` longtext NOT NULL,
`bemerkungTextMaennlich` longtext NOT NULL,
PRIMARY KEY (`bemerkungID`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `noten_bemerkung_textvorlagen_gruppen` (
`gruppeID` int(11) NOT NULL AUTO_INCREMENT,
`gruppeName` mediumtext NOT NULL,
`koppelMVNote` enum('M','V') CHARACTER SET latin1 DEFAULT NULL,
PRIMARY KEY (`gruppeID`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `noten_fach_einstellungen` (
`fachKurzform` varchar(100) NOT NULL,
`fachIsVorrueckungsfach` tinyint(1) NOT NULL,
`fachOrder` int(11) NOT NULL,
`fachNoteZusammenMit` mediumtext NOT NULL COMMENT 'Fachkurzformen der FÃ¤cher, die mit diesem Fach zusammen verrechnet werden. Aktuelles Fach wird als Hauptfach angezeigt. Getrennt durch Komma.',
PRIMARY KEY (`fachKurzform`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `noten_gewichtung` (
`fachKuerzel` varchar(100) NOT NULL,
`fachJahrgangsstufe` int(11) NOT NULL,
`fachGewichtKlein` int(11) NOT NULL DEFAULT 1,
`fachGewichtGross` int(11) NOT NULL DEFAULT 1,
PRIMARY KEY (`fachKuerzel`,`fachJahrgangsstufe`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `noten_mv` (
`mvFachKurzform` varchar(200) NOT NULL,
`mvUnterrichtName` varchar(200) NOT NULL,
`mNote` int(11) NOT NULL,
`vNote` int(11) NOT NULL,
`schuelerAsvID` varchar(100) NOT NULL,
`zeugnisID` int(11) NOT NULL,
`noteKommentar` mediumtext NOT NULL,
PRIMARY KEY (`mvFachKurzform`,`mvUnterrichtName`,`schuelerAsvID`,`zeugnisID`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `noten_noten` (
`noteSchuelerAsvID` varchar(20) NOT NULL,
`noteWert` int(11) NOT NULL,
`noteTendenz` int(11) NOT NULL,
`noteArbeitID` int(11) NOT NULL,
`noteDatum` date DEFAULT NULL,
`noteKommentar` longtext NOT NULL,
`noteIsNachtermin` tinyint(1) NOT NULL DEFAULT 0,
`noteNurWennBesser` tinyint(1) NOT NULL DEFAULT 0,
PRIMARY KEY (`noteSchuelerAsvID`,`noteArbeitID`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `noten_verrechnung` (
`verrechnungID` int(11) NOT NULL AUTO_INCREMENT,
`verrechnungFach` varchar(255) NOT NULL,
`verrechnungUnterricht1` varchar(255) NOT NULL,
`verrechnungUnterricht2` varchar(100) NOT NULL,
`verrechnungGewicht1` int(11) NOT NULL,
`verrechnungGewicht2` int(11) NOT NULL,
PRIMARY KEY (`verrechnungID`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `noten_wahlfach_faecher` (
`wahlfachID` int(11) NOT NULL AUTO_INCREMENT,
`zeugnisID` int(11) NOT NULL,
`fachKurzform` varchar(100) NOT NULL,
`fachUnterrichtName` varchar(100) NOT NULL,
`wahlfachBezeichnung` mediumtext NOT NULL,
PRIMARY KEY (`wahlfachID`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `noten_wahlfach_noten` (
`wahlfachID` int(11) NOT NULL,
`schuelerAsvID` varchar(100) NOT NULL,
`wahlfachNote` int(11) NOT NULL DEFAULT 4,
PRIMARY KEY (`wahlfachID`,`schuelerAsvID`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `noten_zeugnis_bemerkung` (
`bemerkungSchuelerAsvID` varchar(100) NOT NULL,
`bemerkungZeugnisID` int(11) NOT NULL,
`bemerkungText1` longtext NOT NULL,
`bemerkungText2` longtext NOT NULL,
`klassenzielErreicht` tinyint(1) NOT NULL DEFAULT 1,
PRIMARY KEY (`bemerkungSchuelerAsvID`,`bemerkungZeugnisID`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `noten_zeugnis_exemplar` (
`zeugnisID` int(11) NOT NULL,
`schuelerAsvID` varchar(100) NOT NULL,
`uploadID` int(11) NOT NULL,
`createdTime` int(11) NOT NULL,
PRIMARY KEY (`zeugnisID`,`schuelerAsvID`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `noten_zeugnisse` (
`zeugnisID` int(11) NOT NULL AUTO_INCREMENT,
`zeugnisArt` enum('ZZ','JZ','NB','ABZ','SEMZ','ABIZ') NOT NULL,
`zeugnisName` varchar(255) NOT NULL,
PRIMARY KEY (`zeugnisID`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `noten_zeugnisse_klassen` (
`zeugnisID` int(11) NOT NULL,
`zeugnisKlasse` varchar(20) NOT NULL,
`zeugnisDatum` date NOT NULL,
`zeugnisNotenschluss` date NOT NULL,
`zeugnisUnterschriftKlassenleitungAsvID` varchar(20) NOT NULL,
`zeugnisUnterschriftSchulleitungAsvID` varchar(20) NOT NULL,
`zeugnisUnterschriftKlassenleitungAsvIDGezeichnet` tinyint(1) NOT NULL DEFAULT 0,
`zeugnisUnterschriftSchulleitungAsvIDGezeichnet` tinyint(1) NOT NULL DEFAULT 0,
PRIMARY KEY (`zeugnisID`,`zeugnisKlasse`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `noten_zeugnisse_noten` (
`noteSchuelerAsvID` varchar(100) NOT NULL,
`noteZeugnisID` int(11) NOT NULL,
`noteFachKurzform` varchar(100) NOT NULL,
`noteWert` int(11) NOT NULL,
`noteIsPaed` tinyint(1) NOT NULL DEFAULT 0,
`notePaedBegruendung` mediumtext NOT NULL,
PRIMARY KEY (`noteSchuelerAsvID`,`noteZeugnisID`,`noteFachKurzform`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `office365_accounts` (
`accountAsvID` varchar(200) NOT NULL,
`accountUsername` varchar(2000) NOT NULL,
`accountUserID` mediumtext NOT NULL,
`accountInitialPassword` varchar(200) NOT NULL,
`accountDetailsSet` tinyint(1) NOT NULL DEFAULT 0,
`accountLicenseSet` tinyint(1) NOT NULL DEFAULT 0,
`accountCreated` int(11) NOT NULL,
`accountIsTeacher` tinyint(1) NOT NULL DEFAULT 0,
`accountIsPupil` tinyint(1) NOT NULL DEFAULT 0,
PRIMARY KEY (`accountAsvID`,`accountIsTeacher`,`accountIsPupil`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `projekt_lehrer2grade` (
`lehrerUserID` int(11) NOT NULL,
`gradeName` varchar(255) NOT NULL,
PRIMARY KEY (`lehrerUserID`,`gradeName`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `projekt_projekte` (
`userID` varchar(100) NOT NULL,
`projektName` mediumtext NOT NULL,
`projektErfolg` varchar(255) NOT NULL,
`projektFach1` varchar(100) NOT NULL,
`projektFach2` varchar(100) NOT NULL,
`projektLehrer1` varchar(100) NOT NULL,
`projektLehrer2` varchar(100) NOT NULL,
PRIMARY KEY (`userID`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `remote_usersync` (
`syncID` int(11) NOT NULL AUTO_INCREMENT,
`syncName` varchar(200) NOT NULL,
`syncLoginDomain` varchar(200) NOT NULL,
`syncSecret` varchar(200) NOT NULL,
`syncURL` mediumtext NOT NULL,
`syncIsActive` tinyint(1) NOT NULL DEFAULT 1,
`syncLastSync` int(11) NOT NULL DEFAULT 0,
`syncSuccessfull` tinyint(1) NOT NULL DEFAULT 0,
`syncLastSyncResult` longtext NOT NULL,
`syncDirType` enum('ACTIVEDIRECTORY','EDIRECTORY','','') NOT NULL DEFAULT 'ACTIVEDIRECTORY',
PRIMARY KEY (`syncID`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `resetpassword` (
`resetID` int(11) NOT NULL AUTO_INCREMENT,
`resetUserID` int(11) NOT NULL,
`resetNewPasswordHash` varchar(200) NOT NULL,
`resetCode` varchar(200) NOT NULL,
PRIMARY KEY (`resetID`) USING BTREE,
KEY `resetUserID` (`resetUserID`)
) ENGINE=MyISAM AUTO_INCREMENT=1931 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `respizienz` (
`respizienzID` int(11) NOT NULL,
`respizienzFile` mediumtext NOT NULL,
`respizienzFSLFile` mediumtext NOT NULL,
`respizientSLFile` mediumtext NOT NULL,
`respizienzIsAnalog` tinyint(1) NOT NULL DEFAULT 0,
PRIMARY KEY (`respizienzID`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `schaukasten_bildschirme` (
`schaukastenID` int(11) NOT NULL AUTO_INCREMENT,
`schaukastenEinUhrzeit` varchar(5) NOT NULL,
`schaukastenAusUhrzeit` varchar(5) NOT NULL,
`schaukastenAdded` int(11) NOT NULL,
`schaukastenLastUpdate` int(11) NOT NULL,
`schaukastenSystemName` varchar(255) NOT NULL,
`schaukastenSystemID` varchar(255) NOT NULL,
`schaukastenName` varchar(255) NOT NULL,
`schaukastenResolutionX` int(11) NOT NULL,
`schaukastenResolutionY` int(11) NOT NULL,
`schaukastenMode` enum('L','P') DEFAULT NULL,
`schaukastenIsActive` tinyint(4) NOT NULL DEFAULT 0,
`schaukastenScreenShot` blob DEFAULT NULL,
`schaukastenLayout` enum('layout1','layout2','layout3') NOT NULL DEFAULT 'layout1',
`schaukastenLastContentUpdate` int(11) NOT NULL DEFAULT 0,
`schaukastenHasPPT` tinyint(1) NOT NULL DEFAULT 0,
PRIMARY KEY (`schaukastenID`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `schaukasten_inhalt` (
`schaukastenID` int(11) NOT NULL,
`schaukastenPosition` int(11) NOT NULL,
`schaukastenContent` varchar(255) NOT NULL,
PRIMARY KEY (`schaukastenID`,`schaukastenPosition`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `schaukasten_powerpoint` (
`powerpointID` int(11) NOT NULL AUTO_INCREMENT,
`uploadID` int(11) NOT NULL,
`lastUpdate` int(11) NOT NULL,
`powerpointName` mediumtext NOT NULL,
PRIMARY KEY (`powerpointID`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `schaukasten_website` (
`websiteID` int(11) NOT NULL AUTO_INCREMENT,
`websiteURL` mediumtext NOT NULL,
`websiteName` mediumtext NOT NULL,
`websiteLastUpdate` int(11) NOT NULL,
`websiteRefreshSeconds` int(11) NOT NULL DEFAULT 0,
PRIMARY KEY (`websiteID`) USING BTREE,
UNIQUE KEY `websiteName` (`websiteID`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `schueler` (
`schuelerAsvID` varchar(200) NOT NULL,
`schuelerName` text NOT NULL,
`schuelerNameVorgestellt` text DEFAULT NULL,
`schuelerNameNachgestellt` text DEFAULT NULL,
`schuelerVornamen` text NOT NULL,
`schuelerRufname` text NOT NULL,
`schuelerGeschlecht` enum('m','w') NOT NULL,
`schuelerGeburtsdatum` date NOT NULL,
`schuelerKlasse` varchar(200) NOT NULL,
`schuelerJahrgangsstufe` varchar(10) NOT NULL,
`schuelerAustrittDatum` date DEFAULT NULL,
`schuelerUserID` int(11) NOT NULL DEFAULT 0,
`schuelerBekenntnis` varchar(10) NOT NULL,
`schuelerAusbildungsrichtung` varchar(200) NOT NULL,
`schuelerGeburtsort` varchar(255) NOT NULL,
`schuelerGeburtsland` varchar(255) NOT NULL,
`schulerEintrittJahrgangsstufe` varchar(10) NOT NULL,
`schuelerEintrittDatum` date NOT NULL,
`schuelerFoto` int(11) NOT NULL DEFAULT 0,
`schuelerGanztagBetreuung` int(11) NOT NULL DEFAULT 0,
PRIMARY KEY (`schuelerAsvID`) USING BTREE,
KEY `schuelerKlasse` (`schuelerKlasse`),
KEY `schuelerUserID` (`schuelerUserID`),
KEY `schuelerEintrittDatum` (`schuelerEintrittDatum`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `schueler_briefe` (
`briefID` int(11) NOT NULL AUTO_INCREMENT,
`briefAdresse` mediumtext NOT NULL,
`schuelerAsvID` varchar(100) NOT NULL,
`briefLehrerAsvID` varchar(100) NOT NULL,
`briefAnrede` mediumtext NOT NULL,
`briefBetreff` mediumtext NOT NULL,
`briefDatum` date NOT NULL,
`briefText` longtext NOT NULL,
`briefUnterschrift` mediumtext NOT NULL,
`briefVorgangErledigt` int(11) NOT NULL,
`briefGedruckt` int(11) NOT NULL,
`briefErledigtKommentar` mediumtext NOT NULL,
`briefKommentar` longtext NOT NULL,
`briefSaveLonger` int(11) NOT NULL DEFAULT 0 COMMENT 'Bei 0 dauerhafte Speicherung, bei Wert letzte Änderung',
PRIMARY KEY (`briefID`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `schueler_fremdsprache` (
`schuelerAsvID` varchar(100) NOT NULL,
`spracheSortierung` int(11) NOT NULL,
`spracheAbJahrgangsstufe` varchar(10) NOT NULL,
`spracheFach` mediumtext NOT NULL,
`spracheFeststellungspruefung` tinyint(1) NOT NULL,
PRIMARY KEY (`schuelerAsvID`,`spracheSortierung`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `schueler_nachteilsausgleich` (
`schuelerAsvID` varchar(100) NOT NULL,
`artStoerung` enum('rs','lrs','ls') NOT NULL,
`arbeitszeitverlaengerung` varchar(255) NOT NULL,
`notenschutz` tinyint(1) NOT NULL,
`kommentar` mediumtext NOT NULL,
`gueltigBis` date DEFAULT NULL,
`gewichtung` enum('11','12','21') CHARACTER SET latin1 DEFAULT NULL,
PRIMARY KEY (`schuelerAsvID`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `schuelerinfo_dokumente` (
`dokumentID` int(11) NOT NULL AUTO_INCREMENT,
`dokumentSchuelerAsvID` varchar(200) NOT NULL,
`dokumentName` varchar(255) NOT NULL,
`dokumentKommentar` mediumtext NOT NULL,
`dokumentUploadID` int(11) NOT NULL,
PRIMARY KEY (`dokumentID`) USING BTREE,
KEY `dokumentSchuelerAsvID` (`dokumentSchuelerAsvID`)
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `schulbuch_ausleihe` (
`ausleiheID` int(11) NOT NULL AUTO_INCREMENT,
`ausleiheExemplarID` int(11) NOT NULL,
`ausleiheStartDatum` date NOT NULL,
`ausleiheEndDatum` date DEFAULT NULL,
`ausleiherNameUndKlasse` mediumtext NOT NULL,
`ausleiherSchuelerAsvID` varchar(100) NOT NULL,
`ausleiherLehrerAsvID` varchar(100) NOT NULL,
`ausleiherUserID` int(11) NOT NULL,
`rueckgeberUserID` int(11) NOT NULL,
`ausleiheKommentar` longtext NOT NULL,
PRIMARY KEY (`ausleiheID`) USING BTREE,
KEY `ausleiheExemplarID` (`ausleiheExemplarID`),
KEY `ausleiheStartDatum` (`ausleiheStartDatum`,`ausleiheEndDatum`),
KEY `ausleiherSchuelerAsvID` (`ausleiherSchuelerAsvID`),
KEY `ausleiherLehrerAsvID` (`ausleiherLehrerAsvID`),
KEY `ausleiherUserID` (`ausleiherUserID`),
KEY `rueckgeberUserID` (`rueckgeberUserID`)
) ENGINE=InnoDB AUTO_INCREMENT=36717 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `schulbuch_buecher` (
`buchID` int(11) NOT NULL AUTO_INCREMENT,
`buchTitel` mediumtext NOT NULL,
`buchVerlag` mediumtext NOT NULL,
`buchISBN` varchar(20) NOT NULL,
`buchPreis` int(11) NOT NULL COMMENT 'preis in Cent',
`buchFach` varchar(200) NOT NULL,
`buchKlasse` int(11) NOT NULL,
`buchErfasserUserID` int(11) NOT NULL,
PRIMARY KEY (`buchID`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=160 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `schulbuch_exemplare` (
`exemplarID` int(11) NOT NULL AUTO_INCREMENT,
`exemplarBuchID` int(11) NOT NULL,
`exemplarBarcode` varchar(200) NOT NULL,
`exemplarZustand` int(11) NOT NULL DEFAULT 0,
`exemplarAnschaffungsjahr` varchar(5) NOT NULL,
`exemplarIsBankbuch` tinyint(1) NOT NULL DEFAULT 0,
`exemplarLagerort` mediumtext NOT NULL,
`exemplarErfasserUserID` int(11) NOT NULL,
PRIMARY KEY (`exemplarID`,`exemplarBarcode`) USING BTREE,
KEY `exemplarBuchID` (`exemplarBuchID`)
) ENGINE=InnoDB AUTO_INCREMENT=22454 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `schulen` (
`schuleID` int(11) NOT NULL,
`schuleNummer` varchar(255) NOT NULL,
`schuleArt` varchar(255) NOT NULL,
`schuleName` mediumtext NOT NULL,
PRIMARY KEY (`schuleID`) USING BTREE,
KEY `schuleNummer` (`schuleNummer`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `sessions` (
`sessionID` varchar(255) NOT NULL,
`sessionUserID` int(11) NOT NULL,
`sessionType` enum('NORMAL','SAVED') NOT NULL,
`sessionIP` varchar(100) NOT NULL,
`sessionLastActivity` int(11) NOT NULL,
`sessionBrowser` varchar(255) NOT NULL,
`sessionDevice` enum('ANDROIDAPP','IOSAPP','WINDOWSPHONEAPP','NORMAL','SINGLESIGNON') NOT NULL DEFAULT 'NORMAL',
`sessionIsDebug` tinyint(1) NOT NULL DEFAULT 0,
`session2FactorActive` int(11) NOT NULL DEFAULT 0,
`sessionStore` longtext NOT NULL,
PRIMARY KEY (`sessionID`) USING BTREE,
KEY `sessionType` (`sessionType`),
KEY `sessionUserID` (`sessionUserID`),
KEY `sessionLastActivity` (`sessionLastActivity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `settings` (
`settingName` varchar(100) NOT NULL,
`settingValue` mediumtext NOT NULL,
PRIMARY KEY (`settingName`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `settings_history` (
`settingHistoryID` int(11) NOT NULL AUTO_INCREMENT,
`settingHistoryName` varchar(255) NOT NULL,
`settingHistoryChangeTime` int(11) NOT NULL,
`settingHistoryOldValue` mediumtext NOT NULL,
`settingHistoryNewValue` mediumtext NOT NULL,
`settingHistoryUserID` int(11) DEFAULT NULL,
PRIMARY KEY (`settingHistoryID`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=6144 DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `site_activation` (
`siteName` varchar(200) NOT NULL,
`siteIsActive` tinyint(1) NOT NULL,
PRIMARY KEY (`siteName`) USING BTREE,
KEY `siteIsActive` (`siteIsActive`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `sprechtag` (
`sprechtagID` int(11) NOT NULL AUTO_INCREMENT,
`sprechtagDate` date NOT NULL,
`sprechtagName` mediumtext NOT NULL,
`sprechtagBuchbarBis` date NOT NULL,
`sprechtagIsActive` tinyint(1) NOT NULL DEFAULT 0,
`sprechtagBuchbarAb` int(11) NOT NULL COMMENT 'Timestamp, ab dem der Sprechtag buchbar ist',
`sprechtagKlassen` longtext NOT NULL COMMENT 'Liste der Klassen. (leer: alle)',
`sprechtagIsVorlage` tinyint(1) NOT NULL DEFAULT 0,
`sprechtagPercentPerTeacherOnlineBookable` int(11) NOT NULL DEFAULT 100,
`sprechtagBeginTime` int(11) NOT NULL,
`sprechtagInfotext` longtext NOT NULL,
`sprechtagIsOnline` tinyint(1) NOT NULL,
PRIMARY KEY (`sprechtagID`) USING BTREE,
KEY `sprechtagDate` (`sprechtagDate`),
KEY `sprechtagIsActive` (`sprechtagIsActive`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `sprechtag_buchungen` (
`buchungID` int(11) NOT NULL AUTO_INCREMENT,
`lehrerKuerzel` varchar(100) NOT NULL,
`sprechtagID` int(11) NOT NULL,
`slotID` int(11) NOT NULL,
`isBuchbar` int(11) NOT NULL,
`schuelerAsvID` varchar(100) NOT NULL,
`elternUserID` int(11) NOT NULL,
`meetingURL` text DEFAULT NULL,
PRIMARY KEY (`buchungID`) USING BTREE,
KEY `lehrerKuerzel` (`lehrerKuerzel`),
KEY `sprechtagID` (`sprechtagID`),
KEY `schuelerAsvID` (`schuelerAsvID`)
) ENGINE=InnoDB AUTO_INCREMENT=12651 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `sprechtag_raeume` (
`sprechtagID` int(11) NOT NULL,
`lehrerKuerzel` varchar(200) NOT NULL,
`raumName` varchar(200) NOT NULL,
PRIMARY KEY (`sprechtagID`,`lehrerKuerzel`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `sprechtag_slots` (
`slotID` int(11) NOT NULL AUTO_INCREMENT,
`sprechtagID` int(11) NOT NULL,
`slotStart` int(11) NOT NULL,
`slotEnde` int(11) NOT NULL,
`slotIsPause` tinyint(1) NOT NULL DEFAULT 0,
`slotIsOnlineBuchbar` int(11) NOT NULL DEFAULT 1,
PRIMARY KEY (`slotID`) USING BTREE,
KEY `sprechtagID` (`sprechtagID`)
) ENGINE=InnoDB AUTO_INCREMENT=93 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `stundenplan_aufsichten` (
`aufsichtID` int(11) NOT NULL AUTO_INCREMENT,
`stundenplanID` int(11) NOT NULL,
`aufsichtBereich` mediumtext NOT NULL,
`aufsichtVorStunde` int(11) NOT NULL,
`aufsichtTag` int(11) NOT NULL,
`aufsichtLehrerKuerzel` varchar(200) NOT NULL,
PRIMARY KEY (`aufsichtID`) USING BTREE,
KEY `stundenplanID` (`stundenplanID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `stundenplan_plaene` (
`stundenplanID` int(11) NOT NULL AUTO_INCREMENT,
`stundenplanAb` date DEFAULT NULL,
`stundenplanBis` date DEFAULT NULL,
`stundenplanUploadUserID` int(11) NOT NULL,
`stundenplanName` varchar(255) NOT NULL,
`stundenplanIsDeleted` int(11) NOT NULL,
PRIMARY KEY (`stundenplanID`) USING BTREE,
KEY `stundenplanAb` (`stundenplanAb`,`stundenplanBis`)
) ENGINE=InnoDB AUTO_INCREMENT=133 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `stundenplan_stunden` (
`stundeID` int(11) NOT NULL AUTO_INCREMENT,
`stundenplanID` int(11) NOT NULL,
`stundeKlasse` varchar(20) NOT NULL,
`stundeLehrer` varchar(20) CHARACTER SET utf8 COLLATE utf8_german2_ci NOT NULL,
`stundeFach` varchar(20) NOT NULL,
`stundeRaum` varchar(20) NOT NULL,
`stundeTag` int(11) NOT NULL,
`stundeStunde` int(11) NOT NULL,
PRIMARY KEY (`stundeID`) USING BTREE,
KEY `stundenplanID` (`stundenplanID`)
) ENGINE=InnoDB AUTO_INCREMENT=42882 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `templates` (
`templateName` varchar(200) NOT NULL,
`templateCompiledContents` longtext NOT NULL,
PRIMARY KEY (`templateName`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `trenndaten` (
`trennWort` varchar(255) NOT NULL,
`trennWortGetrennt` varchar(255) NOT NULL,
PRIMARY KEY (`trennWort`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `two_factor_trusted_devices` (
`deviceID` int(11) NOT NULL AUTO_INCREMENT,
`deviceCookieData` mediumtext NOT NULL,
`deviceUserID` int(11) NOT NULL,
PRIMARY KEY (`deviceID`) USING BTREE,
KEY `two_factor_trusted_devices_ibfk_1` (`deviceUserID`) USING BTREE,
CONSTRAINT `two_factor_trusted_devices_ibfk_1` FOREIGN KEY (`deviceUserID`) REFERENCES `users` (`userID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `unknown_mails` (
`mailID` int(11) NOT NULL AUTO_INCREMENT,
`mailSubject` mediumtext NOT NULL,
`mailText` longtext NOT NULL,
`mailSender` mediumtext NOT NULL,
PRIMARY KEY (`mailID`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `unterricht` (
`unterrichtID` int(11) NOT NULL COMMENT 'Aus ASV Export File',
`unterrichtLehrerID` int(11) NOT NULL,
`unterrichtFachID` int(11) NOT NULL,
`unterrichtBezeichnung` varchar(200) NOT NULL,
`unterrichtArt` varchar(255) NOT NULL,
`unterrichtStunden` decimal(4,2) NOT NULL,
`unterrichtIsWissenschaftlich` tinyint(1) NOT NULL,
`unterrichtStart` date NOT NULL,
`unterrichtEnde` date NOT NULL,
`unterrichtIsKlassenunterricht` tinyint(1) NOT NULL,
`unterrichtKoppelText` varchar(255) DEFAULT '',
`unterrichtKoppelIsPseudo` tinyint(1) NOT NULL DEFAULT 0,
PRIMARY KEY (`unterrichtID`) USING BTREE,
KEY `unterrichtLehrerID` (`unterrichtLehrerID`),
KEY `unterrichtFachID` (`unterrichtFachID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `unterricht_besuch` (
`unterrichtID` int(11) NOT NULL,
`schuelerAsvID` varchar(200) NOT NULL,
KEY `unterrichtID` (`unterrichtID`,`schuelerAsvID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `uploads` (
`uploadID` int(11) NOT NULL AUTO_INCREMENT,
`uploadFileName` mediumtext NOT NULL,
`uploadFileExtension` varchar(50) NOT NULL,
`uploadFileMimeType` varchar(200) NOT NULL,
`uploadTime` int(11) NOT NULL,
`uploaderUserID` int(11) NOT NULL,
`fileAccessCode` varchar(222) NOT NULL,
PRIMARY KEY (`uploadID`) USING BTREE,
KEY `fileAccessCode` (`fileAccessCode`)
) ENGINE=InnoDB AUTO_INCREMENT=9828 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `user_settings` (
`userID` int(11) NOT NULL,
`skinColor` enum('blue','black','purple','yellow','red','green') NOT NULL DEFAULT 'green',
`startPage` enum('aufeinenblick','vplan','stundenplan') NOT NULL DEFAULT 'aufeinenblick',
PRIMARY KEY (`userID`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `users` (
`userID` int(11) NOT NULL AUTO_INCREMENT,
`userName` mediumtext NOT NULL,
`userFirstName` mediumtext NOT NULL,
`userLastName` mediumtext NOT NULL,
`userCachedPasswordHash` mediumtext NOT NULL,
`userCachedPasswordHashTime` int(11) NOT NULL,
`userLastPasswordChangeRemote` int(11) NOT NULL,
`userNetwork` varchar(25) NOT NULL,
`userEMail` mediumtext NOT NULL,
`userRemoteUserID` mediumtext NOT NULL,
`userAsvID` varchar(100) NOT NULL,
`userFailedLoginCount` int(11) DEFAULT 0,
`userMobilePhoneNumber` text DEFAULT NULL,
`userReceiveEMail` tinyint(1) NOT NULL DEFAULT 1,
`userLastLoginTime` int(11) NOT NULL DEFAULT 0,
`userCanChangePassword` tinyint(1) NOT NULL DEFAULT 1,
`userTOTPSecret` varchar(255) DEFAULT '',
`user2FAactive` tinyint(1) NOT NULL DEFAULT 0,
`userSignature` longtext NOT NULL,
`userMailCreated` varchar(255) DEFAULT '',
`userMailInitialPassword` varchar(255) NOT NULL,
`userAutoresponse` tinyint(1) NOT NULL DEFAULT 0,
`userAutoresponseText` longtext NOT NULL,
PRIMARY KEY (`userID`) USING BTREE,
KEY `userName` (`userName`(1024))
) ENGINE=InnoDB AUTO_INCREMENT=9877 DEFAULT CHARSET=utf8 COMMENT='Enthält alle Benutzer, die SchuleIntern benutzen können';

CREATE TABLE IF NOT EXISTS `users_groups` (
`userID` int(11) NOT NULL,
`groupName` varchar(200) NOT NULL,
PRIMARY KEY (`userID`,`groupName`) USING BTREE,
KEY `groupName` (`groupName`),
KEY `userID` (`userID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `users_groups_own` (
`groupName` varchar(255) NOT NULL,
`groupIsMessageRecipient` tinyint(1) NOT NULL,
`groupContactTeacher` tinyint(1) NOT NULL,
`groupContactPupil` tinyint(1) NOT NULL,
`groupContactParents` int(11) NOT NULL,
`groupNextCloudUserID` int(11) NOT NULL DEFAULT 0,
`groupHasNextcloudShare` tinyint(1) NOT NULL,
PRIMARY KEY (`groupName`) USING BTREE,
KEY `groupIsMessageRecipient` (`groupIsMessageRecipient`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `vplan` (
`vplanName` varchar(20) NOT NULL,
`vplanDate` text NOT NULL,
`vplanContent` longtext NOT NULL,
`vplanUpdate` varchar(200) NOT NULL,
`vplanInfo` mediumtext NOT NULL,
`vplanContentUncensored` longtext NOT NULL,
`schaukastenViewKey` text NOT NULL,
`vplanUpdateTime` int(11) NOT NULL,
PRIMARY KEY (`vplanName`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `vplan_data` (
`vplanDate` date NOT NULL,
`vplanStunde` int(11) NOT NULL,
`vplanLehrer` varchar(200) NOT NULL,
`vplanKlasse` varchar(200) NOT NULL,
`vplanFach` varchar(200) NOT NULL,
`vplanRaum` varchar(200) NOT NULL,
`vplanArt` varchar(200) NOT NULL,
`vplanFachVertreten` varchar(200) NOT NULL,
`vplanLehrerVertreten` varchar(200) NOT NULL,
`isNew` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `wlan_ticket` (
`ticketID` int(11) NOT NULL AUTO_INCREMENT,
`ticketType` enum('GAST','SCHUELER') NOT NULL,
`ticketText` mediumtext NOT NULL,
`ticketAssignedTo` int(11) NOT NULL DEFAULT 0,
`ticketValidMinutes` int(11) NOT NULL,
`ticketAssignedDate` date NOT NULL,
`ticketAssignedBy` int(11) NOT NULL,
`ticketName` mediumtext NOT NULL,
PRIMARY KEY (`ticketID`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;