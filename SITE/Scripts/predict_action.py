# predict_action.py
# Appelé par PHP (exec) pour prédire la prochaine action du médecin.
# Charge le modèle entraîné et retourne du JSON sur stdout.
#
# Usage : python predict_action.py <action> <type_mesure>
# Ex: python predict_action.py supprimer "Fréquence cardiaque"

import sys
import os
import json
import numpy as np
import joblib

# Mapping action ↔ entier (identique à type_action_id en BDD)
ACTION_MAP = {
    'ajouter': 0,
    'supprimer': 1,
    'réduire': 2,
    'agrandir': 3,
}
ACTION_MAP_INV = {v: k for k, v in ACTION_MAP.items()}


def get_storage_dir():
    """Chemin vers SITE/storage/ où sont stockés les .joblib."""
    script_dir = os.path.dirname(os.path.abspath(__file__))
    return os.path.join(os.path.dirname(script_dir), 'storage')


def load_model():
    """Charge le modèle + l'encodeur mesures depuis storage/."""
    storage = get_storage_dir()

    model = joblib.load(os.path.join(storage, 'action_model.joblib'))
    mesure_enc = joblib.load(os.path.join(storage, 'mesure_encoder.joblib'))

    return model, mesure_enc


def predict(action_courante, mesure_courante):
    """Prédit la prochaine action à partir de l'action+mesure courantes."""
    model, mesure_enc = load_model()

    # Vérifier que les valeurs sont connues
    if action_courante not in ACTION_MAP:
        return {
            'success': False,
            'error': f"Action inconnue : '{action_courante}'. "
                     f"Actions connues : {list(ACTION_MAP.keys())}"
        }

    if mesure_courante not in mesure_enc.classes_:
        return {
            'success': False,
            'error': f"Mesure inconnue : '{mesure_courante}'. "
                     f"Mesures connues : {list(mesure_enc.classes_)}"
        }

    # Encoder les entrées : action via type_action_id, mesure via LabelEncoder
    action_id = ACTION_MAP[action_courante]
    mesure_encoded = mesure_enc.transform([mesure_courante])[0]
    X = np.array([[action_id, mesure_encoded]])

    # Multi-output : predict_proba renvoie une liste de 2 arrays
    # [action_probs, mesure_probs] (cf. doc sklearn section 1.10.3)
    proba_list = model.predict_proba(X)
    action_probs = proba_list[0][0]  # probas pour chaque action possible
    mesure_probs = proba_list[1][0]  # probas pour chaque mesure possible

    # Top actions et mesures triées par proba décroissante
    top_action_idx = np.argsort(action_probs)[::-1]
    top_mesure_idx = np.argsort(mesure_probs)[::-1]

    best_action_idx = top_action_idx[0]
    best_mesure_idx = top_mesure_idx[0]

    best_action = ACTION_MAP_INV[int(best_action_idx)]
    best_mesure = str(mesure_enc.inverse_transform([best_mesure_idx])[0])

    # Confiance = produit des 2 probas (action et mesure indépendantes)
    action_conf = float(action_probs[best_action_idx])
    mesure_conf = float(mesure_probs[best_mesure_idx])
    confidence = round(action_conf * mesure_conf, 3)

    # Construire le top 3 des combinaisons les plus probables
    top_predictions = []
    for a_idx in top_action_idx[:3]:
        for m_idx in top_mesure_idx[:3]:
            a_prob = float(action_probs[a_idx])
            m_prob = float(mesure_probs[m_idx])
            combined = a_prob * m_prob
            if combined < 0.05:
                continue
            top_predictions.append({
                'action': ACTION_MAP_INV[int(a_idx)],
                'mesure': str(mesure_enc.inverse_transform([m_idx])[0]),
                'probability': round(combined, 3)
            })

    # Trier par proba décroissante et garder le top 3
    top_predictions.sort(key=lambda x: x['probability'], reverse=True)
    top_predictions = top_predictions[:3]

    if not top_predictions:
        return {
            'success': False,
            'error': 'Aucune prédiction suffisamment fiable'
        }

    return {
        'success': True,
        'prediction': {
            'action': best_action,
            'mesure': best_mesure
        },
        'confidence': confidence,
        'details': {
            'action_confidence': round(action_conf, 3),
            'mesure_confidence': round(mesure_conf, 3)
        },
        'top_predictions': top_predictions
    }


def main():
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
