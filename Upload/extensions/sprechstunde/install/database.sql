CREATE TABLE `ext_sprechstunde_dates` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `date` date DEFAULT NULL,
  `slot_id` int(11) DEFAULT NULL,
  `info` varchar(255) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `block` int(1) DEFAULT NULL,
  `status` int(1) DEFAULT NULL,
  `medium` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `ext_sprechstunde_slots` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `state` tinyint(1) DEFAULT '0',
  `user_id` int(11) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `day` varchar(2) DEFAULT NULL,
  `time` time DEFAULT NULL,
  `duration` int(11) DEFAULT NULL,
  `typ` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
