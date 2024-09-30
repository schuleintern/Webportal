CREATE TABLE `ext_inbox_message_file`
(
    `id`      int(11) unsigned NOT NULL AUTO_INCREMENT,
    `body_id` int(11) DEFAULT NULL,
    `uniqid`  varchar(20)  DEFAULT NULL,
    `file`    varchar(255) DEFAULT NULL,
    `name`    varchar(255) DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `ext_inbox_message_body` CHANGE `files` `files` TINYINT(1) NULL DEFAULT NULL;
