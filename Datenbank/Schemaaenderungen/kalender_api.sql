

CREATE TABLE `kalender_api` (
  `kalenderID` int(11) NOT NULL AUTO_INCREMENT,
  `kalenderName` varchar(255) NOT NULL,
  `kalenderAccessSchueler` tinyint(1) NOT NULL DEFAULT '0',
  `kalenderAccessLehrer` int(11) NOT NULL DEFAULT '0',
  `kalenderAccessEltern` int(11) NOT NULL DEFAULT '0',
  `kalenderAccessLehrerSchreiben` tinyint(1) NOT NULL,
  `kalenderAccessSchuelerSchreiben` tinyint(1) NOT NULL,
  `kalenderAccessElternSchreiben` tinyint(1) NOT NULL,
  `kalenderDeleteOnlyOwn` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`kalenderID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;


CREATE TABLE `kalender_api_eintrag` (
  `eintragID` int(11) NOT NULL AUTO_INCREMENT,
  `kalenderID` int(11) NOT NULL,
  `eintragKategorieID` int(11) NOT NULL DEFAULT '0',
  `eintragTitel` varchar(255) NOT NULL,
  `eintragDatumStart` datetime NOT NULL,
  `eintragDatumEnde` datetime NOT NULL,
  `eintragOrt` varchar(255) NOT NULL,
  `eintragKommentar` tinytext NOT NULL,
  `eintragUser` int(11) NOT NULL,
  `eintragEintragZeitpunkt` datetime NOT NULL,
  PRIMARY KEY (`eintragID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;


CREATE TABLE `kalender_api_kategorie` (
  `kategorieID` int(11) NOT NULL AUTO_INCREMENT,
  `kategorieKalenderID` int(11) NOT NULL,
  `kategorieName` varchar(255) NOT NULL,
  `kategorieFarbe` varchar(7) NOT NULL,
  `kategorieIcon` varchar(255) NOT NULL,
  PRIMARY KEY (`kategorieID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;