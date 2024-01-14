/*
CREATE TABLE `tutoren` (
                           `tutorenID` int(11) NOT NULL AUTO_INCREMENT,
                           `status` varchar(100) DEFAULT NULL,
                           `created` date DEFAULT NULL,
                           `tutorenTutorAsvID` varchar(100) DEFAULT NULL,
                           `fach` varchar(100) DEFAULT NULL,
                           `jahrgang` varchar(100) DEFAULT NULL,
                           `einheiten` int(11) DEFAULT NULL,
                           PRIMARY KEY (`tutorenID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;


CREATE TABLE `tutoren_slots` (
                                 `slotID` int(11) NOT NULL AUTO_INCREMENT,
                                 `slotTutorenID` int(11) NOT NULL,
                                 `slotStatus` varchar(255) NOT NULL,
                                 `slotSchuelerAsvID` varchar(100) NOT NULL,
                                 `slotEinheiten` int(11) NOT NULL,
                                 `slotCreated` date DEFAULT NULL,
                                 `slotDatum` varchar(255) DEFAULT '',
                                 `slotDauer` varchar(255) DEFAULT '',
                                 `slotInfo` text,
                                 PRIMARY KEY (`slotID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;
*/
