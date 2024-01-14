-- Create syntax for TABLE 'ext_fileshare_item'
CREATE TABLE `ext_fileshare_item`
(
    `id`            int(11) unsigned NOT NULL AUTO_INCREMENT,
    `createdTime`   datetime     DEFAULT NULL,
    `createdUserID` int(11) DEFAULT NULL,
    `state`         tinyint(1) DEFAULT NULL,
    `folder`        varchar(20)  DEFAULT NULL,
    `list_id`       varchar(11)  DEFAULT NULL,
    `title`         varchar(255) DEFAULT NULL,
    `filename`      varchar(255) DEFAULT NULL,
    `sort`          tinyint(1) DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8;

-- Create syntax for TABLE 'ext_fileshare_list'
CREATE TABLE `ext_fileshare_list`
(
    `id`            int(11) unsigned NOT NULL AUTO_INCREMENT,
    `createdTime`   datetime     DEFAULT NULL,
    `createdUserID` int(11) DEFAULT NULL,
    `state`         tinyint(1) DEFAULT NULL,
    `title`         varchar(255) DEFAULT NULL,
    `userlist`      text,
    `folder`        varchar(20)  DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8;