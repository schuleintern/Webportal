-- Create syntax for TABLE 'ext_kalender'
CREATE TABLE `ext_kalender`
(
    `id`        int(11) unsigned NOT NULL AUTO_INCREMENT,
    `title`     varchar(255) NOT NULL DEFAULT '',
    `state`     tinyint(1) DEFAULT '1',
    `color`     varchar(7)            DEFAULT NULL,
    `sort`      tinyint(1) DEFAULT NULL,
    `preSelect` tinyint(1) DEFAULT NULL,
    `acl`       int(11) DEFAULT NULL,
    `ferien`    tinyint(1) DEFAULT '0',
    `public`    tinyint(1) NOT NULL,
    `icsfeed`   varchar(255)          DEFAULT NULL,
    `admins`    varchar(500)          DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- Create syntax for TABLE 'ext_kalender_events'
CREATE TABLE `ext_kalender_events`
(
    `id`           int(11) NOT NULL AUTO_INCREMENT,
    `status`       tinyint(4) DEFAULT '0',
    `kalender_id`  int(11) NOT NULL,
    `title`        varchar(255) NOT NULL DEFAULT '',
    `dateStart`    date                  DEFAULT NULL,
    `timeStart`    time                  DEFAULT NULL,
    `dateEnd`      date                  DEFAULT NULL,
    `timeEnd`      time                  DEFAULT NULL,
    `place`        varchar(255)          DEFAULT '',
    `comment`      text,
    `user_id`      int(11) NOT NULL,
    `createdTime`  datetime     NOT NULL,
    `modifiedTime` datetime              DEFAULT NULL,
    `repeat_type`  varchar(10)           DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- Create syntax for TABLE 'ext_kalender_ics'
CREATE TABLE `ext_kalender_ics`
(
    `id`          int(11) unsigned NOT NULL AUTO_INCREMENT,
    `createdTime` date         DEFAULT NULL,
    `user_id`     int(11) DEFAULT NULL,
    `keyCode`     varchar(100) DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;