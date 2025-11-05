# LIRE ABSOLUMENT :
# Voici un programme python pour générer les valeurs des mesures souhaitées et les INSERT INTO directement dans un nouveau fichier
# FAIRE ATTENTION : Pour utiliser ce programme, il faut renseigner les tuples de MESURES dans la liste "mesures" ligne 36
# FAIRE TRES TRES TRES ATTENTION : modifier id_val ligne 40 par le premier id_val de la table VALEURS_MESURES non-utilisé

import random
from datetime import datetime, timedelta

# Configuration générale
start_datetime = datetime(2025, 10, 2, 14, 0)
nb_valeurs = 50

# Intervalles entre deux mesures
intervals = {
    "Température corporelle": timedelta(minutes=1),
    "Tension artérielle": timedelta(seconds=20),
    "Fréquence cardiaque": timedelta(seconds=2),
    "Poids": timedelta(days=7),
    "Glycémie": timedelta(minutes=15),
    "Fréquence respiratoire": timedelta(minutes=1),
    "Saturation en oxygène": timedelta(minutes=1),
}

# Plages réalistes
plages = {
    "Température corporelle": (36.4, 37.8),  # °C
    "Tension artérielle": (110, 140),        # mmHg
    "Fréquence cardiaque": (55, 100),        # bpm
    "Poids": (45, 100),                      # kg
    "Glycémie": (4.5, 7.2),                  # mmol/L
    "Fréquence respiratoire": (12, 20),      # rpm
    "Saturation en oxygène": (95, 100),      # %
}

# mesures
mesures = [...] # à renseigner

# --- Génération des valeurs ---
sql_lines = []
id_val = 1 # à modifier selon le besoin

# dictionnaire pour stocker la valeur de base de chaque patient pour chaque mesure
base_values = {}

for id_mesure, pt_id, type_mesure, unite in mesures:
    current_time = start_datetime
    interval = intervals[type_mesure]
    min_val, max_val = plages[type_mesure]

    # initialiser valeur de base si pas déjà fait
    if pt_id not in base_values:
        base_values[pt_id] = {}
    if type_mesure not in base_values[pt_id]:
        base_values[pt_id][type_mesure] = random.uniform(min_val, max_val)

    valeur_base = base_values[pt_id][type_mesure]
    # pour des variations des valeurs réalistes (pour patient sans probleme de sante)
    for i in range(nb_valeurs):
        if type_mesure == "Poids":
            valeur_base += random.uniform(-0.5, 0.5)
        elif type_mesure == "Température corporelle":
            valeur_base += random.uniform(-0.1, 0.1)
        elif type_mesure == "Tension artérielle":
            valeur_base += random.uniform(-2, 2)
        elif type_mesure == "Fréquence cardiaque":
            valeur_base += random.uniform(-3, 3)
        elif type_mesure == "Glycémie":
            valeur_base += random.uniform(-0.2, 0.2)
        elif type_mesure == "Fréquence respiratoire":
            valeur_base += random.uniform(-1, 1)
        elif type_mesure == "Saturation en oxygène":
            valeur_base += random.uniform(-0.5, 0.5)

        # bornes de sécurité
        valeur_base = max(min(valeur_base, max_val), min_val)
        valeur = round(valeur_base, 1)

        date_str = current_time.strftime("%Y-%m-%d")
        heure_str = current_time.strftime("%H:%M:%S")
        sql_lines.append(f"({id_val}, {valeur}, '{date_str}', '{heure_str}', {id_mesure})")
        id_val += 1
        current_time += interval

# --- Export SQL complet ---
sql_script = (
    "INSERT INTO VALEURS_MESURES (id_val, valeur, date_mesure, heure_mesure, id_mesure)\nVALUES\n"
    + ",\n".join(sql_lines)
    + ";"
)

with open("valeurs_mesures.sql", "w", encoding="utf-8") as f:
    f.write(sql_script)

print(f"✅ Script SQL généré avec {id_val-1} valeurs dans 'valeurs_mesures.sql'")

