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
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

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
    `stunde`       int(11) DEFAULT NULL,
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
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

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
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;


INSERT INTO `ext_klassenkalender_lnw` (`id`, `title`, `short`, `isPublic`, `color`)
VALUES
    (1, 'Schulaufgabe', 'SA', 1, '#a83632'),
    (2, 'Nachholschulaufgabe', 'NSA', 1, '#540d0b'),
    (3, 'Modus Test', 'MT', 0, '#ab2c6e'),
    (4, 'Kurzarbeit', 'KA', 0, '#1a68a3'),
    (5, 'Praktischer Leistungsnachweis', 'PLNW', 1, '#335e12'),
    (6, 'Stegreifaufgabe', 'EX', 0, '#10209c');
