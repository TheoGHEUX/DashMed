-- TABLE MEDECIN
CREATE TABLE MEDECIN
(
    med_id            INT,
    prenom            VARCHAR(50)         NOT NULL,
    nom               VARCHAR(100)        NOT NULL,
    email             VARCHAR(150) UNIQUE NOT NULL,
    mdp               VARCHAR(255)        NOT NULL,
    sexe              CHAR(1)             NOT NULL CHECK (sexe IN ('M', 'F')),
    specialite        VARCHAR(50)         NOT NULL CHECK (specialite IN (
                                                                         'Addictologie', 'Algologie', 'Allergologie',
                                                                         'Anesthésie-Réanimation',
                                                                         'Cancérologie', 'Cardio-vasculaire HTA',
                                                                         'Chirurgie', 'Dermatologie',
                                                                         'Diabétologie-Endocrinologie', 'Génétique',
                                                                         'Gériatrie',
                                                                         'Gynécologie-Obstétrique', 'Hématologie',
                                                                         'Hépato-gastro-entérologie',
                                                                         'Imagerie médicale', 'Immunologie',
                                                                         'Infectiologie', 'Médecine du sport',
                                                                         'Médecine du travail', 'Médecine générale',
                                                                         'Médecine légale',
                                                                         'Médecine physique et de réadaptation',
                                                                         'Néphrologie', 'Neurologie',
                                                                         'Nutrition', 'Ophtalmologie', 'ORL',
                                                                         'Pédiatrie', 'Pneumologie',
                                                                         'Psychiatrie', 'Radiologie', 'Rhumatologie',
                                                                         'Sexologie',
                                                                         'Toxicologie', 'Urologie'
        )),
    compte_actif BOOLEAN,
    email_verified  BOOLEAN,
    email_verification_token VARCHAR(255),
    email_verification_expires DATETIME,
    date_creation     DATETIME            NOT NULL,
    date_activation   DATETIME,
    date_derniere_maj DATETIME,
    token_activation  VARCHAR(255),
    token_expiration  DATETIME,
    CONSTRAINT pk_medecin PRIMARY KEY (med_id)
);

-- TABLE PATIENT
CREATE TABLE PATIENT
(
    pt_id          INT,
    prenom         VARCHAR(50)         NOT NULL,
    nom            VARCHAR(100)        NOT NULL,
    email          VARCHAR(150) UNIQUE NOT NULL,
    sexe           CHAR(1)             NOT NULL CHECK (sexe IN ('M', 'F')),
    groupe_sanguin VARCHAR(3)          NOT NULL CHECK (groupe_sanguin IN (
                                                                          'AB+', 'AB-', 'A+', 'A-', 'B+', 'B-', 'O+',
                                                                          'O-'
        )),
    date_naissance DATE                NOT NULL,
    telephone      VARCHAR(50) UNIQUE  NOT NULL,
    ville          VARCHAR(100),
    code_postal    VARCHAR(5),
    adresse        VARCHAR(255),
    CONSTRAINT pk_patient PRIMARY KEY (pt_id)
);

-- TABLE SUIVRE
CREATE TABLE SUIVRE
(
    med_id     INT,
    pt_id      INT,
    date_debut DATE NOT NULL,
    date_fin   DATE,
    CONSTRAINT pk_suivre PRIMARY KEY (med_id, pt_id),
    CONSTRAINT fk_suivre FOREIGN KEY (med_id) REFERENCES MEDECIN (med_id),
    CONSTRAINT fk2_suivre FOREIGN KEY (pt_id) REFERENCES PATIENT (pt_id) ON DELETE CASCADE
);

-- TABLE RENDEZ_VOUS
CREATE TABLE RENDEZ_VOUS
(
    id_rdv    INT,
    med_id    INT,
    pt_id     INT,
    date_rdv  DATE         NOT NULL,
    heure_rdv TIME         NOT NULL,
    motif     VARCHAR(100) NOT NULL,
    statut    VARCHAR(10)  NOT NULL CHECK (statut IN ('prévu', 'réalisé', 'annulé')),
    CONSTRAINT pk_rdv PRIMARY KEY (id_rdv),
    CONSTRAINT fk_rdv FOREIGN KEY (med_id) REFERENCES MEDECIN (med_id),
    CONSTRAINT fk2_rdv FOREIGN KEY (pt_id) REFERENCES PATIENT (pt_id)
);

