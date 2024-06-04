CREATE TABLE `ext_users_groups`
(
    `id`          int(11) unsigned NOT NULL AUTO_INCREMENT,
    `state`       tinyint(1) DEFAULT NULL,
    `createdTime` datetime     DEFAULT NULL,
    `createdBy`   int(11) DEFAULT NULL,
    `title`       varchar(255) DEFAULT NULL,
    `users`       text,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;