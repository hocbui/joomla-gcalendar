DROP TABLE IF EXISTS `#__gcalendar`;

CREATE TABLE `#__gcalendar` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(100) NOT NULL,
  `xmlUrl` varchar(100) NOT NULL,
  `htmlUrl` varchar(100) NOT NULL,
  `icalUrl` varchar(100) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;
