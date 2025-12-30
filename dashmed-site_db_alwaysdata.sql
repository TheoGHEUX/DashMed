-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: mysql-dashmed-site.alwaysdata.net
-- Generation Time: Dec 30, 2025 at 01:40 AM
-- Server version: 10.11.14-MariaDB
-- PHP Version: 8.4.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `dashmed-site_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `ALERTE`
--

CREATE TABLE `ALERTE` (
  `alerte_id` int(11) NOT NULL,
  `date_alerte` datetime NOT NULL,
  `statut` varchar(50) NOT NULL CHECK (`statut` in ('RAS','préoccupant','critique','fatal')),
  `seuil_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `GRAPHIQUE`
--

CREATE TABLE `GRAPHIQUE` (
  `graph_id` int(11) NOT NULL,
  `id_mesure` bigint(20) DEFAULT NULL,
  `titre` varchar(255) NOT NULL,
  `type_graph` varchar(50) NOT NULL CHECK (`type_graph` in ('histogramme','courbes','nuage','secteurs','autre'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `HISTORIQUE_CONSOLE`
--

CREATE TABLE `HISTORIQUE_CONSOLE` (
  `log_id` bigint(20) NOT NULL,
  `med_id` int(11) DEFAULT NULL,
  `type_action` varchar(20) NOT NULL CHECK (`type_action` in ('réduire','ouvrir')),
  `date_action` date NOT NULL,
  `heure_action` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `MEDECIN`
--

CREATE TABLE `MEDECIN` (
  `med_id` int(11) NOT NULL,
  `prenom` varchar(50) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `mdp` varchar(255) NOT NULL,
  `sexe` char(1) NOT NULL CHECK (`sexe` in ('M','F')),
  `specialite` varchar(50) NOT NULL CHECK (`specialite` in ('Addictologie','Algologie','Allergologie','Anesthésie-Réanimation','Cancérologie','Cardio-vasculaire HTA','Chirurgie','Dermatologie','Diabétologie-Endocrinologie','Génétique','Gériatrie','Gynécologie-Obstétrique','Hématologie','Hépato-gastro-entérologie','Imagerie médicale','Immunologie','Infectiologie','Médecine du sport','Médecine du travail','Médecine générale','Médecine légale','Médecine physique et de réadaptation','Néphrologie','Neurologie','Nutrition','Ophtalmologie','ORL','Pédiatrie','Pneumologie','Psychiatrie','Radiologie','Rhumatologie','Sexologie','Toxicologie','Urologie')),
  `compte_actif` tinyint(1) NOT NULL,
  `email_verified` tinyint(1) NOT NULL DEFAULT 0,
  `email_verification_token` varchar(64) DEFAULT NULL,
  `email_verification_expires` datetime DEFAULT NULL,
  `date_creation` datetime NOT NULL,
  `date_activation` datetime DEFAULT NULL,
  `date_derniere_maj` datetime DEFAULT NULL,
  `token_activation` varchar(255) DEFAULT NULL,
  `token_expiration` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `MEDECIN`
--

INSERT INTO `MEDECIN` (`med_id`, `prenom`, `nom`, `email`, `mdp`, `sexe`, `specialite`, `compte_actif`, `email_verified`, `email_verification_token`, `email_verification_expires`, `date_creation`, `date_activation`, `date_derniere_maj`, `token_activation`, `token_expiration`) VALUES
(1, 'amir', 'chaoui', 'amirnexi900@gmail.com', '$2y$10$N5rRdehxsoBuhqZmX24bVext.madB/f6iSV3Uo/qfQyOpm2N1L2mu', 'M', 'Médecine légale', 1, 1, NULL, NULL, '2025-10-30 14:30:41', '2025-10-30 14:31:17', '2025-10-30 14:31:17', NULL, NULL),
(2, 'theo', 'chaoui', 'amir.taha-chaoui@etu.univ-amu.fr', '$2y$10$uMl5gEMSeKtVw960tuvWB.F3c6nPt1v9PKcbVO9jAQ.yj4Ac2fyYS', 'M', 'Médecine générale', 1, 0, '6a6e665ae73a6b63c814927b4ab193f79214c7078b9be9d4aaf3631b9ab422c0', '2025-10-31 14:46:49', '2025-10-30 14:46:49', NULL, '2025-10-30 14:46:49', NULL, NULL),
(3, 'Théo', 'GHEUX', 'theoxghx@gmail.com', '$2y$10$eSzia3SxFSqpYBG.YQSJeOym74h8.QbQ4papeD1k.gyCrldrrqhUK', 'M', 'Médecine générale', 1, 1, NULL, NULL, '2025-10-30 14:47:42', '2025-10-30 14:48:22', '2025-10-30 14:48:22', NULL, NULL),
(4, 'Ali', 'Uysun', 'abali34000@gmail.com', '$2y$10$FusshEwWxrWKcWzVZaOQxOERgu0358R7Z1ukFSWu3PuQg/HzsxRGG', 'M', 'Addictologie', 1, 1, NULL, NULL, '2025-10-30 18:21:55', '2025-10-30 18:22:25', '2025-10-30 18:22:25', NULL, NULL),
(5, 'Gregory', 'House', 'faucrouddigrare-4605@yopmail.com', '$2y$10$GQjPkdGCLvyMx77qMvM2O.gxwGFkEhHN6wQ8roZdeVFSpjDkI8sGe', 'M', 'Gynécologie-Obstétrique', 1, 1, NULL, NULL, '2025-11-02 00:04:11', '2025-11-02 00:04:33', '2025-11-02 00:04:33', NULL, NULL),
(6, 'Alexis', 'FABRE', 'alexisfabre2006@gmail.com', '$2y$10$jHi86FzRamiI5R5QRiuUeOCwV5wSU.XZQzTpbNL3ZXf5CnzjL85f6', 'M', 'Ophtalmologie', 1, 1, NULL, NULL, '2025-11-02 20:47:49', '2025-11-02 20:49:08', '2025-11-02 20:49:08', NULL, NULL),
(7, 'qdqzd', 'zdqd', 'hellooo@ga.com', '$2y$10$rxA7W9NBqAflr8SzfLzWTe.3U39/XGSVxWazoUXOWG5tET3RVTlRO', 'M', 'Hépato-gastro-entérologie', 1, 0, '127436c470175f9c93518aee63531ed9c79ef74c9e76ee6650173e5f1a1ced2e', '2025-12-17 16:23:38', '2025-12-16 16:23:38', NULL, '2025-12-16 16:23:38', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `MESURES`
--

CREATE TABLE `MESURES` (
  `id_mesure` bigint(20) NOT NULL,
  `pt_id` int(11) DEFAULT NULL,
  `type_mesure` varchar(100) NOT NULL,
  `unite` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `email` varchar(255) NOT NULL,
  `token_hash` char(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `password_resets`
--

INSERT INTO `password_resets` (`id`, `email`, `token_hash`, `expires_at`, `used_at`, `created_at`) VALUES
(19, 'amirnexi900@gmail.com', 'e725bd9af584108bb88190efe1163b1f666da880d96c4a1f679779d095c5efea', '2025-11-10 12:50:51', NULL, '2025-11-10 11:50:51');

-- --------------------------------------------------------

--
-- Table structure for table `PASSWORD_RESETS_2`
--

CREATE TABLE `PASSWORD_RESETS_2` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `email` varchar(255) NOT NULL,
  `token_hash` char(64) NOT NULL,
  `expires_at` datetime NOT NULL CHECK (`expires_at` > `created_at`),
  `used_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `PATIENT`
--

CREATE TABLE `PATIENT` (
  `pt_id` int(11) NOT NULL,
  `prenom` varchar(50) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `sexe` char(1) NOT NULL CHECK (`sexe` in ('M','F')),
  `groupe_sanguin` varchar(3) NOT NULL CHECK (`groupe_sanguin` in ('AB+','AB-','A+','A-','B+','B-','O+','O-')),
  `date_naissance` date NOT NULL,
  `telephone` varchar(50) NOT NULL,
  `ville` varchar(100) DEFAULT NULL,
  `code_postal` varchar(5) DEFAULT NULL,
  `adresse` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `PREFERENCES_MEDECIN`
--

CREATE TABLE `PREFERENCES_MEDECIN` (
  `id_prefp` int(11) NOT NULL,
  `med_id` int(11) DEFAULT NULL,
  `theme` varchar(20) DEFAULT NULL,
  `langue` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `RENDEZ_VOUS`
--

CREATE TABLE `RENDEZ_VOUS` (
  `id_rdv` int(11) NOT NULL,
  `med_id` int(11) DEFAULT NULL,
  `pt_id` int(11) DEFAULT NULL,
  `date_rdv` date NOT NULL,
  `heure_rdv` time NOT NULL,
  `motif` varchar(100) NOT NULL,
  `statut` varchar(10) NOT NULL CHECK (`statut` in ('prévu','réalisé','annulé'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `SEUIL_ALERTE`
--

CREATE TABLE `SEUIL_ALERTE` (
  `seuil_id` int(11) NOT NULL,
  `id_mesure` bigint(20) DEFAULT NULL,
  `seuil_min` double NOT NULL,
  `seuil_max` double NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `SUIVRE`
--

CREATE TABLE `SUIVRE` (
  `med_id` int(11) NOT NULL,
  `pt_id` int(11) NOT NULL,
  `date_debut` date NOT NULL,
  `date_fin` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `email` varchar(190) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `email_verified_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `name`, `last_name`, `email`, `password`, `created_at`, `updated_at`, `email_verified_at`) VALUES
(1, 'Alexandre', 'Jacob', 'alexandre.jacob36@gmail.com', '$2y$10$YN0RN5fjk72CVhRmvWQCae4lxI58AUC5TkBSMxSePOGfMv7IxfjRe', '2025-10-07 18:03:48', '2025-10-07 18:03:48', NULL),
(2, 'amir', 'chaoui', 'amir.chaoui@gmail.com', '$2y$10$6sRMffhl/G7dnbZNJHKM.egm/UUHj8IW./kWQ83w.EHlBq6Tz9sZC', '2025-10-07 18:05:15', '2025-10-07 18:05:15', NULL),
(3, 'Alexis', 'FABRE', 'alexisfabre2006@gmail.com', '$2y$10$izHNBwuBUeRyXwjO/LuEXubJW9jz8LNxmA98U7exoZZGlQVMqfgI6', '2025-10-07 19:29:42', '2025-10-07 19:29:42', NULL),
(4, 'Théo', 'GHEUX', 'theo.gheux@etu.amu-univ.fr', '$2y$10$QQfSZDLdei.AON0GSsHw3ePcHgp5u..rp7xmOJ6GqNgHfeobH6GXq', '2025-10-07 19:31:33', '2025-10-07 19:31:33', NULL),
(5, 'Ali', 'uysun', 'ravus@gmail.com', '$2y$10$.jZfApPxMEOiXXnaYszl2.n6lRGY4UuyVPSnpUuvl7l.YeVKVdJV2', '2025-10-07 19:31:45', '2025-10-07 19:31:45', NULL),
(6, 'Dinesh', 'toto\'); DROP DATABASE mydb; --', 'dinesh-rajdu@gmail.com', '$2y$10$.dDsVQopaF91mJY7A3ZEsOhIdKaInpA81cTjvf/xXwJzhbv3QsYq.', '2025-10-07 19:51:06', '2025-10-07 19:51:06', NULL),
(7, 'Jules', 'Fuselier', 'jules.fuselier@gmail.com', '$2y$10$i6zJc/R6PhPqXJBHFUNytOEjH31pc/39fdx8tu4spxU/EWb92GLRW', '2025-10-07 19:52:22', '2025-10-07 19:52:22', NULL),
(8, 'https://youtu.be/HlwHdHjrkp8?si=VsLjGpIE5IpKJ6tU&t=65', 'https://youtu.be/HlwHdHjrkp8?si=VsLjGpIE5IpKJ6tU&t=65', 'yt@goat.ali', '$2y$10$0eMlo2EZv1wk8nFpgBbSgOfCJs/ew29RboQVlgILutK/KzwFQuCT.', '2025-10-07 20:52:11', '2025-10-07 20:52:11', NULL),
(14, 'Alexande', 'Juif', 'ali.uysun@etu.univ-amu.fr', '$2y$10$X0jaluS40oEm63Hivm41sOctB.bD96UopC49toRmcWIItt0DMk5xu', '2025-10-09 15:16:03', '2025-10-09 15:16:03', NULL),
(15, 'Alex', 'JCB', 'heard.alex36@gmail.com', '$2y$10$F/AZHYpeOr0P9Jx110H.Ue8Euu0P2qiyNHa4pWVbpm3pB2LsAKbe6', '2025-10-09 17:30:26', '2025-10-09 17:30:26', NULL),
(16, 'Théo', 'GHEUX', 'theoxghx@gmail.com', '$2y$10$2BKk0Qmfh2UR21D4sbD/neLm4ggN3wZxEbfB9M1cOu2BvZijp72O.', '2025-10-09 17:47:50', '2025-10-24 06:24:18', NULL),
(17, 'test', 'test', 'mockito.esport@gmail.com', '$2y$10$7nwLUZrApOJOpTChG2E0PeE8UonTw97ywx3k6SBhpgNfijOaLcnG.', '2025-10-09 19:52:16', '2025-10-09 19:52:16', NULL),
(18, 'Ali', 'uysun', 'abali34000@gmail.com', '$2y$10$AH6OdEci9ILaoD38arNrJ.RVQckh2s4Q.VxgBtgqQAILhtcHsLIdC', '2025-10-09 19:54:00', '2025-10-09 19:54:00', NULL),
(19, 'Alex', 'JCB', 'alexandre.secondaire36@gmail.com', '$2y$10$UEt.iO9mIWft8Bw8SgzFeu1QWSBqoNvLuFW8AVPAma7Jx9yrBJvJy', '2025-10-09 21:41:05', '2025-10-09 21:41:05', NULL),
(21, 'Alexis', 'Guyot', 'alexis.guyot@univ-amu.fr', '$2y$10$w67Nu9mHzxiUtE0PlBaTHeQBk8mdQeUWX73bz25XiUVabFuQbI50a', '2025-10-14 14:25:29', '2025-10-14 14:25:29', NULL),
(22, 'Amr', 'Ali', 'lalalallla@gmail.com', '$2y$10$yNsyJxPNWo4PO.d5DKtZcOGF.KsDk6FfEjGmG5xcQUit/BkRamnPK', '2025-10-15 19:17:30', '2025-10-15 19:17:30', NULL),
(23, 'Ali', 'uysun', 'ali.uysun@hotmail.com', '$2y$10$HeNW/uTyE1bFx80ME291Mu0BK0Z0dHBlAZLNM6.o.IY/.9/Uwx3qK', '2025-10-16 13:41:26', '2025-10-21 06:32:59', NULL),
(26, 'amir', 'chaoui', 'amir.taha-chaoui@etu.univ-amu.fr', '$2y$10$qcGv8Nc7pKDucK0RBoQtNePAoTM4fO2Q9VlNU5vQPP8NtkJrXIEni', '2025-10-21 10:15:30', '2025-10-21 10:15:30', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `VALEURS_MESURES`
--

CREATE TABLE `VALEURS_MESURES` (
  `id_val` bigint(20) NOT NULL,
  `valeur` double NOT NULL,
  `date_mesure` date NOT NULL,
  `heure_mesure` time NOT NULL,
  `id_mesure` bigint(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `ALERTE`
--
ALTER TABLE `ALERTE`
  ADD PRIMARY KEY (`alerte_id`),
  ADD KEY `fk_alerte` (`seuil_id`);

--
-- Indexes for table `GRAPHIQUE`
--
ALTER TABLE `GRAPHIQUE`
  ADD PRIMARY KEY (`graph_id`),
  ADD KEY `fk_graph` (`id_mesure`);

--
-- Indexes for table `HISTORIQUE_CONSOLE`
--
ALTER TABLE `HISTORIQUE_CONSOLE`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `fk_historique` (`med_id`);

--
-- Indexes for table `MEDECIN`
--
ALTER TABLE `MEDECIN`
  ADD PRIMARY KEY (`med_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_medecin_email_token` (`email_verification_token`);

--
-- Indexes for table `MESURES`
--
ALTER TABLE `MESURES`
  ADD PRIMARY KEY (`id_mesure`),
  ADD KEY `fk_mesures` (`pt_id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_expires` (`expires_at`);

--
-- Indexes for table `PASSWORD_RESETS_2`
--
ALTER TABLE `PASSWORD_RESETS_2`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token_hash` (`token_hash`);

--
-- Indexes for table `PATIENT`
--
ALTER TABLE `PATIENT`
  ADD PRIMARY KEY (`pt_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `telephone` (`telephone`);

--
-- Indexes for table `PREFERENCES_MEDECIN`
--
ALTER TABLE `PREFERENCES_MEDECIN`
  ADD PRIMARY KEY (`id_prefp`),
  ADD KEY `fk_preferences` (`med_id`);

--
-- Indexes for table `RENDEZ_VOUS`
--
ALTER TABLE `RENDEZ_VOUS`
  ADD PRIMARY KEY (`id_rdv`),
  ADD KEY `fk_rdv` (`med_id`),
  ADD KEY `fk2_rdv` (`pt_id`);

--
-- Indexes for table `SEUIL_ALERTE`
--
ALTER TABLE `SEUIL_ALERTE`
  ADD PRIMARY KEY (`seuil_id`),
  ADD KEY `fk_seuil` (`id_mesure`);

--
-- Indexes for table `SUIVRE`
--
ALTER TABLE `SUIVRE`
  ADD PRIMARY KEY (`med_id`,`pt_id`),
  ADD KEY `fk2_suivre` (`pt_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `uq_users_email` (`email`);

--
-- Indexes for table `VALEURS_MESURES`
--
ALTER TABLE `VALEURS_MESURES`
  ADD PRIMARY KEY (`id_val`),
  ADD KEY `fk_val_mesures` (`id_mesure`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `MEDECIN`
--
ALTER TABLE `MEDECIN`
  MODIFY `med_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `PASSWORD_RESETS_2`
--
ALTER TABLE `PASSWORD_RESETS_2`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `ALERTE`
--
ALTER TABLE `ALERTE`
  ADD CONSTRAINT `fk_alerte` FOREIGN KEY (`seuil_id`) REFERENCES `SEUIL_ALERTE` (`seuil_id`) ON DELETE CASCADE;

--
-- Constraints for table `GRAPHIQUE`
--
ALTER TABLE `GRAPHIQUE`
  ADD CONSTRAINT `fk_graph` FOREIGN KEY (`id_mesure`) REFERENCES `MESURES` (`id_mesure`);

--
-- Constraints for table `HISTORIQUE_CONSOLE`
--
ALTER TABLE `HISTORIQUE_CONSOLE`
  ADD CONSTRAINT `fk_historique` FOREIGN KEY (`med_id`) REFERENCES `MEDECIN` (`med_id`) ON DELETE CASCADE;

--
-- Constraints for table `MESURES`
--
ALTER TABLE `MESURES`
  ADD CONSTRAINT `fk_mesures` FOREIGN KEY (`pt_id`) REFERENCES `PATIENT` (`pt_id`) ON DELETE CASCADE;

--
-- Constraints for table `PREFERENCES_MEDECIN`
--
ALTER TABLE `PREFERENCES_MEDECIN`
  ADD CONSTRAINT `fk_preferences` FOREIGN KEY (`med_id`) REFERENCES `MEDECIN` (`med_id`) ON DELETE CASCADE;

--
-- Constraints for table `RENDEZ_VOUS`
--
ALTER TABLE `RENDEZ_VOUS`
  ADD CONSTRAINT `fk2_rdv` FOREIGN KEY (`pt_id`) REFERENCES `PATIENT` (`pt_id`),
  ADD CONSTRAINT `fk_rdv` FOREIGN KEY (`med_id`) REFERENCES `MEDECIN` (`med_id`);

--
-- Constraints for table `SEUIL_ALERTE`
--
ALTER TABLE `SEUIL_ALERTE`
  ADD CONSTRAINT `fk_seuil` FOREIGN KEY (`id_mesure`) REFERENCES `MESURES` (`id_mesure`) ON DELETE CASCADE;

--
-- Constraints for table `SUIVRE`
--
ALTER TABLE `SUIVRE`
  ADD CONSTRAINT `fk2_suivre` FOREIGN KEY (`pt_id`) REFERENCES `PATIENT` (`pt_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_suivre` FOREIGN KEY (`med_id`) REFERENCES `MEDECIN` (`med_id`);

--
-- Constraints for table `VALEURS_MESURES`
--
ALTER TABLE `VALEURS_MESURES`
  ADD CONSTRAINT `fk_val_mesures` FOREIGN KEY (`id_mesure`) REFERENCES `MESURES` (`id_mesure`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
