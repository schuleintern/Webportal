CREATE TABLE `ext_admintools_changeuser_list`
(
    `id`            int(11) unsigned NOT NULL AUTO_INCREMENT,
    `createdTime`   datetime    DEFAULT NULL,
    `createdUserID` int(11) DEFAULT NULL,
    `state`         tinyint(1) DEFAULT NULL,
    `sort`          tinyint(1) DEFAULT NULL,
    `user_id`       varchar(11) DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;