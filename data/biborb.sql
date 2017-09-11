# phpMyAdmin SQL Dump
# version 2.5.5-pl1
# http://www.phpmyadmin.net
#
# Serveur: localhost
# Généré le : Vendredi 20 Août 2004 à 19:53
# Version du serveur: 4.0.15
# Version de PHP: 4.3.4
# 
# Base de données: `biborb`
# 

# --------------------------------------------------------

#
# Structure of table `biborb_auth`
#

CREATE TABLE `biborb_auth` (
  `user_id` int(11) NOT NULL default '0',
  `db_name` varchar(100) NOT NULL default '',
  `access` char(3) NOT NULL default ''
) TYPE=MyISAM;

# --------------------------------------------------------

#
# Structure of table `biborb_users`
#

CREATE TABLE `biborb_users` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `login` varchar(20) NOT NULL default '',
  `password` varchar(32) NOT NULL default '0',
  `name` varchar(20) NOT NULL default '',
  `firstname` varchar(20) NOT NULL default '',
  `admin` char(1) NOT NULL default 'N',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `numero` (`id`)
) TYPE=MyISAM;


-- 
-- Table structure for table `user_preferences`
-- 

CREATE TABLE `user_preferences` (
  `user_id` int(11) NOT NULL default '0',
  `css_file` char(255) NOT NULL default '',
  `default_language` char(255) NOT NULL default '',
  `default_database` char(255) NOT NULL default '',
  `display_images` char(1) NOT NULL default '',
  `display_txt` char(1) NOT NULL default '',
  `display_abstract` char(1) NOT NULL default '',
  `warn_before_deleting` char(1) NOT NULL default '',
  `display_sort` char(1) NOT NULL default '',
  `default_sort` char(255) NOT NULL default '',
  `default_sort_order` char(255) NOT NULL default '',
  `max_ref_by_page` int(11) NOT NULL default '0',
  `display_shelf_actions` char(1) NOT NULL default '',
  PRIMARY KEY  (`user_id`)
) TYPE=MyISAM;


-- Default values
INSERT INTO `biborb_users` (`id`, `login`, `password`, `name`, `firstname`, `admin`) VALUES (1, 'admin', md5('admin'), '', '','Y');
INSERT INTO `biborb_users` (`login`, `admin`) VALUES ('_anonymous_', 'N');
