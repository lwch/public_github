-- MySQL dump 10.16  Distrib 10.1.9-MariaDB, for Linux (x86_64)
--
-- Host: localhost    Database: main
-- ------------------------------------------------------
-- Server version	10.1.9-MariaDB

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
) ENGINE=InnoDB AUTO_INCREMENT=189 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wp_terms`
--

LOCK TABLES `wp_terms` WRITE;
/*!40000 ALTER TABLE `wp_terms` DISABLE KEYS */;
INSERT INTO `wp_terms` VALUES (1,'other','other',0),(6,'C','c',0),(9,'main','main',0),(12,'Java','java',0),(13,'C++','c-2',0),(14,'JavaScript','javascript',0),(15,'HTML','html',0),(16,'Python','python',0),(17,'CoffeeScript','coffeescript',0),(18,'Go','go',0),(19,'Ruby','ruby',0),(20,'CSS','css',0),(21,'Objective-C','objective-c',0),(22,'C#','c-3',0),(23,'Shell','shell',0),(24,'PHP','php',0),(25,'Scala','scala',0),(26,'VimL','viml',0),(27,'Julia','julia',0),(28,'Groff','groff',0),(29,'Rust','rust',0),(30,'Clojure','clojure',0),(31,'Perl','perl',0),(32,'TeX','tex',0),(33,'Puppet','puppet',0),(34,'PLSQL','plsql',0),(35,'Swift','swift',0),(36,'Forth','forth',0),(37,'Batchfile','batchfile',0),(38,'Nix','nix',0),(39,'Elixir','elixir',0),(40,'Perl6','perl6',0),(41,'TypeScript','typescript',0),(42,'Lua','lua',0),(43,'R','r',0),(44,'Smarty','smarty',0),(45,'Scheme','scheme',0),(46,'Dart','dart',0),(47,'KiCad','kicad',0),(48,'Liquid','liquid',0),(49,'Haskell','haskell',0),(50,'Eagle','eagle',0),(51,'F#','f',0),(52,'Yacc','yacc',0),(53,'ASP','asp',0),(54,'D','d',0),(55,'OCaml','ocaml',0),(56,'Kotlin','kotlin',0),(57,'SQF','sqf',0),(58,'Makefile','makefile',0),(59,'Matlab','matlab',0),(60,'Visual Basic','visual-basic',0),(61,'Chapel','chapel',0),(62,'Tcl','tcl',0),(63,'Mathematica','mathematica',0),(64,'Max','max',0),(65,'Processing','processing',0),(66,'Racket','racket',0),(67,'Arduino','arduino',0),(68,'PLpgSQL','plpgsql',0),(69,'Pascal','pascal',0),(70,'Hack','hack',0),(71,'Groovy','groovy',0),(72,'ActionScript','actionscript',0),(73,'CMake','cmake',0),(74,'Prolog','prolog',0),(75,'Turing','turing',0),(76,'Common Lisp','common-lisp',0),(77,'SourcePawn','sourcepawn',0),(78,'AutoHotkey','autohotkey',0),(79,'Erlang','erlang',0),(80,'PowerShell','powershell',0),(81,'AMPL','ampl',0),(82,'Crystal','crystal',0),(83,'PostScript','postscript',0),(84,'FORTRAN','fortran',0),(85,'XSLT','xslt',0),(86,'Emacs Lisp','emacs-lisp',0),(87,'Assembly','assembly',0),(88,'API Blueprint','api-blueprint',0),(89,'Standard ML','standard-ml',0),(90,'VHDL','vhdl',0),(91,'Haxe','haxe',0),(92,'Grace','grace',0),(93,'Cucumber','cucumber',0),(94,'XQuery','xquery',0),(95,'ApacheConf','apacheconf',0),(96,'Idris','idris',0),(97,'Smali','smali',0),(98,'Pure Data','pure-data',0),(99,'Web Ontology Language','web-ontology-language',0),(100,'PureScript','purescript',0),(101,'Coq','coq',0),(102,'DM','dm',0),(103,'Elm','elm',0),(104,'Apex','apex',0),(105,'NCL','ncl',0),(106,'Vala','vala',0),(107,'GLSL','glsl',0),(108,'Alloy','alloy',0),(109,'BitBake','bitbake',0),(110,'Xtend','xtend',0),(111,'Game Maker Language','game-maker-language',0),(112,'Vue','vue',0),(113,'DIGITAL Command Language','digital-command-language',0),(114,'SQLPL','sqlpl',0),(115,'xBase','xbase',0),(116,'Smalltalk','smalltalk',0),(117,'GAP','gap',0),(118,'LiveScript','livescript',0),(119,'RobotFramework','robotframework',0),(120,'OpenEdge ABL','openedge-abl',0),(121,'Modelica','modelica',0),(122,'Papyrus','papyrus',0),(123,'Objective-C++','objective-c-2',0),(124,'LLVM','llvm',0),(125,'Factor','factor',0),(126,'ABAP','abap',0),(127,'LookML','lookml',0),(128,'Logos','logos',0),(129,'Verilog','verilog',0),(130,'NetLogo','netlogo',0),(131,'Xojo','xojo',0),(132,'Cuda','cuda',0),(133,'SystemVerilog','systemverilog',0),(134,'Ada','ada',0),(135,'Monkey','monkey',0),(136,'ColdFusion','coldfusion',0),(137,'Nginx','nginx',0),(138,'NSIS','nsis',0),(139,'HCL','hcl',0),(140,'Propeller Spin','propeller-spin',0),(141,'OpenSCAD','openscad',0),(142,'Mercury','mercury',0),(143,'Logtalk','logtalk',0),(144,'Nimrod','nimrod',0),(145,'Bluespec','bluespec',0),(146,'PAWN','pawn',0),(147,'ooc','ooc',0),(148,'BlitzMax','blitzmax',0),(149,'Scilab','scilab',0),(150,'Mako','mako',0),(151,'Harbour','harbour',0),(152,'FreeMarker','freemarker',0),(153,'XProc','xproc',0),(154,'Parrot','parrot',0),(155,'AutoIt','autoit',0),(156,'Stata','stata',0),(157,'APL','apl',0),(158,'Agda','agda',0),(159,'XC','xc',0),(160,'CartoCSS','cartocss',0),(161,'LSL','lsl',0),(162,'LilyPond','lilypond',0),(163,'ATS','ats',0),(164,'SaltStack','saltstack',0),(165,'Protocol Buffer','protocol-buffer',0),(166,'Ceylon','ceylon',0),(167,'Pike','pike',0),(168,'Gosu','gosu',0),(169,'QML','qml',0),(170,'Mask','mask',0),(171,'RAML','raml',0),(172,'SuperCollider','supercollider',0),(173,'Volt','volt',0),(174,'E','e',0),(175,'Grammatical Framework','grammatical-framework',0),(176,'Dylan','dylan',0),(177,'IGOR Pro','igor-pro',0),(178,'GDScript','gdscript',0),(179,'Jupyter Notebook','jupyter-notebook',0),(180,'Modula-2','modula-2',0),(181,'mupad','mupad',0),(182,'Component Pascal','component-pascal',0),(183,'MAXScript','maxscript',0),(184,'wisp','wisp',0),(185,'nesC','nesc',0),(186,'Awk','awk',0),(187,'Ragel in Ruby Host','ragel-in-ruby-host',0),(188,'AGS Script','ags-script',0);
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

-- Dump completed on 2016-01-03 18:53:42
