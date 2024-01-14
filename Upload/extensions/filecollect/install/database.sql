DROP TABLE IF EXISTS `ext_filecollect_collection`;
CREATE TABLE `ext_filecollect_collection`
(
    `id`          int(11) unsigned NOT NULL AUTO_INCREMENT,
    `user_id`     int(11) DEFAULT '0',
    `createdTime` datetime     DEFAULT NULL,
    `title`       varchar(255) DEFAULT NULL,
    `info`        text,
    `members`     text,
    `endDate`     datetime     DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY           `created_user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `ext_filecollect_file`;
CREATE TABLE `ext_filecollect_file`
(
    `id`        int(11) unsigned NOT NULL AUTO_INCREMENT,
    `time`      datetime     DEFAULT NULL,
    `user_id`   int(11) DEFAULT '0',
    `folder_id` int(11) DEFAULT '0',
    `filename`  varchar(255) DEFAULT '0',
    `fileid`    varchar(255) DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `ext_filecollect_folders`;
CREATE TABLE `ext_filecollect_folders`
(
    `id`            int(11) unsigned NOT NULL AUTO_INCREMENT,
    `collection_id` int(11) DEFAULT '0',
    `title`         varchar(255) DEFAULT NULL,
    `info`          text,
    `status`        tinyint(1) DEFAULT NULL,
    `anzahl`        tinyint(3) DEFAULT NULL,
    `members`       text,
    `endDate`       datetime     DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;