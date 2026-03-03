"""
Script de prédiction — Prédit la prochaine action du médecin.

Appelé par PHP via exec(). Reçoit l'action courante et le type de mesure
en arguments, charge le modèle entraîné, et renvoie une prédiction en JSON.

Usage :
    python predict_action.py <action_courante> <type_mesure_courante>

Exemples :
    python predict_action.py supprimer "Fréquence cardiaque"
    python predict_action.py réduire "Température corporelle"

Sortie (JSON sur stdout) :
    {
        "success": true,
        "prediction": {
            "action": "supprimer",
            "mesure": "Tension artérielle"
        },
        "confidence": 0.72,
        "top_predictions": [
            {"action": "supprimer", "mesure": "Tension artérielle", "probability": 0.72},
            {"action": "supprimer", "mesure": "Glycémie", "probability": 0.15}
        ]
    }
"""

import sys
import os
import json
import numpy as np
import joblib


def get_storage_dir():
    """Retourne le chemin vers SITE/storage/."""
    script_dir = os.path.dirname(os.path.abspath(__file__))
    return os.path.join(os.path.dirname(script_dir), 'storage')


def load_model():
    """
    Charge le modèle et les encodeurs depuis SITE/storage/.

    Fichiers attendus :
        - action_model.joblib   : l'arbre de décision entraîné
        - action_encoder.joblib : encodeur actions (texte → nombre)
        - mesure_encoder.joblib : encodeur mesures (texte → nombre)
        - label_encoder.joblib  : encodeur labels (nombre → "action|mesure")
    """
    storage = get_storage_dir()

    model = joblib.load(os.path.join(storage, 'action_model.joblib'))
    action_enc = joblib.load(os.path.join(storage, 'action_encoder.joblib'))
    mesure_enc = joblib.load(os.path.join(storage, 'mesure_encoder.joblib'))
    label_enc = joblib.load(os.path.join(storage, 'label_encoder.joblib'))

    return model, action_enc, mesure_enc, label_enc


def predict(action_courante, mesure_courante):
    """
    Prédit la prochaine action à partir de l'action et mesure courantes.

    Processus :
        1. Encode l'action et la mesure en nombres via les encodeurs
        2. Passe le vecteur [action_encodée, mesure_encodée] au modèle
        3. Récupère les probabilités pour chaque classe
        4. Décode les top prédictions en texte lisible

    Retourne un dictionnaire JSON-serializable avec :
        - La prédiction principale (action + mesure)
        - Le niveau de confiance (probabilité)
        - Les 3 meilleures prédictions avec leurs probabilités
    """
    model, action_enc, mesure_enc, label_enc = load_model()

    # Vérifier que l'action et la mesure sont connues du modèle
    if action_courante not in action_enc.classes_:
        return {
            'success': False,
            'error': f"Action inconnue : '{action_courante}'. "
                     f"Actions connues : {list(action_enc.classes_)}"
        }

    if mesure_courante not in mesure_enc.classes_:
        return {
            'success': False,
            'error': f"Mesure inconnue : '{mesure_courante}'. "
                     f"Mesures connues : {list(mesure_enc.classes_)}"
        }

    # Encoder les entrées
    action_encoded = action_enc.transform([action_courante])[0]
    mesure_encoded = mesure_enc.transform([mesure_courante])[0]

    # Construire le vecteur de features (même format que l'entraînement)
    X = np.array([[action_encoded, mesure_encoded]])

    # Prédiction avec probabilités
    probabilities = model.predict_proba(X)[0]

    # Trier par probabilité décroissante
    sorted_indices = np.argsort(probabilities)[::-1]

    # Décoder les top 3 prédictions
    top_predictions = []
    for idx in sorted_indices[:3]:
        prob = probabilities[idx]
        if prob < 0.05:
            break  # Ignorer les prédictions < 5%

        label = label_enc.inverse_transform([idx])[0]
        action_pred, mesure_pred = label.split('|', 1)

        top_predictions.append({
            'action': str(action_pred),
            'mesure': str(mesure_pred),
            'probability': round(float(prob), 3)
        })

    if not top_predictions:
        return {
            'success': False,
            'error': 'Aucune prédiction suffisamment fiable'
        }

    # Résultat principal = la prédiction la plus probable
    best = top_predictions[0]

    return {
        'success': True,
        'prediction': {
            'action': best['action'],
            'mesure': best['mesure']
        },
        'confidence': best['probability'],
        'top_predictions': top_predictions
    }


def main():
    # Vérification des arguments
    if len(sys.argv) != 3:
        result = {
            'success': False,
            'error': 'Usage : python predict_action.py <action> <type_mesure>'
        }
        print(json.dumps(result, ensure_ascii=False))
        sys.exit(1)

    action_courante = sys.argv[1]
    mesure_courante = sys.argv[2]

    try:
        result = predict(action_courante, mesure_courante)
    except FileNotFoundError:
        result = {
            'success': False,
            'error': 'Modèle non trouvé. Lancez d\'abord train_action_model.py'
        }
    except Exception as e:
        result = {
            'success': False,
            'error': f'Erreur de prédiction : {str(e)}'
        }

    print(json.dumps(result, ensure_ascii=False))
    sys.exit(0 if result.get('success') else 1)


if __name__ == '__main__':
    main()
