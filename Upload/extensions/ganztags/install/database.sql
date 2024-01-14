-- Create syntax for TABLE 'ext_ganztags_day'
CREATE TABLE `ext_ganztags_day`
(
    `id`          int(11) unsigned NOT NULL AUTO_INCREMENT,
    `date`        date         DEFAULT NULL,
    `type`        varchar(11)  DEFAULT NULL,
    `title`       varchar(255) DEFAULT NULL,
    `info`        varchar(255) DEFAULT NULL,
    `room`        varchar(20)  DEFAULT NULL,
    `color`       varchar(10)  DEFAULT NULL,
    `leader_id`   int(11) DEFAULT 0,
    `group_id`    int(11) DEFAULT 0,
    `duration`    int(11) DEFAULT 0,
    `createdBy`   int(11) DEFAULT NULL,
    `createdTime` datetime     DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY           `DATE` (`date`)
) ENGINE=InnoDB AUTO_INCREMENT=1182 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Create syntax for TABLE 'ext_ganztags_groups'
CREATE TABLE `ext_ganztags_groups`
(
    `id`        int(11) unsigned NOT NULL AUTO_INCREMENT,
    `type`      varchar(11)  DEFAULT NULL,
    `title`     varchar(255) DEFAULT NULL,
    `leader_id` int(11) DEFAULT NULL,
    `days`      longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`days`)),
    `room`      varchar(20)  DEFAULT NULL,
    `info`      varchar(255) DEFAULT NULL,
    `color`     varchar(10)  DEFAULT NULL,
    `duration`  int(11) DEFAULT 0,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Create syntax for TABLE 'ext_ganztags_leaders'
CREATE TABLE `ext_ganztags_leaders`
(
    `id`      int(11) unsigned NOT NULL AUTO_INCREMENT,
    `user_id` int(11) DEFAULT NULL,
    `days`    longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`days`)),
    `info`    varchar(255) NOT NULL DEFAULT '',
    PRIMARY KEY (`id`),
    KEY       `USER_ID` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Create syntax for TABLE 'ext_ganztags_schueler'
CREATE TABLE `ext_ganztags_schueler`
(
    `id`      int(11) unsigned NOT NULL AUTO_INCREMENT,
    `user_id` int(11) DEFAULT NULL,
    `days`    longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`days`)),
    `info`    varchar(255) DEFAULT NULL,
    `anz`     int(1) DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY       `USER_ID` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=203 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;