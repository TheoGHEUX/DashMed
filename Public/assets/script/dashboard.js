/**
 * Initialisation du tableau de bord
 *
 * Gère le toggle de la liste des patients.
 *
 * Initialise les graphiques via window.initDashboardCharts.
 *
 * @module dashboard
 * @package Assets
 */
document.addEventListener('DOMContentLoaded', () => {
    const chartData = window.patientChartData || {};
    const btn = document.getElementById('togglePatients');
    const overlay = document.getElementById('patientsList');
    const content = document.querySelector('.patients-list-content');

    if (btn && overlay && content) {
        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            overlay.classList.toggle('show');
            btn.setAttribute('aria-expanded', overlay.classList.contains('show'));
        });

        overlay.addEventListener('click', () => {
            overlay.classList.remove('show');
            btn.setAttribute('aria-expanded', 'false');
        });

        content.addEventListener('click', (e) => e.stopPropagation());
    }

    if (typeof window.initDashboardCharts === 'function') {
        window.initDashboardCharts(chartData);
    }
});

