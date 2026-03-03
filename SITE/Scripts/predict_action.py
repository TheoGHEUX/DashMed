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


def get_storage_dir():
    """Chemin vers SITE/storage/ où sont stockés les .joblib."""
    script_dir = os.path.dirname(os.path.abspath(__file__))
    return os.path.join(os.path.dirname(script_dir), 'storage')


def load_model():
    """Charge le modèle + les 3 encodeurs depuis storage/."""
    storage = get_storage_dir()

    model = joblib.load(os.path.join(storage, 'action_model.joblib'))
    action_enc = joblib.load(os.path.join(storage, 'action_encoder.joblib'))
    mesure_enc = joblib.load(os.path.join(storage, 'mesure_encoder.joblib'))
    label_enc = joblib.load(os.path.join(storage, 'label_encoder.joblib'))

    return model, action_enc, mesure_enc, label_enc


def predict(action_courante, mesure_courante):
    """Prédit la prochaine action à partir de l'action+mesure courantes."""
    model, action_enc, mesure_enc, label_enc = load_model()

    # Vérifier que les valeurs sont connues du modèle
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

    # Encoder les entrées (même format que l'entraînement)
    action_encoded = action_enc.transform([action_courante])[0]
    mesure_encoded = mesure_enc.transform([mesure_courante])[0]
    X = np.array([[action_encoded, mesure_encoded]])

    # predict_proba donne les probas pour chaque classe possible
    probabilities = model.predict_proba(X)[0]

    # Trier par proba décroissante et garder le top 3
    sorted_indices = np.argsort(probabilities)[::-1]

    top_predictions = []
    for idx in sorted_indices[:3]:
        prob = probabilities[idx]
        if prob < 0.05:
            break  # en dessous de 5% c'est pas pertinent

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

    # La meilleure prédiction
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
