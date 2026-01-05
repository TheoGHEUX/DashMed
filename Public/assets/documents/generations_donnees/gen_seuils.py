# -*- coding: utf-8 -*-
import os

# liste mesures à renseigner
mesures = [...]

seuils_generiques = {
    'Température corporelle': [
        ('Préoccupant', 36.5, 0),
        ('Préoccupant', 38.5, 1),
        ('Urgent', 35.0, 0),
        ('Urgent', 40.0, 1),
        ('Critique', 32.0, 0),
        ('Critique', 42.0, 1)
    ],
    'Tension artérielle': [
        ('Préoccupant', 110, 0),
        ('Préoccupant', 130, 1),
        ('Urgent', 100, 0),
        ('Urgent', 140, 1),
        ('Critique', 90, 0),
        ('Critique', 160, 1)
    ],
    'Fréquence cardiaque': [
        ('Préoccupant', 60, 0),
        ('Préoccupant', 100, 1),
        ('Urgent', 50, 0),
        ('Urgent', 110, 1),
        ('Critique', 40, 0),
        ('Critique', 130, 1)
    ],
    'Poids': [
        ('Préoccupant', 50, 0),
        ('Préoccupant', 80, 1),
        ('Urgent', 45, 0),
        ('Urgent', 90, 1),
        ('Critique', 40, 0),
        ('Critique', 100, 1)
    ],
    'Glycémie': [
        ('Préoccupant', 4.0, 0),
        ('Préoccupant', 6.0, 1),
        ('Urgent', 3.0, 0),
        ('Urgent', 7.0, 1),
        ('Critique', 2.5, 0),
        ('Critique', 9.0, 1)
    ],
    'Fréquence respiratoire': [
        ('Préoccupant', 12, 0),
        ('Préoccupant', 20, 1),
        ('Urgent', 10, 0),
        ('Urgent', 24, 1),
        ('Critique', 8, 0),
        ('Critique', 28, 1)
    ],
    'Saturation en oxygène': [
        ('Préoccupant', 95, 0),
        ('Urgent', 85, 0),
        ('Critique', 75, 0)
    ]
}

output_file = "insert_seuils.sql"

with open("insert_seuils.sql", "w", encoding="utf-8") as f:
    f.write("-- INSERT INTO SEUIL_ALERTE généré automatiquement\n")
    f.write("DELETE FROM SEUIL_ALERTE;\n\n")

    for mesure in mesures:
        id_mesure, pt_id, type_mesure, unite = mesure
        seuils = seuils_generiques[type_mesure]
        for idx, (statut, seuil, majorant) in enumerate(seuils, start=1):
            seuil_id = int(f"{id_mesure}{idx:02d}")  # id_mesure + idx
            insert_stmt = f"INSERT INTO SEUIL_ALERTE (seuil_id, id_mesure, seuil, majorant, statut) " \
                          f"VALUES ({seuil_id}, {id_mesure}, {seuil}, {majorant}, '{statut}');\n"
            f.write(insert_stmt)

print(f"Fichier SQL généré : {os.path.abspath(output_file)}")

