document.addEventListener('DOMContentLoaded', () => {

    /* ==========================
       DONNÉES GLOBALES
    ========================== */

    const data = window.DASHBOARD_DATA || {};
    const chartData = data.chartData || {};
    const patient = data.activePatient || null;


    /* ==========================
       ACCORDÉON PATIENTS
    ========================== */

    const btn = document.getElementById('togglePatients');
    const list = document.getElementById('patientsList');

    if (btn && list) {
        btn.addEventListener('click', () => {
            const isOpen = btn.getAttribute('aria-expanded') === 'true';
            btn.setAttribute('aria-expanded', String(!isOpen));

            isOpen
                ? list.setAttribute('hidden', '')
                : list.removeAttribute('hidden');
        });
    }


    /* ==========================
       INITIALISATION DES GRAPHIQUES
       (utilise dashboard_charts.js)
    ========================== */

    if (typeof window.initDashboardCharts === 'function') {
        window.initDashboardCharts(chartData);
    }


    /* ==========================
       DEBUG (à enlever plus tard)
    ========================== */

    console.debug('[Dashboard] Patient actif :', patient);
    console.debug('[Dashboard] Données graphiques :', chartData);

});

document.addEventListener('DOMContentLoaded', () => {

    const btn = document.getElementById('togglePatients');
    const overlay = document.getElementById('patientsList');
    const content = document.querySelector('.patients-list-content');

    if (btn && overlay && content) {

        // Toggle overlay
        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            overlay.classList.toggle('show');
            btn.setAttribute('aria-expanded', overlay.classList.contains('show'));
        });

        // Fermer si clic en dehors du menu
        overlay.addEventListener('click', () => {
            overlay.classList.remove('show');
            btn.setAttribute('aria-expanded', 'false');
        });

        // Empêcher fermeture si clic à l’intérieur
        content.addEventListener('click', (e) => e.stopPropagation());
    }

    /* ==========================
       Initialisation des graphiques
    ========================== */
    if (typeof window.initDashboardCharts === 'function') {
        window.initDashboardCharts(window.patientChartData || {});
    }

});