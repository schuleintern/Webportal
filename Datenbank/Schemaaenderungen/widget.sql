CREATE TABLE `widgets` (
`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
`uniqid` varchar(100) DEFAULT NULL,
`position` varchar(100) DEFAULT NULL,
`access` varchar(255) DEFAULT NULL,
PRIMARY KEY (`id`),
KEY `uniqid` (`uniqid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;