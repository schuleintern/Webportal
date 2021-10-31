CREATE TABLE `extensions` (
                              `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                              `name` varchar(255) DEFAULT NULL,
                              `uniqid` varchar(255) DEFAULT NULL,
                              `version` int(11) DEFAULT NULL,
                              `active` tinyint(11) DEFAULT NULL,
                              `folder` varchar(255) DEFAULT NULL,
                              `menuCat` varchar(25) DEFAULT NULL,
                              PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;