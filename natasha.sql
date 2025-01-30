-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : jeu. 30 jan. 2025 à 21:10
-- Version du serveur : 9.1.0
-- Version de PHP : 8.3.15

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `natasha`
--

-- --------------------------------------------------------

--
-- Structure de la table `clients`
--

DROP TABLE IF EXISTS `clients`;
CREATE TABLE IF NOT EXISTS `clients` (
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

--
-- Déchargement des données de la table `clients`
--

INSERT INTO `clients` (`id`, `nom`, `prenom`, `email`, `telephone`, `numero_cni`, `copie_cni`, `adresse`, `cp`, `ville`, `agence`, `etat`) VALUES
(1, 'Bourst', 'Arthur', 'arthur.bourst@mail.fr', '07 07 07 07 07', '12', NULL, '42 rue de Noel', '59550', 'cambrai', 'cambrai', NULL),
(2, 'Trioux', 'Rémy', 'remy.trioux@mail.Fr', '06 06 06 06 06', '13', NULL, '52 rue de l\'an', '45789', 'Lille', 'Cambrai', NULL),
(3, 'Doe', 'John', 'john.doe@example.com', '1234567890', 'CNI123456', 0x706174682f746f2f636e69312e6a7067, '123 Main St', '12345', 'CityA', 'Agency1', 'Active'),
(4, 'Smith', 'Jane', 'jane.smith@example.com', '0987654321', 'CNI654321', 0x706174682f746f2f636e69322e6a7067, '456 Elm St', '54321', 'CityB', 'Agency2', 'Inactive'),
(5, 'Brown', 'Charlie', 'charlie.brown@example.com', '1122334455', 'CNI112233', 0x706174682f746f2f636e69332e6a7067, '789 Oak St', '67890', 'CityC', 'Agency3', 'Active'),
(6, 'Johnson', 'Emily', 'emily.johnson@example.com', '5566778899', 'CNI445566', 0x706174682f746f2f636e69342e6a7067, '321 Pine St', '98765', 'CityD', 'Agency4', 'Inactive'),
(7, 'Williams', 'Michael', 'michael.williams@example.com', '6677889900', 'CNI778899', 0x706174682f746f2f636e69352e6a7067, '654 Maple St', '13579', 'CityE', 'Agency5', 'Active'),
(8, 'Dupont', 'Pierre', 'pierre.dupont@email.com', '0601020304', '1234567890123', NULL, '12 rue des Fleurs', '75001', 'Paris', 'Agence Paris 1', NULL),
(9, 'Martin', 'Sophie', 'sophie.martin@email.com', '0605060708', '2345678901234', NULL, '3 avenue des Champs', '75002', 'Paris', 'Agence Paris 2', NULL),
(10, 'Lemoine', 'Julien', 'julien.lemoine@email.com', '0611121314', '3456789012345', NULL, '5 rue de la Paix', '69001', 'Lyon', 'Agence Lyon 1', NULL),
(11, 'Moreau', 'Claire', 'claire.moreau@email.com', '0622334455', '4567890123456', NULL, '8 boulevard Saint-Germain', '75003', 'Paris', 'Agence Paris 3', NULL),
(12, 'Fournier', 'Nicolas', 'nicolas.fournier@email.com', '0633445566', '5678901234567', NULL, '10 rue de la République', '13001', 'Marseille', 'Agence Marseille 1', NULL),
(13, 'Bernard', 'Isabelle', 'isabelle.bernard@email.com', '0644556677', '6789012345678', NULL, '14 place de l\'Opéra', '75004', 'Paris', 'Agence Paris 4', NULL),
(14, 'Robert', 'Michel', 'michel.robert@email.com', '0655667788', '7890123456789', NULL, '16 rue Victor Hugo', '69002', 'Lyon', 'Agence Lyon 2', NULL),
(15, 'Petit', 'Lucie', 'lucie.petit@email.com', '0676778899', '8901234567890', NULL, '20 rue de la Liberté', '06000', 'Nice', 'Agence Nice 1', NULL),
(16, 'Girard', 'David', 'david.girard@email.com', '0687889900', '9012345678901', NULL, '22 avenue des Ternes', '75005', 'Paris', 'Agence Paris 5', NULL),
(17, 'Dumont', 'Caroline', 'caroline.dumont@email.com', '0698990011', '0123456789012', NULL, '25 rue de la Gare', '44000', 'Nantes', 'Agence Nantes 1', NULL),
(18, 'Roux', 'Pierre', 'pierre.roux@email.com', '0700011122', '1234567890123', NULL, '28 rue des Lilas', '31000', 'Toulouse', 'Agence Toulouse 1', NULL),
(19, 'Clement', 'Sophie', 'sophie.clement@email.com', '0711122233', '2345678901234', NULL, '30 rue de la Montagne', '75006', 'Paris', 'Agence Paris 6', NULL),
(20, 'Pires', 'Carlos', 'carlos.pires@email.com', '0722233344', '3456789012345', NULL, '35 rue de la Liberté', '06001', 'Cannes', 'Agence Cannes 1', NULL),
(21, 'Lemoine', 'Valérie', 'valerie.lemoine@email.com', '0733344455', '4567890123456', NULL, '40 avenue de l\'Europe', '44001', 'Nantes', 'Agence Nantes 2', NULL),
(22, 'Benoit', 'Emilie', 'emilie.benoit@email.com', '0744455566', '5678901234567', NULL, '45 place de la Concorde', '75007', 'Paris', 'Agence Paris 7', NULL),
(23, 'Gauthier', 'Louis', 'louis.gauthier@email.com', '0755566677', '6789012345678', NULL, '50 rue de la Paix', '13002', 'Marseille', 'Agence Marseille 2', NULL),
(24, 'Faure', 'Monique', 'monique.faure@email.com', '0766677788', '7890123456789', NULL, '55 rue du Faubourg', '69003', 'Lyon', 'Agence Lyon 3', NULL),
(25, 'Dufresne', 'Thierry', 'thierry.dufresne@email.com', '0777788899', '8901234567890', NULL, '60 boulevard Montmartre', '06002', 'Nice', 'Agence Nice 2', NULL),
(26, 'Giraud', 'Sébastien', 'sebastien.giraud@email.com', '0788899900', '9012345678901', NULL, '65 rue de l\'Hôtel de Ville', '30000', 'Nîmes', 'Agence Nîmes 1', NULL),
(27, 'Collin', 'Chloé', 'chloe.collin@email.com', '0799000111', '0123456789012', NULL, '70 rue du Commerce', '59000', 'Lille', 'Agence Lille 1', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `testtablelogin`
--

DROP TABLE IF EXISTS `testtablelogin`;
CREATE TABLE IF NOT EXISTS `testtablelogin` (
  `idUser` int NOT NULL AUTO_INCREMENT,
  `identifiantUser` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `passwordUser` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`idUser`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `testtablelogin`
--

INSERT INTO `testtablelogin` (`idUser`, `identifiantUser`, `passwordUser`) VALUES
(1, 'salut', '$2y$10$BdUSZDoin6GDt1ItQm/k1uANp9/OmtWNy22ngnQ/X1fAAdEZ3ZEBK');

-- --------------------------------------------------------

--
-- Structure de la table `vehicules`
--

DROP TABLE IF EXISTS `vehicules`;
CREATE TABLE IF NOT EXISTS `vehicules` (
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
) ENGINE=InnoDB AUTO_INCREMENT=94 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `vehicules`
--

INSERT INTO `vehicules` (`id`, `marque`, `model`, `immatriculation`, `carte_grise`, `puissance`, `type_boite`, `finition`, `kilometrage`, `couleur`, `date_entretien`, `frais_recent`, `frais_prevoir`, `agence`) VALUES
(3, 'TOYOTA', 'YARIS CROSS', 'GY-358-NA', NULL, '5', 'Automatique', 'GRIS', 5500, 'BLANC', '2024-07-20', 'RAS', 'RAS', 'Cambrai'),
(4, 'BUGATTI', 'CHIRON', 'ZZ-111-ZZ', NULL, '7', 'Automatique', 'BLANC', 5555, 'BLANC', '2024-07-24', 'RAS', 'RAS', 'Cambrai'),
(5, 'AUDI', 'A3', 'ZZ-111-12', NULL, '(', 'Automatique', 'DESIGN', 1111, 'BLANC', '2024-01-01', 'RAS', 'RAS', 'Cambrai'),
(6, 'BMW', 'M3', 'ZZ-111-11', '[object Object]', '(', 'Automatique', 'DESIGN', 1111, 'BLANC', '2024-01-01', 'RAS', 'RAS', 'Cambrai'),
(7, 'PORSCHE', 'GT3 RS', 'AA-000-AA', '[object Object]', '(', 'Automatique', 'DESIGN', 1111, 'BLANC', '2024-01-01', 'RAS', 'RAS', 'Cambrai'),
(8, 'CITROEN', 'C3', 'AA-001-AA', '[object Object]', '(', 'Automatique', 'DESIGN', 1111, 'BLANC', '2024-01-01', 'RAS', 'RAS', 'Cambrai'),
(9, 'Peugeot', '208', 'AB-123-CD', '1234567890', '75', 'Manuelle', 'Active', 15000, 'Rouge', '2024-01-10', '150', '300', 'Paris'),
(10, 'Renault', 'Clio', 'EF-234-GH', '0987654321', '90', 'Automatique', 'Intens', 25000, 'Bleu', '2023-12-05', '200', '400', 'Lyon'),
(11, 'Citroen', 'C3', 'IJ-345-KL', '1122334455', '95', 'Manuelle', 'Feel', 18000, 'Blanc', '2024-01-15', '120', '250', 'Marseille'),
(12, 'Volkswagen', 'Golf', 'MN-456-OP', '6677889900', '110', 'Automatique', 'GTI', 12000, 'Noir', '2023-11-30', '180', '350', 'Toulouse'),
(13, 'BMW', 'Serie 3', 'QR-567-ST', '2233445566', '150', 'Manuelle', 'Sport', 45000, 'Gris', '2023-10-22', '250', '500', 'Nice'),
(14, 'Mercedes', 'A-Class', 'UV-678-WX', '3344556677', '130', 'Automatique', 'Premium', 22000, 'Argent', '2023-11-10', '300', '400', 'Bordeaux'),
(15, 'Audi', 'A3', 'YZ-789-AB', '4455667788', '125', 'Manuelle', 'Ambition', 18000, 'Bleu', '2024-01-03', '230', '380', 'Lille'),
(16, 'Toyota', 'Yaris', 'CD-890-EF', '5566778899', '70', 'Automatique', 'Luxe', 10000, 'Jaune', '2023-12-10', '100', '150', 'Strasbourg'),
(17, 'Ford', 'Focus', 'GH-901-IJ', '6677889900', '120', 'Manuelle', 'Titanium', 20000, 'Vert', '2023-11-01', '150', '200', 'Paris'),
(18, 'Nissan', 'Micra', 'KL-012-MN', '7788990011', '85', 'Automatique', 'Visia', 14000, 'Violet', '2024-01-07', '90', '180', 'Lyon'),
(19, 'Honda', 'Civic', 'OP-123-QR', '8899001122', '135', 'Manuelle', 'Executive', 35000, 'Gris', '2023-10-30', '220', '350', 'Marseille'),
(20, 'Kia', 'Rio', 'ST-234-UV', '9900112233', '100', 'Automatique', 'GT Line', 17000, 'Rouge', '2023-12-15', '130', '250', 'Toulouse'),
(21, 'Fiat', 'Punto', 'WX-345-YZ', '1122334455', '75', 'Manuelle', 'Lounge', 16000, 'Blanc', '2023-11-20', '100', '200', 'Nice'),
(22, 'Skoda', 'Octavia', 'YZ-456-AB', '2233445566', '110', 'Automatique', 'Style', 19000, 'Noir', '2024-01-10', '170', '300', 'Bordeaux'),
(23, 'Mazda', '3', 'CD-567-DE', '3344556677', '120', 'Manuelle', 'Sport', 23000, 'Gris', '2023-09-15', '200', '400', 'Lille'),
(24, 'Opel', 'Astra', 'FG-678-HI', '4455667788', '100', 'Automatique', 'Cosmo', 21000, 'Bleu', '2023-12-05', '160', '300', 'Strasbourg'),
(25, 'Peugeot', '3008', 'IJ-789-KL', '5566778899', '160', 'Manuelle', 'GT Line', 28000, 'Rouge', '2023-11-25', '250', '450', 'Paris'),
(26, 'Renault', 'Kadjar', 'KL-890-MN', '6677889900', '140', 'Automatique', 'Zen', 24000, 'Gris', '2023-12-22', '220', '400', 'Lyon'),
(27, 'Citroen', 'C4', 'MN-901-OP', '7788990011', '120', 'Manuelle', 'Business', 19000, 'Blanc', '2024-01-10', '180', '350', 'Marseille'),
(28, 'Volkswagen', 'Tiguan', 'OP-012-QR', '8899001122', '170', 'Automatique', 'R-Line', 30000, 'Noir', '2023-10-10', '300', '500', 'Toulouse'),
(29, 'BMW', 'X1', 'ST-123-UV', '9900112233', '180', 'Manuelle', 'X-Line', 22000, 'Gris', '2023-12-18', '320', '500', 'Nice'),
(30, 'Mercedes', 'B-Class', 'UV-234-WX', '1122334455', '150', 'Automatique', 'AMG', 21000, 'Argent', '2023-11-05', '280', '450', 'Bordeaux'),
(31, 'Audi', 'Q3', 'WX-345-YZ', '2233445566', '190', 'Manuelle', 'S-Line', 25000, 'Bleu', '2024-01-05', '350', '500', 'Lille'),
(32, 'Toyota', 'Corolla', 'YZ-456-AB', '3344556677', '130', 'Automatique', 'Luxe', 22000, 'Jaune', '2023-12-03', '200', '350', 'Strasbourg'),
(33, 'Ford', 'Fiesta', 'AB-567-CD', '4455667788', '85', 'Manuelle', 'Trend', 16000, 'Vert', '2023-10-01', '120', '200', 'Paris'),
(34, 'Nissan', 'Juke', 'EF-678-GH', '5566778899', '115', 'Automatique', 'N-Connecta', 20000, 'Violet', '2023-11-10', '180', '300', 'Lyon'),
(35, 'Honda', 'HR-V', 'GH-789-IJ', '6677889900', '140', 'Manuelle', 'Executive', 27000, 'Gris', '2023-12-05', '250', '400', 'Marseille'),
(36, 'Kia', 'Sportage', 'IJ-890-KL', '7788990011', '160', 'Automatique', 'GT Line', 30000, 'Rouge', '2023-09-15', '270', '500', 'Toulouse'),
(37, 'Fiat', '500', 'KL-901-MN', '8899001122', '70', 'Manuelle', 'Lounge', 12000, 'Blanc', '2024-01-10', '80', '150', 'Nice'),
(38, 'Skoda', 'Superb', 'MN-012-OP', '9900112233', '180', 'Automatique', 'Style', 40000, 'Noir', '2023-12-12', '300', '550', 'Bordeaux'),
(39, 'Mazda', 'CX-5', 'OP-123-QR', '1122334455', '170', 'Manuelle', 'Sport', 35000, 'Gris', '2023-11-25', '280', '500', 'Lille'),
(40, 'Opel', 'Grandland', 'QR-234-ST', '2233445566', '140', 'Automatique', 'Cosmo', 29000, 'Bleu', '2023-12-20', '250', '450', 'Strasbourg'),
(41, 'Peugeot', '5008', 'ST-345-UV', '3344556677', '180', 'Manuelle', 'GT Line', 32000, 'Rouge', '2023-10-30', '330', '550', 'Paris'),
(42, 'Renault', 'Espace', 'UV-456-WX', '4455667788', '200', 'Automatique', 'Initiale', 37000, 'Gris', '2023-11-15', '350', '600', 'Lyon'),
(43, 'Citroen', 'C5 Aircross', 'WX-567-YZ', '5566778899', '180', 'Manuelle', 'Business', 25000, 'Blanc', '2023-12-03', '280', '450', 'Marseille'),
(44, 'Volkswagen', 'Passat', 'YZ-678-AB', '6677889900', '190', 'Automatique', 'Elegance', 28000, 'Noir', '2023-11-05', '300', '500', 'Toulouse'),
(45, 'BMW', 'X3', 'AB-789-CD', '7788990011', '200', 'Manuelle', 'X-Line', 38000, 'Gris', '2023-10-10', '350', '600', 'Nice'),
(46, 'Mercedes', 'GLA', 'EF-890-GH', '8899001122', '220', 'Automatique', 'AMG', 32000, 'Argent', '2023-12-01', '400', '650', 'Bordeaux'),
(47, 'Audi', 'Q5', 'GH-901-IJ', '9900112233', '250', 'Manuelle', 'S-Line', 36000, 'Bleu', '2023-11-20', '450', '700', 'Lille'),
(48, 'Toyota', 'RAV4', 'IJ-012-KL', '1122334455', '180', 'Automatique', 'Luxe', 33000, 'Jaune', '2023-10-25', '370', '600', 'Strasbourg'),
(49, 'Ford', 'Kuga', 'KL-123-MN', '2233445566', '160', 'Manuelle', 'Titanium', 29000, 'Vert', '2023-12-15', '320', '550', 'Paris'),
(50, 'Nissan', 'Qashqai', 'MN-234-OP', '3344556677', '190', 'Automatique', 'N-Connecta', 33000, 'Violet', '2023-11-02', '350', '600', 'Lyon'),
(51, 'Honda', 'Pilot', 'OP-345-QR', '4455667788', '230', 'Manuelle', 'Elite', 40000, 'Gris', '2023-10-28', '400', '700', 'Marseille'),
(52, 'Peugeot', '508', 'AB-234-CD', '1122334455', '180', 'Manuelle', 'GT', 30000, 'Rouge', '2023-08-25', '250', '500', 'Paris'),
(53, 'Renault', 'Talisman', 'CD-345-EF', '6677889900', '200', 'Automatique', 'Intens', 27000, 'Gris', '2023-07-30', '270', '550', 'Lyon'),
(54, 'Citroen', 'Berlingo', 'EF-456-GH', '7788990011', '115', 'Manuelle', 'Feel', 22000, 'Blanc', '2023-09-10', '150', '300', 'Marseille'),
(55, 'Volkswagen', 'Polo', 'GH-567-IJ', '8899001122', '95', 'Automatique', 'Life', 19000, 'Noir', '2023-10-01', '120', '250', 'Toulouse'),
(56, 'BMW', 'X4', 'IJ-678-KL', '9900112233', '240', 'Manuelle', 'M Sport', 34000, 'Gris', '2023-08-15', '350', '600', 'Nice'),
(57, 'Mercedes', 'CLA', 'KL-789-MN', '1122334455', '210', 'Automatique', 'AMG', 25000, 'Argent', '2023-09-20', '320', '600', 'Bordeaux'),
(58, 'Audi', 'Q7', 'MN-890-OP', '2233445566', '300', 'Manuelle', 'S-Line', 45000, 'Bleu', '2023-10-05', '500', '800', 'Lille'),
(59, 'Toyota', 'Land Cruiser', 'OP-901-QR', '3344556677', '275', 'Automatique', 'Luxe', 55000, 'Jaune', '2023-09-01', '600', '900', 'Strasbourg'),
(60, 'Ford', 'Edge', 'QR-012-ST', '4455667788', '240', 'Manuelle', 'Titanium', 40000, 'Vert', '2023-10-25', '500', '750', 'Paris'),
(61, 'Nissan', 'X-Trail', 'ST-123-UV', '5566778899', '180', 'Automatique', 'Tekna', 30000, 'Violet', '2023-08-10', '400', '700', 'Lyon'),
(62, 'Honda', 'Pilot', 'UV-234-WX', '6677889900', '250', 'Manuelle', 'Elite', 42000, 'Gris', '2023-09-12', '450', '750', 'Marseille'),
(63, 'Kia', 'Sorrento', 'WX-345-YZ', '7788990011', '220', 'Automatique', 'GT Line', 37000, 'Blanc', '2023-10-01', '480', '750', 'Toulouse'),
(64, 'Fiat', '500X', 'YZ-456-AB', '8899001122', '115', 'Manuelle', 'Lounge', 20000, 'Rouge', '2023-08-18', '180', '350', 'Nice'),
(65, 'Skoda', 'Kodiaq', 'AB-567-CD', '9900112233', '190', 'Automatique', 'Style', 33000, 'Noir', '2023-07-10', '400', '650', 'Bordeaux'),
(66, 'Mazda', 'CX-3', 'CD-678-DE', '1122334455', '135', 'Manuelle', 'Sport', 28000, 'Gris', '2023-09-05', '300', '550', 'Lille'),
(67, 'Opel', 'Mokka', 'DE-789-FG', '2233445566', '150', 'Automatique', 'Innovation', 26000, 'Bleu', '2023-08-20', '230', '500', 'Strasbourg'),
(68, 'Peugeot', 'Partner', 'FG-890-HI', '3344556677', '120', 'Manuelle', 'Active', 23000, 'Rouge', '2023-07-12', '190', '400', 'Paris'),
(69, 'Renault', 'Scenic', 'HI-901-JK', '4455667788', '130', 'Automatique', 'Zen', 21000, 'Gris', '2023-06-18', '210', '380', 'Lyon'),
(70, 'Citroen', 'Grand C4 Picasso', 'JK-012-LM', '5566778899', '160', 'Manuelle', 'Exclusive', 30000, 'Blanc', '2023-09-28', '320', '600', 'Marseille'),
(71, 'Volkswagen', 'Passat Alltrack', 'LM-123-NO', '6677889900', '200', 'Automatique', 'Business', 37000, 'Noir', '2023-10-20', '380', '650', 'Toulouse'),
(72, 'BMW', 'X5', 'NO-234-OP', '7788990011', '250', 'Manuelle', 'M Sport', 48000, 'Gris', '2023-08-05', '500', '800', 'Nice'),
(73, 'Mercedes', 'GLE', 'OP-345-QR', '8899001122', '280', 'Automatique', 'AMG', 51000, 'Argent', '2023-09-25', '550', '850', 'Bordeaux'),
(74, 'Audi', 'Q8', 'QR-456-ST', '9900112233', '350', 'Manuelle', 'S-Line', 53000, 'Bleu', '2023-07-15', '600', '1000', 'Lille'),
(75, 'Toyota', 'Hilux', 'ST-567-UV', '1122334455', '210', 'Automatique', 'Invincible', 48000, 'Jaune', '2023-09-03', '500', '900', 'Strasbourg'),
(76, 'Ford', 'Mustang', 'UV-678-WX', '2233445566', '450', 'Manuelle', 'GT', 50000, 'Rouge', '2023-07-30', '700', '1200', 'Paris'),
(77, 'Nissan', 'Navara', 'WX-789-YZ', '3344556677', '250', 'Automatique', 'Tekna', 40000, 'Violet', '2023-10-05', '600', '950', 'Lyon'),
(78, 'Honda', 'Accord', 'YZ-890-AB', '4455667788', '190', 'Manuelle', 'Executive', 33000, 'Gris', '2023-08-17', '350', '650', 'Marseille'),
(79, 'Kia', 'Stinger', 'AB-901-CD', '5566778899', '370', 'Automatique', 'GT', 55000, 'Blanc', '2023-09-12', '800', '1300', 'Toulouse'),
(80, 'Fiat', 'Panda', 'CD-012-EF', '6677889900', '70', 'Manuelle', 'Pop', 13000, 'Rouge', '2023-07-01', '100', '200', 'Nice'),
(81, 'Skoda', 'Fabia', 'EF-123-GH', '7788990011', '90', 'Automatique', 'Ambition', 15000, 'Noir', '2023-09-05', '120', '250', 'Bordeaux'),
(82, 'Mazda', 'MX-5', 'GH-234-IJ', '8899001122', '160', 'Manuelle', 'GT', 20000, 'Gris', '2023-08-21', '180', '350', 'Lille'),
(83, 'Opel', 'Insignia', 'IJ-345-KL', '9900112233', '180', 'Automatique', 'Elite', 31000, 'Bleu', '2023-09-18', '250', '450', 'Strasbourg'),
(84, 'Peugeot', '2008', 'KL-456-MN', '1122334455', '130', 'Manuelle', 'Allure', 22000, 'Rouge', '2023-07-10', '200', '400', 'Paris'),
(85, 'Renault', 'Zoe', 'MN-567-OP', '2233445566', '92', 'Automatique', 'Life', 15000, 'Gris', '2023-08-08', '120', '250', 'Lyon'),
(86, 'Citroen', 'DS3', 'OP-678-QR', '3344556677', '110', 'Manuelle', 'Performance', 17000, 'Blanc', '2023-09-05', '150', '300', 'Marseille'),
(87, 'Volkswagen', 'Beetle', 'QR-789-ST', '4455667788', '105', 'Automatique', 'Design', 14000, 'Noir', '2023-08-14', '130', '250', 'Toulouse'),
(88, 'BMW', 'i3', 'ST-890-UV', '5566778899', '170', 'Manuelle', 'Pure', 21000, 'Gris', '2023-09-22', '220', '400', 'Nice'),
(89, 'Mercedes', 'EQC', 'UV-901-WX', '6677889900', '400', 'Automatique', 'AMG', 42000, 'Argent', '2023-10-12', '500', '850', 'Bordeaux'),
(90, 'Audi', 'e-tron', 'WX-012-YZ', '7788990011', '350', 'Manuelle', 'S-Line', 48000, 'Bleu', '2023-08-22', '450', '800', 'Lille'),
(91, 'Toyota', 'Prius', 'YZ-123-AB', '8899001122', '120', 'Automatique', 'Business', 20000, 'Jaune', '2023-09-18', '250', '450', 'Strasbourg'),
(92, 'Ford', 'Escape', 'AB-234-CD', '9900112233', '170', 'Manuelle', 'Titanium', 32000, 'Vert', '2023-10-06', '300', '550', 'Paris'),
(93, 'Nissan', 'Leaf', 'CD-345-EF', '1122334455', '150', 'Automatique', 'Tekna', 29000, 'Violet', '2023-08-30', '400', '650', 'Lyon');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
