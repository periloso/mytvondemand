SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;


CREATE TABLE IF NOT EXISTS `configuration` (
  `key` varchar(30) NOT NULL,
  `value1` varchar(100) NOT NULL,
  `value2` int(11) NOT NULL,
  `value3` date NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `downloaded` (
  `id` int(11) NOT NULL auto_increment,
  `showid` int(11) NOT NULL,
  `season` int(11) NOT NULL,
  `episodeid` int(11) NOT NULL,
  `subbed` tinyint(4) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=30 ;

CREATE TABLE IF NOT EXISTS `episodes` (
  `id` int(11) NOT NULL,
  `showid` int(11) NOT NULL,
  `season` int(11) NOT NULL,
  `title` text NOT NULL,
  `plot` text NOT NULL,
  `aired` date NOT NULL,
  `thumbnail` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `pending` (
  `id` int(11) NOT NULL auto_increment,
  `TR_TORRENT_DIR` text NOT NULL,
  `TR_TORRENT_NAME` text NOT NULL,
  `TR_TORRENT_HASH` text NOT NULL,
  `episode` int(11) NOT NULL,
  `season` int(11) NOT NULL,
  `showid` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=34 ;

CREATE TABLE IF NOT EXISTS `shows` (
  `id` int(11) NOT NULL auto_increment,
  `thetvdbid` int(11) NOT NULL,
  `title` text NOT NULL,
  `plot` text NOT NULL,
  `lastthetvdbupdate` date NOT NULL,
  `lasteztvupdate` date NOT NULL,
  `torrentlink` text NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=428 ;

CREATE TABLE IF NOT EXISTS `subscriptions` (
  `showid` int(11) NOT NULL,
  `quality` tinyint(4) NOT NULL,
  `subtitles` text NOT NULL,
  UNIQUE KEY `showid` (`showid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `torrents` (
  `showid` int(11) NOT NULL,
  `season` int(11) NOT NULL,
  `episode` int(11) NOT NULL,
  `link` text NOT NULL,
  `hash` text NOT NULL,
  `quality` tinyint(4) NOT NULL,
  UNIQUE KEY `showid` (`showid`,`season`,`episode`,`quality`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `configuration` (`key`, `value1`, `value2`, `value3`) VALUES ('transmissionurl', 'http://localhost:9091/transmission/rpc/', 0, '0000-00-00')
