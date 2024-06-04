-- Create syntax for TABLE 'ext_pausen'
CREATE TABLE `ext_pausen`
(
    `id`            int(11) unsigned NOT NULL AUTO_INCREMENT,
    `createdUserID` int(11) DEFAULT NULL,
    `createdTime`   datetime     DEFAULT NULL,
    `state`         tinyint(1) DEFAULT NULL,
    `title`         varchar(255) DEFAULT NULL,
    `start`         time         DEFAULT NULL,
    `end`           time         DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

-- Create syntax for TABLE 'ext_pausen_aufsicht'
CREATE TABLE `ext_pausen_aufsicht`
(
    `id`            int(11) unsigned NOT NULL AUTO_INCREMENT,
    `createdUserID` int(11) DEFAULT NULL,
    `createdTime`   datetime DEFAULT NULL,
    `state`         tinyint(1) DEFAULT NULL,
    `day`           int(11) DEFAULT NULL,
    `pausen_id`     int(11) DEFAULT NULL,
    `user_id`       int(11) DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;