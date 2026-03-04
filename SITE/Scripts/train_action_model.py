# train_action_model.py
# Entraîne un arbre de décision sur l'historique des actions du dashboard
# pour prédire la prochaine action du médecin.
#
# Usage : python SITE/Scripts/train_action_model.py
#
# Le modèle + encodeurs sont sauvegardés en .joblib dans SITE/storage/

import sys
import os
import pymysql
import numpy as np
from sklearn.tree import DecisionTreeClassifier
from sklearn.model_selection import cross_val_score
from sklearn.metrics import make_scorer
from sklearn.preprocessing import LabelEncoder
import joblib

# Mapping action ↔ entier (identique à la colonne type_action_id en BDD)
ACTION_MAP = {
    'ajouter': 0,
    'supprimer': 1,
    'réduire': 2,
    'agrandir': 3,
}
ACTION_MAP_INV = {v: k for k, v in ACTION_MAP.items()}


def get_db_config():
    """Lit le .env pour les identifiants BDD (même logique que Database.php)."""
    # Remonter à la racine du projet
    script_dir = os.path.dirname(os.path.abspath(__file__))
    project_root = os.path.dirname(os.path.dirname(script_dir))
    env_path = os.path.join(project_root, '.env')

    env = {}
    if os.path.isfile(env_path):
        with open(env_path, 'r', encoding='utf-8') as f:
            for line in f:
                line = line.strip()
                if not line or line.startswith('#') or line.startswith(';'):
                    continue
                if '=' not in line:
                    continue
                key, value = line.split('=', 1)
                key = key.strip()
                value = value.strip()
                # Retirer les guillemets
                if len(value) >= 2 and value[0] in ('"', "'") and value[-1] == value[0]:
                    value = value[1:-1]
                env[key] = value

    return {
        'host': env.get('DB_HOST', '127.0.0.1'),
        'port': int(env.get('DB_PORT', '3306')),
        'database': env.get('DB_NAME', 'dashmed-site_db'),
        'user': env.get('DB_USER', 'root'),
        'password': env.get('DB_PASS', ''),
    }


def fetch_training_data(conn):
    """Récupère l'historique des actions avec le type de mesure (JOIN mesures)."""
    # On garde que les lignes avec id_mesure NOT NULL, triées par ordre chronologique
    cur = conn.cursor()
    cur.execute("""
        SELECT 
            h.log_id,
            h.med_id,
            h.type_action,
            h.type_action_id,
            h.pt_id,
            m.type_mesure,
            h.date_action,
            h.heure_action
        FROM historique_console h
        JOIN mesures m ON h.id_mesure = m.id_mesure
        WHERE h.id_mesure IS NOT NULL
          AND h.pt_id IS NOT NULL
        ORDER BY h.med_id, h.pt_id, h.date_action, h.heure_action, h.log_id
    """)
    rows = cur.fetchall()
    cur.close()
    return rows


def build_pairs(rows):
    """
    Construit des paires (action N -> action N+1) groupées par médecin+patient.
    Ex: si le médecin fait supprimer FC puis ajouter Glycémie,
    on obtient la paire (supprimer, FC) -> (ajouter, Glycémie)
    """
    pairs = []
    sessions = {}  # grouper par (med_id, pt_id)
    for row in rows:
        log_id, med_id, action, action_id, pt_id, type_mesure, date_action, heure_action = row
        key = (med_id, pt_id)
        if key not in sessions:
            sessions[key] = []
        sessions[key].append({
            'action': action,
            'action_id': int(action_id),
            'type_mesure': type_mesure,
        })
    
    for key, actions in sessions.items():
        for i in range(len(actions) - 1):
            current = actions[i]
            next_action = actions[i + 1]
            pairs.append({
                'action_courante': current['action'],
                'action_id_courante': current['action_id'],
                'mesure_courante': current['type_mesure'],
                'action_suivante': next_action['action'],
                'action_id_suivante': next_action['action_id'],
                'mesure_suivante': next_action['type_mesure'],
            })
    
    return pairs


