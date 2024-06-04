-- Create syntax for TABLE 'cck_articles'
CREATE TABLE `cck_articles`
(
    `id`          int(11) unsigned NOT NULL AUTO_INCREMENT,
    `form_id`     int(11) DEFAULT NULL,
    `title`       varchar(255) DEFAULT NULL,
    `createdBy`   int(11) DEFAULT NULL,
    `createdTime` datetime     DEFAULT NULL,
    `modifyBy`    int(11) DEFAULT NULL,
    `modifyTime`  datetime     DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

-- Create syntax for TABLE 'cck_content'
CREATE TABLE `cck_content`
(
    `id`         int(11) unsigned NOT NULL AUTO_INCREMENT,
    `content`    text,
    `article_id` int(11) DEFAULT NULL,
    `field_id`   int(11) DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

-- Create syntax for TABLE 'cck_fieldtyp'
CREATE TABLE `cck_fieldtyp`
(
    `id`       int(11) unsigned NOT NULL AUTO_INCREMENT,
    `title`    varchar(255) DEFAULT NULL,
    `template` text,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

-- Create syntax for TABLE 'cck_formfields'
CREATE TABLE `cck_formfields`
(
    `id`       int(11) unsigned NOT NULL AUTO_INCREMENT,
    `form_id`  int(11) DEFAULT NULL,
    `field_id` int(11) DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

-- Create syntax for TABLE 'cck_forms'
CREATE TABLE `cck_forms`
(
    `id`           int(11) unsigned NOT NULL AUTO_INCREMENT,
    `title`        varchar(255) DEFAULT NULL,
    `template`     text,
    `createdTime`  datetime     DEFAULT NULL,
    `createdBy`    int(11) DEFAULT NULL,
    `modifiedTime` datetime     DEFAULT NULL,
    `modifiedBy`   int(11) DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;