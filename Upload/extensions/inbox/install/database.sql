
-- Create syntax for TABLE 'ext_inbox_message'
CREATE TABLE `ext_inbox_message`
(
    `id`         int(11) unsigned NOT NULL AUTO_INCREMENT,
    `inbox_id`   int(11) DEFAULT NULL,
    `folder_id`  int(11) DEFAULT NULL,
    `body_id`    int(11) DEFAULT NULL,
    `isRead`     int(11) DEFAULT NULL,
    `isReadUser` int(11) DEFAULT NULL,
    `isConfirm`  int(11) DEFAULT NULL,
    `isEmail`    int(11) DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;

-- Create syntax for TABLE 'ext_inbox_message_body'
CREATE TABLE `ext_inbox_message_body`
(
    `id`           int(11) unsigned NOT NULL AUTO_INCREMENT,
    `sender`       int(11) DEFAULT NULL,
    `receivers`    text,
    `receivers_cc` text,
    `createdTime`  datetime     DEFAULT NULL,
    `priority`     varchar(11)  DEFAULT NULL,
    `noAnswer`     tinyint(1) DEFAULT NULL,
    `isPrivat`     tinyint(1) DEFAULT NULL,
    `files`        tinyint(1) DEFAULT NULL,
    `umfrage`      int(11) DEFAULT NULL,
    `subject`      varchar(255) DEFAULT NULL,
    `text`         text,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;

-- Create syntax for TABLE 'ext_inbox_message_file'
CREATE TABLE `ext_inbox_message_file`
(
    `id`      int(11) unsigned NOT NULL AUTO_INCREMENT,
    `body_id` int(11) DEFAULT NULL,
    `uniqid`  varchar(20)  DEFAULT NULL,
    `file`    varchar(255) DEFAULT NULL,
    `name`    varchar(255) DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;

-- Create syntax for TABLE 'ext_inbox_user'
CREATE TABLE `ext_inbox_user`
(
    `id`       int(11) unsigned NOT NULL AUTO_INCREMENT,
    `inbox_id` int(11) DEFAULT NULL,
    `user_id`  int(11) DEFAULT NULL,
    `timeOn`   time DEFAULT NULL,
    `timeOff`  time DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;

-- Create syntax for TABLE 'ext_inboxs'
CREATE TABLE `ext_inboxs`
(
    `id`            int(11) unsigned NOT NULL AUTO_INCREMENT,
    `createdUserID` int(11) DEFAULT NULL,
    `createdTime`   datetime     DEFAULT NULL,
    `title`         varchar(255) DEFAULT NULL,
    `type`          varchar(100) DEFAULT NULL,
    `parent_id`     int(11) DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;


INSERT INTO `ext_inbox_folders` (`id`, `title`, `inbox_id`, `sort`)
VALUES (1, 'Posteingang', 0, 1),
       (2, 'Gesendet', 0, 2),
       (3, 'Archive', 0, 3),
       (4, 'Entwurf', 0, 4);