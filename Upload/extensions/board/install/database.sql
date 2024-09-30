-- Create syntax for TABLE 'ext_board'
CREATE TABLE `ext_board`
(
    `id`            int(11) unsigned NOT NULL AUTO_INCREMENT,
    `createdUserID` int(11) DEFAULT NULL,
    `createdTime`   datetime     DEFAULT NULL,
    `state`         tinyint(1) DEFAULT NULL,
    `title`         varchar(255) DEFAULT NULL,
    `cat_id`        int(6) DEFAULT NULL,
    `sort`          int(11) DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;

-- Create syntax for TABLE 'ext_board_category'
CREATE TABLE `ext_board_category`
(
    `id`            int(11) unsigned NOT NULL AUTO_INCREMENT,
    `createdUserID` int(11) DEFAULT NULL,
    `createdTime`   datetime     DEFAULT NULL,
    `state`         tinyint(1) DEFAULT NULL,
    `title`         varchar(255) DEFAULT NULL,
    `acl`           varchar(255) DEFAULT NULL,
    `sort`          int(11) DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;

-- Create syntax for TABLE 'ext_board_item'
CREATE TABLE `ext_board_item`
(
    `id`            int(11) unsigned NOT NULL AUTO_INCREMENT,
    `createdUserID` int(11) DEFAULT NULL,
    `createdTime`   datetime     DEFAULT NULL,
    `state`         tinyint(1) DEFAULT NULL,
    `title`         varchar(255) DEFAULT NULL,
    `board_id`      int(6) DEFAULT NULL,
    `text`          text,
    `pdf`           varchar(255) DEFAULT NULL,
    `cover`         varchar(255) DEFAULT NULL,
    `enddate`       date         DEFAULT NULL,
    `sort`          int(11) DEFAULT NULL,
    `url`           varchar(255) DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;

-- Create syntax for TABLE 'ext_board_item_read'
CREATE TABLE `ext_board_item_read`
(
    `id`      int(11) unsigned NOT NULL AUTO_INCREMENT,
    `item_id` int(11) DEFAULT NULL,
    `user_id` int(11) DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;