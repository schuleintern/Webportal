

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
     `active` tinyint(1) DEFAULT '0',
     `menu_id` int(11) NOT NULL,
     `parent_id` int(11) NOT NULL,
     `sort` int(3) DEFAULT '0',
     `page` varchar(100) DEFAULT '',
     `title` varchar(100) NOT NULL DEFAULT '',
     `icon` varchar(100) DEFAULT NULL,
     `params` text,
     PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


INSERT INTO `menu_item` (`id`, `active`, `menu_id`, `parent_id`, `sort`, `page`, `title`, `icon`, `params`)
VALUES
    (1,1,1,0,0,'','Aktuelles','fa fa-clock',NULL),
    (2,1,1,0,0,'','Informationen','fa fa-clock',NULL),
    (3,1,1,0,0,'','Lehreranwendungen','fa fa-graduation-cap',NULL),
    (4,1,1,0,0,'','Verwaltung','fa fas fa-pencil-alt-square',NULL),
    (5,1,1,0,0,'','Benutzeraccount / Nachrichten','fa fa-user',NULL),
    (6,1,1,0,0,'','Unterricht','fa fa-graduation-cap',NULL),
    (7,1,1,0,0,'','Administration','fa fa-cogs',NULL);