-- TABLE MESURES
CREATE TABLE MESURES
(
    id_mesure   BIGINT,
    pt_id       INT,
    type_mesure VARCHAR(100) NOT NULL,
    unite       VARCHAR(10)  NOT NULL,
    CONSTRAINT pk_mesures PRIMARY KEY (id_mesure),
    CONSTRAINT fk_mesures FOREIGN KEY (pt_id) REFERENCES PATIENT (pt_id) ON DELETE CASCADE
);

-- TABLE VALEURS_MESURES
CREATE TABLE VALEURS_MESURES
(
    id_val       BIGINT,
    valeur       REAL NOT NULL,
    date_mesure  DATE NOT NULL,
    heure_mesure TIME NOT NULL,
    id_mesure    BIGINT,
    CONSTRAINT pk_val_mesures PRIMARY KEY (id_val),
    CONSTRAINT fk_val_mesures FOREIGN KEY (id_mesure) REFERENCES MESURES (id_mesure) ON DELETE CASCADE
);

-- TABLE PREFERENCES_MEDECIN
CREATE TABLE PREFERENCES_MEDECIN
(
    id_prefp INT,
    med_id   INT,
    theme    VARCHAR(20),
    langue   VARCHAR(50),
    CONSTRAINT pk_preferences PRIMARY KEY (id_prefp),
    CONSTRAINT fk_preferences FOREIGN KEY (med_id) REFERENCES MEDECIN (med_id) ON DELETE CASCADE
);

-- TABLE HISTORIQUE_CONSOLE
CREATE TABLE HISTORIQUE_CONSOLE
(
    log_id       BIGINT,
    med_id       INT,
    type_action  VARCHAR(20) NOT NULL CHECK (type_action IN ('réduire', 'ouvrir')),
    date_action  DATE        NOT NULL,
    heure_action TIME        NOT NULL,
    CONSTRAINT pk_historique PRIMARY KEY (log_id),
    CONSTRAINT fk_historique FOREIGN KEY (med_id) REFERENCES MEDECIN (med_id) ON DELETE CASCADE
);

-- TABLE GRAPHIQUE
CREATE TABLE GRAPHIQUE
(
    graph_id   INT,
    id_mesure  BIGINT,
    titre      VARCHAR(255) NOT NULL,
    type_graph VARCHAR(50)  NOT NULL CHECK (type_graph IN ('histogramme', 'courbes', 'nuage', 'secteurs', 'autre')),
    CONSTRAINT pk_graph PRIMARY KEY (graph_id),
    CONSTRAINT fk_graph FOREIGN KEY (id_mesure) REFERENCES MESURES (id_mesure)
);

-- TABLE SEUIL_ALERTE
CREATE TABLE SEUIL_ALERTE
(
    seuil_id  INT,
    id_mesure BIGINT,
    seuil_min REAL NOT NULL,
    seuil_max REAL NOT NULL,
    statut VARCHAR(50) NOT NULL CHECK (statut IN ('RAS', 'préoccupant', 'urgent', 'critique')),
    CONSTRAINT pk_seuil PRIMARY KEY (seuil_id),
    CONSTRAINT fk_seuil FOREIGN KEY (id_mesure) REFERENCES MESURES (id_mesure) ON DELETE CASCADE
);

-- TABLE ALERTE
CREATE TABLE ALERTE
(
    alerte_id   INT,
    date_alerte DATETIME    NOT NULL,
    seuil_id    INT,
    CONSTRAINT pk_alerte PRIMARY KEY (alerte_id),
    CONSTRAINT fk_alerte FOREIGN KEY (seuil_id) REFERENCES SEUIL_ALERTE (seuil_id) ON DELETE CASCADE
);

-- TABLE PASSWORD_RESETS
CREATE TABLE PASSWORD_RESETS
(
    id         BIGINT(20) UNSIGNED AUTO_INCREMENT,
    email      VARCHAR(255)    NOT NULL,
    token_hash CHAR(64) UNIQUE NOT NULL,
    expires_at DATETIME        NOT NULL CHECK (expires_at > created_at),
    used_at    DATETIME                 DEFAULT NULL,
    created_at DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT pk_password_resets PRIMARY KEY (id)
);
