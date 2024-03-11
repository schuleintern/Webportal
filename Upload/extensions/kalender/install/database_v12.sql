CREATE TABLE `ext_kalender_ics`
(
    `id`          int(11) unsigned NOT NULL AUTO_INCREMENT,
    `createdTime` date         DEFAULT NULL,
    `user_id`     int(11) DEFAULT NULL,
    `keyCode`     varchar(100) DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;