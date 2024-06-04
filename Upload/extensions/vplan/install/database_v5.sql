
ALTER TABLE `ext_vplan_list` CHANGE `raum` `raum_alt` VARCHAR(50)  CHARACTER SET utf8  COLLATE utf8_general_ci  NULL  DEFAULT NULL;
ALTER TABLE `ext_vplan_list` ADD `raum_neu` VARCHAR(50)  NULL  DEFAULT NULL  AFTER `raum_alt`;

ALTER TABLE `ext_vplan_list` CHANGE `info` `info_1` VARCHAR(400)  CHARACTER SET utf8  COLLATE utf8_general_ci  NULL  DEFAULT NULL;

ALTER TABLE `ext_vplan_list` ADD `info_2` VARCHAR(400)  NULL  DEFAULT NULL  AFTER `info_1`;
ALTER TABLE `ext_vplan_list` ADD `info_3` VARCHAR(400)  NULL  DEFAULT NULL  AFTER `info_2`;
