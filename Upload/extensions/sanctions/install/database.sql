-- Create syntax for TABLE 'ext_sanctions_count'
CREATE TABLE `ext_sanctions_count`
(
    `id`           int(11) unsigned NOT NULL AUTO_INCREMENT,
    `parent_id`    int(11) DEFAULT NULL,
    `count`        tinyint(11) DEFAULT NULL,
    `createDate`   datetime DEFAULT NULL,
    `createInfo`   text,
    `createBy`     int(11) DEFAULT NULL,
    `createUserID` int(11) DEFAULT NULL,
    `doneDate`     datetime DEFAULT NULL,
    `doneInfo`     text,
    `doneUserID`   int(11) DEFAULT NULL,
    `doneBy`       int(11) DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- Create syntax for TABLE 'ext_sanctions_users'
CREATE TABLE `ext_sanctions_users`
(
    `id`      int(11) unsigned NOT NULL AUTO_INCREMENT,
    `user_id` int(11) DEFAULT NULL,
    `status`  tinyint(1) DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;