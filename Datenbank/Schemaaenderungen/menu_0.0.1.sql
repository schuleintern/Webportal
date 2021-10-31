

CREATE TABLE `menu` (
    `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
    `alias` varchar(100) DEFAULT NULL,
    `title` varchar(100) NOT NULL DEFAULT '',
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


INSERT INTO `menu` (`id`, `alias`, `title`)
VALUES (1,'main','Main');



CREATE TABLE `menu_item` (
     `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
     `menu_id` int(11) NOT NULL,
     `parent_id` int(11) NOT NULL,
     `page` varchar(100) DEFAULT '',
     `title` varchar(100) NOT NULL DEFAULT '',
     `icon` varchar(100) DEFAULT NULL,
     `params` text,
     PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


INSERT INTO `menu_item` (`id`, `menu_id`, `parent_id`, `page`, `title`, `icon`, `params`)
VALUES
    (1,1,0,'','Aktuelles',NULL,NULL),
    (2,1,0,'','Informationen',NULL,NULL),
    (3,1,0,'','Lehreranwendungen',NULL,NULL),
    (4,1,0,'','Verwaltung',NULL,NULL),
    (5,1,0,'','Benutzeraccount / Nachrichten',NULL,NULL),
    (6,1,0,'','Unterricht',NULL,NULL),
    (7,1,0,'','Administration',NULL,NULL);




