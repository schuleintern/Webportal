SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;


CREATE TABLE `absenzen_absenzen` (
                                     `absenzID` int(11) NOT NULL,
                                     `absenzSchuelerAsvID` varchar(50) NOT NULL,
                                     `absenzDatum` date NOT NULL,
                                     `absenzDatumEnde` date NOT NULL,
                                     `absenzQuelle` enum('TELEFON','WEBPORTAL','LEHRER','PERSOENLICH','FAX') NOT NULL,
                                     `absenzBemerkung` mediumtext NOT NULL,
                                     `absenzErfasstTime` int(11) NOT NULL,
                                     `absenzErfasstUserID` int(11) NOT NULL,
                                     `absenzBefreiungID` int(11) NOT NULL,
                                     `absenzBeurlaubungID` int(11) NOT NULL DEFAULT 0,
                                     `absenzStunden` mediumtext NOT NULL,
                                     `absenzisEntschuldigt` tinyint(1) NOT NULL,
                                     `absenzIsSchriftlichEntschuldigt` tinyint(1) NOT NULL,
                                     `absenzKommtSpaeter` tinyint(1) NOT NULL DEFAULT 0,
                                     `absenzGanztagsNotiz` tinytext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

CREATE TABLE `absenzen_attestpflicht` (
                                          `attestpflichtID` int(11) NOT NULL,
                                          `schuelerAsvID` varchar(100) NOT NULL,
                                          `attestpflichtStart` date NOT NULL,
                                          `attestpflichtEnde` date NOT NULL,
                                          `attestpflichtUserID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

CREATE TABLE `absenzen_befreiungen` (
                                        `befreiungID` int(11) NOT NULL,
                                        `befreiungUhrzeit` varchar(100) NOT NULL,
                                        `befreiungLehrer` varchar(100) NOT NULL,
                                        `befreiungPrinted` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

CREATE TABLE `absenzen_beurlaubungen` (
                                          `beurlaubungID` int(11) NOT NULL,
                                          `beurlaubungCreatorID` int(11) NOT NULL,
                                          `beurlaubungPrinted` tinyint(1) NOT NULL DEFAULT 0,
                                          `beurlaubungIsInternAbwesend` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

CREATE TABLE `absenzen_comments` (
                                     `schuelerAsvID` varchar(100) NOT NULL,
                                     `commentText` longtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

CREATE TABLE `absenzen_krankmeldungen` (
                                           `krankmeldungID` int(11) NOT NULL,
                                           `krankmeldungSchuelerASVID` varchar(50) NOT NULL,
                                           `krankmeldungDate` date NOT NULL,
                                           `krankmeldungUntilDate` date NOT NULL,
                                           `krankmeldungElternID` int(11) NOT NULL,
                                           `krankmeldungDurch` enum('m','v','s','schueleru18','schuelerue18') NOT NULL,
                                           `krankmeldungKommentar` mediumtext NOT NULL,
                                           `krankmeldungAbsenzID` int(11) NOT NULL DEFAULT 0,
                                           `krankmeldungTime` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

CREATE TABLE `absenzen_meldung` (
                                    `meldungDatum` date NOT NULL,
                                    `meldungKlasse` varchar(100) NOT NULL,
                                    `meldungUserID` int(11) NOT NULL,
                                    `meldungTime` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

CREATE TABLE `absenzen_merker` (
                                   `merkerID` int(11) NOT NULL,
                                   `merkerSchuelerAsvID` varchar(100) NOT NULL,
                                   `merkerDate` date NOT NULL,
                                   `merkerText` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

CREATE TABLE `absenzen_sanizimmer` (
                                       `sanizimmerID` int(11) NOT NULL,
                                       `sanizimmerSchuelerAsvID` varchar(20) NOT NULL,
                                       `sanizimmerTimeStart` int(11) NOT NULL DEFAULT 0,
                                       `sanizimmerTimeEnde` int(11) NOT NULL DEFAULT 0,
                                       `sanizimmerErfasserUserID` int(11) NOT NULL,
                                       `sanizimmerResult` enum('ZURUECK','BEFREIUNG','RETTUNGSDIENST') NOT NULL,
                                       `sanizimmerGrund` mediumtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

CREATE TABLE `acl` (
                       `id` int(10) UNSIGNED NOT NULL,
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
                       `owneDelete` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `amtsbezeichnungen` (
                                     `amtsbezeichnungID` int(11) NOT NULL,
                                     `amtsbezeichnungKurzform` mediumtext NOT NULL,
                                     `amtsbezeichnungAnzeigeform` mediumtext NOT NULL,
                                     `amtsbezeichnungKurzformW` mediumtext NOT NULL,
                                     `amtsbezeichnungAnzeigeformW` mediumtext NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

CREATE TABLE `andere_kalender_kategorie` (
                                             `kategorieID` int(11) NOT NULL,
                                             `kategorieKalenderID` int(11) NOT NULL,
                                             `kategorieName` mediumtext NOT NULL,
                                             `kategorieFarbe` varchar(7) NOT NULL,
                                             `kategorieIcon` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

CREATE TABLE `anschrifttyp` (
                                `anschriftTypID` varchar(10) NOT NULL,
                                `anschriftTypKurzform` varchar(255) NOT NULL,
                                `anschriftTypAnzeigeform` mediumtext NOT NULL,
                                `anschriftTypLangform` mediumtext NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

CREATE TABLE `aufeinenblick_settings` (
                                          `aufeinenblickSettingsID` int(11) NOT NULL,
                                          `aufeinenblickUserID` int(11) NOT NULL,
                                          `aufeinenblickHourCanceltoday` int(11) NOT NULL,
                                          `aufeinenblickShowVplan` int(11) NOT NULL,
                                          `aufeinenblickShowCalendar` int(11) NOT NULL,
                                          `aufeinenblickShowStundenplan` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED;

CREATE TABLE `ausleihe_ausleihe` (
                                     `ausleiheID` int(11) NOT NULL,
                                     `ausleiheObjektID` int(11) NOT NULL,
                                     `ausleiheObjektIndex` int(11) NOT NULL,
                                     `ausleiheDatum` date NOT NULL,
                                     `ausleiheAusleiherUserID` int(11) NOT NULL,
                                     `ausleiheStunde` int(11) NOT NULL,
                                     `ausleiheKlasse` varchar(10) NOT NULL,
                                     `ausleiheLehrer` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

CREATE TABLE `ausleihe_objekte` (
                                    `objektID` int(11) NOT NULL,
                                    `objektName` mediumtext NOT NULL,
                                    `objektAnzahl` int(11) NOT NULL,
                                    `isActive` tinyint(1) NOT NULL,
                                    `sortOrder` int(11) NOT NULL,
                                    `sumItems` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

CREATE TABLE `ausweise` (
                            `ausweisID` int(11) NOT NULL,
                            `ausweisErsteller` int(11) NOT NULL,
                            `ausweisArt` enum('SCHUELER','LEHRER','MITARBEITER','GAST') DEFAULT 'SCHUELER',
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

CREATE TABLE `bad_mail` (
                            `badMailID` int(11) NOT NULL,
                            `badMail` mediumtext NOT NULL,
                            `badMailDone` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

CREATE TABLE `beobachtungsbogen_boegen` (
                                            `beobachtungsbogenID` int(11) NOT NULL,
                                            `beobachtungsbogenName` varchar(200) NOT NULL,
                                            `beobachtungsbogenDatum` date NOT NULL,
                                            `beobachtungsbogenStartDate` date NOT NULL,
                                            `beobachtungsbogenDeadline` date NOT NULL,
                                            `beobachtungsbogenText` mediumtext NOT NULL,
                                            `beobachtungsbogenTitel` mediumtext NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

CREATE TABLE `beobachtungsbogen_eintragungsfrist` (
                                                      `beobachtungsbogenID` int(11) NOT NULL,
                                                      `userID` int(11) NOT NULL,
                                                      `frist` date NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED;

CREATE TABLE `beobachtungsbogen_fragen` (
                                            `frageID` int(11) NOT NULL,
                                            `beobachtungsbogenID` int(11) NOT NULL,
                                            `frageText` mediumtext NOT NULL,
                                            `frageTyp` enum('1','2') NOT NULL COMMENT '#1: 2 bis -2 ( :-) :-) bis :-( :-( ) #2: 2-0 ( :-) :-) bis :-| )',
                                            `frageZugriff` enum('LEHRER','KLASSENLEITUNG') NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

CREATE TABLE `beobachtungsbogen_fragen_daten` (
                                                  `frageID` int(11) NOT NULL,
                                                  `schuelerID` int(11) NOT NULL,
                                                  `bewertung` int(11) NOT NULL,
                                                  `lehrerKuerzel` varchar(100) NOT NULL,
                                                  `fachName` varchar(100) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

CREATE TABLE `beobachtungsbogen_klassenleitung` (
                                                    `beobachtungsbogenID` int(11) NOT NULL,
                                                    `klassenName` varchar(100) NOT NULL,
                                                    `klassenleitungUserID` int(11) NOT NULL,
                                                    `klassenleitungTyp` int(11) NOT NULL DEFAULT 1
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

CREATE TABLE `beobachtungsbogen_klasse_fach_lehrer` (
                                                        `beobachtungsbogenID` int(11) NOT NULL,
                                                        `klasseName` varchar(100) NOT NULL,
                                                        `fachName` varchar(100) NOT NULL,
                                                        `lehrerKuerzel` varchar(100) NOT NULL,
                                                        `isOK` tinyint(1) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

CREATE TABLE `beobachtungsbogen_schueler_namen` (
                                                    `beobachtungsbogenID` int(11) NOT NULL,
                                                    `schuelerID` int(11) NOT NULL,
                                                    `schuelerFirstName` varchar(255) NOT NULL,
                                                    `schulerLastName` varchar(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

CREATE TABLE `cache` (
                         `cacheKey` varchar(255) NOT NULL,
                         `cacheTTL` int(11) NOT NULL,
                         `cacheType` enum('object','text','base64') NOT NULL DEFAULT 'text',
                         `cacheData` longtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

CREATE TABLE `cron_execution` (
                                  `cronRunID` int(11) NOT NULL,
                                  `cronName` varchar(255) NOT NULL,
                                  `cronStartTime` int(11) NOT NULL,
                                  `cronEndTime` int(11) NOT NULL,
                                  `cronSuccess` tinyint(1) NOT NULL,
                                  `cronResult` longtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

CREATE TABLE `dashboard` (
                             `id` int(11) UNSIGNED NOT NULL,
                             `title` varchar(255) DEFAULT NULL,
                             `uniqid` varchar(100) DEFAULT NULL,
                             `user_id` varchar(100) DEFAULT NULL,
                             `widget_id` varchar(100) DEFAULT NULL,
                             `param` varchar(255) DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `datenschutz_erklaerung` (
                                          `userID` int(11) NOT NULL,
                                          `userConfirmed` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED;

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
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

CREATE TABLE `dokumente_gruppen` (
                                     `gruppenID` int(11) NOT NULL,
                                     `gruppenName` varchar(255) NOT NULL,
                                     `kategorieID` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

CREATE TABLE `dokumente_kategorien` (
                                        `kategorieID` int(11) NOT NULL,
                                        `kategorieName` varchar(255) NOT NULL,
                                        `kategorieAccessSchueler` tinyint(1) NOT NULL,
                                        `kategorieAccessLehrer` tinyint(1) NOT NULL,
                                        `kategorieAccessEltern` tinyint(1) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

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
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

CREATE TABLE `eltern_codes` (
                                `codeID` int(11) NOT NULL,
                                `codeSchuelerAsvID` varchar(100) NOT NULL,
                                `codeText` varchar(50) NOT NULL,
                                `codeUserID` text NOT NULL,
                                `codePrinted` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

CREATE TABLE `eltern_email` (
                                `elternEMail` varchar(255) NOT NULL,
                                `elternSchuelerAsvID` varchar(20) NOT NULL,
                                `elternUserID` int(11) NOT NULL DEFAULT 0,
                                `elternAdresseID` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

CREATE TABLE `eltern_register` (
                                   `registerID` int(11) NOT NULL,
                                   `registerCheckKey` varchar(200) NOT NULL,
                                   `registerSchuelerKey` varchar(200) NOT NULL,
                                   `registerTime` int(11) NOT NULL,
                                   `registerPassword` varchar(200) NOT NULL,
                                   `registerMail` varchar(255) NOT NULL,
                                   `firstName` text NOT NULL,
                                   `lastName` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

CREATE TABLE `eltern_telefon` (
                                  `telefonNummer` varchar(255) NOT NULL,
                                  `schuelerAsvID` varchar(50) NOT NULL,
                                  `telefonTyp` enum('telefon','mobiltelefon','fax') NOT NULL DEFAULT 'telefon',
                                  `kontaktTyp` varchar(10) NOT NULL,
                                  `adresseID` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

CREATE TABLE `eltern_to_schueler` (
                                      `elternUserID` int(11) NOT NULL,
                                      `schuelerUserID` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED;

CREATE TABLE `email_addresses` (
                                   `userID` int(11) NOT NULL,
                                   `userEMail` mediumtext NOT NULL,
                                   `userConfirmCode` mediumtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

CREATE TABLE `extensions` (
                              `id` int(11) UNSIGNED NOT NULL,
                              `name` varchar(255) DEFAULT NULL,
                              `uniqid` varchar(255) DEFAULT NULL,
                              `version` int(11) DEFAULT NULL,
                              `active` tinyint(11) DEFAULT NULL,
                              `folder` varchar(255) DEFAULT NULL,
                              `menuCat` varchar(25) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `externe_kalender` (
                                    `kalenderID` int(11) NOT NULL,
                                    `kalenderName` varchar(255) NOT NULL,
                                    `kalenderAccessSchueler` tinyint(1) NOT NULL DEFAULT 0,
                                    `kalenderAccessLehrer` int(11) NOT NULL DEFAULT 0,
                                    `kalenderAccessEltern` int(11) NOT NULL DEFAULT 0,
                                    `kalenderIcalFeed` mediumtext NOT NULL,
                                    `office365Username` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

CREATE TABLE `externe_kalender_kategorien` (
                                               `kalenderID` int(11) NOT NULL,
                                               `kategorieName` varchar(255) NOT NULL,
                                               `kategorieText` text NOT NULL,
                                               `kategorieFarbe` varchar(7) NOT NULL DEFAULT '#000000',
                                               `kategorieIcon` varchar(200) NOT NULL DEFAULT 'fa fa-calendar'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;

CREATE TABLE `faecher` (
                           `fachID` int(11) NOT NULL COMMENT 'Aus XML File',
                           `fachKurzform` mediumtext NOT NULL,
                           `fachLangform` mediumtext NOT NULL,
                           `fachIstSelbstErstellt` tinyint(1) NOT NULL DEFAULT 0,
                           `fachASDID` varchar(100) NOT NULL,
                           `fachOrdnung` int(11) NOT NULL DEFAULT 10
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

CREATE TABLE `fremdlogin` (
                              `fremdloginID` int(11) NOT NULL,
                              `userID` int(11) NOT NULL,
                              `adminUserID` int(11) NOT NULL,
                              `loginMessage` longtext NOT NULL,
                              `loginTime` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

CREATE TABLE `ganztags_events` (
                                   `id` int(11) UNSIGNED NOT NULL,
                                   `date` date DEFAULT NULL,
                                   `gruppenID` int(11) DEFAULT NULL,
                                   `title` varchar(255) DEFAULT NULL,
                                   `room` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ganztags_gruppen` (
                                    `id` int(10) UNSIGNED NOT NULL,
                                    `sortOrder` int(11) DEFAULT NULL,
                                    `name` varchar(255) DEFAULT NULL,
                                    `raum` varchar(30) NOT NULL,
                                    `farbe` varchar(8) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

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
                                     `tag_so` tinyint(1) DEFAULT NULL,
                                     `tag_mo_info` varchar(255) NOT NULL DEFAULT '',
                                     `tag_di_info` varchar(255) NOT NULL DEFAULT '',
                                     `tag_mi_info` varchar(255) NOT NULL DEFAULT '',
                                     `tag_do_info` varchar(255) NOT NULL DEFAULT '',
                                     `tag_fr_info` varchar(255) NOT NULL DEFAULT '',
                                     `tag_sa_info` varchar(255) NOT NULL DEFAULT '',
                                     `tag_so_info` varchar(255) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `icsfeeds` (
                            `feedID` int(11) NOT NULL,
                            `feedType` enum('KL','AK','EK') NOT NULL,
                            `feedData` mediumtext NOT NULL,
                            `feedKey` varchar(255) NOT NULL,
                            `feedKey2` varchar(255) NOT NULL,
                            `feedUserID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

CREATE TABLE `image_uploads` (
                                 `uploadID` int(11) NOT NULL,
                                 `uploadTime` int(11) NOT NULL,
                                 `uploadUserName` varchar(20) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

CREATE TABLE `initialpasswords` (
                                    `initialPasswordID` int(11) NOT NULL,
                                    `initialPasswordUserID` int(11) NOT NULL,
                                    `initialPassword` varchar(200) NOT NULL,
                                    `passwordPrinted` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

CREATE TABLE `kalender_allInOne` (
                                     `kalenderID` int(11) NOT NULL,
                                     `kalenderName` varchar(255) NOT NULL,
                                     `kalenderColor` varchar(7) DEFAULT NULL,
                                     `kalenderSort` tinyint(1) DEFAULT NULL,
                                     `kalenderPreSelect` tinyint(1) DEFAULT NULL,
                                     `kalenderAcl` int(11) DEFAULT NULL,
                                     `kalenderFerien` tinyint(1) DEFAULT 0,
                                     `kalenderPublic` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

CREATE TABLE `kalender_allInOne_eintrag` (
                                             `eintragID` int(11) NOT NULL,
                                             `kalenderID` int(11) NOT NULL,
                                             `eintragKategorieID` int(11) NOT NULL DEFAULT 0,
                                             `eintragTitel` varchar(255) NOT NULL,
                                             `eintragDatumStart` date NOT NULL,
                                             `eintragTimeStart` time NOT NULL,
                                             `eintragDatumEnde` date NOT NULL,
                                             `eintragTimeEnde` time NOT NULL,
                                             `eintragOrt` varchar(255) NOT NULL,
                                             `eintragKommentar` tinytext NOT NULL,
                                             `eintragUserID` int(11) NOT NULL,
                                             `eintragCreatedTime` datetime NOT NULL,
                                             `eintragModifiedTime` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

CREATE TABLE `kalender_allInOne_kategorie` (
                                               `kategorieID` int(11) NOT NULL,
                                               `kategorieKalenderID` int(11) NOT NULL,
                                               `kategorieName` varchar(255) NOT NULL,
                                               `kategorieFarbe` varchar(7) NOT NULL,
                                               `kategorieIcon` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

CREATE TABLE `kalender_ferien` (
                                   `ferienID` int(11) NOT NULL,
                                   `ferienName` mediumtext NOT NULL,
                                   `ferienStart` date NOT NULL,
                                   `ferienEnde` date NOT NULL,
                                   `ferienSchuljahr` varchar(7) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Alle Ferien in den nächsten Jahren' ROW_FORMAT=DYNAMIC;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

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
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

CREATE TABLE `klassen` (
                           `id` int(11) UNSIGNED NOT NULL,
                           `klassenname` varchar(50) DEFAULT NULL,
                           `klassenname_lang` varchar(50) DEFAULT NULL,
                           `klassenname_naechstes_schuljahr` varchar(50) DEFAULT NULL,
                           `klassenname_zeugnis` varchar(50) DEFAULT NULL,
                           `klassenart` varchar(50) DEFAULT NULL,
                           `ausgelagert` tinyint(1) DEFAULT NULL,
                           `aussenklasse` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `klassenleitung` (
                                  `klasseName` varchar(200) NOT NULL,
                                  `lehrerID` int(11) NOT NULL,
                                  `klassenleitungArt` int(11) NOT NULL DEFAULT 1
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

CREATE TABLE `klassentagebuch_fehl` (
                                        `fehlID` int(11) NOT NULL,
                                        `fehlDatum` date NOT NULL,
                                        `fehlStunde` int(11) NOT NULL,
                                        `fehlKlasse` varchar(100) NOT NULL,
                                        `fehlFach` varchar(100) NOT NULL,
                                        `fehlLehrer` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

CREATE TABLE `klassentagebuch_pdf` (
                                       `pdfKlasse` varchar(100) NOT NULL,
                                       `pdfJahr` int(11) NOT NULL,
                                       `pdfMonat` int(11) NOT NULL,
                                       `pdfUploadID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

CREATE TABLE `kms` (
                       `kmsID` int(11) NOT NULL,
                       `kmsAktenzeichen` varchar(255) DEFAULT NULL,
                       `kmsTitel` text DEFAULT NULL,
                       `kmsSchularten` int(11) DEFAULT NULL,
                       `kmsUploadID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

CREATE TABLE `kondolenzbuch` (
                                 `eintragID` int(11) NOT NULL,
                                 `eintragName` mediumtext NOT NULL,
                                 `eintragText` longtext NOT NULL,
                                 `eintragTime` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

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
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

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
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

CREATE TABLE `lehrer` (
                          `lehrerID` int(11) NOT NULL COMMENT 'XML ID aus ASV',
                          `lehrerAsvID` varchar(100) NOT NULL,
                          `lehrerKuerzel` varchar(100) NOT NULL,
                          `lehrerName` mediumtext NOT NULL,
                          `lehrerVornamen` mediumtext NOT NULL,
                          `lehrerRufname` mediumtext NOT NULL,
                          `lehrerGeschlecht` enum('w','m') NOT NULL,
                          `lehrerZeugnisunterschrift` mediumtext NOT NULL,
                          `lehrerAmtsbezeichnung` int(11) NOT NULL,
                          `lehrerUserID` int(11) NOT NULL DEFAULT 0,
                          `lehrerNameVorgestellt` varchar(255) NOT NULL,
                          `lehrerNameNachgestellt` varchar(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

CREATE TABLE `lerntutoren` (
                               `lerntutorID` int(11) NOT NULL,
                               `lerntutorSchuelerAsvID` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;

CREATE TABLE `lerntutoren_slots` (
                                     `slotID` int(11) NOT NULL,
                                     `slotLerntutorID` int(11) NOT NULL,
                                     `slotFach` varchar(255) NOT NULL,
                                     `slotJahrgangsstufe` varchar(255) NOT NULL,
                                     `slotSchuelerBelegt` varchar(255) DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;

CREATE TABLE `loginstat` (
                             `statTimestamp` timestamp NOT NULL DEFAULT current_timestamp(),
                             `statLoggedInTeachers` int(11) DEFAULT NULL,
                             `statLoggedInStudents` int(11) DEFAULT NULL,
                             `statLoggedInParents` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

CREATE TABLE `mail_change_requests` (
                                        `changeRequestID` int(11) NOT NULL,
                                        `changeRequestUserID` int(11) NOT NULL,
                                        `changeRequestTime` int(11) NOT NULL,
                                        `changeRequestSecret` text NOT NULL,
                                        `changeRequestNewMail` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;

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

CREATE TABLE `math_captcha` (
                                `captchaID` int(11) NOT NULL,
                                `captchaQuestion` varchar(100) NOT NULL,
                                `captchaSolution` int(11) NOT NULL,
                                `captchaSecret` varchar(5) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

CREATE TABLE `mebis_accounts` (
                                  `mebisAccountID` int(11) NOT NULL,
                                  `mebisAccountVorname` varchar(200) NOT NULL,
                                  `mebisAccountNachname` varchar(200) NOT NULL,
                                  `mebisAccountBenutzername` varchar(200) NOT NULL,
                                  `mebisAccountPasswort` varchar(200) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

CREATE TABLE `mensa_order` (
                               `id` int(11) NOT NULL,
                               `userID` int(11) DEFAULT NULL,
                               `speiseplanID` int(11) DEFAULT NULL,
                               `time` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `mensa_speiseplan` (
                                    `id` int(10) UNSIGNED NOT NULL,
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
                                    `regional` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `menu` (
                        `id` int(11) UNSIGNED NOT NULL,
                        `alias` varchar(100) DEFAULT NULL,
                        `title` varchar(100) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `menu` (`id`, `alias`, `title`)
VALUES (1,'main','Hauptmenü');

CREATE TABLE `menu_item` (
                             `id` int(11) UNSIGNED NOT NULL,
                             `active` tinyint(1) DEFAULT 0,
                             `menu_id` int(11) NOT NULL,
                             `parent_id` int(11) NOT NULL,
                             `sort` int(3) DEFAULT 0,
                             `page` varchar(100) DEFAULT '',
                             `title` varchar(100) NOT NULL DEFAULT '',
                             `icon` varchar(100) DEFAULT NULL,
                             `params` text DEFAULT NULL,
                             `access` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `menu_item` (`id`, `active`, `menu_id`, `parent_id`, `sort`, `page`, `title`, `icon`, `params`, `access`)
        VALUES
            (1,1,1,0,0,'','Aktuelles','fa fa-clock',NULL,NULL),
            (2,1,1,0,0,'','Informationen','fa fa-clock',NULL,NULL),
            (3,1,1,0,0,'','Lehreranwendungen','fa fa-graduation-cap',NULL,NULL),
            (4,1,1,0,0,'','Verwaltung','fa fas fa-pencil-alt-square',NULL,NULL),
            (5,1,1,0,0,'','Benutzeraccount / Nachrichten','fa fa-user',NULL,NULL),
            (6,1,1,0,0,'','Unterricht','fa fa-graduation-cap',NULL,NULL),
            (7,1,1,0,0,'','Administration','fa fa-cogs',NULL,NULL);


CREATE TABLE `messages_attachment` (
                                       `attachmentID` int(11) NOT NULL,
                                       `attachmentFileUploadID` int(11) NOT NULL,
                                       `attachmentAccessCode` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

CREATE TABLE `messages_folders` (
                                    `folderID` int(11) NOT NULL,
                                    `folderName` text NOT NULL,
                                    `folderUserID` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

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
                                     `messageIsForwardFrom` int(11) NOT NULL DEFAULT 0,
                                     `messageMyRecipientSaveString` varchar(255) DEFAULT NULL COMMENT 'In welchem Empfänger ist der Empfänger dieser Nachricht enthalten. (Wen betrifft es.)',
                                     `messageIsConfidential` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

CREATE TABLE `messages_questions` (
                                      `questionID` int(11) NOT NULL,
                                      `questionText` mediumtext NOT NULL,
                                      `questionType` enum('BOOLEAN','TEXT','NUMBER','FILE') NOT NULL DEFAULT 'TEXT',
                                      `questionUserID` int(11) NOT NULL,
                                      `questionSecret` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

CREATE TABLE `messages_questions_answers` (
                                              `answerID` int(11) NOT NULL,
                                              `answerQuestionID` int(11) NOT NULL,
                                              `answerMessageID` int(11) NOT NULL,
                                              `answerData` longtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

CREATE TABLE `modul_admin_notes` (
                                     `noteID` int(11) NOT NULL,
                                     `noteModuleName` varchar(255) NOT NULL,
                                     `noteText` text NOT NULL,
                                     `noteUserID` int(11) NOT NULL,
                                     `noteTime` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

CREATE TABLE `nextcloud_users` (
                                   `userID` int(11) NOT NULL COMMENT 'Same UserID as in SI',
                                   `nextcloudUsername` text NOT NULL,
                                   `userPasswordSet` int(11) NOT NULL DEFAULT 0,
                                   `userQuota` varchar(200) NOT NULL,
                                   `userGroups` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;

CREATE TABLE `noten_arbeiten` (
                                  `arbeitID` int(11) NOT NULL,
                                  `arbeitBereich` enum('SA','KA','EX','MDL') NOT NULL,
                                  `arbeitName` mediumtext NOT NULL,
                                  `arbeitLehrerKuerzel` varchar(10) NOT NULL,
                                  `arbeitIsMuendlich` tinyint(1) NOT NULL,
                                  `arbeitGewicht` decimal(4,2) NOT NULL DEFAULT 1.00,
                                  `arbeitFachKurzform` varchar(200) NOT NULL COMMENT 'Kurzform, nicMht ASD ID, da eigene Unterrichte erstellt sein könnten.',
                                  `arbeitDatum` date DEFAULT NULL,
                                  `arbeitUnterrichtName` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

CREATE TABLE `noten_bemerkung_textvorlagen` (
                                                `bemerkungID` int(11) NOT NULL,
                                                `bemerkungGruppeID` int(11) NOT NULL,
                                                `bemerkungNote` int(11) NOT NULL DEFAULT 0,
                                                `bemerkungTextWeiblich` longtext NOT NULL,
                                                `bemerkungTextMaennlich` longtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

CREATE TABLE `noten_bemerkung_textvorlagen_gruppen` (
                                                        `gruppeID` int(11) NOT NULL,
                                                        `gruppeName` mediumtext NOT NULL,
                                                        `koppelMVNote` enum('M','V') DEFAULT 'M'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

CREATE TABLE `noten_fach_einstellungen` (
                                            `fachKurzform` varchar(100) NOT NULL,
                                            `fachIsVorrueckungsfach` tinyint(1) NOT NULL,
                                            `fachOrder` int(11) NOT NULL,
                                            `fachNoteZusammenMit` mediumtext NOT NULL COMMENT 'Fachkurzformen der Fächer, die mit diesem Fach zusammen verrechnet werden. Aktuelles Fach wird als Hauptfach angezeigt. Getrennt durch Komma.'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

CREATE TABLE `noten_gewichtung` (
                                    `fachKuerzel` varchar(100) NOT NULL,
                                    `fachJahrgangsstufe` int(11) NOT NULL,
                                    `fachGewichtKlein` int(11) NOT NULL DEFAULT 1,
                                    `fachGewichtGross` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

CREATE TABLE `noten_mv` (
                            `mvFachKurzform` varchar(200) NOT NULL,
                            `mvUnterrichtName` varchar(200) NOT NULL,
                            `mNote` int(11) NOT NULL,
                            `vNote` int(11) NOT NULL,
                            `schuelerAsvID` varchar(100) NOT NULL,
                            `zeugnisID` int(11) NOT NULL,
                            `noteKommentar` mediumtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

CREATE TABLE `noten_noten` (
                               `noteSchuelerAsvID` varchar(20) NOT NULL,
                               `noteWert` int(11) NOT NULL,
                               `noteTendenz` int(11) NOT NULL,
                               `noteArbeitID` int(11) NOT NULL,
                               `noteDatum` date DEFAULT NULL,
                               `noteKommentar` longtext NOT NULL,
                               `noteIsNachtermin` tinyint(1) NOT NULL DEFAULT 0,
                               `noteNurWennBesser` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

CREATE TABLE `noten_verrechnung` (
                                     `verrechnungID` int(11) NOT NULL,
                                     `verrechnungFach` varchar(255) NOT NULL,
                                     `verrechnungUnterricht1` varchar(255) NOT NULL,
                                     `verrechnungUnterricht2` varchar(100) NOT NULL,
                                     `verrechnungGewicht1` int(11) NOT NULL,
                                     `verrechnungGewicht2` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

CREATE TABLE `noten_wahlfach_faecher` (
                                          `wahlfachID` int(11) NOT NULL,
                                          `zeugnisID` int(11) NOT NULL,
                                          `fachKurzform` varchar(100) NOT NULL,
                                          `fachUnterrichtName` varchar(100) NOT NULL,
                                          `wahlfachBezeichnung` mediumtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

CREATE TABLE `noten_wahlfach_noten` (
                                        `wahlfachID` int(11) NOT NULL,
                                        `schuelerAsvID` varchar(100) NOT NULL,
                                        `wahlfachNote` int(11) NOT NULL DEFAULT 4
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

CREATE TABLE `noten_zeugnisse` (
                                   `zeugnisID` int(11) NOT NULL,
                                   `zeugnisArt` enum('ZZ','JZ','NB','ABZ','SEMZ','ABIZ') NOT NULL,
                                   `zeugnisName` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

CREATE TABLE `noten_zeugnisse_klassen` (
                                           `zeugnisID` int(11) NOT NULL,
                                           `zeugnisKlasse` varchar(20) NOT NULL,
                                           `zeugnisDatum` date NOT NULL,
                                           `zeugnisNotenschluss` date NOT NULL,
                                           `zeugnisUnterschriftKlassenleitungAsvID` varchar(20) NOT NULL,
                                           `zeugnisUnterschriftSchulleitungAsvID` varchar(20) NOT NULL,
                                           `zeugnisUnterschriftKlassenleitungAsvIDGezeichnet` tinyint(1) NOT NULL DEFAULT 0,
                                           `zeugnisUnterschriftSchulleitungAsvIDGezeichnet` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

CREATE TABLE `noten_zeugnisse_noten` (
                                         `noteSchuelerAsvID` varchar(100) NOT NULL,
                                         `noteZeugnisID` int(11) NOT NULL,
                                         `noteFachKurzform` varchar(100) NOT NULL,
                                         `noteWert` int(11) NOT NULL,
                                         `noteIsPaed` tinyint(1) NOT NULL DEFAULT 0,
                                         `notePaedBegruendung` mediumtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

CREATE TABLE `noten_zeugnis_bemerkung` (
                                           `bemerkungSchuelerAsvID` varchar(100) NOT NULL,
                                           `bemerkungZeugnisID` int(11) NOT NULL,
                                           `bemerkungText1` longtext NOT NULL,
                                           `bemerkungText2` longtext NOT NULL,
                                           `klassenzielErreicht` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

CREATE TABLE `noten_zeugnis_exemplar` (
                                          `zeugnisID` int(11) NOT NULL,
                                          `schuelerAsvID` varchar(100) NOT NULL,
                                          `uploadID` int(11) NOT NULL,
                                          `createdTime` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

CREATE TABLE `projekt_lehrer2grade` (
                                        `lehrerUserID` int(11) NOT NULL,
                                        `gradeName` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

CREATE TABLE `projekt_projekte` (
                                    `userID` varchar(100) NOT NULL,
                                    `projektName` mediumtext NOT NULL,
                                    `projektErfolg` varchar(255) NOT NULL,
                                    `projektFach1` varchar(100) NOT NULL,
                                    `projektFach2` varchar(100) NOT NULL,
                                    `projektLehrer1` varchar(100) NOT NULL,
                                    `projektLehrer2` varchar(100) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

CREATE TABLE `raumplan_stunden` (
                                    `stundeID` int(11) UNSIGNED NOT NULL,
                                    `stundenplanID` int(11) DEFAULT NULL,
                                    `stundeKlasse` varchar(20) DEFAULT NULL,
                                    `stundeLehrer` varchar(20) DEFAULT NULL,
                                    `stundeFach` varchar(20) DEFAULT NULL,
                                    `stundeRaum` varchar(20) DEFAULT NULL,
                                    `stundeDatum` date DEFAULT NULL,
                                    `stundeStunde` int(2) DEFAULT NULL,
                                    `createdBy` int(11) DEFAULT NULL,
                                    `createdTime` datetime DEFAULT NULL ON UPDATE current_timestamp(),
                                    `state` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

CREATE TABLE `resetpassword` (
                                 `resetID` int(11) NOT NULL,
                                 `resetUserID` int(11) NOT NULL,
                                 `resetNewPasswordHash` varchar(200) NOT NULL,
                                 `resetCode` varchar(200) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

CREATE TABLE `respizienz` (
                              `respizienzID` int(11) NOT NULL,
                              `respizienzFile` mediumtext NOT NULL,
                              `respizienzFSLFile` mediumtext NOT NULL,
                              `respizientSLFile` mediumtext NOT NULL,
                              `respizienzIsAnalog` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

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
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

CREATE TABLE `schaukasten_inhalt` (
                                      `schaukastenID` int(11) NOT NULL,
                                      `schaukastenPosition` int(11) NOT NULL,
                                      `schaukastenContent` varchar(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

CREATE TABLE `schaukasten_powerpoint` (
                                          `powerpointID` int(11) NOT NULL,
                                          `uploadID` int(11) NOT NULL,
                                          `lastUpdate` int(11) NOT NULL,
                                          `powerpointName` mediumtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

CREATE TABLE `schaukasten_website` (
                                       `websiteID` int(11) NOT NULL,
                                       `websiteURL` mediumtext NOT NULL,
                                       `websiteName` mediumtext NOT NULL,
                                       `websiteLastUpdate` int(11) NOT NULL,
                                       `websiteRefreshSeconds` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

CREATE TABLE `schueler` (
                            `schuelerAsvID` varchar(200) NOT NULL,
                            `schuelerName` text NOT NULL,
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
                            `schuelerNameVorgestellt` varchar(255) NOT NULL,
                            `schuelerNameNachgestellt` varchar(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

CREATE TABLE `schuelerinfo_dokumente` (
                                          `dokumentID` int(11) NOT NULL,
                                          `dokumentSchuelerAsvID` varchar(200) NOT NULL,
                                          `dokumentName` varchar(255) NOT NULL,
                                          `dokumentKommentar` mediumtext NOT NULL,
                                          `dokumentUploadID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

CREATE TABLE `schueler_fremdsprache` (
                                         `schuelerAsvID` varchar(100) NOT NULL,
                                         `spracheSortierung` int(11) NOT NULL,
                                         `spracheAbJahrgangsstufe` varchar(10) NOT NULL,
                                         `spracheFach` mediumtext NOT NULL,
                                         `spracheFeststellungspruefung` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

CREATE TABLE `schueler_nachteilsausgleich` (
                                               `schuelerAsvID` varchar(100) NOT NULL,
                                               `artStoerung` enum('rs','lrs','ls') NOT NULL,
                                               `arbeitszeitverlaengerung` varchar(255) NOT NULL,
                                               `notenschutz` tinyint(1) NOT NULL,
                                               `kommentar` mediumtext NOT NULL,
                                               `gueltigBis` date DEFAULT NULL,
                                               `gewichtung` enum('11','12','21') DEFAULT '12'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

CREATE TABLE `schueler_quarantaene` (
                                        `quarantaeneID` int(11) NOT NULL,
                                        `quarantaeneSchuelerAsvID` varchar(200) NOT NULL,
                                        `quarantaeneStart` date DEFAULT NULL,
                                        `quarantaeneEnde` date DEFAULT NULL,
                                        `quarantaeneArt` enum('I','K1','S') NOT NULL DEFAULT 'S',
                                        `quarantaeneKommentar` text NOT NULL,
                                        `quarantaeneCreatedByUserID` int(11) NOT NULL,
                                        `quarantaeneFileUpload` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

CREATE TABLE `schulbuch_buecher` (
                                     `buchID` int(11) NOT NULL,
                                     `buchTitel` mediumtext NOT NULL,
                                     `buchVerlag` mediumtext NOT NULL,
                                     `buchISBN` varchar(20) NOT NULL,
                                     `buchPreis` int(11) NOT NULL COMMENT 'preis in Cent',
                                     `buchFach` varchar(200) NOT NULL,
                                     `buchKlasse` int(11) NOT NULL,
                                     `buchErfasserUserID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

CREATE TABLE `schulbuch_exemplare` (
                                       `exemplarID` int(11) NOT NULL,
                                       `exemplarBuchID` int(11) NOT NULL,
                                       `exemplarBarcode` varchar(200) NOT NULL,
                                       `exemplarZustand` int(11) NOT NULL DEFAULT 0,
                                       `exemplarAnschaffungsjahr` varchar(5) NOT NULL,
                                       `exemplarIsBankbuch` tinyint(1) NOT NULL DEFAULT 0,
                                       `exemplarLagerort` mediumtext NOT NULL,
                                       `exemplarErfasserUserID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

CREATE TABLE `schulen` (
                           `schuleID` int(11) NOT NULL,
                           `schuleNummer` varchar(255) NOT NULL,
                           `schuleArt` varchar(255) NOT NULL,
                           `schuleName` mediumtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

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
                            `sessionStore` longtext DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

CREATE TABLE `settings` (
                            `settingsExtension` varchar(100) NOT NULL,
                            `settingName` varchar(100) NOT NULL,
                            `settingValue` mediumtext NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

CREATE TABLE `settings_history` (
                                    `settingHistoryID` int(11) NOT NULL,
                                    `settingHistoryName` varchar(255) NOT NULL,
                                    `settingHistoryChangeTime` int(11) NOT NULL,
                                    `settingHistoryOldValue` mediumtext NOT NULL,
                                    `settingHistoryNewValue` mediumtext NOT NULL,
                                    `settingHistoryUserID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

CREATE TABLE `site_activation` (
                                   `siteName` varchar(200) NOT NULL,
                                   `siteIsActive` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

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
                             `sprechtagBeginTime` int(11) NOT NULL,
                             `sprechtagInfotext` longtext NOT NULL,
                             `sprechtagIsOnline` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

CREATE TABLE `sprechtag_buchungen` (
                                       `buchungID` int(11) NOT NULL,
                                       `lehrerKuerzel` varchar(100) NOT NULL,
                                       `sprechtagID` int(11) NOT NULL,
                                       `slotID` int(11) NOT NULL,
                                       `isBuchbar` int(11) NOT NULL,
                                       `schuelerAsvID` varchar(100) NOT NULL,
                                       `elternUserID` int(11) NOT NULL,
                                       `meetingURL` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

CREATE TABLE `sprechtag_raeume` (
                                    `sprechtagID` int(11) NOT NULL,
                                    `lehrerKuerzel` varchar(200) NOT NULL,
                                    `raumName` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

CREATE TABLE `sprechtag_slots` (
                                   `slotID` int(11) NOT NULL,
                                   `sprechtagID` int(11) NOT NULL,
                                   `slotStart` int(11) NOT NULL,
                                   `slotEnde` int(11) NOT NULL,
                                   `slotIsPause` tinyint(1) NOT NULL DEFAULT 0,
                                   `slotIsOnlineBuchbar` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

CREATE TABLE `stundenplan_aufsichten` (
                                          `aufsichtID` int(11) NOT NULL,
                                          `stundenplanID` int(11) NOT NULL,
                                          `aufsichtBereich` mediumtext NOT NULL,
                                          `aufsichtVorStunde` int(11) NOT NULL,
                                          `aufsichtTag` int(11) NOT NULL,
                                          `aufsichtLehrerKuerzel` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

CREATE TABLE `stundenplan_plaene` (
                                      `stundenplanID` int(11) NOT NULL,
                                      `stundenplanAb` date DEFAULT NULL,
                                      `stundenplanBis` date DEFAULT NULL,
                                      `stundenplanUploadUserID` int(11) NOT NULL,
                                      `stundenplanName` varchar(255) NOT NULL,
                                      `stundenplanIsDeleted` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

CREATE TABLE `stundenplan_stunden` (
                                       `stundeID` int(11) NOT NULL,
                                       `stundenplanID` int(11) NOT NULL,
                                       `stundeKlasse` varchar(20) NOT NULL,
                                       `stundeLehrer` varchar(20) CHARACTER SET utf8 COLLATE utf8_german2_ci NOT NULL,
                                       `stundeFach` varchar(20) NOT NULL,
                                       `stundeRaum` varchar(20) NOT NULL,
                                       `stundeTag` int(11) NOT NULL,
                                       `stundeStunde` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

CREATE TABLE `templates` (
                             `templateName` varchar(200) NOT NULL,
                             `templateCompiledContents` longtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

CREATE TABLE `trenndaten` (
                              `trennWort` varchar(255) NOT NULL,
                              `trennWortGetrennt` varchar(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

CREATE TABLE `tutoren` (
                           `tutorenID` int(11) NOT NULL,
                           `status` varchar(100) DEFAULT NULL,
                           `created` date DEFAULT NULL,
                           `tutorenTutorAsvID` varchar(100) DEFAULT NULL,
                           `fach` varchar(100) DEFAULT NULL,
                           `jahrgang` varchar(100) DEFAULT NULL,
                           `einheiten` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;

CREATE TABLE `tutoren_slots` (
                                 `slotID` int(11) NOT NULL,
                                 `slotTutorenID` int(11) NOT NULL,
                                 `slotStatus` varchar(255) NOT NULL,
                                 `slotSchuelerAsvID` varchar(100) NOT NULL,
                                 `slotEinheiten` int(11) NOT NULL,
                                 `slotCreated` date DEFAULT NULL,
                                 `slotDatum` varchar(255) DEFAULT '',
                                 `slotDauer` varchar(255) DEFAULT '',
                                 `slotInfo` text DEFAULT NULL,
                                 `slotDates` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;

CREATE TABLE `two_factor_trusted_devices` (
                                              `deviceID` int(11) NOT NULL,
                                              `deviceCookieData` mediumtext NOT NULL,
                                              `deviceUserID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

CREATE TABLE `unknown_mails` (
                                 `mailID` int(11) NOT NULL,
                                 `mailSubject` mediumtext NOT NULL,
                                 `mailText` longtext NOT NULL,
                                 `mailSender` mediumtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

CREATE TABLE `unterricht` (
                              `unterrichtID` int(11) NOT NULL COMMENT 'Aus ASV Export File',
                              `unterrichtElementASVID` varchar(200) DEFAULT NULL,
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
                              `unterrichtKlassen` varchar(255) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

CREATE TABLE `unterricht_besuch` (
                                     `unterrichtID` int(11) NOT NULL,
                                     `schuelerAsvID` varchar(200) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

CREATE TABLE `uploads` (
                           `uploadID` int(11) NOT NULL,
                           `uploadFileName` mediumtext NOT NULL,
                           `uploadFileExtension` varchar(50) NOT NULL,
                           `uploadFileMimeType` varchar(200) NOT NULL,
                           `uploadTime` int(11) NOT NULL,
                           `uploaderUserID` int(11) NOT NULL,
                           `fileAccessCode` varchar(222) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

CREATE TABLE `users_groups` (
                                `userID` int(11) NOT NULL,
                                `groupName` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

CREATE TABLE `users_groups_own` (
                                    `groupName` varchar(255) NOT NULL,
                                    `groupIsMessageRecipient` tinyint(1) NOT NULL,
                                    `groupContactTeacher` tinyint(1) NOT NULL,
                                    `groupContactPupil` tinyint(1) NOT NULL,
                                    `groupContactParents` int(11) NOT NULL,
                                    `groupNextCloudUserID` int(11) NOT NULL DEFAULT 0,
                                    `groupHasNextcloudShare` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

CREATE TABLE `user_settings` (
                                 `userID` int(11) NOT NULL,
                                 `skinColor` enum('blue','black','purple','yellow','red','green') NOT NULL DEFAULT 'green',
                                 `startPage` enum('aufeinenblick','vplan','stundenplan') NOT NULL DEFAULT 'aufeinenblick'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED;

CREATE TABLE `vplan` (
                         `vplanName` varchar(20) NOT NULL,
                         `vplanDate` text NOT NULL,
                         `vplanContent` longtext NOT NULL,
                         `vplanUpdate` varchar(200) NOT NULL,
                         `vplanInfo` mediumtext NOT NULL,
                         `vplanContentUncensored` longtext NOT NULL,
                         `schaukastenViewKey` text NOT NULL,
                         `vplanUpdateTime` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

CREATE TABLE `widgets` (
                           `id` int(11) UNSIGNED NOT NULL,
                           `uniqid` varchar(100) DEFAULT NULL,
                           `position` varchar(100) DEFAULT NULL,
                           `access` varchar(255) DEFAULT NULL,
                           `params` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `wlan_ticket` (
                               `ticketID` int(11) NOT NULL,
                               `ticketType` enum('GAST','SCHUELER') NOT NULL,
                               `ticketText` mediumtext NOT NULL,
                               `ticketAssignedTo` int(11) NOT NULL,
                               `ticketValidMinutes` int(11) NOT NULL,
                               `ticketAssignedDate` varchar(255) DEFAULT NULL,
                               `ticketAssignedBy` int(11) NOT NULL,
                               `ticketName` varchar(255) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;


ALTER TABLE `absenzen_absenzen`
    ADD PRIMARY KEY (`absenzID`),
  ADD KEY `absenzDatum` (`absenzDatum`) USING BTREE,
  ADD KEY `absenzSchuelerAsvID` (`absenzSchuelerAsvID`) USING BTREE;

ALTER TABLE `absenzen_attestpflicht`
    ADD PRIMARY KEY (`attestpflichtID`),
  ADD KEY `attestpflichtStart` (`attestpflichtStart`,`attestpflichtEnde`) USING BTREE,
  ADD KEY `schuelerAsvID` (`schuelerAsvID`) USING BTREE;

ALTER TABLE `absenzen_befreiungen`
    ADD PRIMARY KEY (`befreiungID`);

ALTER TABLE `absenzen_beurlaubungen`
    ADD PRIMARY KEY (`beurlaubungID`);

ALTER TABLE `absenzen_beurlaubung_antrag`
    ADD PRIMARY KEY (`antragID`);

ALTER TABLE `absenzen_comments`
    ADD PRIMARY KEY (`schuelerAsvID`);

ALTER TABLE `absenzen_krankmeldungen`
    ADD PRIMARY KEY (`krankmeldungID`),
  ADD KEY `krankmeldungAbsenzID` (`krankmeldungAbsenzID`) USING BTREE,
  ADD KEY `krankmeldungDate` (`krankmeldungDate`) USING BTREE,
  ADD KEY `krankmeldungElternID` (`krankmeldungElternID`) USING BTREE,
  ADD KEY `krankmeldungSchuelerASVID` (`krankmeldungSchuelerASVID`) USING BTREE;

ALTER TABLE `absenzen_meldung`
    ADD PRIMARY KEY (`meldungDatum`,`meldungKlasse`);

ALTER TABLE `absenzen_merker`
    ADD PRIMARY KEY (`merkerID`);

ALTER TABLE `absenzen_sanizimmer`
    ADD PRIMARY KEY (`sanizimmerID`),
  ADD KEY `sanizimmerSchuelerAsvID` (`sanizimmerSchuelerAsvID`) USING BTREE;

ALTER TABLE `absenzen_verspaetungen`
    ADD PRIMARY KEY (`verspaetungID`);

ALTER TABLE `acl`
    ADD PRIMARY KEY (`id`),
  ADD KEY `moduleClass` (`moduleClass`) USING BTREE,
  ADD KEY `moduleClassParent` (`moduleClassParent`) USING BTREE;

ALTER TABLE `amtsbezeichnungen`
    ADD PRIMARY KEY (`amtsbezeichnungID`);

ALTER TABLE `andere_kalender`
    ADD PRIMARY KEY (`kalenderID`);

ALTER TABLE `andere_kalender_kategorie`
    ADD PRIMARY KEY (`kategorieID`);

ALTER TABLE `anschrifttyp`
    ADD PRIMARY KEY (`anschriftTypID`);

ALTER TABLE `aufeinenblick_settings`
    ADD PRIMARY KEY (`aufeinenblickSettingsID`),
  ADD KEY `aufeinenblickUserID` (`aufeinenblickUserID`) USING BTREE;

ALTER TABLE `ausleihe_ausleihe`
    ADD PRIMARY KEY (`ausleiheID`),
  ADD KEY `ausleiheObjektID` (`ausleiheObjektID`) USING BTREE;

ALTER TABLE `ausleihe_objekte`
    ADD PRIMARY KEY (`objektID`);

ALTER TABLE `ausweise`
    ADD PRIMARY KEY (`ausweisID`),
  ADD KEY `ausweisArt` (`ausweisArt`) USING BTREE,
  ADD KEY `ausweisBezahlt` (`ausweisBezahlt`) USING BTREE,
  ADD KEY `ausweisErsteller` (`ausweisErsteller`) USING BTREE,
  ADD KEY `ausweisStatus` (`ausweisStatus`) USING BTREE;

ALTER TABLE `bad_mail`
    ADD PRIMARY KEY (`badMailID`),
  ADD KEY `badMailDone` (`badMailDone`) USING BTREE;

ALTER TABLE `beobachtungsbogen_boegen`
    ADD PRIMARY KEY (`beobachtungsbogenID`);

ALTER TABLE `beobachtungsbogen_eintragungsfrist`
    ADD PRIMARY KEY (`beobachtungsbogenID`,`userID`);

ALTER TABLE `beobachtungsbogen_fragen`
    ADD PRIMARY KEY (`frageID`);

ALTER TABLE `beobachtungsbogen_fragen_daten`
    ADD PRIMARY KEY (`frageID`,`schuelerID`,`lehrerKuerzel`,`fachName`);

ALTER TABLE `beobachtungsbogen_klassenleitung`
    ADD PRIMARY KEY (`beobachtungsbogenID`,`klassenName`,`klassenleitungTyp`);

ALTER TABLE `beobachtungsbogen_klasse_fach_lehrer`
    ADD PRIMARY KEY (`beobachtungsbogenID`,`klasseName`,`fachName`,`lehrerKuerzel`);

ALTER TABLE `beobachtungsbogen_schueler_namen`
    ADD PRIMARY KEY (`beobachtungsbogenID`,`schuelerID`);

ALTER TABLE `beurlaubung_antrag`
    ADD PRIMARY KEY (`antragID`),
  ADD KEY `antragAbsenzID` (`antragAbsenzID`) USING BTREE,
  ADD KEY `antragSchuelerAsvID` (`antragSchuelerAsvID`) USING BTREE,
  ADD KEY `antragUserID` (`antragUserID`) USING BTREE;

ALTER TABLE `cron_execution`
    ADD PRIMARY KEY (`cronRunID`) USING BTREE,
  ADD KEY `cronName` (`cronName`);

ALTER TABLE `dashboard`
    ADD PRIMARY KEY (`id`),
  ADD KEY `uniqid` (`uniqid`);

ALTER TABLE `datenschutz_erklaerung`
    ADD PRIMARY KEY (`userID`) USING BTREE;

ALTER TABLE `dokumente_dateien`
    ADD PRIMARY KEY (`dateiID`) USING BTREE,
  ADD KEY `gruppenID` (`gruppenID`);

ALTER TABLE `dokumente_gruppen`
    ADD PRIMARY KEY (`gruppenID`) USING BTREE;

ALTER TABLE `dokumente_kategorien`
    ADD PRIMARY KEY (`kategorieID`) USING BTREE;

ALTER TABLE `eltern_adressen`
    ADD PRIMARY KEY (`adresseID`) USING BTREE,
  ADD KEY `adresseSchuelerAsvID` (`adresseSchuelerAsvID`);

ALTER TABLE `eltern_codes`
    ADD PRIMARY KEY (`codeID`) USING BTREE,
  ADD KEY `codeSchuelerAsvID` (`codeSchuelerAsvID`);

ALTER TABLE `eltern_email`
    ADD PRIMARY KEY (`elternEMail`,`elternSchuelerAsvID`) USING BTREE,
  ADD KEY `elternUserID` (`elternUserID`);

ALTER TABLE `eltern_register`
    ADD PRIMARY KEY (`registerID`) USING BTREE;

ALTER TABLE `eltern_telefon`
    ADD PRIMARY KEY (`telefonNummer`,`schuelerAsvID`,`adresseID`) USING BTREE,
  ADD KEY `telefonNummer` (`telefonNummer`,`schuelerAsvID`,`telefonTyp`) USING BTREE,
  ADD KEY `telefonNummer_2` (`telefonNummer`,`schuelerAsvID`) USING BTREE;

ALTER TABLE `eltern_to_schueler`
    ADD PRIMARY KEY (`elternUserID`,`schuelerUserID`) USING BTREE;

ALTER TABLE `email_addresses`
    ADD PRIMARY KEY (`userID`) USING BTREE;

ALTER TABLE `extensions`
    ADD PRIMARY KEY (`id`);

ALTER TABLE `externe_kalender`
    ADD PRIMARY KEY (`kalenderID`) USING BTREE;

ALTER TABLE `externe_kalender_kategorien`
    ADD PRIMARY KEY (`kalenderID`,`kategorieName`) USING BTREE;

ALTER TABLE `faecher`
    ADD PRIMARY KEY (`fachID`) USING BTREE;

ALTER TABLE `fremdlogin`
    ADD PRIMARY KEY (`fremdloginID`);

ALTER TABLE `ganztags_events`
    ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`);

ALTER TABLE `ganztags_gruppen`
    ADD PRIMARY KEY (`id`) USING BTREE;

ALTER TABLE `ganztags_schueler`
    ADD PRIMARY KEY (`asvid`) USING BTREE,
  ADD KEY `gruppe` (`gruppe`);

ALTER TABLE `icsfeeds`
    ADD PRIMARY KEY (`feedID`) USING BTREE,
  ADD KEY `feedKey2` (`feedKey2`),
  ADD KEY `feedKey` (`feedKey`),
  ADD KEY `feedType` (`feedType`),
  ADD KEY `feedUserID` (`feedUserID`);

ALTER TABLE `image_uploads`
    ADD PRIMARY KEY (`uploadID`) USING BTREE,
  ADD KEY `uploadUserName` (`uploadUserName`);

ALTER TABLE `initialpasswords`
    ADD PRIMARY KEY (`initialPasswordID`) USING BTREE,
  ADD KEY `initialPasswordUserID` (`initialPasswordUserID`),
  ADD KEY `passwordPrinted` (`passwordPrinted`);

ALTER TABLE `kalender_allInOne_eintrag`
    ADD PRIMARY KEY (`eintragID`),
  ADD UNIQUE KEY `eintragID` (`eintragID`);

ALTER TABLE `kalender_andere`
    ADD PRIMARY KEY (`eintragID`) USING BTREE,
  ADD KEY `eintragDatumStart` (`eintragDatumStart`,`eintragDatumEnde`),
  ADD KEY `kalenderID` (`kalenderID`);

ALTER TABLE `kalender_extern`
    ADD PRIMARY KEY (`eintragID`) USING BTREE,
  ADD KEY `eintragDatumStart` (`eintragDatumStart`,`eintragDatumEnde`),
  ADD KEY `kalenderID` (`kalenderID`);

ALTER TABLE `kalender_ferien`
    ADD PRIMARY KEY (`ferienID`) USING BTREE,
  ADD KEY `ferienStart` (`ferienStart`,`ferienEnde`);

ALTER TABLE `kalender_klassentermin`
    ADD PRIMARY KEY (`eintragID`) USING BTREE,
  ADD KEY `eintragDatumStart` (`eintragDatumStart`,`eintragDatumEnde`);

ALTER TABLE `kalender_lnw`
    ADD PRIMARY KEY (`eintragID`) USING BTREE,
  ADD KEY `eintragArt` (`eintragArt`),
  ADD KEY `eintragDatumStart` (`eintragDatumStart`,`eintragDatumEnde`),
  ADD KEY `eintragKlasse` (`eintragKlasse`),
  ADD KEY `eintragLehrer` (`eintragLehrer`);

ALTER TABLE `klassen`
    ADD PRIMARY KEY (`id`);

ALTER TABLE `klassenleitung`
    ADD PRIMARY KEY (`klasseName`,`lehrerID`) USING BTREE;

ALTER TABLE `klassentagebuch_fehl`
    ADD PRIMARY KEY (`fehlID`) USING BTREE,
  ADD KEY `fehlDatum` (`fehlDatum`),
  ADD KEY `fehlKlasse` (`fehlKlasse`),
  ADD KEY `fehlLehrer` (`fehlLehrer`);

ALTER TABLE `klassentagebuch_klassen`
    ADD PRIMARY KEY (`entryID`) USING BTREE,
  ADD KEY `entryGrade` (`entryGrade`),
  ADD KEY `entryTeacher` (`entryTeacher`);

ALTER TABLE `klassentagebuch_pdf`
    ADD PRIMARY KEY (`pdfKlasse`,`pdfJahr`,`pdfMonat`) USING BTREE;

ALTER TABLE `kondolenzbuch`
    ADD PRIMARY KEY (`eintragID`) USING BTREE;

ALTER TABLE `laufzettel`
    ADD PRIMARY KEY (`laufzettelID`) USING BTREE,
  ADD KEY `laufzettelDatum` (`laufzettelDatum`),
  ADD KEY `laufzettelErsteller` (`laufzettelErsteller`);

ALTER TABLE `laufzettel_stunden`
    ADD PRIMARY KEY (`laufzettelStundeID`) USING BTREE,
  ADD KEY `laufzettelID` (`laufzettelID`),
  ADD KEY `laufzettelLehrer` (`laufzettelLehrer`);

ALTER TABLE `lehrer`
    ADD PRIMARY KEY (`lehrerAsvID`) USING BTREE,
  ADD KEY `lehrerID` (`lehrerID`),
  ADD KEY `lehrerKuerzel` (`lehrerKuerzel`),
  ADD KEY `lehrerUserID` (`lehrerUserID`);

ALTER TABLE `lerntutoren`
    ADD PRIMARY KEY (`lerntutorID`) USING BTREE,
  ADD KEY `lerntutorSchuelerAsvID` (`lerntutorSchuelerAsvID`);

ALTER TABLE `lerntutoren_slots`
    ADD PRIMARY KEY (`slotID`) USING BTREE;

ALTER TABLE `loginstat`
    ADD PRIMARY KEY (`statTimestamp`) USING BTREE;

ALTER TABLE `mail_send`
    ADD PRIMARY KEY (`mailID`) USING BTREE,
  ADD KEY `mailSent` (`mailSent`,`mailCrawler`);

ALTER TABLE `math_captcha`
    ADD PRIMARY KEY (`captchaID`) USING BTREE;

ALTER TABLE `mebis_accounts`
    ADD PRIMARY KEY (`mebisAccountID`) USING BTREE,
  ADD KEY `mebisAccountNachname` (`mebisAccountNachname`),
  ADD KEY `mebisAccountVorname` (`mebisAccountVorname`);

ALTER TABLE `mensa_speiseplan`
    ADD PRIMARY KEY (`id`) USING BTREE;

ALTER TABLE `menu`
    ADD PRIMARY KEY (`id`);

ALTER TABLE `menu_item`
    ADD PRIMARY KEY (`id`);

ALTER TABLE `messages_attachment`
    ADD PRIMARY KEY (`attachmentID`) USING BTREE;

ALTER TABLE `messages_folders`
    ADD PRIMARY KEY (`folderID`) USING BTREE,
  ADD KEY `folderUserID` (`folderUserID`);

ALTER TABLE `messages_messages`
    ADD PRIMARY KEY (`messageID`) USING BTREE,
  ADD KEY `messagesKey` (`messageUserID`,`messageSender`,`messageFolder`,`messageFolderID`,`messageIsRead`,`messageIsDeleted`) USING BTREE;

ALTER TABLE `messages_questions`
    ADD PRIMARY KEY (`questionID`) USING BTREE,
  ADD KEY `questionUserID` (`questionUserID`);

ALTER TABLE `messages_questions_answers`
    ADD PRIMARY KEY (`answerID`) USING BTREE,
  ADD KEY `answerMessageID` (`answerMessageID`),
  ADD KEY `answerQuestionID` (`answerQuestionID`);

ALTER TABLE `modul_admin_notes`
    ADD PRIMARY KEY (`noteID`) USING BTREE,
  ADD KEY `noteModuleName` (`noteModuleName`);

ALTER TABLE `nextcloud_users`
    ADD PRIMARY KEY (`userID`) USING BTREE;

ALTER TABLE `noten_arbeiten`
    ADD PRIMARY KEY (`arbeitID`) USING BTREE;

ALTER TABLE `noten_bemerkung_textvorlagen`
    ADD PRIMARY KEY (`bemerkungID`) USING BTREE;

ALTER TABLE `noten_bemerkung_textvorlagen_gruppen`
    ADD PRIMARY KEY (`gruppeID`) USING BTREE;

ALTER TABLE `noten_fach_einstellungen`
    ADD PRIMARY KEY (`fachKurzform`) USING BTREE;

ALTER TABLE `noten_gewichtung`
    ADD PRIMARY KEY (`fachKuerzel`,`fachJahrgangsstufe`) USING BTREE;

ALTER TABLE `noten_mv`
    ADD PRIMARY KEY (`mvFachKurzform`,`mvUnterrichtName`,`schuelerAsvID`,`zeugnisID`) USING BTREE;

ALTER TABLE `noten_noten`
    ADD PRIMARY KEY (`noteSchuelerAsvID`,`noteArbeitID`) USING BTREE;

ALTER TABLE `noten_verrechnung`
    ADD PRIMARY KEY (`verrechnungID`) USING BTREE;

ALTER TABLE `noten_wahlfach_faecher`
    ADD PRIMARY KEY (`wahlfachID`) USING BTREE;

ALTER TABLE `noten_wahlfach_noten`
    ADD PRIMARY KEY (`wahlfachID`,`schuelerAsvID`) USING BTREE;

ALTER TABLE `noten_zeugnisse`
    ADD PRIMARY KEY (`zeugnisID`) USING BTREE;

ALTER TABLE `noten_zeugnisse_klassen`
    ADD PRIMARY KEY (`zeugnisID`,`zeugnisKlasse`) USING BTREE;

ALTER TABLE `noten_zeugnisse_noten`
    ADD PRIMARY KEY (`noteSchuelerAsvID`,`noteZeugnisID`,`noteFachKurzform`) USING BTREE;

ALTER TABLE `noten_zeugnis_bemerkung`
    ADD PRIMARY KEY (`bemerkungSchuelerAsvID`,`bemerkungZeugnisID`) USING BTREE;

ALTER TABLE `noten_zeugnis_exemplar`
    ADD PRIMARY KEY (`zeugnisID`,`schuelerAsvID`) USING BTREE;

ALTER TABLE `office365_accounts`
    ADD PRIMARY KEY (`accountAsvID`,`accountIsTeacher`,`accountIsPupil`) USING BTREE;

ALTER TABLE `projekt_lehrer2grade`
    ADD PRIMARY KEY (`lehrerUserID`,`gradeName`) USING BTREE;

ALTER TABLE `projekt_projekte`
    ADD PRIMARY KEY (`userID`) USING BTREE;

ALTER TABLE `remote_usersync`
    ADD PRIMARY KEY (`syncID`) USING BTREE;

ALTER TABLE `resetpassword`
    ADD PRIMARY KEY (`resetID`) USING BTREE,
  ADD KEY `resetUserID` (`resetUserID`);

ALTER TABLE `respizienz`
    ADD PRIMARY KEY (`respizienzID`) USING BTREE;

ALTER TABLE `schaukasten_bildschirme`
    ADD PRIMARY KEY (`schaukastenID`) USING BTREE;

ALTER TABLE `schaukasten_inhalt`
    ADD PRIMARY KEY (`schaukastenID`,`schaukastenPosition`) USING BTREE;

ALTER TABLE `schaukasten_powerpoint`
    ADD PRIMARY KEY (`powerpointID`) USING BTREE;

ALTER TABLE `schaukasten_website`
    ADD PRIMARY KEY (`websiteID`) USING BTREE,
  ADD UNIQUE KEY `websiteName` (`websiteID`) USING BTREE;

ALTER TABLE `schueler`
    ADD PRIMARY KEY (`schuelerAsvID`) USING BTREE,
  ADD KEY `schuelerEintrittDatum` (`schuelerEintrittDatum`),
  ADD KEY `schuelerKlasse` (`schuelerKlasse`),
  ADD KEY `schuelerUserID` (`schuelerUserID`);

ALTER TABLE `schuelerinfo_dokumente`
    ADD PRIMARY KEY (`dokumentID`) USING BTREE,
  ADD KEY `dokumentSchuelerAsvID` (`dokumentSchuelerAsvID`);

ALTER TABLE `schueler_briefe`
    ADD PRIMARY KEY (`briefID`) USING BTREE;

ALTER TABLE `schueler_fremdsprache`
    ADD PRIMARY KEY (`schuelerAsvID`,`spracheSortierung`) USING BTREE;

ALTER TABLE `schueler_nachteilsausgleich`
    ADD PRIMARY KEY (`schuelerAsvID`) USING BTREE;

ALTER TABLE `schueler_quarantaene`
    ADD PRIMARY KEY (`quarantaeneID`),
  ADD KEY `quarantaeneSchuelerAsvID` (`quarantaeneSchuelerAsvID`(191)),
  ADD KEY `quarantaeneStart` (`quarantaeneStart`,`quarantaeneEnde`);

ALTER TABLE `schulbuch_ausleihe`
    ADD PRIMARY KEY (`ausleiheID`) USING BTREE,
  ADD KEY `ausleiheExemplarID` (`ausleiheExemplarID`),
  ADD KEY `ausleiherLehrerAsvID` (`ausleiherLehrerAsvID`),
  ADD KEY `ausleiherSchuelerAsvID` (`ausleiherSchuelerAsvID`),
  ADD KEY `ausleiherUserID` (`ausleiherUserID`),
  ADD KEY `ausleiheStartDatum` (`ausleiheStartDatum`,`ausleiheEndDatum`),
  ADD KEY `rueckgeberUserID` (`rueckgeberUserID`);

ALTER TABLE `schulbuch_buecher`
    ADD PRIMARY KEY (`buchID`) USING BTREE;

ALTER TABLE `schulbuch_exemplare`
    ADD PRIMARY KEY (`exemplarID`,`exemplarBarcode`) USING BTREE,
  ADD KEY `exemplarBuchID` (`exemplarBuchID`);

ALTER TABLE `schulen`
    ADD PRIMARY KEY (`schuleID`) USING BTREE,
  ADD KEY `schuleNummer` (`schuleNummer`);

ALTER TABLE `sessions`
    ADD PRIMARY KEY (`sessionID`) USING BTREE,
  ADD KEY `sessionLastActivity` (`sessionLastActivity`),
  ADD KEY `sessionType` (`sessionType`),
  ADD KEY `sessionUserID` (`sessionUserID`);

ALTER TABLE `settings`
    ADD PRIMARY KEY (`settingName`);

ALTER TABLE `settings_history`
    ADD PRIMARY KEY (`settingHistoryID`) USING BTREE;

ALTER TABLE `site_activation`
    ADD PRIMARY KEY (`siteName`) USING BTREE,
  ADD KEY `siteIsActive` (`siteIsActive`);

ALTER TABLE `sprechtag`
    ADD PRIMARY KEY (`sprechtagID`) USING BTREE,
  ADD KEY `sprechtagDate` (`sprechtagDate`),
  ADD KEY `sprechtagIsActive` (`sprechtagIsActive`);

ALTER TABLE `sprechtag_buchungen`
    ADD PRIMARY KEY (`buchungID`) USING BTREE,
  ADD KEY `lehrerKuerzel` (`lehrerKuerzel`),
  ADD KEY `schuelerAsvID` (`schuelerAsvID`),
  ADD KEY `sprechtagID` (`sprechtagID`);

ALTER TABLE `sprechtag_raeume`
    ADD PRIMARY KEY (`sprechtagID`,`lehrerKuerzel`) USING BTREE;

ALTER TABLE `sprechtag_slots`
    ADD PRIMARY KEY (`slotID`) USING BTREE,
  ADD KEY `sprechtagID` (`sprechtagID`);

ALTER TABLE `stundenplan_aufsichten`
    ADD PRIMARY KEY (`aufsichtID`) USING BTREE,
  ADD KEY `stundenplanID` (`stundenplanID`);

ALTER TABLE `stundenplan_plaene`
    ADD PRIMARY KEY (`stundenplanID`) USING BTREE,
  ADD KEY `stundenplanAb` (`stundenplanAb`,`stundenplanBis`);

ALTER TABLE `stundenplan_stunden`
    ADD PRIMARY KEY (`stundeID`) USING BTREE,
  ADD KEY `stundenplanID` (`stundenplanID`);

ALTER TABLE `templates`
    ADD PRIMARY KEY (`templateName`) USING BTREE;

ALTER TABLE `trenndaten`
    ADD PRIMARY KEY (`trennWort`) USING BTREE;

ALTER TABLE `tutoren`
    ADD PRIMARY KEY (`tutorenID`);

ALTER TABLE `tutoren_slots`
    ADD PRIMARY KEY (`slotID`);

ALTER TABLE `two_factor_trusted_devices`
    ADD PRIMARY KEY (`deviceID`) USING BTREE,
  ADD KEY `two_factor_trusted_devices_ibfk_1` (`deviceUserID`) USING BTREE;

ALTER TABLE `unknown_mails`
    ADD PRIMARY KEY (`mailID`) USING BTREE;

ALTER TABLE `unterricht`
    ADD PRIMARY KEY (`unterrichtID`) USING BTREE,
  ADD KEY `unterrichtFachID` (`unterrichtFachID`),
  ADD KEY `unterrichtLehrerID` (`unterrichtLehrerID`);

ALTER TABLE `unterricht_besuch`
    ADD KEY `unterrichtID` (`unterrichtID`,`schuelerAsvID`);

ALTER TABLE `uploads`
    ADD PRIMARY KEY (`uploadID`) USING BTREE,
  ADD KEY `fileAccessCode` (`fileAccessCode`);

ALTER TABLE `users`
    ADD PRIMARY KEY (`userID`) USING BTREE,
  ADD KEY `userName` (`userName`(255));

ALTER TABLE `users_groups`
    ADD PRIMARY KEY (`userID`,`groupName`) USING BTREE,
  ADD KEY `groupName` (`groupName`),
  ADD KEY `userID` (`userID`);

ALTER TABLE `users_groups_own`
    ADD PRIMARY KEY (`groupName`) USING BTREE,
  ADD KEY `groupIsMessageRecipient` (`groupIsMessageRecipient`);

ALTER TABLE `user_settings`
    ADD PRIMARY KEY (`userID`) USING BTREE;

ALTER TABLE `vplan`
    ADD PRIMARY KEY (`vplanName`) USING BTREE;

ALTER TABLE `widgets`
    ADD PRIMARY KEY (`id`),
  ADD KEY `uniqid` (`uniqid`);

ALTER TABLE `wlan_ticket`
    ADD PRIMARY KEY (`ticketID`) USING BTREE;


ALTER TABLE `absenzen_absenzen`
    MODIFY `absenzID` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `absenzen_attestpflicht`
    MODIFY `attestpflichtID` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `absenzen_befreiungen`
    MODIFY `befreiungID` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `absenzen_beurlaubungen`
    MODIFY `beurlaubungID` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `absenzen_beurlaubung_antrag`
    MODIFY `antragID` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `absenzen_krankmeldungen`
    MODIFY `krankmeldungID` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `absenzen_merker`
    MODIFY `merkerID` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `absenzen_sanizimmer`
    MODIFY `sanizimmerID` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `absenzen_verspaetungen`
    MODIFY `verspaetungID` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `acl`
    MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `andere_kalender`
    MODIFY `kalenderID` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `andere_kalender_kategorie`
    MODIFY `kategorieID` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `aufeinenblick_settings`
    MODIFY `aufeinenblickSettingsID` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `ausleihe_ausleihe`
    MODIFY `ausleiheID` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `ausleihe_objekte`
    MODIFY `objektID` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `ausweise`
    MODIFY `ausweisID` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `bad_mail`
    MODIFY `badMailID` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `beobachtungsbogen_boegen`
    MODIFY `beobachtungsbogenID` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `beobachtungsbogen_fragen`
    MODIFY `frageID` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `beurlaubung_antrag`
    MODIFY `antragID` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `cron_execution`
    MODIFY `cronRunID` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `dashboard`
    MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `dokumente_dateien`
    MODIFY `dateiID` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `dokumente_gruppen`
    MODIFY `gruppenID` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `dokumente_kategorien`
    MODIFY `kategorieID` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `eltern_adressen`
    MODIFY `adresseID` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `eltern_codes`
    MODIFY `codeID` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `eltern_register`
    MODIFY `registerID` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `extensions`
    MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `externe_kalender`
    MODIFY `kalenderID` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `fremdlogin`
    MODIFY `fremdloginID` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `ganztags_events`
    MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `ganztags_gruppen`
    MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `icsfeeds`
    MODIFY `feedID` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `image_uploads`
    MODIFY `uploadID` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `initialpasswords`
    MODIFY `initialPasswordID` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `kalender_allInOne_eintrag`
    MODIFY `eintragID` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `kalender_andere`
    MODIFY `eintragID` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `kalender_extern`
    MODIFY `eintragID` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `kalender_ferien`
    MODIFY `ferienID` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `kalender_klassentermin`
    MODIFY `eintragID` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `kalender_lnw`
    MODIFY `eintragID` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `klassen`
    MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `klassentagebuch_fehl`
    MODIFY `fehlID` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `klassentagebuch_klassen`
    MODIFY `entryID` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `kondolenzbuch`
    MODIFY `eintragID` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `laufzettel`
    MODIFY `laufzettelID` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `laufzettel_stunden`
    MODIFY `laufzettelStundeID` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `lerntutoren`
    MODIFY `lerntutorID` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `lerntutoren_slots`
    MODIFY `slotID` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `mail_send`
    MODIFY `mailID` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `math_captcha`
    MODIFY `captchaID` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `mebis_accounts`
    MODIFY `mebisAccountID` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `mensa_speiseplan`
    MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `menu`
    MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `menu_item`
    MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `messages_attachment`
    MODIFY `attachmentID` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `messages_folders`
    MODIFY `folderID` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `messages_messages`
    MODIFY `messageID` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `messages_questions`
    MODIFY `questionID` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `messages_questions_answers`
    MODIFY `answerID` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `modul_admin_notes`
    MODIFY `noteID` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `nextcloud_users`
    MODIFY `userID` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Same UserID as in SI';

ALTER TABLE `noten_arbeiten`
    MODIFY `arbeitID` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `noten_bemerkung_textvorlagen`
    MODIFY `bemerkungID` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `noten_bemerkung_textvorlagen_gruppen`
    MODIFY `gruppeID` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `noten_verrechnung`
    MODIFY `verrechnungID` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `noten_wahlfach_faecher`
    MODIFY `wahlfachID` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `noten_zeugnisse`
    MODIFY `zeugnisID` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `remote_usersync`
    MODIFY `syncID` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `resetpassword`
    MODIFY `resetID` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `schaukasten_bildschirme`
    MODIFY `schaukastenID` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `schaukasten_powerpoint`
    MODIFY `powerpointID` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `schaukasten_website`
    MODIFY `websiteID` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `schuelerinfo_dokumente`
    MODIFY `dokumentID` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `schueler_briefe`
    MODIFY `briefID` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `schueler_quarantaene`
    MODIFY `quarantaeneID` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `schulbuch_ausleihe`
    MODIFY `ausleiheID` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `schulbuch_buecher`
    MODIFY `buchID` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `schulbuch_exemplare`
    MODIFY `exemplarID` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `settings_history`
    MODIFY `settingHistoryID` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `sprechtag`
    MODIFY `sprechtagID` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `sprechtag_buchungen`
    MODIFY `buchungID` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `sprechtag_slots`
    MODIFY `slotID` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `stundenplan_aufsichten`
    MODIFY `aufsichtID` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `stundenplan_plaene`
    MODIFY `stundenplanID` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `stundenplan_stunden`
    MODIFY `stundeID` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `tutoren`
    MODIFY `tutorenID` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `tutoren_slots`
    MODIFY `slotID` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `two_factor_trusted_devices`
    MODIFY `deviceID` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `unknown_mails`
    MODIFY `mailID` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `uploads`
    MODIFY `uploadID` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `users`
    MODIFY `userID` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `widgets`
    MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `wlan_ticket`
    MODIFY `ticketID` int(11) NOT NULL AUTO_INCREMENT;


ALTER TABLE `two_factor_trusted_devices`
    ADD CONSTRAINT `two_factor_trusted_devices_ibfk_1` FOREIGN KEY (`deviceUserID`) REFERENCES `users` (`userID`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
