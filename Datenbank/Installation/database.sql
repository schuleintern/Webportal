-- phpMyAdmin SQL Dump
-- version 5.0.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Erstellungszeit: 06. Sep 2020 um 14:38
-- Server-Version: 10.4.13-MariaDB
-- PHP-Version: 7.4.7

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Datenbank: `schuleintern_dev`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `absenzen_absenzen`
--

CREATE TABLE `absenzen_absenzen` (
  `absenzID` int(11) NOT NULL,
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
  `absenzKommtSpaeter` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Absenzen der Schüler';

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `absenzen_absenzen_stunden`
--

CREATE TABLE `absenzen_absenzen_stunden` (
  `absenzID` int(11) NOT NULL,
  `absenzStunde` int(11) NOT NULL,
  `absenzStundeEntschuldigt` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Abwesende Stunden';

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `absenzen_attestpflicht`
--

CREATE TABLE `absenzen_attestpflicht` (
  `attestpflichtID` int(11) NOT NULL,
  `schuelerAsvID` varchar(100) NOT NULL,
  `attestpflichtStart` date NOT NULL,
  `attestpflichtEnde` date NOT NULL,
  `attestpflichtUserID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `absenzen_befreiungen`
--

CREATE TABLE `absenzen_befreiungen` (
  `befreiungID` int(11) NOT NULL,
  `befreiungUhrzeit` varchar(100) NOT NULL,
  `befreiungLehrer` varchar(100) NOT NULL,
  `befreiungPrinted` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `absenzen_beurlaubungen`
--

CREATE TABLE `absenzen_beurlaubungen` (
  `beurlaubungID` int(11) NOT NULL,
  `beurlaubungCreatorID` int(11) NOT NULL,
  `beurlaubungPrinted` tinyint(1) NOT NULL DEFAULT 0,
  `beurlaubungIsInternAbwesend` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `absenzen_beurlaubung_antrag`
--

CREATE TABLE `absenzen_beurlaubung_antrag` (
  `antragID` int(11) NOT NULL,
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
  `antragStunden` mediumtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `absenzen_comments`
--

CREATE TABLE `absenzen_comments` (
  `schuelerAsvID` varchar(100) NOT NULL,
  `commentText` longtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `absenzen_krankmeldungen`
--

CREATE TABLE `absenzen_krankmeldungen` (
  `krankmeldungID` int(11) NOT NULL,
  `krankmeldungSchuelerASVID` varchar(50) NOT NULL,
  `krankmeldungDate` date NOT NULL,
  `krankmeldungUntilDate` date NOT NULL,
  `krankmeldungElternID` int(11) NOT NULL,
  `krankmeldungDurch` enum('m','v','s','schueleru18','schuelerue18') NOT NULL,
  `krankmeldungKommentar` mediumtext NOT NULL,
  `krankmeldungAbsenzID` tinyint(1) NOT NULL DEFAULT 0,
  `krankmeldungTime` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `absenzen_meldung`
--

CREATE TABLE `absenzen_meldung` (
  `meldungDatum` date NOT NULL,
  `meldungKlasse` varchar(100) NOT NULL,
  `meldungUserID` int(11) NOT NULL,
  `meldungTime` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `absenzen_merker`
--

CREATE TABLE `absenzen_merker` (
  `merkerID` int(11) NOT NULL,
  `merkerSchuelerAsvID` varchar(100) NOT NULL,
  `merkerDate` date NOT NULL,
  `merkerText` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `absenzen_sanizimmer`
--

CREATE TABLE `absenzen_sanizimmer` (
  `sanizimmerID` int(11) NOT NULL,
  `sanizimmerSchuelerAsvID` varchar(20) NOT NULL,
  `sanizimmerTimeStart` int(11) NOT NULL DEFAULT 0,
  `sanizimmerTimeEnde` int(11) NOT NULL DEFAULT 0,
  `sanizimmerErfasserUserID` int(11) NOT NULL,
  `sanizimmerResult` enum('ZURUECK','BEFREIUNG','RETTUNGSDIENST') NOT NULL,
  `sanizimmerGrund` mediumtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `absenzen_verspaetungen`
--

CREATE TABLE `absenzen_verspaetungen` (
  `verspaetungID` int(11) NOT NULL,
  `verspaetungSchuelerAsvID` varchar(50) NOT NULL,
  `verspaetungDate` date NOT NULL,
  `verspaetungMinuten` int(11) NOT NULL,
  `verspaetungKommentar` mediumtext NOT NULL,
  `verspaetungStunde` int(11) NOT NULL DEFAULT 1,
  `verspaetungBearbeitet` int(11) NOT NULL DEFAULT 0,
  `verspaetungBearbeitetKommentar` text NOT NULL,
  `verspaetungBenachrichtigt` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `amtsbezeichnungen`
--

CREATE TABLE `amtsbezeichnungen` (
  `amtsbezeichnungID` int(11) NOT NULL,
  `amtsbezeichnungKurzform` mediumtext NOT NULL,
  `amtsbezeichnungAnzeigeform` mediumtext NOT NULL,
  `amtsbezeichnungKurzformW` mediumtext NOT NULL,
  `amtsbezeichnungAnzeigeformW` mediumtext NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `andere_kalender`
--

CREATE TABLE `andere_kalender` (
  `kalenderID` int(11) NOT NULL,
  `kalenderName` varchar(255) NOT NULL,
  `kalenderAccessSchueler` tinyint(1) NOT NULL DEFAULT 0,
  `kalenderAccessLehrer` int(11) NOT NULL DEFAULT 0,
  `kalenderAccessEltern` int(11) NOT NULL DEFAULT 0,
  `kalenderAccessLehrerSchreiben` tinyint(1) NOT NULL,
  `kalenderAccessSchuelerSchreiben` tinyint(1) NOT NULL,
  `kalenderAccessElternSchreiben` tinyint(1) NOT NULL,
  `kalenderDeleteOnlyOwn` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `andere_kalender_kategorie`
--

CREATE TABLE `andere_kalender_kategorie` (
  `kategorieID` int(11) NOT NULL,
  `kategorieKalenderID` int(11) NOT NULL,
  `kategorieName` mediumtext NOT NULL,
  `kategorieFarbe` varchar(7) NOT NULL,
  `kategorieIcon` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `anschrifttyp`
--

CREATE TABLE `anschrifttyp` (
  `anschriftTypID` varchar(10) NOT NULL,
  `anschriftTypKurzform` varchar(255) NOT NULL,
  `anschriftTypAnzeigeform` mediumtext NOT NULL,
  `anschriftTypLangform` mediumtext NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `aufeinenblick_settings`
--

CREATE TABLE `aufeinenblick_settings` (
  `aufeinenblickSettingsID` int(11) NOT NULL,
  `aufeinenblickUserID` int(11) NOT NULL,
  `aufeinenblickHourCanceltoday` int(11) NOT NULL,
  `aufeinenblickShowVplan` int(11) NOT NULL,
  `aufeinenblickShowCalendar` int(11) NOT NULL,
  `aufeinenblickShowStundenplan` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `ausleihe_ausleihe`
--

CREATE TABLE `ausleihe_ausleihe` (
  `ausleiheID` int(11) NOT NULL,
  `ausleiheObjektID` int(11) NOT NULL,
  `ausleiheObjektIndex` int(11) NOT NULL,
  `ausleiheDatum` date NOT NULL,
  `ausleiheAusleiherUserID` int(11) NOT NULL,
  `ausleiheStunde` int(11) NOT NULL,
  `ausleiheKlasse` varchar(10) NOT NULL,
  `ausleiheLehrer` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `ausleihe_objekte`
--

CREATE TABLE `ausleihe_objekte` (
  `objektID` int(11) NOT NULL,
  `objektName` mediumtext NOT NULL,
  `objektAnzahl` int(11) NOT NULL,
  `isActive` tinyint(1) NOT NULL,
  `sortOrder` int(11) NOT NULL,
  `sumItems` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Auszuleihende Gegenstände';

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `ausweise`
--

CREATE TABLE `ausweise` (
  `ausweisID` int(11) NOT NULL,
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
  `ausweisKommentar` longtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `bad_mail`
--

CREATE TABLE `bad_mail` (
  `badMailID` int(11) NOT NULL,
  `badMail` mediumtext NOT NULL,
  `badMailDone` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `beobachtungsbogen_boegen`
--

CREATE TABLE `beobachtungsbogen_boegen` (
  `beobachtungsbogenID` int(11) NOT NULL,
  `beobachtungsbogenName` varchar(200) NOT NULL,
  `beobachtungsbogenDatum` date NOT NULL,
  `beobachtungsbogenStartDate` date NOT NULL,
  `beobachtungsbogenDeadline` date NOT NULL,
  `beobachtungsbogenText` mediumtext NOT NULL,
  `beobachtungsbogenTitel` mediumtext NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `beobachtungsbogen_eintragungsfrist`
--

CREATE TABLE `beobachtungsbogen_eintragungsfrist` (
  `beobachtungsbogenID` int(11) NOT NULL,
  `userID` int(11) NOT NULL,
  `frist` date NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `beobachtungsbogen_fragen`
--

CREATE TABLE `beobachtungsbogen_fragen` (
  `frageID` int(11) NOT NULL,
  `beobachtungsbogenID` int(11) NOT NULL,
  `frageText` mediumtext NOT NULL,
  `frageTyp` enum('1','2') NOT NULL COMMENT '#1: 2 bis -2 ( :-) :-) bis :-( :-( ) #2: 2-0 ( :-) :-) bis :-| )',
  `frageZugriff` enum('LEHRER','KLASSENLEITUNG') NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `beobachtungsbogen_fragen_daten`
--

CREATE TABLE `beobachtungsbogen_fragen_daten` (
  `frageID` int(11) NOT NULL,
  `schuelerID` int(11) NOT NULL,
  `bewertung` int(11) NOT NULL,
  `lehrerKuerzel` varchar(100) NOT NULL,
  `fachName` varchar(100) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `beobachtungsbogen_klassenleitung`
--

CREATE TABLE `beobachtungsbogen_klassenleitung` (
  `beobachtungsbogenID` int(11) NOT NULL,
  `klassenName` varchar(100) NOT NULL,
  `klassenleitungUserID` int(11) NOT NULL,
  `klassenleitungTyp` int(11) NOT NULL DEFAULT 1
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `beobachtungsbogen_klasse_fach_lehrer`
--

CREATE TABLE `beobachtungsbogen_klasse_fach_lehrer` (
  `beobachtungsbogenID` int(11) NOT NULL,
  `klasseName` varchar(100) NOT NULL,
  `fachName` varchar(100) NOT NULL,
  `lehrerKuerzel` varchar(100) NOT NULL,
  `isOK` tinyint(1) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `beobachtungsbogen_schueler_namen`
--

CREATE TABLE `beobachtungsbogen_schueler_namen` (
  `beobachtungsbogenID` int(11) NOT NULL,
  `schuelerID` int(11) NOT NULL,
  `schuelerFirstName` varchar(255) NOT NULL,
  `schulerLastName` varchar(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `beurlaubungsantraege`
--

CREATE TABLE `beurlaubungsantraege` (
  `baID` int(11) NOT NULL,
  `baUserID` int(11) NOT NULL,
  `baSchuelerAsvID` varchar(200) NOT NULL,
  `baSchuelerText` longtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `beurlaubung_antrag`
--

CREATE TABLE `beurlaubung_antrag` (
  `antragID` int(11) NOT NULL,
  `antragUserID` int(11) NOT NULL,
  `antragSchuelerAsvID` varchar(100) NOT NULL,
  `antragStartDate` date NOT NULL,
  `antragEndDate` date NOT NULL,
  `antragStunden` mediumtext NOT NULL,
  `antragBegruendung` longtext NOT NULL,
  `antragGenehmigung` int(11) NOT NULL DEFAULT -1,
  `antragGenehmigungKommentar` longtext NOT NULL,
  `antragAbsenzID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `cron_execution`
--

CREATE TABLE `cron_execution` (
  `cronRunID` int(11) NOT NULL,
  `cronName` varchar(255) NOT NULL,
  `cronStartTime` int(11) NOT NULL,
  `cronEndTime` int(11) NOT NULL,
  `cronSuccess` tinyint(1) NOT NULL,
  `cronResult` longtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `database_database`
--

CREATE TABLE `database_database` (
  `databaseID` int(11) NOT NULL,
  `databaseName` varchar(255) NOT NULL,
  `databaseUserID` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `database_user2database`
--

CREATE TABLE `database_user2database` (
  `userID` int(11) NOT NULL,
  `databaseID` int(11) NOT NULL,
  `rights` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `database_users`
--

CREATE TABLE `database_users` (
  `userID` int(11) NOT NULL,
  `userPassword` varchar(255) NOT NULL,
  `userUserID` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `datenschutz_erklaerung`
--

CREATE TABLE `datenschutz_erklaerung` (
  `userID` int(11) NOT NULL,
  `userConfirmed` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `dokumente_dateien`
--

CREATE TABLE `dokumente_dateien` (
  `dateiID` int(11) NOT NULL,
  `gruppenID` int(11) NOT NULL,
  `dateiName` varchar(255) NOT NULL,
  `dateiAvailibleDate` date DEFAULT NULL,
  `dateiUploadTime` int(11) NOT NULL,
  `dateiDownloads` int(11) NOT NULL DEFAULT 0,
  `dateiKommentar` mediumtext NOT NULL,
  `dateiMimeType` varchar(200) NOT NULL,
  `dateiExtension` varchar(20) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `dokumente_gruppen`
--

CREATE TABLE `dokumente_gruppen` (
  `gruppenID` int(11) NOT NULL,
  `gruppenName` varchar(255) NOT NULL,
  `kategorieID` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `dokumente_kategorien`
--

CREATE TABLE `dokumente_kategorien` (
  `kategorieID` int(11) NOT NULL,
  `kategorieName` varchar(255) NOT NULL,
  `kategorieAccessSchueler` tinyint(1) NOT NULL,
  `kategorieAccessLehrer` tinyint(1) NOT NULL,
  `kategorieAccessEltern` tinyint(1) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `elternbriefe`
--

CREATE TABLE `elternbriefe` (
  `briefID` int(11) NOT NULL,
  `briefTitel` mediumtext NOT NULL,
  `briefDatum` date NOT NULL,
  `briefUploadUserID` int(11) NOT NULL,
  `briefUploadTime` int(11) NOT NULL,
  `orderNumber` int(11) NOT NULL DEFAULT 0
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `eltern_adressen`
--

CREATE TABLE `eltern_adressen` (
  `adresseID` int(11) NOT NULL,
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
  `adressePersonentyp` varchar(20) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `eltern_codes`
--

CREATE TABLE `eltern_codes` (
  `codeID` int(11) NOT NULL,
  `codeSchuelerAsvID` varchar(100) NOT NULL,
  `codeText` varchar(50) NOT NULL,
  `codeUserID` text NOT NULL,
  `codePrinted` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `eltern_email`
--

CREATE TABLE `eltern_email` (
  `elternEMail` varchar(255) NOT NULL,
  `elternSchuelerAsvID` varchar(20) NOT NULL,
  `elternUserID` int(11) NOT NULL DEFAULT 0,
  `elternAdresseID` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `eltern_register`
--

CREATE TABLE `eltern_register` (
  `registerID` int(11) NOT NULL,
  `registerCheckKey` varchar(200) NOT NULL,
  `registerSchuelerKey` varchar(200) NOT NULL,
  `registerTime` int(11) NOT NULL,
  `registerPassword` varchar(200) NOT NULL,
  `registerMail` varchar(255) NOT NULL,
  `firstName` text NOT NULL,
  `lastName` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `eltern_telefon`
--

CREATE TABLE `eltern_telefon` (
  `telefonNummer` varchar(255) NOT NULL,
  `schuelerAsvID` varchar(50) NOT NULL,
  `telefonTyp` enum('telefon','mobiltelefon','fax') NOT NULL DEFAULT 'telefon',
  `kontaktTyp` varchar(10) NOT NULL,
  `adresseID` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `eltern_to_schueler`
--

CREATE TABLE `eltern_to_schueler` (
  `elternUserID` int(11) NOT NULL,
  `schuelerUserID` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `email_addresses`
--

CREATE TABLE `email_addresses` (
  `userID` int(11) NOT NULL,
  `userEMail` mediumtext NOT NULL,
  `userConfirmCode` mediumtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `externe_kalender`
--

CREATE TABLE `externe_kalender` (
  `kalenderID` int(11) NOT NULL,
  `kalenderName` varchar(255) NOT NULL,
  `kalenderAccessSchueler` tinyint(1) NOT NULL DEFAULT 0,
  `kalenderAccessLehrer` int(11) NOT NULL DEFAULT 0,
  `kalenderAccessEltern` int(11) NOT NULL DEFAULT 0,
  `kalenderIcalFeed` mediumtext NOT NULL,
  `office365Username` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `externe_kalender_kategorien`
--

CREATE TABLE `externe_kalender_kategorien` (
  `kalenderID` int(11) NOT NULL,
  `kategorieName` varchar(255) NOT NULL,
  `kategorieText` text NOT NULL,
  `kategorieFarbe` varchar(7) NOT NULL DEFAULT '#000000',
  `kategorieIcon` varchar(200) NOT NULL DEFAULT 'fa fa-calendar'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `faecher`
--

CREATE TABLE `faecher` (
  `fachID` int(11) NOT NULL COMMENT 'Aus XML File',
  `fachKurzform` mediumtext NOT NULL,
  `fachLangform` mediumtext NOT NULL,
  `fachIstSelbstErstellt` tinyint(1) NOT NULL DEFAULT 0,
  `fachASDID` varchar(100) NOT NULL,
  `fachOrdnung` int(11) NOT NULL DEFAULT 10
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `ffbumfrage`
--

CREATE TABLE `ffbumfrage` (
  `codeID` int(11) NOT NULL,
  `codeText` varchar(100) NOT NULL,
  `codeUserID` int(11) NOT NULL,
  `codeType` enum('SCHUELER','ELTERN','','') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fremdlogin`
--

CREATE TABLE `fremdlogin` (
  `fremdloginID` int(11) NOT NULL,
  `userID` int(11) NOT NULL,
  `adminUserID` int(11) NOT NULL,
  `loginMessage` longtext NOT NULL,
  `loginTime` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `ganztags_gruppen`
--

CREATE TABLE `ganztags_gruppen` (
  `id` int(11) UNSIGNED NOT NULL,
  `sortOrder` int(11) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `ganztags_schueler`
--

CREATE TABLE `ganztags_schueler` (
  `asvid` varchar(200) NOT NULL DEFAULT '',
  `info` varchar(255) DEFAULT NULL,
  `gruppe` int(11) DEFAULT NULL,
  `tag_mo` tinyint(1) DEFAULT NULL,
  `tag_di` tinyint(1) DEFAULT NULL,
  `tag_mi` tinyint(1) DEFAULT NULL,
  `tag_do` tinyint(1) DEFAULT NULL,
  `tag_fr` tinyint(1) DEFAULT NULL,
  `tag_sa` tinyint(1) DEFAULT NULL,
  `tag_so` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `icsfeeds`
--

CREATE TABLE `icsfeeds` (
  `feedID` int(11) NOT NULL,
  `feedType` enum('KL','AK','EK') NOT NULL,
  `feedData` mediumtext NOT NULL,
  `feedKey` varchar(255) NOT NULL,
  `feedKey2` varchar(255) NOT NULL,
  `feedUserID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `image_uploads`
--

CREATE TABLE `image_uploads` (
  `uploadID` int(11) NOT NULL,
  `uploadTime` int(11) NOT NULL,
  `uploadUserName` varchar(20) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `initialpasswords`
--

CREATE TABLE `initialpasswords` (
  `initialPasswordID` int(11) NOT NULL,
  `initialPasswordUserID` int(11) NOT NULL,
  `initialPassword` varchar(200) NOT NULL,
  `passwordPrinted` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `kalender_andere`
--

CREATE TABLE `kalender_andere` (
  `eintragID` int(11) NOT NULL,
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
  `eintragKategorie` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `kalender_extern`
--

CREATE TABLE `kalender_extern` (
  `eintragID` int(11) NOT NULL,
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
  `eintragKategorieName` varchar(200) DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `kalender_ferien`
--

CREATE TABLE `kalender_ferien` (
  `ferienID` int(11) NOT NULL,
  `ferienName` mediumtext NOT NULL,
  `ferienStart` date NOT NULL,
  `ferienEnde` date NOT NULL,
  `ferienSchuljahr` varchar(7) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Alle Ferien in den nächsten Jahren';

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `kalender_klassentermin`
--

CREATE TABLE `kalender_klassentermin` (
  `eintragID` int(11) NOT NULL,
  `eintragTitel` text NOT NULL,
  `eintragDatumStart` date NOT NULL,
  `eintragDatumEnde` date NOT NULL,
  `eintragKlassen` text NOT NULL,
  `eintragBetrifft` text NOT NULL,
  `eintragLehrer` text NOT NULL,
  `eintragStunden` text NOT NULL,
  `eintragEintragZeitpunkt` int(11) NOT NULL,
  `eintragOrt` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `kalender_lnw`
--

CREATE TABLE `kalender_lnw` (
  `eintragID` int(11) NOT NULL,
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
  `eintragAlwaysShow` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `klassenkalender`
--

CREATE TABLE `klassenkalender` (
  `eintragID` int(11) NOT NULL,
  `eintragTitel` mediumtext NOT NULL,
  `eintragArt` enum('SCHULAUFGABE','KURZARBEIT','STEGREIFAUFGABE','KLASSENTERMIN','PLNW','MODUSTEST','NACHHOLSCHULAUFGABE') NOT NULL,
  `eintragDatumStart` date NOT NULL,
  `eintragDatumEnde` date NOT NULL,
  `eintragKlasse` varchar(200) NOT NULL,
  `eintragBetrifft` varchar(255) NOT NULL,
  `eintragLehrer` varchar(20) NOT NULL,
  `eintragFach` varchar(100) NOT NULL,
  `eintragEintragZeitpunkt` int(11) NOT NULL DEFAULT 0,
  `eintragStunden` varchar(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `klassenleitung`
--

CREATE TABLE `klassenleitung` (
  `klasseName` varchar(200) NOT NULL,
  `lehrerID` int(11) NOT NULL,
  `klassenleitungArt` int(11) NOT NULL DEFAULT 1
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `klassentagebuch_fehl`
--

CREATE TABLE `klassentagebuch_fehl` (
  `fehlID` int(11) NOT NULL,
  `fehlDatum` date NOT NULL,
  `fehlStunde` int(11) NOT NULL,
  `fehlKlasse` varchar(100) NOT NULL,
  `fehlFach` varchar(100) NOT NULL,
  `fehlLehrer` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `klassentagebuch_klassen`
--

CREATE TABLE `klassentagebuch_klassen` (
  `entryID` int(11) NOT NULL,
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
  `entryFilesPublic` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `klassentagebuch_lehrer`
--

CREATE TABLE `klassentagebuch_lehrer` (
  `entryID` int(11) NOT NULL,
  `entryDate` date NOT NULL,
  `entryGrade` varchar(200) NOT NULL,
  `entryFach` varchar(200) NOT NULL,
  `entryStunde` int(11) NOT NULL,
  `entryStoff` int(11) NOT NULL,
  `entryTeacher` varchar(200) NOT NULL,
  `entryKommentar` text NOT NULL,
  `entryEntfall` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `klassentagebuch_pdf`
--

CREATE TABLE `klassentagebuch_pdf` (
  `pdfKlasse` varchar(100) NOT NULL,
  `pdfJahr` int(11) NOT NULL,
  `pdfMonat` int(11) NOT NULL,
  `pdfUploadID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `kondolenzbuch`
--

CREATE TABLE `kondolenzbuch` (
  `eintragID` int(11) NOT NULL,
  `eintragName` mediumtext NOT NULL,
  `eintragText` longtext NOT NULL,
  `eintragTime` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `laufzettel`
--

CREATE TABLE `laufzettel` (
  `laufzettelID` int(11) NOT NULL,
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
  `laufzettelZustimmungSchulleitungPerson` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `laufzettel_stunden`
--

CREATE TABLE `laufzettel_stunden` (
  `laufzettelStundeID` int(11) NOT NULL,
  `laufzettelID` int(11) NOT NULL,
  `laufzettelKlasse` varchar(50) NOT NULL,
  `laufzettelLehrer` varchar(50) NOT NULL,
  `laufzettelFach` varchar(50) NOT NULL,
  `laufzettelStunde` int(11) NOT NULL,
  `laufzettelZustimmung` tinyint(1) NOT NULL DEFAULT 0,
  `laufzettelZustimmungZeit` int(11) DEFAULT NULL,
  `laufzettelZustimmungKommentar` mediumtext DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `lehrer`
--

CREATE TABLE `lehrer` (
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
  `lehrerUserID` int(11) NOT NULL DEFAULT 0
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `lerntutoren`
--

CREATE TABLE `lerntutoren` (
  `lerntutorID` int(11) NOT NULL,
  `lerntutorSchuelerAsvID` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `lerntutoren_slots`
--

CREATE TABLE `lerntutoren_slots` (
  `slotID` int(11) NOT NULL,
  `slotLerntutorID` int(11) NOT NULL,
  `slotFach` varchar(255) NOT NULL,
  `slotJahrgangsstufe` varchar(255) NOT NULL,
  `slotSchuelerBelegt` varchar(255) DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `mail_change_requests`
--

CREATE TABLE `mail_change_requests` (
  `changeRequestID` int(11) NOT NULL,
  `changeRequestUserID` int(11) NOT NULL,
  `changeRequestTime` int(11) NOT NULL,
  `changeRequestSecret` text NOT NULL,
  `changeRequestNewMail` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `mail_send`
--

CREATE TABLE `mail_send` (
  `mailID` int(11) NOT NULL,
  `mailRecipient` mediumtext NOT NULL,
  `mailSubject` mediumtext NOT NULL,
  `mailText` mediumtext NOT NULL,
  `mailSent` int(11) NOT NULL DEFAULT 0,
  `mailCrawler` int(11) NOT NULL DEFAULT 1,
  `replyTo` varchar(255) DEFAULT '',
  `mailCC` varchar(255) DEFAULT '',
  `mailLesebestaetigung` tinyint(1) NOT NULL DEFAULT 0,
  `mailIsHTML` tinyint(1) NOT NULL DEFAULT 0,
  `mailAttachments` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `math_captcha`
--

CREATE TABLE `math_captcha` (
  `captchaID` int(11) NOT NULL,
  `captchaQuestion` varchar(100) NOT NULL,
  `captchaSolution` int(11) NOT NULL,
  `captchaSecret` varchar(5) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `mebis_accounts`
--

CREATE TABLE `mebis_accounts` (
  `mebisAccountID` int(11) NOT NULL,
  `mebisAccountVorname` varchar(200) NOT NULL,
  `mebisAccountNachname` varchar(200) NOT NULL,
  `mebisAccountBenutzername` varchar(200) NOT NULL,
  `mebisAccountPasswort` varchar(200) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `messages_attachment`
--

CREATE TABLE `messages_attachment` (
  `attachmentID` int(11) NOT NULL,
  `attachmentFileUploadID` int(11) NOT NULL,
  `attachmentAccessCode` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `messages_folders`
--

CREATE TABLE `messages_folders` (
  `folderID` int(11) NOT NULL,
  `folderName` text NOT NULL,
  `folderUserID` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `messages_messages`
--

CREATE TABLE `messages_messages` (
  `messageID` int(11) NOT NULL,
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
  `messageIsForwardFrom` int(11) NOT NULL DEFAULT 0
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `messages_questions`
--

CREATE TABLE `messages_questions` (
  `questionID` int(11) NOT NULL,
  `questionText` mediumtext NOT NULL,
  `questionType` enum('BOOLEAN','TEXT','NUMBER','FILE') NOT NULL DEFAULT 'TEXT',
  `questionUserID` int(11) NOT NULL,
  `questionSecret` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `messages_questions_answers`
--

CREATE TABLE `messages_questions_answers` (
  `answerID` int(11) NOT NULL,
  `answerQuestionID` int(11) NOT NULL,
  `answerMessageID` int(11) NOT NULL,
  `answerData` longtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `modul_admin_notes`
--

CREATE TABLE `modul_admin_notes` (
  `noteID` int(11) NOT NULL,
  `noteModuleName` varchar(255) NOT NULL,
  `noteText` text NOT NULL,
  `noteUserID` int(11) NOT NULL,
  `noteTime` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `nextcloud_users`
--

CREATE TABLE `nextcloud_users` (
  `userID` int(11) NOT NULL COMMENT 'Same UserID as in SI',
  `nextcloudUsername` text NOT NULL,
  `userPasswordSet` int(11) NOT NULL DEFAULT 0,
  `userQuota` varchar(200) NOT NULL,
  `userGroups` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `noten_arbeiten`
--

CREATE TABLE `noten_arbeiten` (
  `arbeitID` int(11) NOT NULL,
  `arbeitBereich` enum('SA','KA','EX','MDL') NOT NULL,
  `arbeitName` mediumtext NOT NULL,
  `arbeitLehrerKuerzel` varchar(10) NOT NULL,
  `arbeitIsMuendlich` tinyint(1) NOT NULL,
  `arbeitGewicht` decimal(4,2) NOT NULL DEFAULT 1.00,
  `arbeitFachKurzform` varchar(200) NOT NULL COMMENT 'Kurzform, nicht ASD ID, da eigene Unterrichte erstellt sein kÃ¶nnten.',
  `arbeitDatum` date DEFAULT NULL,
  `arbeitUnterrichtName` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `noten_bemerkung_textvorlagen`
--

CREATE TABLE `noten_bemerkung_textvorlagen` (
  `bemerkungID` int(11) NOT NULL,
  `bemerkungGruppeID` int(11) NOT NULL,
  `bemerkungNote` int(11) NOT NULL DEFAULT 0,
  `bemerkungTextWeiblich` longtext NOT NULL,
  `bemerkungTextMaennlich` longtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `noten_bemerkung_textvorlagen_gruppen`
--

CREATE TABLE `noten_bemerkung_textvorlagen_gruppen` (
  `gruppeID` int(11) NOT NULL,
  `gruppeName` mediumtext NOT NULL,
  `koppelMVNote` enum('M','V') CHARACTER SET latin1 DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `noten_fach_einstellungen`
--

CREATE TABLE `noten_fach_einstellungen` (
  `fachKurzform` varchar(100) NOT NULL,
  `fachIsVorrueckungsfach` tinyint(1) NOT NULL,
  `fachOrder` int(11) NOT NULL,
  `fachNoteZusammenMit` mediumtext NOT NULL COMMENT 'Fachkurzformen der FÃ¤cher, die mit diesem Fach zusammen verrechnet werden. Aktuelles Fach wird als Hauptfach angezeigt. Getrennt durch Komma.'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `noten_gewichtung`
--

CREATE TABLE `noten_gewichtung` (
  `fachKuerzel` varchar(100) NOT NULL,
  `fachJahrgangsstufe` int(11) NOT NULL,
  `fachGewichtKlein` int(11) NOT NULL DEFAULT 1,
  `fachGewichtGross` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `noten_mv`
--

CREATE TABLE `noten_mv` (
  `mvFachKurzform` varchar(200) NOT NULL,
  `mvUnterrichtName` varchar(200) NOT NULL,
  `mNote` int(11) NOT NULL,
  `vNote` int(11) NOT NULL,
  `schuelerAsvID` varchar(100) NOT NULL,
  `zeugnisID` int(11) NOT NULL,
  `noteKommentar` mediumtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `noten_noten`
--

CREATE TABLE `noten_noten` (
  `noteSchuelerAsvID` varchar(20) NOT NULL,
  `noteWert` int(11) NOT NULL,
  `noteTendenz` int(11) NOT NULL,
  `noteArbeitID` int(11) NOT NULL,
  `noteDatum` date DEFAULT NULL,
  `noteKommentar` longtext NOT NULL,
  `noteIsNachtermin` tinyint(1) NOT NULL DEFAULT 0,
  `noteNurWennBesser` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `noten_verrechnung`
--

CREATE TABLE `noten_verrechnung` (
  `verrechnungID` int(11) NOT NULL,
  `verrechnungFach` varchar(255) NOT NULL,
  `verrechnungUnterricht1` varchar(255) NOT NULL,
  `verrechnungUnterricht2` varchar(100) NOT NULL,
  `verrechnungGewicht1` int(11) NOT NULL,
  `verrechnungGewicht2` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `noten_wahlfach_faecher`
--

CREATE TABLE `noten_wahlfach_faecher` (
  `wahlfachID` int(11) NOT NULL,
  `zeugnisID` int(11) NOT NULL,
  `fachKurzform` varchar(100) NOT NULL,
  `fachUnterrichtName` varchar(100) NOT NULL,
  `wahlfachBezeichnung` mediumtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `noten_wahlfach_noten`
--

CREATE TABLE `noten_wahlfach_noten` (
  `wahlfachID` int(11) NOT NULL,
  `schuelerAsvID` varchar(100) NOT NULL,
  `wahlfachNote` int(11) NOT NULL DEFAULT 4
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `noten_zeugnisse`
--

CREATE TABLE `noten_zeugnisse` (
  `zeugnisID` int(11) NOT NULL,
  `zeugnisArt` enum('ZZ','JZ','NB','ABZ','SEMZ','ABIZ') NOT NULL,
  `zeugnisName` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `noten_zeugnisse_klassen`
--

CREATE TABLE `noten_zeugnisse_klassen` (
  `zeugnisID` int(11) NOT NULL,
  `zeugnisKlasse` varchar(20) NOT NULL,
  `zeugnisDatum` date NOT NULL,
  `zeugnisNotenschluss` date NOT NULL,
  `zeugnisUnterschriftKlassenleitungAsvID` varchar(20) NOT NULL,
  `zeugnisUnterschriftSchulleitungAsvID` varchar(20) NOT NULL,
  `zeugnisUnterschriftKlassenleitungAsvIDGezeichnet` tinyint(1) NOT NULL DEFAULT 0,
  `zeugnisUnterschriftSchulleitungAsvIDGezeichnet` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `noten_zeugnisse_noten`
--

CREATE TABLE `noten_zeugnisse_noten` (
  `noteSchuelerAsvID` varchar(100) NOT NULL,
  `noteZeugnisID` int(11) NOT NULL,
  `noteFachKurzform` varchar(100) NOT NULL,
  `noteWert` int(11) NOT NULL,
  `noteIsPaed` tinyint(1) NOT NULL DEFAULT 0,
  `notePaedBegruendung` mediumtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `noten_zeugnis_bemerkung`
--

CREATE TABLE `noten_zeugnis_bemerkung` (
  `bemerkungSchuelerAsvID` varchar(100) NOT NULL,
  `bemerkungZeugnisID` int(11) NOT NULL,
  `bemerkungText1` longtext NOT NULL,
  `bemerkungText2` longtext NOT NULL,
  `klassenzielErreicht` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `noten_zeugnis_exemplar`
--

CREATE TABLE `noten_zeugnis_exemplar` (
  `zeugnisID` int(11) NOT NULL,
  `schuelerAsvID` varchar(100) NOT NULL,
  `uploadID` int(11) NOT NULL,
  `createdTime` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `office365_accounts`
--

CREATE TABLE `office365_accounts` (
  `accountAsvID` varchar(200) NOT NULL,
  `accountUsername` varchar(2000) NOT NULL,
  `accountUserID` mediumtext NOT NULL,
  `accountInitialPassword` varchar(200) NOT NULL,
  `accountDetailsSet` tinyint(1) NOT NULL DEFAULT 0,
  `accountLicenseSet` tinyint(1) NOT NULL DEFAULT 0,
  `accountCreated` int(11) NOT NULL,
  `accountIsTeacher` tinyint(1) NOT NULL DEFAULT 0,
  `accountIsPupil` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `projekt_lehrer2grade`
--

CREATE TABLE `projekt_lehrer2grade` (
  `lehrerUserID` int(11) NOT NULL,
  `gradeName` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `projekt_projekte`
--

CREATE TABLE `projekt_projekte` (
  `userID` varchar(100) NOT NULL,
  `projektName` mediumtext NOT NULL,
  `projektErfolg` varchar(255) NOT NULL,
  `projektFach1` varchar(100) NOT NULL,
  `projektFach2` varchar(100) NOT NULL,
  `projektLehrer1` varchar(100) NOT NULL,
  `projektLehrer2` varchar(100) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `pupil_grade`
--

CREATE TABLE `pupil_grade` (
  `userID` int(11) NOT NULL,
  `userGrade` varchar(200) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `remote_usersync`
--

CREATE TABLE `remote_usersync` (
  `syncID` int(11) NOT NULL,
  `syncName` varchar(200) NOT NULL,
  `syncLoginDomain` varchar(200) NOT NULL,
  `syncSecret` varchar(200) NOT NULL,
  `syncURL` mediumtext NOT NULL,
  `syncIsActive` tinyint(1) NOT NULL DEFAULT 1,
  `syncLastSync` int(11) NOT NULL DEFAULT 0,
  `syncSuccessfull` tinyint(1) NOT NULL DEFAULT 0,
  `syncLastSyncResult` longtext NOT NULL,
  `syncDirType` enum('ACTIVEDIRECTORY','EDIRECTORY','','') NOT NULL DEFAULT 'ACTIVEDIRECTORY'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `resetpassword`
--

CREATE TABLE `resetpassword` (
  `resetID` int(11) NOT NULL,
  `resetUserID` int(11) NOT NULL,
  `resetNewPasswordHash` varchar(200) NOT NULL,
  `resetCode` varchar(200) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `respizienz`
--

CREATE TABLE `respizienz` (
  `respizienzID` int(11) NOT NULL,
  `respizienzFile` mediumtext NOT NULL,
  `respizienzFSLFile` mediumtext NOT NULL,
  `respizientSLFile` mediumtext NOT NULL,
  `respizienzIsAnalog` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `rsu_persons`
--

CREATE TABLE `rsu_persons` (
  `personID` int(11) NOT NULL,
  `personNachname` mediumtext NOT NULL,
  `personVorname` mediumtext NOT NULL,
  `personKuerzel` mediumtext NOT NULL,
  `personFaecher` mediumtext NOT NULL,
  `personFunktion` mediumtext NOT NULL,
  `personSectionID` int(11) NOT NULL,
  `personhasPicture` int(11) NOT NULL,
  `personIsActive` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `rsu_print`
--

CREATE TABLE `rsu_print` (
  `printPerRow` int(11) NOT NULL,
  `printHeading` mediumtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `rsu_sections`
--

CREATE TABLE `rsu_sections` (
  `sectionID` int(11) NOT NULL,
  `sectionName` mediumtext NOT NULL,
  `sectionOrder` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `schaukasten_bildschirme`
--

CREATE TABLE `schaukasten_bildschirme` (
  `schaukastenID` int(11) NOT NULL,
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
  `schaukastenHasPPT` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `schaukasten_inhalt`
--

CREATE TABLE `schaukasten_inhalt` (
  `schaukastenID` int(11) NOT NULL,
  `schaukastenPosition` int(11) NOT NULL,
  `schaukastenContent` varchar(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `schaukasten_powerpoint`
--

CREATE TABLE `schaukasten_powerpoint` (
  `powerpointID` int(11) NOT NULL,
  `uploadID` int(11) NOT NULL,
  `lastUpdate` int(11) NOT NULL,
  `powerpointName` mediumtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `schaukasten_website`
--

CREATE TABLE `schaukasten_website` (
  `websiteID` int(11) NOT NULL,
  `websiteURL` mediumtext NOT NULL,
  `websiteName` mediumtext NOT NULL,
  `websiteLastUpdate` int(11) NOT NULL,
  `websiteRefreshSeconds` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `schueler`
--

CREATE TABLE `schueler` (
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
  `schuelerGanztagBetreuung` int(11) NOT NULL DEFAULT 0
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `schuelerinfo_dokumente`
--

CREATE TABLE `schuelerinfo_dokumente` (
  `dokumentID` int(11) NOT NULL,
  `dokumentSchuelerAsvID` varchar(200) NOT NULL,
  `dokumentName` varchar(255) NOT NULL,
  `dokumentKommentar` mediumtext NOT NULL,
  `dokumentUploadID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `schueler_briefe`
--

CREATE TABLE `schueler_briefe` (
  `briefID` int(11) NOT NULL,
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
  `briefSaveLonger` int(11) NOT NULL DEFAULT 0 COMMENT 'Bei 0 dauerhafte Speicherung, bei Wert letzte Änderung'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `schueler_fremdsprache`
--

CREATE TABLE `schueler_fremdsprache` (
  `schuelerAsvID` varchar(100) NOT NULL,
  `spracheSortierung` int(11) NOT NULL,
  `spracheAbJahrgangsstufe` varchar(10) NOT NULL,
  `spracheFach` mediumtext NOT NULL,
  `spracheFeststellungspruefung` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `schueler_nachteilsausgleich`
--

CREATE TABLE `schueler_nachteilsausgleich` (
  `schuelerAsvID` varchar(100) NOT NULL,
  `artStoerung` enum('rs','lrs','ls') NOT NULL,
  `arbeitszeitverlaengerung` varchar(255) NOT NULL,
  `notenschutz` tinyint(1) NOT NULL,
  `kommentar` mediumtext NOT NULL,
  `gueltigBis` date DEFAULT NULL,
  `gewichtung` enum('11','12','21') CHARACTER SET latin1 DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `schulbuch_ausleihe`
--

CREATE TABLE `schulbuch_ausleihe` (
  `ausleiheID` int(11) NOT NULL,
  `ausleiheExemplarID` int(11) NOT NULL,
  `ausleiheStartDatum` date NOT NULL,
  `ausleiheEndDatum` date DEFAULT NULL,
  `ausleiherNameUndKlasse` mediumtext NOT NULL,
  `ausleiherSchuelerAsvID` varchar(100) NOT NULL,
  `ausleiherLehrerAsvID` varchar(100) NOT NULL,
  `ausleiherUserID` int(11) NOT NULL,
  `rueckgeberUserID` int(11) NOT NULL,
  `ausleiheKommentar` longtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `schulbuch_buecher`
--

CREATE TABLE `schulbuch_buecher` (
  `buchID` int(11) NOT NULL,
  `buchTitel` mediumtext NOT NULL,
  `buchVerlag` mediumtext NOT NULL,
  `buchISBN` varchar(20) NOT NULL,
  `buchPreis` int(11) NOT NULL COMMENT 'preis in Cent',
  `buchFach` varchar(200) NOT NULL,
  `buchKlasse` int(11) NOT NULL,
  `buchErfasserUserID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `schulbuch_exemplare`
--

CREATE TABLE `schulbuch_exemplare` (
  `exemplarID` int(11) NOT NULL,
  `exemplarBuchID` int(11) NOT NULL,
  `exemplarBarcode` varchar(200) NOT NULL,
  `exemplarZustand` int(11) NOT NULL DEFAULT 0,
  `exemplarAnschaffungsjahr` varchar(5) NOT NULL,
  `exemplarIsBankbuch` tinyint(1) NOT NULL DEFAULT 0,
  `exemplarLagerort` mediumtext NOT NULL,
  `exemplarErfasserUserID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `schulen`
--

CREATE TABLE `schulen` (
  `schuleID` int(11) NOT NULL,
  `schuleNummer` varchar(255) NOT NULL,
  `schuleArt` varchar(255) NOT NULL,
  `schuleName` mediumtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `sessions`
--

CREATE TABLE `sessions` (
  `sessionID` varchar(255) NOT NULL,
  `sessionUserID` int(11) NOT NULL,
  `sessionType` enum('NORMAL','SAVED') NOT NULL,
  `sessionIP` varchar(100) NOT NULL,
  `sessionLastActivity` int(11) NOT NULL,
  `sessionBrowser` varchar(255) NOT NULL,
  `sessionDevice` enum('ANDROIDAPP','IOSAPP','WINDOWSPHONEAPP','NORMAL','SINGLESIGNON') NOT NULL DEFAULT 'NORMAL',
  `sessionIsDebug` tinyint(1) NOT NULL DEFAULT 0,
  `session2FactorActive` int(11) NOT NULL DEFAULT 0,
  `sessionStore` longtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `settings`
--

CREATE TABLE `settings` (
  `settingName` varchar(100) NOT NULL,
  `settingValue` mediumtext NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `site_activation`
--

CREATE TABLE `site_activation` (
  `siteName` varchar(200) NOT NULL,
  `siteIsActive` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `sprechtag`
--

CREATE TABLE `sprechtag` (
  `sprechtagID` int(11) NOT NULL,
  `sprechtagDate` date NOT NULL,
  `sprechtagName` mediumtext NOT NULL,
  `sprechtagBuchbarBis` date NOT NULL,
  `sprechtagIsActive` tinyint(1) NOT NULL DEFAULT 0,
  `sprechtagBuchbarAb` int(11) NOT NULL COMMENT 'Timestamp, ab dem der Sprechtag buchbar ist',
  `sprechtagKlassen` longtext NOT NULL COMMENT 'Liste der Klassen. (leer: alle)',
  `sprechtagIsVorlage` tinyint(1) NOT NULL DEFAULT 0,
  `sprechtagPercentPerTeacherOnlineBookable` int(11) NOT NULL DEFAULT 100,
  `sprechtagBeginTime` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `sprechtag_buchungen`
--

CREATE TABLE `sprechtag_buchungen` (
  `buchungID` int(11) NOT NULL,
  `lehrerKuerzel` varchar(100) NOT NULL,
  `sprechtagID` int(11) NOT NULL,
  `slotID` int(11) NOT NULL,
  `isBuchbar` int(11) NOT NULL,
  `schuelerAsvID` varchar(100) NOT NULL,
  `elternUserID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `sprechtag_raeume`
--

CREATE TABLE `sprechtag_raeume` (
  `sprechtagID` int(11) NOT NULL,
  `lehrerKuerzel` varchar(200) NOT NULL,
  `raumName` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `sprechtag_slots`
--

CREATE TABLE `sprechtag_slots` (
  `slotID` int(11) NOT NULL,
  `sprechtagID` int(11) NOT NULL,
  `slotStart` int(11) NOT NULL,
  `slotEnde` int(11) NOT NULL,
  `slotIsPause` tinyint(1) NOT NULL DEFAULT 0,
  `slotIsOnlineBuchbar` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `stundenplan_aufsichten`
--

CREATE TABLE `stundenplan_aufsichten` (
  `aufsichtID` int(11) NOT NULL,
  `stundenplanID` int(11) NOT NULL,
  `aufsichtBereich` mediumtext NOT NULL,
  `aufsichtVorStunde` int(11) NOT NULL,
  `aufsichtTag` int(11) NOT NULL,
  `aufsichtLehrerKuerzel` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `stundenplan_plaene`
--

CREATE TABLE `stundenplan_plaene` (
  `stundenplanID` int(11) NOT NULL,
  `stundenplanAb` date DEFAULT NULL,
  `stundenplanBis` date DEFAULT NULL,
  `stundenplanUploadUserID` int(11) NOT NULL,
  `stundenplanName` varchar(255) NOT NULL,
  `stundenplanIsDeleted` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `stundenplan_stunden`
--

CREATE TABLE `stundenplan_stunden` (
  `stundeID` int(11) NOT NULL,
  `stundenplanID` int(11) NOT NULL,
  `stundeKlasse` varchar(20) NOT NULL,
  `stundeLehrer` varchar(20) CHARACTER SET utf8 COLLATE utf8_german2_ci NOT NULL,
  `stundeFach` varchar(20) NOT NULL,
  `stundeRaum` varchar(20) NOT NULL,
  `stundeTag` int(11) NOT NULL,
  `stundeStunde` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `templates`
--

CREATE TABLE `templates` (
  `templateName` varchar(200) NOT NULL,
  `templateCompiledContents` longtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `trenndaten`
--

CREATE TABLE `trenndaten` (
  `trennWort` varchar(255) NOT NULL,
  `trennWortGetrennt` varchar(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `two_factor_trusted_devices`
--

CREATE TABLE `two_factor_trusted_devices` (
  `deviceID` int(11) NOT NULL,
  `deviceCookieData` mediumtext NOT NULL,
  `deviceUserID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `unknown_mails`
--

CREATE TABLE `unknown_mails` (
  `mailID` int(11) NOT NULL,
  `mailSubject` mediumtext NOT NULL,
  `mailText` longtext NOT NULL,
  `mailSender` mediumtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `unterricht`
--

CREATE TABLE `unterricht` (
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
  `unterrichtKoppelIsPseudo` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `unterricht_besuch`
--

CREATE TABLE `unterricht_besuch` (
  `unterrichtID` int(11) NOT NULL,
  `schuelerAsvID` varchar(200) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `uploads`
--

CREATE TABLE `uploads` (
  `uploadID` int(11) NOT NULL,
  `uploadFileName` mediumtext NOT NULL,
  `uploadFileExtension` varchar(50) NOT NULL,
  `uploadFileMimeType` varchar(200) NOT NULL,
  `uploadTime` int(11) NOT NULL,
  `uploaderUserID` int(11) NOT NULL,
  `fileAccessCode` varchar(222) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `users`
--

CREATE TABLE `users` (
  `userID` int(11) NOT NULL,
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
  `userAutoresponseText` longtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Enthält alle Benutzer, die SchuleIntern benutzen können';

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `users_groups`
--

CREATE TABLE `users_groups` (
  `userID` int(11) NOT NULL,
  `groupName` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `users_groups_own`
--

CREATE TABLE `users_groups_own` (
  `groupName` varchar(255) NOT NULL,
  `groupIsMessageRecipient` tinyint(1) NOT NULL,
  `groupContactTeacher` tinyint(1) NOT NULL,
  `groupContactPupil` tinyint(1) NOT NULL,
  `groupContactParents` int(11) NOT NULL,
  `groupNextCloudUserID` int(11) NOT NULL DEFAULT 0,
  `groupHasNextcloudShare` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `user_images`
--

CREATE TABLE `user_images` (
  `userImageID` int(11) NOT NULL,
  `userImageUserName` varchar(255) NOT NULL,
  `userImageViewCode` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `user_settings`
--

CREATE TABLE `user_settings` (
  `userID` int(11) NOT NULL,
  `skinColor` enum('blue','black','purple','yellow','red','green') NOT NULL DEFAULT 'green',
  `startPage` enum('aufeinenblick','vplan','stundenplan') NOT NULL DEFAULT 'aufeinenblick'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `vplan`
--

CREATE TABLE `vplan` (
  `vplanName` varchar(20) NOT NULL,
  `vplanDate` text NOT NULL,
  `vplanContent` longtext NOT NULL,
  `vplanUpdate` varchar(200) NOT NULL,
  `vplanInfo` mediumtext NOT NULL,
  `vplanContentUncensored` longtext NOT NULL,
  `schaukastenViewKey` text NOT NULL,
  `vplanUpdateTime` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `vplan_data`
--

CREATE TABLE `vplan_data` (
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

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `wlan_schueler`
--

CREATE TABLE `wlan_schueler` (
  `schuelerID` int(11) NOT NULL,
  `lehrerID` int(11) NOT NULL,
  `wlanEndTime` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `wlan_ticket`
--

CREATE TABLE `wlan_ticket` (
  `ticketID` int(11) NOT NULL,
  `ticketType` enum('GAST','SCHUELER') NOT NULL,
  `ticketText` mediumtext NOT NULL,
  `ticketAssignedTo` int(11) NOT NULL DEFAULT 0,
  `ticketValidMinutes` int(11) NOT NULL,
  `ticketAssignedDate` date NOT NULL,
  `ticketAssignedBy` int(11) NOT NULL,
  `ticketName` mediumtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `absenzen_absenzen`
--
ALTER TABLE `absenzen_absenzen`
  ADD PRIMARY KEY (`absenzID`) USING BTREE;

--
-- Indizes für die Tabelle `absenzen_absenzen_stunden`
--
ALTER TABLE `absenzen_absenzen_stunden`
  ADD PRIMARY KEY (`absenzID`,`absenzStunde`) USING BTREE;

--
-- Indizes für die Tabelle `absenzen_attestpflicht`
--
ALTER TABLE `absenzen_attestpflicht`
  ADD PRIMARY KEY (`attestpflichtID`) USING BTREE;

--
-- Indizes für die Tabelle `absenzen_befreiungen`
--
ALTER TABLE `absenzen_befreiungen`
  ADD PRIMARY KEY (`befreiungID`) USING BTREE;

--
-- Indizes für die Tabelle `absenzen_beurlaubungen`
--
ALTER TABLE `absenzen_beurlaubungen`
  ADD PRIMARY KEY (`beurlaubungID`) USING BTREE;

--
-- Indizes für die Tabelle `absenzen_beurlaubung_antrag`
--
ALTER TABLE `absenzen_beurlaubung_antrag`
  ADD PRIMARY KEY (`antragID`) USING BTREE;

--
-- Indizes für die Tabelle `absenzen_comments`
--
ALTER TABLE `absenzen_comments`
  ADD PRIMARY KEY (`schuelerAsvID`) USING BTREE;

--
-- Indizes für die Tabelle `absenzen_krankmeldungen`
--
ALTER TABLE `absenzen_krankmeldungen`
  ADD PRIMARY KEY (`krankmeldungID`) USING BTREE;

--
-- Indizes für die Tabelle `absenzen_meldung`
--
ALTER TABLE `absenzen_meldung`
  ADD PRIMARY KEY (`meldungDatum`,`meldungKlasse`) USING BTREE;

--
-- Indizes für die Tabelle `absenzen_merker`
--
ALTER TABLE `absenzen_merker`
  ADD PRIMARY KEY (`merkerID`) USING BTREE;

--
-- Indizes für die Tabelle `absenzen_sanizimmer`
--
ALTER TABLE `absenzen_sanizimmer`
  ADD PRIMARY KEY (`sanizimmerID`) USING BTREE;

--
-- Indizes für die Tabelle `absenzen_verspaetungen`
--
ALTER TABLE `absenzen_verspaetungen`
  ADD PRIMARY KEY (`verspaetungID`) USING BTREE;

--
-- Indizes für die Tabelle `amtsbezeichnungen`
--
ALTER TABLE `amtsbezeichnungen`
  ADD PRIMARY KEY (`amtsbezeichnungID`) USING BTREE;

--
-- Indizes für die Tabelle `andere_kalender`
--
ALTER TABLE `andere_kalender`
  ADD PRIMARY KEY (`kalenderID`) USING BTREE;

--
-- Indizes für die Tabelle `andere_kalender_kategorie`
--
ALTER TABLE `andere_kalender_kategorie`
  ADD PRIMARY KEY (`kategorieID`) USING BTREE;

--
-- Indizes für die Tabelle `anschrifttyp`
--
ALTER TABLE `anschrifttyp`
  ADD PRIMARY KEY (`anschriftTypID`) USING BTREE;

--
-- Indizes für die Tabelle `aufeinenblick_settings`
--
ALTER TABLE `aufeinenblick_settings`
  ADD PRIMARY KEY (`aufeinenblickSettingsID`) USING BTREE;

--
-- Indizes für die Tabelle `ausleihe_ausleihe`
--
ALTER TABLE `ausleihe_ausleihe`
  ADD PRIMARY KEY (`ausleiheID`) USING BTREE;

--
-- Indizes für die Tabelle `ausleihe_objekte`
--
ALTER TABLE `ausleihe_objekte`
  ADD PRIMARY KEY (`objektID`) USING BTREE;

--
-- Indizes für die Tabelle `ausweise`
--
ALTER TABLE `ausweise`
  ADD PRIMARY KEY (`ausweisID`) USING BTREE;

--
-- Indizes für die Tabelle `bad_mail`
--
ALTER TABLE `bad_mail`
  ADD PRIMARY KEY (`badMailID`) USING BTREE;

--
-- Indizes für die Tabelle `beobachtungsbogen_boegen`
--
ALTER TABLE `beobachtungsbogen_boegen`
  ADD PRIMARY KEY (`beobachtungsbogenID`) USING BTREE;

--
-- Indizes für die Tabelle `beobachtungsbogen_eintragungsfrist`
--
ALTER TABLE `beobachtungsbogen_eintragungsfrist`
  ADD PRIMARY KEY (`beobachtungsbogenID`,`userID`) USING BTREE;

--
-- Indizes für die Tabelle `beobachtungsbogen_fragen`
--
ALTER TABLE `beobachtungsbogen_fragen`
  ADD PRIMARY KEY (`frageID`) USING BTREE;

--
-- Indizes für die Tabelle `beobachtungsbogen_fragen_daten`
--
ALTER TABLE `beobachtungsbogen_fragen_daten`
  ADD PRIMARY KEY (`frageID`,`schuelerID`,`lehrerKuerzel`,`fachName`) USING BTREE;

--
-- Indizes für die Tabelle `beobachtungsbogen_klassenleitung`
--
ALTER TABLE `beobachtungsbogen_klassenleitung`
  ADD PRIMARY KEY (`beobachtungsbogenID`,`klassenName`,`klassenleitungTyp`) USING BTREE;

--
-- Indizes für die Tabelle `beobachtungsbogen_klasse_fach_lehrer`
--
ALTER TABLE `beobachtungsbogen_klasse_fach_lehrer`
  ADD PRIMARY KEY (`beobachtungsbogenID`,`klasseName`,`fachName`,`lehrerKuerzel`) USING BTREE;

--
-- Indizes für die Tabelle `beobachtungsbogen_schueler_namen`
--
ALTER TABLE `beobachtungsbogen_schueler_namen`
  ADD PRIMARY KEY (`beobachtungsbogenID`,`schuelerID`) USING BTREE;

--
-- Indizes für die Tabelle `beurlaubungsantraege`
--
ALTER TABLE `beurlaubungsantraege`
  ADD PRIMARY KEY (`baID`) USING BTREE;

--
-- Indizes für die Tabelle `beurlaubung_antrag`
--
ALTER TABLE `beurlaubung_antrag`
  ADD PRIMARY KEY (`antragID`) USING BTREE;

--
-- Indizes für die Tabelle `cron_execution`
--
ALTER TABLE `cron_execution`
  ADD PRIMARY KEY (`cronRunID`) USING BTREE;

--
-- Indizes für die Tabelle `database_database`
--
ALTER TABLE `database_database`
  ADD PRIMARY KEY (`databaseID`) USING BTREE;

--
-- Indizes für die Tabelle `database_user2database`
--
ALTER TABLE `database_user2database`
  ADD PRIMARY KEY (`userID`,`databaseID`) USING BTREE;

--
-- Indizes für die Tabelle `database_users`
--
ALTER TABLE `database_users`
  ADD PRIMARY KEY (`userID`) USING BTREE;

--
-- Indizes für die Tabelle `datenschutz_erklaerung`
--
ALTER TABLE `datenschutz_erklaerung`
  ADD PRIMARY KEY (`userID`) USING BTREE;

--
-- Indizes für die Tabelle `dokumente_dateien`
--
ALTER TABLE `dokumente_dateien`
  ADD PRIMARY KEY (`dateiID`) USING BTREE;

--
-- Indizes für die Tabelle `dokumente_gruppen`
--
ALTER TABLE `dokumente_gruppen`
  ADD PRIMARY KEY (`gruppenID`) USING BTREE;

--
-- Indizes für die Tabelle `dokumente_kategorien`
--
ALTER TABLE `dokumente_kategorien`
  ADD PRIMARY KEY (`kategorieID`) USING BTREE;

--
-- Indizes für die Tabelle `elternbriefe`
--
ALTER TABLE `elternbriefe`
  ADD PRIMARY KEY (`briefID`) USING BTREE;

--
-- Indizes für die Tabelle `eltern_adressen`
--
ALTER TABLE `eltern_adressen`
  ADD PRIMARY KEY (`adresseID`) USING BTREE;

--
-- Indizes für die Tabelle `eltern_codes`
--
ALTER TABLE `eltern_codes`
  ADD PRIMARY KEY (`codeID`) USING BTREE;

--
-- Indizes für die Tabelle `eltern_email`
--
ALTER TABLE `eltern_email`
  ADD PRIMARY KEY (`elternEMail`,`elternSchuelerAsvID`) USING BTREE;

--
-- Indizes für die Tabelle `eltern_register`
--
ALTER TABLE `eltern_register`
  ADD PRIMARY KEY (`registerID`) USING BTREE;

--
-- Indizes für die Tabelle `eltern_telefon`
--
ALTER TABLE `eltern_telefon`
  ADD PRIMARY KEY (`telefonNummer`,`schuelerAsvID`,`adresseID`) USING BTREE,
  ADD KEY `telefonNummer` (`telefonNummer`,`schuelerAsvID`,`telefonTyp`) USING BTREE,
  ADD KEY `telefonNummer_2` (`telefonNummer`,`schuelerAsvID`) USING BTREE;

--
-- Indizes für die Tabelle `eltern_to_schueler`
--
ALTER TABLE `eltern_to_schueler`
  ADD PRIMARY KEY (`elternUserID`,`schuelerUserID`) USING BTREE;

--
-- Indizes für die Tabelle `email_addresses`
--
ALTER TABLE `email_addresses`
  ADD PRIMARY KEY (`userID`) USING BTREE;

--
-- Indizes für die Tabelle `externe_kalender`
--
ALTER TABLE `externe_kalender`
  ADD PRIMARY KEY (`kalenderID`) USING BTREE;

--
-- Indizes für die Tabelle `externe_kalender_kategorien`
--
ALTER TABLE `externe_kalender_kategorien`
  ADD PRIMARY KEY (`kalenderID`,`kategorieName`) USING BTREE;

--
-- Indizes für die Tabelle `faecher`
--
ALTER TABLE `faecher`
  ADD PRIMARY KEY (`fachID`) USING BTREE;

--
-- Indizes für die Tabelle `ffbumfrage`
--
ALTER TABLE `ffbumfrage`
  ADD PRIMARY KEY (`codeID`) USING BTREE;

--
-- Indizes für die Tabelle `fremdlogin`
--
ALTER TABLE `fremdlogin`
  ADD PRIMARY KEY (`fremdloginID`) USING BTREE;

--
-- Indizes für die Tabelle `ganztags_gruppen`
--
ALTER TABLE `ganztags_gruppen`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `ganztags_schueler`
--
ALTER TABLE `ganztags_schueler`
  ADD PRIMARY KEY (`asvid`);

--
-- Indizes für die Tabelle `icsfeeds`
--
ALTER TABLE `icsfeeds`
  ADD PRIMARY KEY (`feedID`) USING BTREE;

--
-- Indizes für die Tabelle `image_uploads`
--
ALTER TABLE `image_uploads`
  ADD PRIMARY KEY (`uploadID`) USING BTREE;

--
-- Indizes für die Tabelle `initialpasswords`
--
ALTER TABLE `initialpasswords`
  ADD PRIMARY KEY (`initialPasswordID`) USING BTREE;

--
-- Indizes für die Tabelle `kalender_andere`
--
ALTER TABLE `kalender_andere`
  ADD PRIMARY KEY (`eintragID`) USING BTREE;

--
-- Indizes für die Tabelle `kalender_extern`
--
ALTER TABLE `kalender_extern`
  ADD PRIMARY KEY (`eintragID`) USING BTREE;

--
-- Indizes für die Tabelle `kalender_ferien`
--
ALTER TABLE `kalender_ferien`
  ADD PRIMARY KEY (`ferienID`) USING BTREE;

--
-- Indizes für die Tabelle `kalender_klassentermin`
--
ALTER TABLE `kalender_klassentermin`
  ADD PRIMARY KEY (`eintragID`) USING BTREE;

--
-- Indizes für die Tabelle `kalender_lnw`
--
ALTER TABLE `kalender_lnw`
  ADD PRIMARY KEY (`eintragID`) USING BTREE;

--
-- Indizes für die Tabelle `klassenkalender`
--
ALTER TABLE `klassenkalender`
  ADD PRIMARY KEY (`eintragID`) USING BTREE;

--
-- Indizes für die Tabelle `klassenleitung`
--
ALTER TABLE `klassenleitung`
  ADD PRIMARY KEY (`klasseName`,`lehrerID`) USING BTREE;

--
-- Indizes für die Tabelle `klassentagebuch_fehl`
--
ALTER TABLE `klassentagebuch_fehl`
  ADD PRIMARY KEY (`fehlID`) USING BTREE;

--
-- Indizes für die Tabelle `klassentagebuch_klassen`
--
ALTER TABLE `klassentagebuch_klassen`
  ADD PRIMARY KEY (`entryID`) USING BTREE;

--
-- Indizes für die Tabelle `klassentagebuch_lehrer`
--
ALTER TABLE `klassentagebuch_lehrer`
  ADD PRIMARY KEY (`entryID`) USING BTREE;

--
-- Indizes für die Tabelle `klassentagebuch_pdf`
--
ALTER TABLE `klassentagebuch_pdf`
  ADD PRIMARY KEY (`pdfKlasse`,`pdfJahr`,`pdfMonat`) USING BTREE;

--
-- Indizes für die Tabelle `kondolenzbuch`
--
ALTER TABLE `kondolenzbuch`
  ADD PRIMARY KEY (`eintragID`) USING BTREE;

--
-- Indizes für die Tabelle `laufzettel`
--
ALTER TABLE `laufzettel`
  ADD PRIMARY KEY (`laufzettelID`) USING BTREE;

--
-- Indizes für die Tabelle `laufzettel_stunden`
--
ALTER TABLE `laufzettel_stunden`
  ADD PRIMARY KEY (`laufzettelStundeID`) USING BTREE;

--
-- Indizes für die Tabelle `lehrer`
--
ALTER TABLE `lehrer`
  ADD PRIMARY KEY (`lehrerAsvID`) USING BTREE;

--
-- Indizes für die Tabelle `lerntutoren`
--
ALTER TABLE `lerntutoren`
  ADD PRIMARY KEY (`lerntutorID`) USING BTREE;

--
-- Indizes für die Tabelle `lerntutoren_slots`
--
ALTER TABLE `lerntutoren_slots`
  ADD PRIMARY KEY (`slotID`) USING BTREE;

--
-- Indizes für die Tabelle `mail_send`
--
ALTER TABLE `mail_send`
  ADD PRIMARY KEY (`mailID`) USING BTREE;

--
-- Indizes für die Tabelle `math_captcha`
--
ALTER TABLE `math_captcha`
  ADD PRIMARY KEY (`captchaID`) USING BTREE;

--
-- Indizes für die Tabelle `mebis_accounts`
--
ALTER TABLE `mebis_accounts`
  ADD PRIMARY KEY (`mebisAccountID`) USING BTREE;

--
-- Indizes für die Tabelle `messages_attachment`
--
ALTER TABLE `messages_attachment`
  ADD PRIMARY KEY (`attachmentID`) USING BTREE;

--
-- Indizes für die Tabelle `messages_folders`
--
ALTER TABLE `messages_folders`
  ADD PRIMARY KEY (`folderID`) USING BTREE;

--
-- Indizes für die Tabelle `messages_messages`
--
ALTER TABLE `messages_messages`
  ADD PRIMARY KEY (`messageID`) USING BTREE,
  ADD KEY `messagesKey` (`messageUserID`,`messageSender`,`messageFolder`,`messageFolderID`,`messageIsRead`,`messageIsDeleted`) USING BTREE;

--
-- Indizes für die Tabelle `messages_questions`
--
ALTER TABLE `messages_questions`
  ADD PRIMARY KEY (`questionID`) USING BTREE;

--
-- Indizes für die Tabelle `messages_questions_answers`
--
ALTER TABLE `messages_questions_answers`
  ADD PRIMARY KEY (`answerID`) USING BTREE;

--
-- Indizes für die Tabelle `modul_admin_notes`
--
ALTER TABLE `modul_admin_notes`
  ADD PRIMARY KEY (`noteID`) USING BTREE;

--
-- Indizes für die Tabelle `nextcloud_users`
--
ALTER TABLE `nextcloud_users`
  ADD PRIMARY KEY (`userID`) USING BTREE;

--
-- Indizes für die Tabelle `noten_arbeiten`
--
ALTER TABLE `noten_arbeiten`
  ADD PRIMARY KEY (`arbeitID`) USING BTREE;

--
-- Indizes für die Tabelle `noten_bemerkung_textvorlagen`
--
ALTER TABLE `noten_bemerkung_textvorlagen`
  ADD PRIMARY KEY (`bemerkungID`) USING BTREE;

--
-- Indizes für die Tabelle `noten_bemerkung_textvorlagen_gruppen`
--
ALTER TABLE `noten_bemerkung_textvorlagen_gruppen`
  ADD PRIMARY KEY (`gruppeID`) USING BTREE;

--
-- Indizes für die Tabelle `noten_fach_einstellungen`
--
ALTER TABLE `noten_fach_einstellungen`
  ADD PRIMARY KEY (`fachKurzform`) USING BTREE;

--
-- Indizes für die Tabelle `noten_gewichtung`
--
ALTER TABLE `noten_gewichtung`
  ADD PRIMARY KEY (`fachKuerzel`,`fachJahrgangsstufe`) USING BTREE;

--
-- Indizes für die Tabelle `noten_mv`
--
ALTER TABLE `noten_mv`
  ADD PRIMARY KEY (`mvFachKurzform`,`mvUnterrichtName`,`schuelerAsvID`,`zeugnisID`) USING BTREE;

--
-- Indizes für die Tabelle `noten_noten`
--
ALTER TABLE `noten_noten`
  ADD PRIMARY KEY (`noteSchuelerAsvID`,`noteArbeitID`) USING BTREE;

--
-- Indizes für die Tabelle `noten_verrechnung`
--
ALTER TABLE `noten_verrechnung`
  ADD PRIMARY KEY (`verrechnungID`) USING BTREE;

--
-- Indizes für die Tabelle `noten_wahlfach_faecher`
--
ALTER TABLE `noten_wahlfach_faecher`
  ADD PRIMARY KEY (`wahlfachID`) USING BTREE;

--
-- Indizes für die Tabelle `noten_wahlfach_noten`
--
ALTER TABLE `noten_wahlfach_noten`
  ADD PRIMARY KEY (`wahlfachID`,`schuelerAsvID`) USING BTREE;

--
-- Indizes für die Tabelle `noten_zeugnisse`
--
ALTER TABLE `noten_zeugnisse`
  ADD PRIMARY KEY (`zeugnisID`) USING BTREE;

--
-- Indizes für die Tabelle `noten_zeugnisse_klassen`
--
ALTER TABLE `noten_zeugnisse_klassen`
  ADD PRIMARY KEY (`zeugnisID`,`zeugnisKlasse`) USING BTREE;

--
-- Indizes für die Tabelle `noten_zeugnisse_noten`
--
ALTER TABLE `noten_zeugnisse_noten`
  ADD PRIMARY KEY (`noteSchuelerAsvID`,`noteZeugnisID`,`noteFachKurzform`) USING BTREE;

--
-- Indizes für die Tabelle `noten_zeugnis_bemerkung`
--
ALTER TABLE `noten_zeugnis_bemerkung`
  ADD PRIMARY KEY (`bemerkungSchuelerAsvID`,`bemerkungZeugnisID`) USING BTREE;

--
-- Indizes für die Tabelle `noten_zeugnis_exemplar`
--
ALTER TABLE `noten_zeugnis_exemplar`
  ADD PRIMARY KEY (`zeugnisID`,`schuelerAsvID`) USING BTREE;

--
-- Indizes für die Tabelle `office365_accounts`
--
ALTER TABLE `office365_accounts`
  ADD PRIMARY KEY (`accountAsvID`,`accountIsTeacher`,`accountIsPupil`) USING BTREE;

--
-- Indizes für die Tabelle `projekt_lehrer2grade`
--
ALTER TABLE `projekt_lehrer2grade`
  ADD PRIMARY KEY (`lehrerUserID`,`gradeName`) USING BTREE;

--
-- Indizes für die Tabelle `projekt_projekte`
--
ALTER TABLE `projekt_projekte`
  ADD PRIMARY KEY (`userID`) USING BTREE;

--
-- Indizes für die Tabelle `pupil_grade`
--
ALTER TABLE `pupil_grade`
  ADD PRIMARY KEY (`userID`) USING BTREE;

--
-- Indizes für die Tabelle `remote_usersync`
--
ALTER TABLE `remote_usersync`
  ADD PRIMARY KEY (`syncID`) USING BTREE;

--
-- Indizes für die Tabelle `resetpassword`
--
ALTER TABLE `resetpassword`
  ADD PRIMARY KEY (`resetID`) USING BTREE;

--
-- Indizes für die Tabelle `respizienz`
--
ALTER TABLE `respizienz`
  ADD PRIMARY KEY (`respizienzID`) USING BTREE;

--
-- Indizes für die Tabelle `rsu_persons`
--
ALTER TABLE `rsu_persons`
  ADD PRIMARY KEY (`personID`) USING BTREE;

--
-- Indizes für die Tabelle `rsu_sections`
--
ALTER TABLE `rsu_sections`
  ADD PRIMARY KEY (`sectionID`) USING BTREE;

--
-- Indizes für die Tabelle `schaukasten_bildschirme`
--
ALTER TABLE `schaukasten_bildschirme`
  ADD PRIMARY KEY (`schaukastenID`) USING BTREE;

--
-- Indizes für die Tabelle `schaukasten_inhalt`
--
ALTER TABLE `schaukasten_inhalt`
  ADD PRIMARY KEY (`schaukastenID`,`schaukastenPosition`) USING BTREE;

--
-- Indizes für die Tabelle `schaukasten_powerpoint`
--
ALTER TABLE `schaukasten_powerpoint`
  ADD PRIMARY KEY (`powerpointID`) USING BTREE;

--
-- Indizes für die Tabelle `schaukasten_website`
--
ALTER TABLE `schaukasten_website`
  ADD PRIMARY KEY (`websiteID`) USING BTREE,
  ADD UNIQUE KEY `websiteName` (`websiteID`) USING BTREE;

--
-- Indizes für die Tabelle `schueler`
--
ALTER TABLE `schueler`
  ADD PRIMARY KEY (`schuelerAsvID`) USING BTREE;

--
-- Indizes für die Tabelle `schuelerinfo_dokumente`
--
ALTER TABLE `schuelerinfo_dokumente`
  ADD PRIMARY KEY (`dokumentID`) USING BTREE;

--
-- Indizes für die Tabelle `schueler_briefe`
--
ALTER TABLE `schueler_briefe`
  ADD PRIMARY KEY (`briefID`) USING BTREE;

--
-- Indizes für die Tabelle `schueler_fremdsprache`
--
ALTER TABLE `schueler_fremdsprache`
  ADD PRIMARY KEY (`schuelerAsvID`,`spracheSortierung`) USING BTREE;

--
-- Indizes für die Tabelle `schueler_nachteilsausgleich`
--
ALTER TABLE `schueler_nachteilsausgleich`
  ADD PRIMARY KEY (`schuelerAsvID`) USING BTREE;

--
-- Indizes für die Tabelle `schulbuch_ausleihe`
--
ALTER TABLE `schulbuch_ausleihe`
  ADD PRIMARY KEY (`ausleiheID`) USING BTREE;

--
-- Indizes für die Tabelle `schulbuch_buecher`
--
ALTER TABLE `schulbuch_buecher`
  ADD PRIMARY KEY (`buchID`) USING BTREE;

--
-- Indizes für die Tabelle `schulbuch_exemplare`
--
ALTER TABLE `schulbuch_exemplare`
  ADD PRIMARY KEY (`exemplarID`,`exemplarBarcode`) USING BTREE;

--
-- Indizes für die Tabelle `schulen`
--
ALTER TABLE `schulen`
  ADD PRIMARY KEY (`schuleID`) USING BTREE;

--
-- Indizes für die Tabelle `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`sessionID`) USING BTREE;

--
-- Indizes für die Tabelle `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`settingName`) USING BTREE;

--
-- Indizes für die Tabelle `site_activation`
--
ALTER TABLE `site_activation`
  ADD PRIMARY KEY (`siteName`) USING BTREE;

--
-- Indizes für die Tabelle `sprechtag`
--
ALTER TABLE `sprechtag`
  ADD PRIMARY KEY (`sprechtagID`) USING BTREE;

--
-- Indizes für die Tabelle `sprechtag_buchungen`
--
ALTER TABLE `sprechtag_buchungen`
  ADD PRIMARY KEY (`buchungID`) USING BTREE;

--
-- Indizes für die Tabelle `sprechtag_raeume`
--
ALTER TABLE `sprechtag_raeume`
  ADD PRIMARY KEY (`sprechtagID`,`lehrerKuerzel`) USING BTREE;

--
-- Indizes für die Tabelle `sprechtag_slots`
--
ALTER TABLE `sprechtag_slots`
  ADD PRIMARY KEY (`slotID`) USING BTREE;

--
-- Indizes für die Tabelle `stundenplan_aufsichten`
--
ALTER TABLE `stundenplan_aufsichten`
  ADD PRIMARY KEY (`aufsichtID`) USING BTREE;

--
-- Indizes für die Tabelle `stundenplan_plaene`
--
ALTER TABLE `stundenplan_plaene`
  ADD PRIMARY KEY (`stundenplanID`) USING BTREE;

--
-- Indizes für die Tabelle `stundenplan_stunden`
--
ALTER TABLE `stundenplan_stunden`
  ADD PRIMARY KEY (`stundeID`) USING BTREE;

--
-- Indizes für die Tabelle `templates`
--
ALTER TABLE `templates`
  ADD PRIMARY KEY (`templateName`) USING BTREE;

--
-- Indizes für die Tabelle `trenndaten`
--
ALTER TABLE `trenndaten`
  ADD PRIMARY KEY (`trennWort`) USING BTREE;

--
-- Indizes für die Tabelle `two_factor_trusted_devices`
--
ALTER TABLE `two_factor_trusted_devices`
  ADD PRIMARY KEY (`deviceID`) USING BTREE,
  ADD KEY `two_factor_trusted_devices_ibfk_1` (`deviceUserID`) USING BTREE;

--
-- Indizes für die Tabelle `unknown_mails`
--
ALTER TABLE `unknown_mails`
  ADD PRIMARY KEY (`mailID`) USING BTREE;

--
-- Indizes für die Tabelle `unterricht`
--
ALTER TABLE `unterricht`
  ADD PRIMARY KEY (`unterrichtID`) USING BTREE;

--
-- Indizes für die Tabelle `uploads`
--
ALTER TABLE `uploads`
  ADD PRIMARY KEY (`uploadID`) USING BTREE;

--
-- Indizes für die Tabelle `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`userID`) USING BTREE;

--
-- Indizes für die Tabelle `users_groups`
--
ALTER TABLE `users_groups`
  ADD PRIMARY KEY (`userID`,`groupName`) USING BTREE;

--
-- Indizes für die Tabelle `users_groups_own`
--
ALTER TABLE `users_groups_own`
  ADD PRIMARY KEY (`groupName`) USING BTREE;

--
-- Indizes für die Tabelle `user_images`
--
ALTER TABLE `user_images`
  ADD PRIMARY KEY (`userImageID`) USING BTREE;

--
-- Indizes für die Tabelle `user_settings`
--
ALTER TABLE `user_settings`
  ADD PRIMARY KEY (`userID`) USING BTREE;

--
-- Indizes für die Tabelle `vplan`
--
ALTER TABLE `vplan`
  ADD PRIMARY KEY (`vplanName`) USING BTREE;

--
-- Indizes für die Tabelle `wlan_schueler`
--
ALTER TABLE `wlan_schueler`
  ADD PRIMARY KEY (`schuelerID`) USING BTREE;

--
-- Indizes für die Tabelle `wlan_ticket`
--
ALTER TABLE `wlan_ticket`
  ADD PRIMARY KEY (`ticketID`) USING BTREE;

--
-- AUTO_INCREMENT für exportierte Tabellen
--

--
-- AUTO_INCREMENT für Tabelle `absenzen_absenzen`
--
ALTER TABLE `absenzen_absenzen`
  MODIFY `absenzID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `absenzen_attestpflicht`
--
ALTER TABLE `absenzen_attestpflicht`
  MODIFY `attestpflichtID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `absenzen_befreiungen`
--
ALTER TABLE `absenzen_befreiungen`
  MODIFY `befreiungID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `absenzen_beurlaubungen`
--
ALTER TABLE `absenzen_beurlaubungen`
  MODIFY `beurlaubungID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `absenzen_beurlaubung_antrag`
--
ALTER TABLE `absenzen_beurlaubung_antrag`
  MODIFY `antragID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `absenzen_krankmeldungen`
--
ALTER TABLE `absenzen_krankmeldungen`
  MODIFY `krankmeldungID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `absenzen_merker`
--
ALTER TABLE `absenzen_merker`
  MODIFY `merkerID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `absenzen_sanizimmer`
--
ALTER TABLE `absenzen_sanizimmer`
  MODIFY `sanizimmerID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `absenzen_verspaetungen`
--
ALTER TABLE `absenzen_verspaetungen`
  MODIFY `verspaetungID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `andere_kalender`
--
ALTER TABLE `andere_kalender`
  MODIFY `kalenderID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `andere_kalender_kategorie`
--
ALTER TABLE `andere_kalender_kategorie`
  MODIFY `kategorieID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `aufeinenblick_settings`
--
ALTER TABLE `aufeinenblick_settings`
  MODIFY `aufeinenblickSettingsID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `ausleihe_ausleihe`
--
ALTER TABLE `ausleihe_ausleihe`
  MODIFY `ausleiheID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `ausleihe_objekte`
--
ALTER TABLE `ausleihe_objekte`
  MODIFY `objektID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `ausweise`
--
ALTER TABLE `ausweise`
  MODIFY `ausweisID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `bad_mail`
--
ALTER TABLE `bad_mail`
  MODIFY `badMailID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `beobachtungsbogen_boegen`
--
ALTER TABLE `beobachtungsbogen_boegen`
  MODIFY `beobachtungsbogenID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `beobachtungsbogen_fragen`
--
ALTER TABLE `beobachtungsbogen_fragen`
  MODIFY `frageID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `beurlaubungsantraege`
--
ALTER TABLE `beurlaubungsantraege`
  MODIFY `baID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `beurlaubung_antrag`
--
ALTER TABLE `beurlaubung_antrag`
  MODIFY `antragID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `cron_execution`
--
ALTER TABLE `cron_execution`
  MODIFY `cronRunID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `database_database`
--
ALTER TABLE `database_database`
  MODIFY `databaseID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `database_users`
--
ALTER TABLE `database_users`
  MODIFY `userID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `dokumente_dateien`
--
ALTER TABLE `dokumente_dateien`
  MODIFY `dateiID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `dokumente_gruppen`
--
ALTER TABLE `dokumente_gruppen`
  MODIFY `gruppenID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `dokumente_kategorien`
--
ALTER TABLE `dokumente_kategorien`
  MODIFY `kategorieID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `elternbriefe`
--
ALTER TABLE `elternbriefe`
  MODIFY `briefID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `eltern_adressen`
--
ALTER TABLE `eltern_adressen`
  MODIFY `adresseID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `eltern_codes`
--
ALTER TABLE `eltern_codes`
  MODIFY `codeID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `eltern_register`
--
ALTER TABLE `eltern_register`
  MODIFY `registerID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `externe_kalender`
--
ALTER TABLE `externe_kalender`
  MODIFY `kalenderID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `ffbumfrage`
--
ALTER TABLE `ffbumfrage`
  MODIFY `codeID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `fremdlogin`
--
ALTER TABLE `fremdlogin`
  MODIFY `fremdloginID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `ganztags_gruppen`
--
ALTER TABLE `ganztags_gruppen`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `icsfeeds`
--
ALTER TABLE `icsfeeds`
  MODIFY `feedID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `image_uploads`
--
ALTER TABLE `image_uploads`
  MODIFY `uploadID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `initialpasswords`
--
ALTER TABLE `initialpasswords`
  MODIFY `initialPasswordID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `kalender_andere`
--
ALTER TABLE `kalender_andere`
  MODIFY `eintragID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `kalender_extern`
--
ALTER TABLE `kalender_extern`
  MODIFY `eintragID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `kalender_ferien`
--
ALTER TABLE `kalender_ferien`
  MODIFY `ferienID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `kalender_klassentermin`
--
ALTER TABLE `kalender_klassentermin`
  MODIFY `eintragID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `kalender_lnw`
--
ALTER TABLE `kalender_lnw`
  MODIFY `eintragID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `klassenkalender`
--
ALTER TABLE `klassenkalender`
  MODIFY `eintragID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `klassentagebuch_fehl`
--
ALTER TABLE `klassentagebuch_fehl`
  MODIFY `fehlID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `klassentagebuch_klassen`
--
ALTER TABLE `klassentagebuch_klassen`
  MODIFY `entryID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `klassentagebuch_lehrer`
--
ALTER TABLE `klassentagebuch_lehrer`
  MODIFY `entryID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `kondolenzbuch`
--
ALTER TABLE `kondolenzbuch`
  MODIFY `eintragID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `laufzettel`
--
ALTER TABLE `laufzettel`
  MODIFY `laufzettelID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `laufzettel_stunden`
--
ALTER TABLE `laufzettel_stunden`
  MODIFY `laufzettelStundeID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `lerntutoren`
--
ALTER TABLE `lerntutoren`
  MODIFY `lerntutorID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `lerntutoren_slots`
--
ALTER TABLE `lerntutoren_slots`
  MODIFY `slotID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `mail_send`
--
ALTER TABLE `mail_send`
  MODIFY `mailID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `math_captcha`
--
ALTER TABLE `math_captcha`
  MODIFY `captchaID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `mebis_accounts`
--
ALTER TABLE `mebis_accounts`
  MODIFY `mebisAccountID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `messages_attachment`
--
ALTER TABLE `messages_attachment`
  MODIFY `attachmentID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `messages_folders`
--
ALTER TABLE `messages_folders`
  MODIFY `folderID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `messages_messages`
--
ALTER TABLE `messages_messages`
  MODIFY `messageID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `messages_questions`
--
ALTER TABLE `messages_questions`
  MODIFY `questionID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `messages_questions_answers`
--
ALTER TABLE `messages_questions_answers`
  MODIFY `answerID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `modul_admin_notes`
--
ALTER TABLE `modul_admin_notes`
  MODIFY `noteID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `nextcloud_users`
--
ALTER TABLE `nextcloud_users`
  MODIFY `userID` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Same UserID as in SI';

--
-- AUTO_INCREMENT für Tabelle `noten_arbeiten`
--
ALTER TABLE `noten_arbeiten`
  MODIFY `arbeitID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `noten_bemerkung_textvorlagen`
--
ALTER TABLE `noten_bemerkung_textvorlagen`
  MODIFY `bemerkungID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `noten_bemerkung_textvorlagen_gruppen`
--
ALTER TABLE `noten_bemerkung_textvorlagen_gruppen`
  MODIFY `gruppeID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `noten_verrechnung`
--
ALTER TABLE `noten_verrechnung`
  MODIFY `verrechnungID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `noten_wahlfach_faecher`
--
ALTER TABLE `noten_wahlfach_faecher`
  MODIFY `wahlfachID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `noten_zeugnisse`
--
ALTER TABLE `noten_zeugnisse`
  MODIFY `zeugnisID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `remote_usersync`
--
ALTER TABLE `remote_usersync`
  MODIFY `syncID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `resetpassword`
--
ALTER TABLE `resetpassword`
  MODIFY `resetID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `rsu_persons`
--
ALTER TABLE `rsu_persons`
  MODIFY `personID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `rsu_sections`
--
ALTER TABLE `rsu_sections`
  MODIFY `sectionID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `schaukasten_bildschirme`
--
ALTER TABLE `schaukasten_bildschirme`
  MODIFY `schaukastenID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `schaukasten_powerpoint`
--
ALTER TABLE `schaukasten_powerpoint`
  MODIFY `powerpointID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `schaukasten_website`
--
ALTER TABLE `schaukasten_website`
  MODIFY `websiteID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `schuelerinfo_dokumente`
--
ALTER TABLE `schuelerinfo_dokumente`
  MODIFY `dokumentID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `schueler_briefe`
--
ALTER TABLE `schueler_briefe`
  MODIFY `briefID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `schulbuch_ausleihe`
--
ALTER TABLE `schulbuch_ausleihe`
  MODIFY `ausleiheID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `schulbuch_buecher`
--
ALTER TABLE `schulbuch_buecher`
  MODIFY `buchID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `schulbuch_exemplare`
--
ALTER TABLE `schulbuch_exemplare`
  MODIFY `exemplarID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `sprechtag`
--
ALTER TABLE `sprechtag`
  MODIFY `sprechtagID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `sprechtag_buchungen`
--
ALTER TABLE `sprechtag_buchungen`
  MODIFY `buchungID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `sprechtag_slots`
--
ALTER TABLE `sprechtag_slots`
  MODIFY `slotID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `stundenplan_aufsichten`
--
ALTER TABLE `stundenplan_aufsichten`
  MODIFY `aufsichtID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `stundenplan_plaene`
--
ALTER TABLE `stundenplan_plaene`
  MODIFY `stundenplanID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `stundenplan_stunden`
--
ALTER TABLE `stundenplan_stunden`
  MODIFY `stundeID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `two_factor_trusted_devices`
--
ALTER TABLE `two_factor_trusted_devices`
  MODIFY `deviceID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `unknown_mails`
--
ALTER TABLE `unknown_mails`
  MODIFY `mailID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `uploads`
--
ALTER TABLE `uploads`
  MODIFY `uploadID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `users`
--
ALTER TABLE `users`
  MODIFY `userID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `user_images`
--
ALTER TABLE `user_images`
  MODIFY `userImageID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `wlan_ticket`
--
ALTER TABLE `wlan_ticket`
  MODIFY `ticketID` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints der exportierten Tabellen
--

--
-- Constraints der Tabelle `two_factor_trusted_devices`
--
ALTER TABLE `two_factor_trusted_devices`
  ADD CONSTRAINT `two_factor_trusted_devices_ibfk_1` FOREIGN KEY (`deviceUserID`) REFERENCES `users` (`userID`) ON DELETE CASCADE;
COMMIT;
