CREATE TABLE `ext_podcast_items` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT NULL,
  `info` mediumtext,
  `author` varchar(255) DEFAULT NULL,
  `cover` varchar(100) DEFAULT NULL,
  `file` varchar(100) DEFAULT NULL,
  `count` int(11) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;