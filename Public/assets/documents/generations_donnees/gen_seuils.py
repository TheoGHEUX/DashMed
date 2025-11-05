# -*- coding: utf-8 -*-
import os

# liste mesures à renseigner
mesures = [...]

seuils_generiques = {
    'Température corporelle': [
        ('RAS', 36.5, 38.5),
        ('Préoccupant', 35.0, 36.5),
        ('Préoccupant', 38.5, 40.0),
        ('Urgent', 32.0, 35.0),
        ('Urgent', 40.0, 42.0),
        ('Critique', 24.0, 32.0),
        ('Critique', 42.0, 50.0)
    ],
    'Tension artérielle': [
        ('RAS', 110, 130),
        ('Préoccupant', 100, 110),
        ('Préoccupant', 130, 140),
        ('Urgent', 90, 100),
        ('Urgent', 140, 160),
        ('Critique', 80, 90),
        ('Critique', 160, 200)
    ],
    'Fréquence cardiaque': [
        ('RAS', 60, 100),
        ('Préoccupant', 50, 60),
        ('Préoccupant', 100, 110),
        ('Urgent', 40, 50),
        ('Urgent', 110, 130),
        ('Critique', 30, 40),
        ('Critique', 130, 200)
    ],
    'Poids': [
        ('RAS', 50, 80),
        ('Préoccupant', 45, 50),
        ('Préoccupant', 80, 90),
        ('Urgent', 40, 45),
        ('Urgent', 90, 100),
        ('Critique', 30, 40),
        ('Critique', 100, 150)
    ],
    'Glycémie': [
        ('RAS', 4.0, 6.0),
        ('Préoccupant', 3.0, 4.0),
        ('Préoccupant', 6.0, 7.0),
        ('Urgent', 2.5, 3.0),
        ('Urgent', 7.0, 9.0),
        ('Critique', 2.0, 2.5),
        ('Critique', 9.0, 20.0)
    ],
    'Fréquence respiratoire': [
        ('RAS', 12, 20),
        ('Préoccupant', 10, 12),
        ('Préoccupant', 20, 24),
        ('Urgent', 8, 10),
        ('Urgent', 24, 28),
        ('Critique', 6, 8),
        ('Critique', 28, 50)
    ],
    'Saturation en oxygène': [
        ('RAS', 95, 100),
        ('Préoccupant', 85, 95),
        ('Urgent', 75, 85),
        ('Critique', 0, 75)
    ]
}

output_file = "insert_seuils.sql"

with open("insert_seuils.sql", "w", encoding="utf-8") as f:
    f.write("-- INSERT INTO SEUIL_ALERTE généré automatiquement\n")
    f.write("DELETE FROM SEUIL_ALERTE;\n\n")

    for mesure in mesures:
        id_mesure, pt_id, type_mesure, unite = mesure
        seuils = seuils_generiques[type_mesure]
        for idx, (statut, seuil_min, seuil_max) in enumerate(seuils, start=1):
            seuil_id = int(f"{id_mesure}{idx:02d}")  # id_mesure + idx
            insert_stmt = f"INSERT INTO SEUIL_ALERTE (seuil_id, id_mesure, seuil_min, seuil_max, statut) " \
                          f"VALUES ({seuil_id}, {id_mesure}, {seuil_min}, {seuil_max}, '{statut}');\n"
            f.write(insert_stmt)

print(f"Fichier SQL généré : {os.path.abspath(output_file)}")

