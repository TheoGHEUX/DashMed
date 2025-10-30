-- =========================================================================
-- Ajout de la vérification d'email à la table MEDECIN
-- =========================================================================
-- Ce script ajoute les colonnes nécessaires pour la vérification d'email
-- =========================================================================

-- Ajout des colonnes de vérification d'email
ALTER TABLE MEDECIN 
ADD COLUMN IF NOT EXISTS email_verified BOOLEAN NOT NULL DEFAULT 0 AFTER compte_actif,
ADD COLUMN IF NOT EXISTS email_verification_token VARCHAR(64) NULL AFTER email_verified,
ADD COLUMN IF NOT EXISTS email_verification_expires DATETIME NULL AFTER email_verification_token;

-- Index sur le token pour accélérer les recherches
CREATE INDEX IF NOT EXISTS idx_medecin_email_token ON MEDECIN (email_verification_token);

-- Optionnel : Mettre à jour les comptes existants comme vérifiés
-- Décommenter si vous voulez que les comptes déjà créés soient considérés comme vérifiés
-- UPDATE MEDECIN SET email_verified = 1 WHERE email_verified = 0 AND date_creation < NOW();

-- Vérification
SELECT 
    'MEDECIN' AS table_name,
    COUNT(*) AS total,
    SUM(email_verified) AS verified,
    SUM(CASE WHEN email_verified = 0 THEN 1 ELSE 0 END) AS not_verified
FROM MEDECIN;
