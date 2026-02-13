-- Liens d'Espoir - Schéma BDD + données de test
-- Base recommandée : liens_espoir

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS contact_messages;
DROP TABLE IF EXISTS dons;
DROP TABLE IF EXISTS documents;
DROP TABLE IF EXISTS cagnottes;
DROP TABLE IF EXISTS users;

-- Table 1 : Utilisateurs
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    created_by INT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    nom_complet VARCHAR(100) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    telephone VARCHAR(20) UNIQUE,
    mot_de_passe VARCHAR(255) NOT NULL,
    type ENUM('donateur', 'patient', 'admin_gestionnaire', 'admin_general') DEFAULT 'donateur',
    photo_profil VARCHAR(255) DEFAULT 'default-avatar.png',
    date_inscription TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    -- Nouveaux champs profil
    adresse VARCHAR(255) NULL,
    ville VARCHAR(100) NULL,
    pays VARCHAR(100) NULL DEFAULT 'Côte d''Ivoire',
    code_postal VARCHAR(20) NULL,
    date_naissance DATE NULL,
    bio TEXT NULL,
    site_web VARCHAR(255) NULL,
    INDEX idx_email (email),
    INDEX idx_type (type),
    INDEX idx_created_by (created_by),
    INDEX idx_ville (ville),
    CONSTRAINT fk_users_created_by FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table 2 : Cagnottes
CREATE TABLE cagnottes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    created_by INT NULL,
    nom_patient VARCHAR(200) NOT NULL,
    age_patient INT,
    photo_patient VARCHAR(255),
    diagnostic TEXT NOT NULL,
    pathologie ENUM('cancer', 'chirurgie', 'accident', 'maternite', 'autre') NOT NULL,
    hopital VARCHAR(200),
    montant_objectif DECIMAL(12,2) NOT NULL,
    montant_collecte DECIMAL(12,2) DEFAULT 0.00,
    urgence ENUM('critique', 'elevee', 'moderee') DEFAULT 'moderee',
    statut ENUM('en_attente', 'active', 'completee', 'rejetee') DEFAULT 'en_attente',
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    date_modification TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_statut (statut),
    INDEX idx_urgence (urgence),
    INDEX idx_pathologie (pathologie),
    INDEX idx_created_by (created_by)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table 3 : Documents
