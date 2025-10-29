### Protocole pour générer des patients

-	Création à la main de patients qui serviront de modèles pour en générer d’autres :
  
INSERT INTO PATIENT (pt_id, prenom, nom, email, sexe, groupe_sanguin, date_naissance, telephone, ville, code_postal, adresse) VALUES 
(1, 'Alexandre', 'Jacob', 'alexandre.jacob@gmail.com', 'M', 'O+', '2006-08-15', '06-85-96-21-03', 'Mallemort', '13370', NULL),
(2, 'Jules', 'Fuselier', 'jules.fuselier@gmail.com', 'M', 'B-', '2006-04-26', '06-15-94-22-17', 'Aix-en-Provence', '13100', NULL),
(3, 'Hugo', 'Brest-Lestrade', 'hugo.brest@gmail.com', 'M', 'AB+', '2006-03-01', '06-28-11-65-71', 'Villeneuve', '04180', NULL),
(4, 'Ewan', 'Acemyan de Oliveira', 'ewan.acemyan@gmail.com', 'M', 'O+', '2006-03-16', '06-33-03-42-15', 'Marseille', '13000', NULL),
(5, 'Véronique', 'Klaxon', 'veronique.klaxon@gmail.com', 'F', 'AB+', '1949-04-24', '06-17-85-33-05', 'Boulogne-Billancourt', '92012', NULL),
(6, 'Chantal', 'Ladessu', 'chantal.ladessu@gmail.com', 'F', 'O-', '1948-05-05', '06-05-03-49-97', 'Roubaix', '59512', NULL);

-	Génération de nouveaux patients en suivant les modèles précedents :
  
Prompt : « Génére des INSERT INTO en sql pour 44 nouveaux patients en suivant le même paterne suivant tout en ajoutant une adresse plutôt que NULL. »

