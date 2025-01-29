-- MySQL dump 10.13  Distrib 8.0.19, for Win64 (x86_64)
--
-- Host: localhost    Database: natasha
-- ------------------------------------------------------
-- Server version	9.1.0

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `clients`
--

DROP TABLE IF EXISTS `clients`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `clients` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `prenom` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `telephone` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `numero_cni` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `copie_cni` blob,
  `adresse` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `cp` varchar(10) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ville` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `agence` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `etat` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `clients`
--

LOCK TABLES `clients` WRITE;
/*!40000 ALTER TABLE `clients` DISABLE KEYS */;
INSERT INTO `clients` VALUES (1,'Bourst','Arthur','arthur.bourst@mail.fr','07 07 07 07 07','12',NULL,'42 rue de Noel','59550','cambrai','cambrai',NULL),(2,'Trioux','Rémy','remy.trioux@mail.Fr','06 06 06 06 06','13',NULL,'52 rue de l\'an','45789','Lille','Cambrai',NULL),(3,'Doe','John','john.doe@example.com','1234567890','CNI123456',_binary 'path/to/cni1.jpg','123 Main St','12345','CityA','Agency1','Active'),(4,'Smith','Jane','jane.smith@example.com','0987654321','CNI654321',_binary 'path/to/cni2.jpg','456 Elm St','54321','CityB','Agency2','Inactive'),(5,'Brown','Charlie','charlie.brown@example.com','1122334455','CNI112233',_binary 'path/to/cni3.jpg','789 Oak St','67890','CityC','Agency3','Active'),(6,'Johnson','Emily','emily.johnson@example.com','5566778899','CNI445566',_binary 'path/to/cni4.jpg','321 Pine St','98765','CityD','Agency4','Inactive'),(7,'Williams','Michael','michael.williams@example.com','6677889900','CNI778899',_binary 'path/to/cni5.jpg','654 Maple St','13579','CityE','Agency5','Active'),(8,'Dupont','Pierre','pierre.dupont@email.com','0601020304','1234567890123',NULL,'12 rue des Fleurs','75001','Paris','Agence Paris 1',NULL),(9,'Martin','Sophie','sophie.martin@email.com','0605060708','2345678901234',NULL,'3 avenue des Champs','75002','Paris','Agence Paris 2',NULL),(10,'Lemoine','Julien','julien.lemoine@email.com','0611121314','3456789012345',NULL,'5 rue de la Paix','69001','Lyon','Agence Lyon 1',NULL),(11,'Moreau','Claire','claire.moreau@email.com','0622334455','4567890123456',NULL,'8 boulevard Saint-Germain','75003','Paris','Agence Paris 3',NULL),(12,'Fournier','Nicolas','nicolas.fournier@email.com','0633445566','5678901234567',NULL,'10 rue de la République','13001','Marseille','Agence Marseille 1',NULL),(13,'Bernard','Isabelle','isabelle.bernard@email.com','0644556677','6789012345678',NULL,'14 place de l\'Opéra','75004','Paris','Agence Paris 4',NULL),(14,'Robert','Michel','michel.robert@email.com','0655667788','7890123456789',NULL,'16 rue Victor Hugo','69002','Lyon','Agence Lyon 2',NULL),(15,'Petit','Lucie','lucie.petit@email.com','0676778899','8901234567890',NULL,'20 rue de la Liberté','06000','Nice','Agence Nice 1',NULL),(16,'Girard','David','david.girard@email.com','0687889900','9012345678901',NULL,'22 avenue des Ternes','75005','Paris','Agence Paris 5',NULL),(17,'Dumont','Caroline','caroline.dumont@email.com','0698990011','0123456789012',NULL,'25 rue de la Gare','44000','Nantes','Agence Nantes 1',NULL),(18,'Roux','Pierre','pierre.roux@email.com','0700011122','1234567890123',NULL,'28 rue des Lilas','31000','Toulouse','Agence Toulouse 1',NULL),(19,'Clement','Sophie','sophie.clement@email.com','0711122233','2345678901234',NULL,'30 rue de la Montagne','75006','Paris','Agence Paris 6',NULL),(20,'Pires','Carlos','carlos.pires@email.com','0722233344','3456789012345',NULL,'35 rue de la Liberté','06001','Cannes','Agence Cannes 1',NULL),(21,'Lemoine','Valérie','valerie.lemoine@email.com','0733344455','4567890123456',NULL,'40 avenue de l\'Europe','44001','Nantes','Agence Nantes 2',NULL),(22,'Benoit','Emilie','emilie.benoit@email.com','0744455566','5678901234567',NULL,'45 place de la Concorde','75007','Paris','Agence Paris 7',NULL),(23,'Gauthier','Louis','louis.gauthier@email.com','0755566677','6789012345678',NULL,'50 rue de la Paix','13002','Marseille','Agence Marseille 2',NULL),(24,'Faure','Monique','monique.faure@email.com','0766677788','7890123456789',NULL,'55 rue du Faubourg','69003','Lyon','Agence Lyon 3',NULL),(25,'Dufresne','Thierry','thierry.dufresne@email.com','0777788899','8901234567890',NULL,'60 boulevard Montmartre','06002','Nice','Agence Nice 2',NULL),(26,'Giraud','Sébastien','sebastien.giraud@email.com','0788899900','9012345678901',NULL,'65 rue de l\'Hôtel de Ville','30000','Nîmes','Agence Nîmes 1',NULL),(27,'Collin','Chloé','chloe.collin@email.com','0799000111','0123456789012',NULL,'70 rue du Commerce','59000','Lille','Agence Lille 1',NULL);
/*!40000 ALTER TABLE `clients` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `testtablelogin`
--

DROP TABLE IF EXISTS `testtablelogin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `testtablelogin` (
  `idUser` int NOT NULL AUTO_INCREMENT,
  `identifiantUser` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `passwordUser` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`idUser`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `testtablelogin`
--

LOCK TABLES `testtablelogin` WRITE;
/*!40000 ALTER TABLE `testtablelogin` DISABLE KEYS */;
INSERT INTO `testtablelogin` VALUES (1,'salut','$2y$10$BdUSZDoin6GDt1ItQm/k1uANp9/OmtWNy22ngnQ/X1fAAdEZ3ZEBK');
/*!40000 ALTER TABLE `testtablelogin` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vehicules`
--

DROP TABLE IF EXISTS `vehicules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `vehicules` (
  `id` int NOT NULL AUTO_INCREMENT,
  `marque` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `model` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `immatriculation` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `carte_grise` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `puissance` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `type_boite` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `finition` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `kilometrage` int DEFAULT NULL,
  `couleur` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `date_entretien` date DEFAULT NULL,
  `frais_recent` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `frais_prevoir` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `agence` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vehicules`
--

LOCK TABLES `vehicules` WRITE;
/*!40000 ALTER TABLE `vehicules` DISABLE KEYS */;
INSERT INTO `vehicules` VALUES (3,'TOYOTA','YARIS CROSS','GY-358-NA',NULL,'5','Automatique','GRIS',5500,'BLANC','2024-07-20','RAS','RAS','Cambrai'),(4,'BUGATTI','CHIRON','ZZ-111-ZZ',NULL,'7','Automatique','BLANC',5555,'BLANC','2024-07-24','RAS','RAS','Cambrai'),(5,'AUDI','A3','ZZ-111-12',NULL,'(','Automatique','DESIGN',1111,'BLANC','2024-01-01','RAS','RAS','Cambrai'),(6,'BMW','M3','ZZ-111-11','[object Object]','(','Automatique','DESIGN',1111,'BLANC','2024-01-01','RAS','RAS','Cambrai'),(7,'PORSCHE','GT3 RS','AA-000-AA','[object Object]','(','Automatique','DESIGN',1111,'BLANC','2024-01-01','RAS','RAS','Cambrai'),(8,'CITROEN','C3','AA-001-AA','[object Object]','(','Automatique','DESIGN',1111,'BLANC','2024-01-01','RAS','RAS','Cambrai');
/*!40000 ALTER TABLE `vehicules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'natasha'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-01-29 20:55:01
