CREATE TABLE `awstats_summary` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `journal_id` int(10) unsigned NOT NULL,
  `year` int(10) unsigned NOT NULL,
  `month` int(10) unsigned NOT NULL,
  `section` varchar(100) character set latin1 NOT NULL,
  `rank` int(10) unsigned NOT NULL,
  `value1` varchar(200) character set latin1 default NULL,
  `value2` varchar(200) character set latin1 default NULL,
  `value3` varchar(200) character set latin1 default NULL,
  `value4` varchar(200) character set latin1 default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
