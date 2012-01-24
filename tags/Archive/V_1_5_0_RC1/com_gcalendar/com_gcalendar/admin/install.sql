
CREATE TABLE IF NOT EXISTS `#__gcalendar` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(100) NOT NULL,
  `xmlUrl` varchar(255) NOT NULL,
  `htmlUrl` varchar(255) NOT NULL,
  `icalUrl` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=0;

