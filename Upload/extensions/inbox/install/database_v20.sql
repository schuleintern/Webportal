CREATE TABLE `ext_inbox_message_isread`
(
    `id`         int(11) unsigned NOT NULL AUTO_INCREMENT,
    `message_id` int(11) DEFAULT NULL,
    `isRead`     int(11) DEFAULT NULL,
    `isReadUser` int(11) DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `isRead` (`isRead`)
) ENGINE = InnoDB
  AUTO_INCREMENT = 0
  DEFAULT CHARSET = utf8;