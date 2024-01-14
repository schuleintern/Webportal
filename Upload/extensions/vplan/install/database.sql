-- Create syntax for TABLE 'ext_vplan_day'
CREATE TABLE `ext_vplan_day` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `createdTime` datetime DEFAULT NULL,
  `createdUser` int(11) DEFAULT NULL,
  `date` varchar(20) DEFAULT NULL,
  `text` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=19365 DEFAULT CHARSET=utf8;

-- Create syntax for TABLE 'ext_vplan_list'
CREATE TABLE `ext_vplan_list` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `createdTime` datetime DEFAULT NULL,
  `createdUser` int(11) DEFAULT NULL,
  `date` varchar(20) DEFAULT NULL,
  `klasse` varchar(20) DEFAULT NULL,
  `stunde` varchar(20) DEFAULT NULL,
  `user_alt` varchar(20) DEFAULT NULL,
  `user_neu` varchar(20) DEFAULT NULL,
  `fach_neu` varchar(20) DEFAULT NULL,
  `fach_alt` varchar(20) DEFAULT NULL,
  `raum` varchar(20) DEFAULT NULL,
  `info` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2650 DEFAULT CHARSET=utf8;