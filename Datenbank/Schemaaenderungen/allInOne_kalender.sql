
CREATE TABLE `kalender_allInOne` (
  `kalenderID` int(11) NOT NULL AUTO_INCREMENT,
  `kalenderName` varchar(255) NOT NULL,
  `kalenderColor` varchar(7) DEFAULT NULL,
  `kalenderSort` tinyint(1) DEFAULT NULL,
  `kalenderPreSelect` tinyint(1) DEFAULT NULL,
  `kalenderAcl` int(11) DEFAULT NULL,
  `kalenderFerien` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`kalenderID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

CREATE TABLE `kalender_allInOne_eintrag` (
  `eintragID` int(11) NOT NULL AUTO_INCREMENT,
  `kalenderID` int(11) NOT NULL,
  `eintragKategorieID` int(11) NOT NULL DEFAULT '0',
  `eintragTitel` varchar(255) NOT NULL,
  `eintragDatumStart` date NOT NULL,
  `eintragTimeStart` time NOT NULL,
  `eintragDatumEnde` date NOT NULL,
  `eintragTimeEnde` time NOT NULL,
  `eintragOrt` varchar(255) NOT NULL,
  `eintragKommentar` tinytext NOT NULL,
  `eintragUserID` int(11) NOT NULL,
  `eintragCreatedTime` datetime NOT NULL,
  `eintragModifiedTime` datetime NOT NULL,
  PRIMARY KEY (`eintragID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

CREATE TABLE `kalender_allInOne_kategorie` (
  `kategorieID` int(11) NOT NULL AUTO_INCREMENT,
  `kategorieKalenderID` int(11) NOT NULL,
  `kategorieName` varchar(255) NOT NULL,
  `kategorieFarbe` varchar(7) NOT NULL,
  `kategorieIcon` varchar(255) NOT NULL,
  PRIMARY KEY (`kategorieID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
