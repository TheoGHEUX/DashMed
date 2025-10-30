# LIRE ABSOLUMENT :
# Voici un programme python pour générer les valeurs des mesures souhaitées et les INSERT INTO directement dans un nouveau fichier
# FAIRE ATTENTION : Pour utiliser ce programme, il faut renseigner les tuples de MESURES dans la liste "mesures" ligne 32
# FAIRE TRES TRES TRES ATTENTION : modifier id_val ligne 36 par le premier id_val de la table VALEURS_MESURES non-utilisé

import random
from datetime import datetime, timedelta

# Configuration générale
start_datetime = datetime(2025, 12, 2, 14, 53)
nb_valeurs = 50

# Intervalles entre deux mesures
intervals = {
    "Température corporelle": timedelta(minutes=1),
    "Tension artérielle": timedelta(seconds=20),
    "Fréquence cardiaque": timedelta(seconds=2),
    "Poids": timedelta(days=7),
    "Glycémie": timedelta(minutes=15)
}

# Plages de valeurs (pour patients sans problème particulier)
plages = {
    "Température corporelle": (36.4, 37.8), # °C
    "Tension artérielle": (110, 140),   # mmHg
    "Fréquence cardiaque": (55, 100),   # bpm
    "Poids": (45, 100),                 # kg
    "Glycémie": (4.5, 7.2)              # mmol/L
}

# tuples de MESURES :
mesures = [...] # A renseigner

# Génération des valeurs
sql_lines = []
id_val = ... # A renseigner

# dictionnaire pour stocker le poids de départ de chaque patient
# On fait un procédé différent pour le poids pour avoir des variations réalistes ( ne pas passer de 45 à 90 kg en 1 semaine par exemple )
poids_base = {}

for id_mesure, pt_id, type_mesure, unite in mesures:
    current_time = start_datetime
    interval = intervals[type_mesure]
    min_val, max_val = plages[type_mesure]

    # pour le poids, initialiser un poids de base pour le patient si pas déjà fait
    if type_mesure == "Poids":
        if pt_id not in poids_base:
            poids_base[pt_id] = random.uniform(min_val, max_val)
        poids = poids_base[pt_id]

    for i in range(nb_valeurs):
        if type_mesure == "Poids":
            # variation douce de plus ou moins 0.5 kg max par semaine
            poids += random.uniform(-0.5, 0.5)
            poids = max(min(poids, 200), 30)  # bornes de sécurité
            valeur = round(poids, 1)
        else:
            valeur = round(random.uniform(min_val, max_val), 1)

        date_str = current_time.strftime("%Y-%m-%d")
        heure_str = current_time.strftime("%H:%M:%S")
        sql_lines.append(f"({id_val}, {valeur}, '{date_str}', '{heure_str}', {id_mesure})")
        id_val += 1
        current_time += interval

# Export SQL vers un autre fichier
sql_script = (
    "INSERT INTO VALEURS_MESURES (id_val, valeur, date_mesure, heure_mesure, id_mesure)\nVALUES\n"
    + ",\n".join(sql_lines)
    + ";"
)

with open("valeurs_mesures.sql", "w", encoding="utf-8") as f:
    f.write(sql_script)

print(f"✅ Script SQL généré avec succès !")