CREATE TABLE documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cagnotte_id INT NOT NULL,
    type_document ENUM('ordonnance', 'devis', 'certificat', 'autre') NOT NULL,
    nom_fichier VARCHAR(255) NOT NULL,
    chemin_fichier VARCHAR(500) NOT NULL,
    taille_fichier INT,
    date_upload TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cagnotte_id) REFERENCES cagnottes(id) ON DELETE CASCADE,
    INDEX idx_cagnotte (cagnotte_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table 4 : Dons
CREATE TABLE dons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cagnotte_id INT NOT NULL,
    donateur_id INT NULL,
    montant DECIMAL(10,2) NOT NULL,
    est_anonyme BOOLEAN DEFAULT FALSE,
    nom_donateur VARCHAR(100),
    email_donateur VARCHAR(255),
    message TEXT,
    reference_don VARCHAR(50) UNIQUE,
    date_don TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cagnotte_id) REFERENCES cagnottes(id) ON DELETE CASCADE,
    FOREIGN KEY (donateur_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_cagnotte (cagnotte_id),
    INDEX idx_donateur (donateur_id),
    INDEX idx_date (date_don)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table 5 : Messages de contact
CREATE TABLE contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL,
    sujet VARCHAR(200),
    message TEXT NOT NULL,
    statut ENUM('non_lu', 'lu', 'traite') DEFAULT 'non_lu',
    date_envoi TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_statut (statut)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Données de test
-- Admin général (password: admin123)
INSERT INTO users (nom_complet, email, telephone, mot_de_passe, type) VALUES
('Administrateur Principal', 'admin@liensespoir.ci', '+2250700000000',
 '$2y$10$tJZk8e3ER4fu/uL/hpEMEeg9Yd5yHuRO44lX/KdwmDbPXa6njwDVu', 'admin_general');

-- Admin gestionnaire (password: password123)
INSERT INTO users (created_by, nom_complet, email, telephone, mot_de_passe, type) VALUES
(1, 'Gestionnaire 1', 'gestionnaire@liensespoir.ci', '+2250709999999',
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin_gestionnaire');

-- Patients (password: password123)
INSERT INTO users (nom_complet, email, telephone, mot_de_passe, type) VALUES
('Kouassi Jean', 'jean.kouassi@email.ci', '+2250748123456',
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'patient'),
('Diallo Fatou', 'fatou.diallo@email.ci', '+2250759234567',
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'patient');

-- Donateurs (password: password123)
INSERT INTO users (nom_complet, email, telephone, mot_de_passe, type) VALUES
('Koné Marie', 'marie.kone@email.ci', '+2250701345678',
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'donateur'),
('Ouattara Ibrahim', 'ibrahim.o@email.ci', '+2250712456789',
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'donateur');

-- Cagnottes de test (35 cagnottes)
INSERT INTO cagnottes (user_id, created_by, nom_patient, age_patient, diagnostic, pathologie, hopital, montant_objectif, montant_collecte, urgence, statut) VALUES
(2, 2, 'Aya Traoré', 36, 'Leucémie aiguë nécessitant chimiothérapie urgente au CHU de Treichville', 'autre', 'Centre Médical International', 9000000, 0, 'moderee', 'en_attente'),
(3, 2, 'Kofi Mensah', 51, 'Opération cardiaque programmée - Pontage coronarien au Centre Hospitalier de Yopougon', 'accident', 'CHU de Treichville', 12000000, 0, 'critique', 'en_attente'),
(2, 2, 'Aminata Sow', 44, 'Césarienne d''urgence - Grossesse à risque au Clinique des Mamans', 'chirurgie', 'Hôpital Général d''Abidjan', 800000, 0, 'elevee', 'en_attente'),
(3, 2, 'Yao Kouassi', 22, 'Traitement du cancer du sein - Mastectomie et chimiothérapie au Hôpital Mère-Enfant', 'accident', 'CHU de Treichville', 2000000, 0, 'elevee', 'en_attente'),
(2, 2, 'Fatou Diallo', 21, 'Accident de la route - Fractures multiples nécessitant chirurgie au Clinique Pasteur', 'cancer', 'Clinique des Mamans', 9000000, 0, 'critique', 'en_attente'),
(3, 2, 'Moussa Camara', 71, 'Insuffisance rénale chronique - Dialyse et greffe rénale au Clinique des Mamans', 'maternite', 'Hôpital Général d''Abidjan', 9000000, 0, 'elevee', 'en_attente'),
(2, 2, 'Aissatou Bah', 55, 'Tumeur cérébrale bénigne - Chirurgie d''exérèse au Centre Hospitalier de Yopougon', 'chirurgie', 'Clinique des Mamans', 1500000, 0, 'critique', 'en_attente'),
(3, 2, 'Ibrahim Touré', 6, 'Brûlures graves - Greffe de peau et soins intensifs au Hôpital Mère-Enfant', 'autre', 'Hôpital Mère-Enfant', 20000000, 0, 'critique', 'en_attente'),
(2, 2, 'Mariam Koné', 58, 'Maladie cardiaque congénitale - Chirurgie cardiaque pédiatrique au Polyclinique Sainte Marie', 'cancer', 'CHU de Cocody', 5000000, 0, 'moderee', 'en_attente'),
(3, 2, 'Sékou Diabaté', 21, 'Complications post-opératoires - Réintervention urgente au Hôpital Mère-Enfant', 'accident', 'Polyclinique Sainte Marie', 6000000, 0, 'moderee', 'en_attente'),
(2, 2, 'Kadiatou Keita', 31, 'Cancer du poumon - Chimiothérapie et radiothérapie au Clinique des Mamans', 'cancer', 'Centre Hospitalier de Yopougon', 1500000, 1159095, 'critique', 'active'),
(3, 2, 'Ousmane Sarr', 60, 'Accident vasculaire cérébral - Rééducation et soins au Hôpital Mère-Enfant', 'autre', 'Clinique Pasteur', 12000000, 2711905, 'critique', 'active'),
(2, 2, 'Awa Ndiaye', 13, 'Grossesse gémellaire à risque - Césarienne programmée au Centre Hospitalier de Yopougon', 'autre', 'Hôpital Général d''Abidjan', 2500000, 240448, 'critique', 'active'),
(3, 2, 'Boubacar Fofana', 70, 'Fracture de la hanche - Prothèse totale de hanche au Hôpital Mère-Enfant', 'chirurgie', 'Centre Médical International', 2000000, 1429349, 'critique', 'active'),
(2, 2, 'Ramatou Coulibaly', 69, 'Traitement du diabète compliqué - Amputation et prothèse au Polyclinique Sainte Marie', 'autre', 'Centre Hospitalier de Yopougon', 1500000, 555734, 'critique', 'active'),
(3, 2, 'Amadou Ba', 68, 'Cancer du foie - Chirurgie hépatique et traitement au Centre Hospitalier de Yopougon', 'accident', 'Centre Médical International', 10000000, 3824736, 'critique', 'active'),
(2, 2, 'Hawa Sylla', 34, 'Accident domestique - Brûlures et soins intensifs au Centre Médical International', 'chirurgie', 'Hôpital Général d''Abidjan', 2500000, 1630499, 'elevee', 'active'),
(3, 2, 'Mamadou Diallo', 65, 'Maladie rénale - Dialyse et transplantation au CHU de Cocody', 'autre', 'Hôpital Général d''Abidjan', 8000000, 4705207, 'critique', 'active'),
(2, 2, 'Aminata Traoré', 11, 'Tumeur osseuse - Résection et reconstruction au Centre Médical International', 'cancer', 'Polyclinique Sainte Marie', 2000000, 1590172, 'moderee', 'active'),
(3, 2, 'Ibrahima Sall', 8, 'Complications obstétricales - Soins intensifs néonatals au CHU de Cocody', 'cancer', 'Centre Hospitalier de Yopougon', 1000000, 460616, 'moderee', 'active'),
(2, 2, 'Kadija Diallo', 26, 'Cancer colorectal - Chirurgie et chimiothérapie au CHU de Cocody', 'cancer', 'Centre Hospitalier de Yopougon', 7000000, 2688419, 'critique', 'active'),
(3, 2, 'Moussa Ba', 55, 'Traumatisme crânien - Neurochirurgie urgente au Clinique Pasteur', 'accident', 'Polyclinique Sainte Marie', 4000000, 2992307, 'elevee', 'active'),
(2, 2, 'Fatoumata Keita', 13, 'Insuffisance cardiaque - Transplantation cardiaque au Polyclinique Sainte Marie', 'autre', 'CHU de Cocody', 7000000, 2928096, 'moderee', 'active'),
(3, 2, 'Oumar Traoré', 28, 'Maladie auto-immune - Traitement immunosuppresseur au Centre Médical International', 'cancer', 'Polyclinique Sainte Marie', 2500000, 1448471, 'critique', 'active'),
(2, 2, 'Aissatou Diallo', 40, 'Accident de travail - Rééducation fonctionnelle au Centre Hospitalier de Yopougon', 'autre', 'CHU de Cocody', 6000000, 4757757, 'critique', 'active'),
(3, 2, 'Bakary Camara', 24, 'Cancer de la prostate - Chirurgie et radiothérapie au Centre Hospitalier de Yopougon', 'chirurgie', 'Polyclinique Sainte Marie', 12000000, 12486873, 'elevee', 'completee'),
(2, 2, 'Mariama Sow', 40, 'Complications post-partum - Soins intensifs au Hôpital Général d''Abidjan', 'cancer', 'Centre Médical International', 20000000, 21277665, 'critique', 'completee'),
(3, 2, 'Ibrahima Ndiaye', 20, 'Fracture complexe - Chirurgie orthopédique au Clinique des Mamans', 'chirurgie', 'Centre Hospitalier de Yopougon', 4000000, 4498419, 'moderee', 'completee'),
(2, 2, 'Awa Diop', 75, 'Maladie infectieuse grave - Traitement antibiotique intensif au CHU de Cocody', 'accident', 'CHU de Treichville', 1500000, 1715207, 'elevee', 'completee'),
(3, 2, 'Sékou Touré', 14, 'Tumeur digestive - Chirurgie et chimiothérapie au Hôpital Mère-Enfant', 'maternite', 'Polyclinique Sainte Marie', 1000000, 1057920, 'moderee', 'completee'),
(2, 2, 'Kadiatou Ba', 46, 'Accident vasculaire - Rééducation neurologique au Polyclinique Sainte Marie', 'maternite', 'Clinique des Mamans', 1000000, 1038610, 'moderee', 'completee'),
(3, 2, 'Ousmane Koné', 39, 'Cancer pédiatrique - Protocole de chimiothérapie au Clinique des Mamans', 'chirurgie', 'CHU de Treichville', 1500000, 1667178, 'elevee', 'completee'),
(2, 2, 'Ramatou Diallo', 52, 'Greffe d''organe - Suivi post-transplantation au CHU de Cocody', 'cancer', 'CHU de Cocody', 2000000, 0, 'moderee', 'rejetee'),
(3, 2, 'Amadou Traoré', 15, 'Maladie rare - Traitement spécialisé au CHU de Treichville', 'chirurgie', 'Polyclinique Sainte Marie', 5000000, 0, 'moderee', 'rejetee'),
(2, 2, 'Hawa Keita', 12, 'Traumatisme multiple - Soins multidisciplinaires au Centre Médical International', 'autre', 'Clinique Pasteur', 12000000, 0, 'moderee', 'rejetee');

-- Dons de test
INSERT INTO dons (cagnotte_id, donateur_id, montant, nom_donateur, email_donateur, message, reference_don) VALUES
(1, 4, 250000, 'Marie Koné', 'marie.kone@email.ci', 'Courage petite Aya ! Toute ma famille prie pour toi.', 'LE-1737389000-1'),
(1, NULL, 500000, 'Anonyme', NULL, NULL, 'LE-1737389100-2'),
(1, 5, 100000, 'Ouattara Ibrahim', 'ibrahim.o@email.ci', 'Force à la famille', 'LE-1737389200-3'),
(2, 4, 200000, 'Marie Koné', 'marie.kone@email.ci', 'Prompt rétablissement Monsieur Kofi', 'LE-1737389300-4'),
(3, 5, 150000, 'Ouattara Ibrahim', 'ibrahim.o@email.ci', 'Pour la future maman et son bébé', 'LE-1737389400-5');

SET FOREIGN_KEY_CHECKS = 1;