def train_model(pairs):
    """Encode les données en nombres et entraîne un DecisionTreeClassifier multi-output."""
    if len(pairs) < 5:
        print(f"ERREUR : Seulement {len(pairs)} paires trouvées. Il faut plus de données.")
        print("Utilisez le dashboard pour générer de l'historique, puis relancez.")
        sys.exit(1)
    
    # Les actions utilisent directement type_action_id de la BDD (0-3)
    # Seules les mesures ont besoin d'un encodeur (texte -> nombre)
    mesure_encoder = LabelEncoder()
    
    # Extraire les colonnes
    action_ids_courantes = [p['action_id_courante'] for p in pairs]
    mesures_courantes = [p['mesure_courante'] for p in pairs]
    action_ids_suivantes = [p['action_id_suivante'] for p in pairs]
    mesures_suivantes = [p['mesure_suivante'] for p in pairs]
    
    # Fit de l'encodeur mesures sur toutes les valeurs rencontrées
    all_mesures = list(set(mesures_courantes + mesures_suivantes))
    mesure_encoder.fit(all_mesures)
    
    # Features : [type_action_id, mesure_encoded]
    X = np.column_stack([
        np.array(action_ids_courantes),
        mesure_encoder.transform(mesures_courantes),
    ])
    
    # Multi-output : y = [action_id_suivante, mesure_suivante_encoded]
    # cf. doc sklearn section 1.10.3 "Multi-output problems"
    y = np.column_stack([
        np.array(action_ids_suivantes),
        mesure_encoder.transform(mesures_suivantes),
    ])
    
    print(f"Données d'entraînement : {len(pairs)} paires")
    print(f"Actions (type_action_id) : {sorted(set(action_ids_courantes + action_ids_suivantes))}")
    print(f"Mesures distinctes     : {list(mesure_encoder.classes_)}")
    print(f"Sorties                : {len(ACTION_MAP)} actions x {len(mesure_encoder.classes_)} mesures")
    
    # Entraînement
    # max_depth=10 pour éviter le sur-apprentissage
    # min_samples_leaf=5 comme recommandé par la doc sklearn (section 1.10.5 Tips)
    model = DecisionTreeClassifier(
        max_depth=10,
        min_samples_leaf=5,
        random_state=42
    )
    model.fit(X, y)
    
    # Pour le multi-output, accuracy_score ne marche pas directement.
    # On calcule manuellement : une prédiction est correcte si les 2 sorties sont bonnes
    def multioutput_accuracy(y_true, y_pred):
        correct = np.all(y_true == y_pred, axis=1)
        return np.mean(correct)
    
    scorer = make_scorer(multioutput_accuracy)
    
    # Validation croisée pour voir si le modèle généralise un minimum
    if len(pairs) >= 10:
        scores = cross_val_score(model, X, y, cv=min(5, len(pairs) // 2), scoring=scorer)
        print(f"Précision (cross-val)  : {scores.mean():.1%} ± {scores.std():.1%}")
    else:
        print("Pas assez de données pour la validation croisée.")
    
    # Score sur l'ensemble d'entraînement
    y_pred = model.predict(X)
    train_score = multioutput_accuracy(y, y_pred)
    print(f"Précision (train)      : {train_score:.1%}")
    
    return model, mesure_encoder


def save_model(model, mesure_encoder):
    """Sauvegarde le modèle et l'encodeur mesures en .joblib dans SITE/storage/."""
    script_dir = os.path.dirname(os.path.abspath(__file__))
    storage_dir = os.path.join(os.path.dirname(script_dir), 'storage')
    os.makedirs(storage_dir, exist_ok=True)
    
    joblib.dump(model, os.path.join(storage_dir, 'action_model.joblib'))
    joblib.dump(mesure_encoder, os.path.join(storage_dir, 'mesure_encoder.joblib'))
    
    print(f"\nModèle sauvegardé dans : {storage_dir}/")
    print("Fichiers : action_model.joblib, mesure_encoder.joblib")


def main():
    print("=" * 60)
    print("Entraînement du modèle de prédiction d'actions")
    print("=" * 60)
    
    # Connexion
    config = get_db_config()
    print(f"\nConnexion à {config['host']}:{config['port']}/{config['database']}...")
    conn = pymysql.connect(**config)
    print("Connecté !")
    
    # Récupération des données
    print("\nRécupération de l'historique...")
    rows = fetch_training_data(conn)
    print(f"Lignes récupérées : {len(rows)}")
    conn.close()
    
    # Construction des paires
    print("\nConstruction des paires d'actions consécutives...")
    pairs = build_pairs(rows)
    print(f"Paires générées : {len(pairs)}")
    
    # Entraînement
    print("\nEntraînement de l'arbre de décision (multi-output)...")
    print("-" * 40)
    model, mesure_enc = train_model(pairs)
    
    # Sauvegarde
    save_model(model, mesure_enc)
    
    print("\n✓ Terminé !")


if __name__ == '__main__':
    main()
