/*
SQLyog Ultimate v12.09 (64 bit)
MySQL - 5.6.23 : Database - test
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
/*Table structure for table `lw_talk` */

DROP TABLE IF EXISTS `lw_talk`;

CREATE TABLE `lw_talk` (
  `tid` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(11) unsigned NOT NULL,
  `ptid` int(10) unsigned NOT NULL,
  `uid` int(10) unsigned NOT NULL,
  `subject` varchar(50) NOT NULL,
  `message` text NOT NULL,
  `dateline` int(10) NOT NULL,
  PRIMARY KEY (`tid`),
  KEY `ptid` (`ptid`),
  KEY `ix2` (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `lw_user` */

DROP TABLE IF EXISTS `lw_user`;

CREATE TABLE `lw_user` (
  `uid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` char(32) NOT NULL,
  `email` varchar(128) NOT NULL,
  `regtime` int(11) NOT NULL,
  `regip` varchar(15) NOT NULL,
  `logtime` int(11) NOT NULL,
  `logip` varchar(15) NOT NULL,
  `prevlogtime` int(11) NOT NULL,
  `prevlogip` varchar(15) NOT NULL,
  `is_profiled` smallint(1) unsigned NOT NULL,
  PRIMARY KEY (`uid`),
  UNIQUE KEY `ix1` (`username`),
  UNIQUE KEY `ix2` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `lw_user_profile` */

DROP TABLE IF EXISTS `lw_user_profile`;

CREATE TABLE `lw_user_profile` (
  `uid` int(10) unsigned NOT NULL,
  `nickname` varchar(30) NOT NULL,
  `sex` tinyint(1) unsigned NOT NULL,
  `birthday` int(11) NOT NULL,
  `qq` bigint(5) unsigned NOT NULL,
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
