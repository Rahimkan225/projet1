-- Migration vers Supabase (PostgreSQL)
-- Schéma adapté pour PostgreSQL depuis MySQL

-- Extension pour UUID si nécessaire
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";

-- Table 1 : Utilisateurs
CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    created_by INTEGER NULL REFERENCES users(id) ON DELETE SET NULL,
    is_active BOOLEAN NOT NULL DEFAULT true,
    nom_complet VARCHAR(100) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    telephone VARCHAR(20) UNIQUE,
    mot_de_passe VARCHAR(255) NOT NULL,
    type VARCHAR(20) NOT NULL DEFAULT 'donateur' CHECK (type IN ('donateur', 'patient', 'admin_gestionnaire', 'admin_general')),
    photo_profil VARCHAR(255) DEFAULT 'default-avatar.png',
    date_inscription TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    -- Champs profil étendus
    adresse VARCHAR(255) NULL,
    ville VARCHAR(100) NULL,
    pays VARCHAR(100) NULL DEFAULT 'Côte d''Ivoire',
    code_postal VARCHAR(20) NULL,
    date_naissance DATE NULL,
    bio TEXT NULL,
    site_web VARCHAR(255) NULL
);

CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_type ON users(type);
CREATE INDEX idx_users_created_by ON users(created_by);
CREATE INDEX idx_users_ville ON users(ville);

-- Table 2 : Cagnottes
CREATE TABLE cagnottes (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    created_by INTEGER NULL REFERENCES users(id) ON DELETE SET NULL,
    nom_patient VARCHAR(200) NOT NULL,
    age_patient INTEGER,
    photo_patient VARCHAR(255),
    diagnostic TEXT NOT NULL,
    pathologie VARCHAR(20) NOT NULL CHECK (pathologie IN ('cancer', 'chirurgie', 'accident', 'maternite', 'autre')),
    hopital VARCHAR(200),
    montant_objectif DECIMAL(12,2) NOT NULL,
    montant_collecte DECIMAL(12,2) DEFAULT 0.00,
    urgence VARCHAR(20) DEFAULT 'moderee' CHECK (urgence IN ('critique', 'elevee', 'moderee')),
    statut VARCHAR(20) DEFAULT 'en_attente' CHECK (statut IN ('en_attente', 'active', 'completee', 'rejetee')),
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    date_modification TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_cagnottes_statut ON cagnottes(statut);
CREATE INDEX idx_cagnottes_urgence ON cagnottes(urgence);
CREATE INDEX idx_cagnottes_pathologie ON cagnottes(pathologie);
CREATE INDEX idx_cagnottes_created_by ON cagnottes(created_by);
CREATE INDEX idx_cagnottes_user_id ON cagnottes(user_id);

-- Trigger pour date_modification
CREATE OR REPLACE FUNCTION update_modified_column()
RETURNS TRIGGER AS $$
BEGIN
    NEW.date_modification = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ language 'plpgsql';

CREATE TRIGGER update_cagnottes_modtime BEFORE UPDATE ON cagnottes
    FOR EACH ROW EXECUTE FUNCTION update_modified_column();

-- Table 3 : Documents
CREATE TABLE documents (
    id SERIAL PRIMARY KEY,
    cagnotte_id INTEGER NOT NULL REFERENCES cagnottes(id) ON DELETE CASCADE,
    type_document VARCHAR(20) NOT NULL CHECK (type_document IN ('ordonnance', 'devis', 'certificat', 'autre')),
    nom_fichier VARCHAR(255) NOT NULL,
    chemin_fichier VARCHAR(500) NOT NULL,
    taille_fichier INTEGER,
    date_upload TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_documents_cagnotte ON documents(cagnotte_id);

-- Table 4 : Dons
CREATE TABLE dons (
    id SERIAL PRIMARY KEY,
    cagnotte_id INTEGER NOT NULL REFERENCES cagnottes(id) ON DELETE CASCADE,
    donateur_id INTEGER NULL REFERENCES users(id) ON DELETE SET NULL,
    montant DECIMAL(10,2) NOT NULL,
    est_anonyme BOOLEAN DEFAULT false,
    nom_donateur VARCHAR(100),
    email_donateur VARCHAR(255),
    message TEXT,
    reference_don VARCHAR(50) UNIQUE,
    date_don TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_dons_cagnotte ON dons(cagnotte_id);
CREATE INDEX idx_dons_donateur ON dons(donateur_id);
CREATE INDEX idx_dons_date ON dons(date_don);

-- Table 5 : Messages de contact
CREATE TABLE contact_messages (
    id SERIAL PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL,
    sujet VARCHAR(200),
    message TEXT NOT NULL,
    statut VARCHAR(20) DEFAULT 'non_lu' CHECK (statut IN ('non_lu', 'lu', 'traite')),
    date_envoi TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_contact_statut ON contact_messages(statut);

-- Note: Les données de test doivent être insérées séparément
-- Les séquences SERIAL commencent à 1, donc les IDs seront différents de MySQL

