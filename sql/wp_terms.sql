-- MySQL dump 10.16  Distrib 10.1.8-MariaDB, for Linux (x86_64)
--
-- Host: localhost    Database: main
-- ------------------------------------------------------
-- Server version	10.1.8-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `wp_terms`
--

DROP TABLE IF EXISTS `wp_terms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wp_terms` (
  `term_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `slug` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `term_group` bigint(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`term_id`),
  KEY `slug` (`slug`(191)),
  KEY `name` (`name`(191))
) ENGINE=InnoDB AUTO_INCREMENT=93 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wp_terms`
--

LOCK TABLES `wp_terms` WRITE;
/*!40000 ALTER TABLE `wp_terms` DISABLE KEYS */;
INSERT INTO `wp_terms` VALUES (1,'other','other',0),(6,'C','c',0),(9,'main','main',0),(12,'Java','java',0),(13,'C++','c-2',0),(14,'JavaScript','javascript',0),(15,'HTML','html',0),(16,'Python','python',0),(17,'CoffeeScript','coffeescript',0),(18,'Go','go',0),(19,'Ruby','ruby',0),(20,'CSS','css',0),(21,'Objective-C','objective-c',0),(22,'C#','c-3',0),(23,'Shell','shell',0),(24,'PHP','php',0),(25,'Scala','scala',0),(26,'VimL','viml',0),(27,'Julia','julia',0),(28,'Groff','groff',0),(29,'Rust','rust',0),(30,'Clojure','clojure',0),(31,'Perl','perl',0),(32,'TeX','tex',0),(33,'Puppet','puppet',0),(34,'PLSQL','plsql',0),(35,'Swift','swift',0),(36,'Forth','forth',0),(37,'Batchfile','batchfile',0),(38,'Nix','nix',0),(39,'Elixir','elixir',0),(40,'Perl6','perl6',0),(41,'TypeScript','typescript',0),(42,'Lua','lua',0),(43,'R','r',0),(44,'Smarty','smarty',0),(45,'Scheme','scheme',0),(46,'Dart','dart',0),(47,'KiCad','kicad',0),(48,'Liquid','liquid',0),(49,'Haskell','haskell',0),(50,'Eagle','eagle',0),(51,'F#','f',0),(52,'Yacc','yacc',0),(53,'ASP','asp',0),(54,'D','d',0),(55,'OCaml','ocaml',0),(56,'Kotlin','kotlin',0),(57,'SQF','sqf',0),(58,'Makefile','makefile',0),(59,'Matlab','matlab',0),(60,'Visual Basic','visual-basic',0),(61,'Chapel','chapel',0),(62,'Tcl','tcl',0),(63,'Mathematica','mathematica',0),(64,'Max','max',0),(65,'Processing','processing',0),(66,'Racket','racket',0),(67,'Arduino','arduino',0),(68,'PLpgSQL','plpgsql',0),(69,'Pascal','pascal',0),(70,'Hack','hack',0),(71,'Groovy','groovy',0),(72,'ActionScript','actionscript',0),(73,'CMake','cmake',0),(74,'Prolog','prolog',0),(75,'Turing','turing',0),(76,'Common Lisp','common-lisp',0),(77,'SourcePawn','sourcepawn',0),(78,'AutoHotkey','autohotkey',0),(79,'Erlang','erlang',0),(80,'PowerShell','powershell',0),(81,'AMPL','ampl',0),(82,'Crystal','crystal',0),(83,'PostScript','postscript',0),(84,'FORTRAN','fortran',0),(85,'XSLT','xslt',0),(86,'Emacs Lisp','emacs-lisp',0),(87,'Assembly','assembly',0),(88,'API Blueprint','api-blueprint',0),(89,'Standard ML','standard-ml',0),(90,'VHDL','vhdl',0),(91,'Haxe','haxe',0),(92,'Grace','grace',0);
/*!40000 ALTER TABLE `wp_terms` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2015-11-03 16:41:44
