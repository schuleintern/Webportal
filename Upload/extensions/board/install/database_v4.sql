ALTER TABLE `ext_board_category` ADD `sort` INT  NULL  DEFAULT NULL  AFTER `acl`;
ALTER TABLE `ext_board` ADD `sort` INT  NULL  DEFAULT NULL  AFTER `cat_id`;

ALTER TABLE `ext_board_item` ADD `sort` INT  NULL  DEFAULT NULL  AFTER `enddate`;
