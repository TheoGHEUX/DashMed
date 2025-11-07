/*
Ce fichier rassemble toutes les contraintes de type TRIGGER de notre base de données et des ALTER TABLE (pour éviter de modifier le script initial)
Comme nous avons appris l'implémentation sur postgreSQL et que notre base de données utilise MySQL qui n'a pas la même structure pour les triggers, nous avons d'abord implémenté les triggers en postgreSQL puis avons généré leur équivalent pour MySQL
Le code qui n'est pas destiné à être exécuté (comme les triggers au format postgreSQL) est mis en commentaire.
*/

------------------------------------------- TRIGGERS :

/* TRIGGER verif_dates_medecin() :
Ce trigger vérifie que la date de création du compte est toujours inférieure à la date d'activation.
De cette manière, on protège la cohérence des données.
Ce trigger gère aussi le cas où l'on n'a pas encore activé le compte avec la date_activation qui est NULL.
*/

-- Version en PostgreSQL :

/*
CREATE OR REPLACE FUNCTION verif_dates_medecin()
RETURNS TRIGGER AS $$
BEGIN
  IF NEW.date_activation IS NOT NULL
     AND NEW.date_creation IS NOT NULL
     AND NEW.date_activation <= NEW.date_creation THEN
    RAISE EXCEPTION 'La date d’activation doit être postérieure à la date de création';
END IF;
RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER verif_dates_medecin
BEFORE INSERT ON medecin
FOR EACH ROW
EXECUTE FUNCTION verif_dates_medecin();
*/

-- Equivalent en MySQL :

DELIMITER $$

CREATE TRIGGER verif_dates_medecin
    BEFORE INSERT ON medecin
    FOR EACH ROW
BEGIN
    IF NEW.date_activation <= NEW.date_creation THEN
    SIGNAL SQLSTATE '45000'
    SET MESSAGE_TEXT = 'La date d’activation doit être postérieure à la date de création';
END IF;
END$$

DELIMITER ;

------------------------------------------- ALTER TABLE :

/* Ajout d'une contrainte pour empêcher la suppression d'une médecin s'il suit ou a déjà suivi un patient.
On veut pouvoir conserver les données concernant le suivi de patient même si le médecin n'exerce plus.
*/

ALTER TABLE suivre
    ADD CONSTRAINT fk_suivre_medecin
        FOREIGN KEY (med_id)
            REFERENCES medecin(med_id)
            ON DELETE RESTRICT;

/* Ajout d'une contrainte pour empêcher la suppression d'un patient s'il a des mesures enregistrées.
On veut pouvoir conserver les données concernant les mesures enregistrées sur les patients.
Comme on avait ON DELETE CASCADE par défaut, on supprime d'abord la contrainte pour la réécrire.
*/

ALTER TABLE mesures
DROP FOREIGN KEY fk_mesures;

ALTER TABLE mesures
    ADD CONSTRAINT fk_mesures
        FOREIGN KEY (pt_id)
            REFERENCES patient(pt_id)
            ON DELETE RESTRICT;