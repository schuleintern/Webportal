ALTER TABLE `ext_board_item`
    ADD `url` VARCHAR(255) NULL  DEFAULT NULL  AFTER `sort`;

CREATE TABLE `ext_board_item_read`
(
    `id`      int(11) unsigned NOT NULL AUTO_INCREMENT,
    `item_id` int(11) DEFAULT NULL,
    `user_id` int(11) DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;