-- Create syntax for TABLE 'ext_fehltage_items'
CREATE TABLE `ext_fehltage_items` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `total` int(11) DEFAULT NULL,
  `tage` int(11) DEFAULT NULL,
  `slot_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=632 DEFAULT CHARSET=utf8;

-- Create syntax for TABLE 'ext_fehltage_slots'
CREATE TABLE `ext_fehltage_slots` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `tage` int(11) DEFAULT NULL,
  `info` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;