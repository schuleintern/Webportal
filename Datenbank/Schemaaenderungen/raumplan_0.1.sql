DROP TABLE IF EXISTS `raumplan_stunden`;

CREATE TABLE `raumplan_stunden` (
                                    `stundeID` int(11) unsigned NOT NULL AUTO_INCREMENT,
                                    `stundenplanID` int(11) DEFAULT NULL,
                                    `stundeKlasse` varchar(20) DEFAULT NULL,
                                    `stundeLehrer` varchar(20) DEFAULT NULL,
                                    `stundeFach` varchar(20) DEFAULT NULL,
                                    `stundeRaum` varchar(20) DEFAULT NULL,
                                    `stundeDatum` date DEFAULT NULL,
                                    `stundeStunde` int(2) DEFAULT NULL,
                                    PRIMARY KEY (`stundeID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;