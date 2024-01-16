CREATE TABLE `ext_import_asv_log`
(
    `id`            int(11) unsigned NOT NULL AUTO_INCREMENT,
    `createdTime`   datetime DEFAULT NULL,
    `createdUserID` int(11) DEFAULT NULL,
    `log`           mediumtext,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8;