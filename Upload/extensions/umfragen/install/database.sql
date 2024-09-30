-- Create syntax for TABLE 'ext_umfragen_answer'
CREATE TABLE `ext_umfragen_answer`
(
    `id`            int(11) unsigned NOT NULL AUTO_INCREMENT,
    `createdTime`   datetime     DEFAULT NULL,
    `createdUserID` int(11) DEFAULT NULL,
    `list_id`       int(11) DEFAULT NULL,
    `item_id`       int(11) DEFAULT NULL,
    `content`       varchar(255) DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;

-- Create syntax for TABLE 'ext_umfragen_item'
CREATE TABLE `ext_umfragen_item`
(
    `id`            int(11) unsigned NOT NULL AUTO_INCREMENT,
    `createdTime`   datetime     DEFAULT NULL,
    `createdUserID` int(11) DEFAULT NULL,
    `state`         tinyint(1) DEFAULT NULL,
    `list_id`       int(11) DEFAULT NULL,
    `title`         varchar(255) DEFAULT NULL,
    `typ`           varchar(20)  DEFAULT NULL,
    `sort`          tinyint(2) DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;

-- Create syntax for TABLE 'ext_umfragen_list'
CREATE TABLE `ext_umfragen_list`
(
    `id`            int(11) unsigned NOT NULL AUTO_INCREMENT,
    `createdTime`   datetime     DEFAULT NULL,
    `createdUserID` int(11) DEFAULT NULL,
    `state`         tinyint(1) DEFAULT NULL,
    `title`         varchar(255) DEFAULT NULL,
    `userlist`      text,
    `type`          varchar(50)  DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;