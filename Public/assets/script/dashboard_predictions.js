/**
 * Module de prédiction IA pour le dashboard médical.
 *
 * Après chaque action utilisateur (ajouter, supprimer, agrandir, réduire),
 * appelle l'API /api/predict-action qui utilise un arbre de décision
 * (scikit-learn) pour suggérer la prochaine action probable.
 *
 * La suggestion est affichée sous forme de pop-up dans le conteneur
 * de notifications existant, avec un style distinct (bleu/violet IA).
 *
 * @module dashboard_predictions
 */
document.addEventListener('DOMContentLoaded', function () {

    /**
     * Mapping chartId ↔ nom de mesure tel qu'enregistré en base.
     * Doit rester synchronisé avec METRICS_CONFIG dans DashboardController.php
     */
    const CHART_TO_MESURE = {
        'temperature':       'Température corporelle',
        'blood-pressure':    'Tension artérielle',
        'heart-rate':        'Fréquence cardiaque',
        'respiration':       'Fréquence respiratoire',
        'glucose-trend':     'Glycémie',
        'weight':            'Poids',
        'oxygen-saturation': 'Saturation en oxygène'
    };

    /** Mapping inverse : nom de mesure → chartId */
    const MESURE_TO_CHART = {};
    for (const [chartId, mesure] of Object.entries(CHART_TO_MESURE)) {
        MESURE_TO_CHART[mesure] = chartId;
    }

    /** Empêche d'afficher plusieurs suggestions en même temps */
    let predictionActive = false;

    /** Seuil de confiance minimum pour afficher une suggestion (5 %) */
    const MIN_CONFIDENCE = 0.05;

    /**
     * Appelle l'API de prédiction et affiche le résultat en pop-up.
     *
     * @param {string} action  - L'action que l'utilisateur vient d'effectuer
     * @param {string|null} chartId - L'ID du graphique concerné
     */
    function fetchAndShowPrediction(action, chartId) {
        // Ne pas empiler les suggestions
        if (predictionActive) return;

        // Résoudre le nom de mesure depuis le chartId
        const mesure = chartId ? CHART_TO_MESURE[chartId] : null;
        if (!mesure) return;

        predictionActive = true;

        fetch('/api/predict-action', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': window.csrfToken || ''
            },
            body: JSON.stringify({ action: action, mesure: mesure })
        })
        .then(response => {
            if (!response.ok) throw new Error('Erreur API prédiction');
            return response.json();
        })
        .then(data => {
            if (data.error) {
                predictionActive = false;
                return;
            }

            const predictedAction = data.action;
            const predictedMesure = data.mesure;
            const confidence = data.confidence || 0;

            // Ne pas suggérer si la confiance est trop faible
            if (confidence < MIN_CONFIDENCE) {
                predictionActive = false;
                return;
            }

            // Ne pas suggérer la même action sur la même mesure
            if (predictedAction === action && predictedMesure === mesure) {
                predictionActive = false;
                return;
            }

            showPredictionPopup(predictedAction, predictedMesure, confidence);
        })
        .catch(() => {
            predictionActive = false;
        });
    }

    /**
     * Affiche une pop-up de suggestion IA dans le conteneur de notifications.
     *
     * @param {string} action     - Action prédite ('ajouter', 'supprimer', etc.)
     * @param {string} mesure     - Nom de la mesure prédite
     * @param {number} confidence - Niveau de confiance [0-1]
     */
    function showPredictionPopup(action, mesure, confidence) {
        const container = document.getElementById('notification-container');
        if (!container) {
            predictionActive = false;
            return;
        }

        const targetChartId = MESURE_TO_CHART[mesure] || null;
        const confidencePercent = Math.round(confidence * 100);

        // Libellé lisible de l'action
        const actionLabels = {
            'ajouter':   'Ajouter',
            'supprimer': 'Supprimer',
            'agrandir':  'Agrandir',
            'réduire':   'Réduire'
        };
        const actionLabel = actionLabels[action] || action;

        const popup = document.createElement('div');
        popup.className = 'notification-toast prediction-suggestion';
        popup.innerHTML = `
            <div class="notification-header">
                <span class="notification-title">🤖 Suggestion IA</span>
                <span class="prediction-confidence">${confidencePercent} %</span>
            </div>
            <div class="notification-message">
                <strong>${actionLabel}</strong> le graphique <strong>${mesure}</strong> ?
            </div>
            <div class="notification-actions">
                <button class="btn-notif btn-notif-accept">Appliquer</button>
                <button class="btn-notif btn-notif-ignore">Ignorer</button>
            </div>
        `;

        // Bouton « Appliquer » : exécute l'action prédite
        popup.querySelector('.btn-notif-accept').addEventListener('click', () => {
            executePredictedAction(action, targetChartId);
            closePrediction(popup);
        });

        // Bouton « Ignorer » : ferme la pop-up
        popup.querySelector('.btn-notif-ignore').addEventListener('click', () => {
            closePrediction(popup);
        });

        container.appendChild(popup);

        // Auto-fermeture après 8 secondes
        setTimeout(() => {
            if (popup.parentNode) {
                closePrediction(popup);
            }
        }, 8000);
    }

    /**
     * Ferme une pop-up de prédiction avec animation.
     *
     * @param {HTMLElement} element - Élément DOM de la pop-up
     */
    function closePrediction(element) {
        element.classList.add('closing');
        setTimeout(() => {
            if (element.parentNode) element.parentNode.removeChild(element);
            predictionActive = false;
        }, 300);
    }

    /**
     * Exécute l'action prédite sur le graphique cible.
     *
     * Utilise les fonctions globales exposées par dashboard_charts.js :
     * - window.dashboardAddChart(chartId)
     * - window.dashboardRemoveChart(chartId)
     *
     * Pour agrandir/réduire, scrolle vers le graphique pour signaler
     * visuellement l'action (le redimensionnement nécessite le mode édition).
     *
     * @param {string} action  - Action à exécuter
     * @param {string|null} chartId - ID du graphique cible
     */
    function executePredictedAction(action, chartId) {
        if (!chartId) return;

        switch (action) {
            case 'ajouter':
                if (window.dashboardAddChart) {
                    window.dashboardAddChart(chartId);
                }
                break;

            case 'supprimer':
                if (window.dashboardRemoveChart) {
                    window.dashboardRemoveChart(chartId);
                }
                break;

            case 'agrandir':
            case 'réduire':
                // Scroller vers le graphique pour montrer qu'il faudrait agir dessus
                highlightChart(chartId);
                break;
        }
    }

    /**
     * Met en surbrillance un graphique pour attirer l'attention.
     *
     * @param {string} chartId - ID du graphique à mettre en surbrillance
     */
    function highlightChart(chartId) {
        const card = document.querySelector(`article[data-chart-id="${chartId}"]`);
        if (card) {
            card.scrollIntoView({ behavior: 'smooth', block: 'center' });
            card.style.transition = 'box-shadow 0.5s';
            card.style.boxShadow = '0 0 20px rgba(99, 102, 241, 0.6)';
            setTimeout(() => { card.style.boxShadow = ''; }, 2500);
        }
    }

    // Exposer la fonction globalement pour dashboard_charts.js
    window.dashboardFetchPrediction = fetchAndShowPrediction;
});
