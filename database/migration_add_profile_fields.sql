-- Migration : Ajout des champs de profil étendus
-- À exécuter si vous avez déjà une base de données existante

ALTER TABLE users
ADD COLUMN IF NOT EXISTS adresse VARCHAR(255) NULL AFTER date_inscription,
ADD COLUMN IF NOT EXISTS ville VARCHAR(100) NULL AFTER adresse,
ADD COLUMN IF NOT EXISTS pays VARCHAR(100) NULL DEFAULT 'Côte d''Ivoire' AFTER ville,
ADD COLUMN IF NOT EXISTS code_postal VARCHAR(20) NULL AFTER pays,
ADD COLUMN IF NOT EXISTS date_naissance DATE NULL AFTER code_postal,
ADD COLUMN IF NOT EXISTS bio TEXT NULL AFTER date_naissance,
ADD COLUMN IF NOT EXISTS site_web VARCHAR(255) NULL AFTER bio;

-- Ajouter index sur ville si nécessaire
CREATE INDEX IF NOT EXISTS idx_ville ON users(ville);

