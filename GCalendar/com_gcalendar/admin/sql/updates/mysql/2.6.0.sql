-- ACL access fields
ALTER TABLE `#__gcalendar` ADD `access` TINYINT UNSIGNED NOT NULL DEFAULT '1';
ALTER TABLE `#__gcalendar` ADD `access_content` TINYINT UNSIGNED NOT NULL DEFAULT '1';

-- Credentials
ALTER TABLE `#__gcalendar` ADD `username` VARCHAR( 255 ) NULL DEFAULT NULL AFTER `magic_cookie`;
ALTER TABLE `#__gcalendar` ADD `password` text NULL DEFAULT NULL AFTER `username`;