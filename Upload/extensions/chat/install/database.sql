

CREATE TABLE `ext_chat_groups` (
   `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
   `status` tinyint(4) DEFAULT NULL,
   `title` varchar(100) DEFAULT NULL,
   `lastMsgTime` int(11) DEFAULT NULL,
   PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `ext_chat_groups_member` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `group_id` int(11) DEFAULT NULL,
  `user_id` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `ext_chat_msg` (
    `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
    `group_id` int(11) DEFAULT NULL,
    `from` varchar(100) DEFAULT NULL,
    `msg` varchar(1000) DEFAULT NULL,
    `time` int(11) DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
