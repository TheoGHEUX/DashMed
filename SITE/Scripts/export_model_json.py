# export_model_json.py
# Exporte le DecisionTreeClassifier multi-output en JSON pur
# pour permettre la prédiction directement en PHP (sans Python au runtime).
#
# Usage : python SITE/Scripts/export_model_json.py
# Produit : SITE/storage/model.json

import os
import json
import numpy as np
import joblib


def tree_to_dict(tree, output_idx):
    """Convertit un sklearn Tree en structure JSON récursive pour un output donné."""
    t = tree.tree_

    def recurse(node_id):
        if t.children_left[node_id] == -1:  # feuille
            # Probas pour cet output : normaliser le compte dans la feuille
            values = t.value[node_id][output_idx]
            total = values.sum()
            probas = (values / total).tolist() if total > 0 else values.tolist()
            return {'leaf': True, 'probas': [round(p, 4) for p in probas]}
        else:
            return {
                'leaf': False,
                'feature': int(t.feature[node_id]),
                'threshold': round(float(t.threshold[node_id]), 6),
                'left': recurse(int(t.children_left[node_id])),
                'right': recurse(int(t.children_right[node_id])),
            }

    return recurse(0)


def main():
    script_dir = os.path.dirname(os.path.abspath(__file__))
    storage = os.path.join(os.path.dirname(script_dir), 'storage')

    model = joblib.load(os.path.join(storage, 'action_model.joblib'))
    mesure_enc = joblib.load(os.path.join(storage, 'mesure_encoder.joblib'))

    # L'arbre multi-output a un seul Tree interne mais 2 outputs
    # tree_.n_outputs = 2, tree_.value shape = (n_nodes, 2, max_n_classes)
    n_outputs = model.tree_.n_outputs
    action_classes = [int(c) for c in model.classes_[0]]
    mesure_classes = [int(c) for c in model.classes_[1]]

    export = {
        'feature_names': ['type_action_id', 'mesure_encoded', 'heure', 'position'],
        'action_map': {'ajouter': 0, 'supprimer': 1, 'réduire': 2, 'agrandir': 3},
        'action_map_inv': {str(v): k for k, v in {'ajouter': 0, 'supprimer': 1, 'réduire': 2, 'agrandir': 3}.items()},
        'mesure_classes': list(mesure_enc.classes_),
        'action_classes': action_classes,
        'mesure_output_classes': mesure_classes,
        'tree': {
            'action': tree_to_dict(model, 0),
            'mesure': tree_to_dict(model, 1),
        }
    }

    output_path = os.path.join(storage, 'model.json')
    with open(output_path, 'w', encoding='utf-8') as f:
        json.dump(export, f, ensure_ascii=False, indent=2)

    size_kb = os.path.getsize(output_path) / 1024
    print(f"Modèle exporté vers {output_path} ({size_kb:.1f} Ko)")
    print(f"Actions : {action_classes}")
    print(f"Mesures : {list(mesure_enc.classes_)}")


if __name__ == '__main__':
    main()
