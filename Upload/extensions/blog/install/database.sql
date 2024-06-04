-- Create syntax for TABLE 'ext_finanzen_antrag'
CREATE TABLE `ext_finanzen_antrag`
(
    `id`            int(11) unsigned NOT NULL AUTO_INCREMENT,
    `createdUserID` int(11) DEFAULT NULL,
    `createdTime`   datetime     DEFAULT NULL,
    `state`         tinyint(1) DEFAULT NULL,
    `title`         varchar(255) DEFAULT NULL,
    `payee`         varchar(255) DEFAULT NULL,
    `users`         text,
    `amount`        float        DEFAULT NULL,
    `dueDate`       date         DEFAULT NULL,
    `receipt`       int(11) DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

-- Create syntax for TABLE 'ext_finanzen_buchung'
CREATE TABLE `ext_finanzen_buchung`
(
    `id`            int(11) unsigned NOT NULL AUTO_INCREMENT,
    `createdUserID` int(11) DEFAULT NULL,
    `createdTime`   datetime     DEFAULT NULL,
    `orderNr`       varchar(25)  DEFAULT NULL,
    `state`         tinyint(1) DEFAULT NULL,
    `antrag_id`     int(11) DEFAULT NULL,
    `user_id`       int(11) DEFAULT NULL,
    `amount`        float        DEFAULT NULL,
    `title`         varchar(255) DEFAULT NULL,
    `quant`         int(11) DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8;