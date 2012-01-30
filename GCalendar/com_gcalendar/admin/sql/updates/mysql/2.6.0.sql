-- ACL access fields
ALTER TABLE `#__gcalendar` ADD `access` TINYINT UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `#__gcalendar` ADD `access_content` TINYINT UNSIGNED NOT NULL DEFAULT '0';