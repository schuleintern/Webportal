-- Create syntax for TABLE 'ext_ausweis_antrag'
CREATE TABLE `ext_ausweis_antrag`
(
    `id`          int(11) unsigned NOT NULL AUTO_INCREMENT,
    `user_id`     int(11) DEFAULT NULL,
    `user_typ`    varchar(20)  DEFAULT NULL,
    `createdTime` datetime     DEFAULT NULL,
    `state`       tinyint(1) DEFAULT NULL,
    `image`       varchar(255) DEFAULT NULL,
    `doneTime`    datetime     DEFAULT NULL,
    `doneUser`    int(11) DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

-- Create syntax for TABLE 'ext_ausweis_ausweis'
CREATE TABLE `ext_ausweis_ausweis`
(
    `id`            int(11) unsigned NOT NULL AUTO_INCREMENT,
    `createdUserID` int(11) DEFAULT NULL,
    `createdTime`   datetime     DEFAULT NULL,
    `state`         tinyint(1) DEFAULT NULL,
    `antrag_id`     int(11) DEFAULT NULL,
    `user_id`       int(11) DEFAULT NULL,
    `front_path`    varchar(255) DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;