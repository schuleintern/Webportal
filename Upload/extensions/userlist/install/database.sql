DROP TABLE IF EXISTS `ext_userlist_list`;
CREATE TABLE `ext_userlist_list`
(
    `id`          int(11) unsigned NOT NULL AUTO_INCREMENT,
    `createdTime` date         DEFAULT NULL,
    `createdBy`   int(11) DEFAULT NULL,
    `title`       varchar(255) DEFAULT NULL,
    `info`        text,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



DROP TABLE IF EXISTS `ext_userlist_list_content`;
CREATE TABLE `ext_userlist_list_content`
(
    `id`        int(11) unsigned NOT NULL AUTO_INCREMENT,
    `tab_id`    int(11) DEFAULT NULL,
    `list_id`   int(11) DEFAULT NULL,
    `member_id` int(11) DEFAULT NULL,
    `toggle`    tinyint(1) DEFAULT NULL,
    `info`      varchar(255) DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `ext_userlist_list_members`;
CREATE TABLE `ext_userlist_list_members`
(
    `id`      int(11) unsigned NOT NULL AUTO_INCREMENT,
    `list_id` int(11) DEFAULT NULL,
    `user_id` int(11) DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



DROP TABLE IF EXISTS `ext_userlist_list_owner`;
CREATE TABLE `ext_userlist_list_owner`
(
    `id`       int(11) unsigned NOT NULL AUTO_INCREMENT,
    `list_id`  int(11) DEFAULT NULL,
    `user_id`  int(11) DEFAULT NULL,
    `favorite` tinyint(1) DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



DROP TABLE IF EXISTS `ext_userlist_list_tab`;
CREATE TABLE `ext_userlist_list_tab`
(
    `id`      int(11) unsigned NOT NULL AUTO_INCREMENT,
    `list_id` int(11) DEFAULT NULL,
    `title`   varchar(255) DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
