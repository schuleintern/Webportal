CREATE TABLE `ext_krankmeldung_antrag`
(
    `id`            int(11) NOT NULL AUTO_INCREMENT,
    `state`         tinyint(1) DEFAULT NULL,
    `createdTime`   datetime    DEFAULT NULL,
    `createdUserID` int(11) DEFAULT NULL,
    `user_id`       int(11) NOT NULL,
    `asv_id`        varchar(20) DEFAULT NULL,
    `dateStart`     date NOT NULL,
    `dateEnd`       date NOT NULL,
    `days`          int(1) DEFAULT NULL,
    `info`          text,
    `absenzID`      int(11) DEFAULT '0',
    PRIMARY KEY (`id`),
    KEY             `absenzID` (`absenzID`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;