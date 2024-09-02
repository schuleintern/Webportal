-- Create syntax for TABLE 'ext_klassenkalender'
CREATE TABLE `ext_klassenkalender`
(
    `id`            int(11) unsigned NOT NULL AUTO_INCREMENT,
    `state`         tinyint(1) DEFAULT NULL,
    `sort`          int(11) DEFAULT NULL,
    `createdTime`   datetime              DEFAULT NULL,
    `createdUserID` int(11) DEFAULT NULL,
    `title`         varchar(255) NOT NULL DEFAULT '',
    `color`         varchar(7)            DEFAULT NULL,
    `acl`           int(11) DEFAULT NULL,
    `admins`        text,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- Create syntax for TABLE 'ext_klassenkalender_events'
CREATE TABLE `ext_klassenkalender_events`
(
    `id`           int(11) NOT NULL AUTO_INCREMENT,
    `typ`          varchar(10)           DEFAULT NULL,
    `user_id`      int(11) NOT NULL,
    `status`       tinyint(4) DEFAULT '0',
    `kalender_id`  int(11) NOT NULL,
    `title`        varchar(255) NOT NULL DEFAULT '',
    `dateStart`    date                  DEFAULT NULL,
    `dateEnd`      date                  DEFAULT NULL,
    `stunde`       varchar(100)          DEFAULT NULL,
    `timeStart`    time                  DEFAULT NULL,
    `timeEnd`      time                  DEFAULT NULL,
    `place`        varchar(255)          DEFAULT '',
    `comment`      text,
    `createdTime`  datetime              DEFAULT NULL,
    `modifiedTime` datetime              DEFAULT NULL,
    `repeat_type`  varchar(10)           DEFAULT NULL,
    `art`          int(11) DEFAULT NULL,
    `fach`         int(11) DEFAULT NULL,
    `teacher`      int(11) DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- Create syntax for TABLE 'ext_klassenkalender_lnw'
CREATE TABLE `ext_klassenkalender_lnw`
(
    `id`       int(11) NOT NULL AUTO_INCREMENT,
    `title`    varchar(50) DEFAULT NULL,
    `short`    varchar(50) DEFAULT NULL,
    `isPublic` int(11) DEFAULT NULL,
    `color`    varchar(11) DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;