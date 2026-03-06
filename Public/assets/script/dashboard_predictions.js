// dashboard_predictions.js
// Après chaque action (ajouter/supprimer/agrandir/réduire), appelle l'API
// /api/predict-action pour suggérer la prochaine action via un pop-up.

document.addEventListener('DOMContentLoaded', function () {

    // Mapping chartId -> nom de mesure en base (doit rester synchro avec METRICS_CONFIG côté PHP)
    const CHART_TO_MESURE = {
        'temperature':       'Température corporelle',
        'blood-pressure':    'Tension artérielle',
        'heart-rate':        'Fréquence cardiaque',
        'respiration':       'Fréquence respiratoire',
        'glucose-trend':     'Glycémie',
        'weight':            'Poids',
        'oxygen-saturation': 'Saturation en oxygène'
    };

    // Mapping inverse pour retrouver le chartId depuis le nom de mesure
    const MESURE_TO_CHART = {};
    for (const [chartId, mesure] of Object.entries(CHART_TO_MESURE)) {
        MESURE_TO_CHART[mesure] = chartId;
    }

    let predictionActive = false; // empêche d'afficher plusieurs suggestions en même temps
    const MIN_CONFIDENCE = 0.05; // seuil minimum pour afficher une suggestion
    let sessionActionCount = 0;  // compteur d'actions dans la session courante

    // Appelle l'API de prédiction après une action utilisateur
    function fetchAndShowPrediction(action, chartId) {
        console.log('[IA] fetchAndShowPrediction appelé :', action, chartId);

        // Ne pas empiler les suggestions
        if (predictionActive) { console.log('[IA] bloqué : prédiction déjà active'); return; }

        // Résoudre le nom de mesure depuis le chartId
        const mesure = chartId ? CHART_TO_MESURE[chartId] : null;
        if (!mesure) { console.log('[IA] bloqué : mesure introuvable pour', chartId); return; }

        predictionActive = true;
        sessionActionCount++;

        fetch('/api/predict-action', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': window.csrfToken || ''
            },
            body: JSON.stringify({
                action: action,
                mesure: mesure,
                heure: new Date().getHours(),
                position: sessionActionCount
            })
        })
        .then(response => {
            console.log('[IA] réponse API :', response.status);
            // 503 = IA non disponible sur ce serveur (pas de modèle ou exec désactivé)
            if (response.status === 503) {
                predictionActive = false;
                return null;
            }
            if (!response.ok) throw new Error('Erreur API prédiction : ' + response.status);
            return response.json();
        })
        .then(data => {
            if (!data) return;
            console.log('[IA] données reçues :', data);
            if (data.error || !data.prediction) {
                console.log('[IA] bloqué : erreur ou pas de prédiction', data.error);
                predictionActive = false;
                return;
            }

            // Construire la liste de candidats : meilleure prédiction + top_predictions
            const candidates = [
                { action: data.prediction.action, mesure: data.prediction.mesure, probability: data.confidence || 0 },
                ...(data.top_predictions || [])
            ];

            // Parcourir les candidats et prendre le premier cohérent
            for (const candidate of candidates) {
                const pAction = candidate.action;
                const pMesure = candidate.mesure;
                const pConf = candidate.probability;

                // Confiance trop faible
                if (pConf < MIN_CONFIDENCE) {
                    console.log('[IA] candidat ignoré (confiance faible) :', pAction, pMesure, pConf);
                    continue;
                }

                // Même action sur la même mesure que celle qu'on vient de faire
                if (pAction === action && pMesure === mesure) {
                    console.log('[IA] candidat ignoré (même action/mesure) :', pAction, pMesure);
                    continue;
                }

                // Vérifier la cohérence avec le dashboard
                const targetChartId = MESURE_TO_CHART[pMesure] || null;
                if (targetChartId && typeof window.dashboardIsChartVisible === 'function') {
                    const isVisible = window.dashboardIsChartVisible(targetChartId);
                    if (pAction === 'ajouter' && isVisible) {
                        console.log('[IA] candidat ignoré (déjà visible) :', pAction, pMesure);
                        continue;
                    }
                    if (['supprimer', 'réduire', 'agrandir'].includes(pAction) && !isVisible) {
                        console.log('[IA] candidat ignoré (absent) :', pAction, pMesure);
                        continue;
                    }
                }

                // Ce candidat est cohérent → afficher
                console.log('[IA] ✅ affichage popup :', pAction, pMesure, pConf);
                showPredictionPopup(pAction, pMesure, pConf);
                return;
            }

            // Aucun candidat cohérent trouvé
            console.log('[IA] aucun candidat cohérent parmi', candidates.length, 'prédictions');
            predictionActive = false;
        })
        .catch(err => {
            console.error('[IA] ❌ erreur fetch :', err);
            predictionActive = false;
        });
    }

    // Affiche le pop-up de suggestion dans le conteneur de notifications
    function showPredictionPopup(action, mesure, confidence) {
        const container = document.getElementById('notification-container');
        if (!container) {
            predictionActive = false;
            return;
        }

        const targetChartId = MESURE_TO_CHART[mesure] || null;
        const confidencePercent = Math.round(confidence * 100);

        // Libellé lisible
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

        // Bouton Appliquer
        popup.querySelector('.btn-notif-accept').addEventListener('click', () => {
            executePredictedAction(action, targetChartId);
            closePrediction(popup);
        });

        // Bouton Ignorer
        popup.querySelector('.btn-notif-ignore').addEventListener('click', () => {
            closePrediction(popup);
        });

        container.appendChild(popup);
    }

    // Ferme un pop-up avec l'animation CSS .closing
    function closePrediction(element) {
        element.classList.add('closing');
        setTimeout(() => {
            if (element.parentNode) element.parentNode.removeChild(element);
            predictionActive = false;
        }, 300);
    }

    // Exécute l'action prédite (utilise les fonctions globales de dashboard_charts.js)
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
                // On peut pas redimensionner par code, on scroll juste vers le graphique
                highlightChart(chartId);
                break;
        }
    }

    // Highlight un graphique pour attirer l'attention dessus
    function highlightChart(chartId) {
        const card = document.querySelector(`article[data-chart-id="${chartId}"]`);
        if (card) {
            card.scrollIntoView({ behavior: 'smooth', block: 'center' });
            card.style.transition = 'box-shadow 0.5s';
            card.style.boxShadow = '0 0 20px rgba(99, 102, 241, 0.6)';
            setTimeout(() => { card.style.boxShadow = ''; }, 2500);
        }
    }

    // Expose globalement pour que dashboard_charts.js puisse l'appeler
    window.dashboardFetchPrediction = fetchAndShowPrediction;
});
