ALTER TABLE `ext_inbox_message` ADD `isAnswer` INT(11)  NULL  DEFAULT NULL  AFTER `isEmail`;
ALTER TABLE `ext_inbox_message` ADD `isForward` INT(11)  NULL  DEFAULT NULL  AFTER `isAnswer`;