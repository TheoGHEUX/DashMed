document.addEventListener('DOMContentLoaded', function () {
    // Récupération des données injectées par PHP
    const chartData = window.patientChartData || {};
    const container = document.getElementById('notification-container');

    if (!container || Object.keys(chartData).length === 0) return;

    // Métriques dont une notification est actuellement ouverte
    const activeNotifications = new Set();

    // Dernière valeur notifiée par métrique (évite les répétitions au polling)
    const notifiedValues = {};
    Object.keys(chartData).forEach(key => {
        notifiedValues[key] = chartData[key]?.lastValue ?? null;
    });

    const scrollable = document.createElement('div');
    scrollable.id = 'notifications-scrollable';
    scrollable.className = 'notifications-scrollable';
    container.appendChild(scrollable);

    function getAlertLevel(metricKey, data) {
        const val = parseFloat(data.lastValue);
        if (isNaN(val)) return null;

        // Seuils MAX
        if (data.seuil_critique   && val >= data.seuil_critique)   return 'critique';
        if (data.seuil_urgent     && val >= data.seuil_urgent)     return 'urgent';
        if (data.seuil_preoccupant && val >= data.seuil_preoccupant) return 'préoccupant';

        // Seuils MIN
        if (data.seuil_critique_min   && val <= data.seuil_critique_min)   return 'critique';
        if (data.seuil_urgent_min     && val <= data.seuil_urgent_min)     return 'urgent';
        if (data.seuil_preoccupant_min && val <= data.seuil_preoccupant_min) return 'préoccupant';

        return null;
    }

    function getOrCreateDismissAllBtn() {
        let btn = document.getElementById('dismiss-all-btn');
        if (!btn) {
            btn = document.createElement('button');
            btn.id = 'dismiss-all-btn';
            btn.className = 'btn-dismiss-all';
            btn.textContent = 'Ignorer tout';
            btn.addEventListener('click', () => {
                scrollable.querySelectorAll('.notification-toast').forEach(n => closeNotification(n));
                activeNotifications.clear();
                btn.remove();
            });
            // Inséré avant le wrapper scrollable → toujours visible en haut
            container.insertBefore(btn, scrollable);
        }
        return btn;
    }

    function removeDismissAllBtnIfEmpty() {
        const remaining = scrollable.querySelectorAll('.notification-toast').length;
        if (remaining === 0) {
            document.getElementById('dismiss-all-btn')?.remove();
        }
    }

    function createNotification(metricKey, level, value, unit) {
        // Bloquer si une notification est déjà ouverte pour cette métrique
        if (activeNotifications.has(metricKey)) return;
        activeNotifications.add(metricKey);

        // S'assurer que le bouton "Ignorer tout" est présent
        getOrCreateDismissAllBtn();

        const titleMap = {
            'critique':    'Seuil Critique Atteint',
            'urgent':      'Seuil Urgent Atteint',
            'préoccupant': 'Attention Requise'
        };

        const cardTitle = document.querySelector(`article[data-chart-id="${metricKey}"] .card-title`)?.innerText || metricKey;
        const cleanTitle = cardTitle.split('(')[0].trim();

        const notif = document.createElement('div');
        notif.className = `notification-toast ${level}`;
        notif.innerHTML = `
            <div class="notification-header">
                <span class="notification-title">⚠️ ${titleMap[level]}</span>
            </div>
            <div class="notification-message">
                <strong>${cleanTitle}</strong> à <strong>${value} ${unit}</strong>.
            </div>
            <div class="notification-actions">
                <button class="btn-notif btn-notif-view">Voir l'alerte</button>
                <button class="btn-notif btn-notif-ignore">Ignorer</button>
            </div>
        `;

        // Voir l'alerte
        notif.querySelector('.btn-notif-view').addEventListener('click', () => {
            activeNotifications.delete(metricKey);

            const isVisible = window.dashboardIsChartVisible && window.dashboardIsChartVisible(metricKey);

            if (!isVisible && window.dashboardAddChart) {
                window.dashboardAddChart(metricKey);
                setTimeout(() => scrollToChart(metricKey), 100);
            } else {
                scrollToChart(metricKey);
            }

            closeNotification(notif);
        });

        // Ignorer
        notif.querySelector('.btn-notif-ignore').addEventListener('click', () => {
            activeNotifications.delete(metricKey);
            closeNotification(notif);
        });

        // Insérer dans le wrapper scrollable
        scrollable.appendChild(notif);
    }

    function scrollToChart(metricKey) {
        const chartCard = document.querySelector(`article[data-chart-id="${metricKey}"]`);
        if (!chartCard) return;
        chartCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
        chartCard.style.transition = 'box-shadow 0.5s';
        chartCard.style.boxShadow = '0 0 20px rgba(229, 62, 62, 0.6)';
        setTimeout(() => { chartCard.style.boxShadow = ''; }, 2000);
    }

    function closeNotification(element) {
        element.classList.add('closing');
        setTimeout(() => {
            if (element.parentNode) element.parentNode.removeChild(element);
            removeDismissAllBtnIfEmpty();
        }, 300);
    }

    Object.keys(chartData).forEach(key => {
        const metric = chartData[key];
        const level = getAlertLevel(key, metric);
        if (level) {
            createNotification(key, level, metric.lastValue, metric.unit);
        }
    });

    window.addEventListener('chartDataUpdated', (e) => {
        const newData = e.detail.chartData;

        Object.keys(newData).forEach(key => {
            const metric = newData[key];
            const newValue = metric.lastValue;

            // Ne notifier que si la valeur a changé depuis la dernière notification
            if (newValue === notifiedValues[key]) return;
            notifiedValues[key] = newValue;

            const level = getAlertLevel(key, metric);
            if (level) {
                createNotification(key, level, newValue, metric.unit);
            }
        });
    });
});