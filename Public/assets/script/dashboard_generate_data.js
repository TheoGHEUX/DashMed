document.getElementById('generateDataBtn').addEventListener('click', () => {

    const btn = document.getElementById('generateDataBtn');
    btn.disabled = true;
    btn.textContent = "Live en cours...";

    let compteur = 0;

    const interval = setInterval(async () => {

        await fetch('/generate-data', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: 'patient=25'
        });

        compteur++;

        console.log("Valeur générée :", compteur);

        if (compteur >= 5) {
            clearInterval(interval);
            btn.disabled = false;
            btn.textContent = "Générer 5 mesures";
        }

    }, 3000); // 3 secondes
});

/**
 * Mise à jour en temps réel des graphiques du dashboard
 * Polling toutes les 15 secondes vers /api/dashboard/chart-data
 */
(function () {
    const POLL_INTERVAL_MS = 1000;
    let pollingTimer = null;
    let lastKnownData = window.patientChartData || {};

    /**
     * Récupère les données fraîches depuis le serveur.
     */
    async function fetchChartData(patientId) {
        const response = await fetch(`/api/dashboard/chart-data?ptId=${patientId}`, {
            method: 'GET',
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json' }
        });

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }

        const json = await response.json();
        if (!json.success) throw new Error(json.error || 'Erreur serveur');
        return json.chartData;
    }

    /**
     * Détecte si de nouvelles mesures sont disponibles en comparant
     * la dernière valeur et le nombre de points de chaque métrique.
     */
    function hasNewData(oldData, newData) {
        for (const key of Object.keys(newData)) {
            const oldMetric = oldData[key];
            const newMetric = newData[key];

            if (!oldMetric) return true; // nouvelle métrique apparue

            if (oldMetric.lastValue !== newMetric.lastValue) return true;

            const oldLen = (oldMetric.values || []).length;
            const newLen = (newMetric.values || []).length;
            if (oldLen !== newLen) return true;
        }
        return false;
    }

    /**
     * Met à jour window.patientChartData et demande à dashboard_charts.js
     * de redessiner tous les graphiques visibles.
     */
    function applyNewData(newData) {
        // Mettre à jour la source de données globale utilisée par CHART_DEFINITIONS
        window.patientChartData = newData;

        // Déclencher un événement custom que dashboard_charts.js peut écouter
        window.dispatchEvent(new CustomEvent('chartDataUpdated', { detail: { chartData: newData } }));
    }

    /**
     * Boucle de polling principale.
     */
    async function poll() {
        const patient = window.activePatient;
        if (!patient || !patient.pt_id) return;

        try {
            const newData = await fetchChartData(patient.pt_id);

            if (hasNewData(lastKnownData, newData)) {
                console.log('[Realtime] Nouvelles données détectées, mise à jour des graphiques.');
                lastKnownData = newData;
                applyNewData(newData);
            }
        } catch (err) {
            console.warn('[Realtime] Échec du polling :', err.message);
            // On ne stoppe pas le polling en cas d'erreur réseau ponctuelle
        }
    }

    /**
     * Démarre le polling.
     */
    function startPolling() {
        if (pollingTimer) return;
        pollingTimer = setInterval(poll, POLL_INTERVAL_MS);
        console.log(`[Realtime] Polling démarré (intervalle : ${POLL_INTERVAL_MS / 1000}s)`);
    }

    /**
     * Arrête le polling (ex: onglet en arrière-plan).
     */
    function stopPolling() {
        if (pollingTimer) {
            clearInterval(pollingTimer);
            pollingTimer = null;
            console.log('[Realtime] Polling arrêté.');
        }
    }

    // Pause automatique quand l'onglet est caché (économie de ressources)
    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            stopPolling();
        } else {
            poll();          // rafraîchissement immédiat au retour
            startPolling();
        }
    });

    // Démarrer une fois que dashboard_charts.js est prêt
    document.addEventListener('DOMContentLoaded', () => {
        startPolling();
    });
})();