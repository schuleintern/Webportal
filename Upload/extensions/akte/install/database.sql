-- Create syntax for TABLE 'ext_akte_items'
CREATE TABLE `ext_akte_items`
(
    `id`            int(11) unsigned NOT NULL AUTO_INCREMENT,
    `createdUserID` int(11) DEFAULT NULL,
    `createdTime`   datetime     DEFAULT NULL,
    `state`         tinyint(1) DEFAULT NULL,
    `user_id`       int(11) DEFAULT NULL,
    `tags`          varchar(255) DEFAULT NULL,
    `text`          text,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Create syntax for TABLE 'ext_akte_tags'
CREATE TABLE `ext_akte_tags`
(
    `id`            int(11) unsigned NOT NULL AUTO_INCREMENT,
    `createdUserID` int(11) DEFAULT NULL,
    `createdTime`   datetime     DEFAULT NULL,
    `state`         tinyint(1) DEFAULT NULL,
    `title`         varchar(255) DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;