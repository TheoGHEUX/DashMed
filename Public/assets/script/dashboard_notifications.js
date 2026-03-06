document.addEventListener('DOMContentLoaded', function () {
    // Récupération des données injectées par PHP
    const chartData = window.patientChartData || {};
    const container = document.getElementById('notification-container');

    if (!container) return;

    // Création du wrapper et du bouton toggle
    // Le wrapper englobe [bouton toggle] + [panneau de notifications]
    const wrapper = document.createElement('div');
    wrapper.className = 'notification-wrapper';

    const toggleBtn = document.createElement('button');
    toggleBtn.className = 'notification-toggle-btn';
    toggleBtn.setAttribute('aria-label', 'Ouvrir/fermer les notifications');
    
    const arrow = document.createElement('span');
    arrow.className = 'toggle-arrow';
    arrow.textContent = '❯';
    
    const count = document.createElement('span');
    count.className = 'notif-count';
    
    toggleBtn.appendChild(arrow);
    toggleBtn.appendChild(count);

    // Insérer le wrapper autour du container existant
    container.parentNode.insertBefore(wrapper, container);
    wrapper.appendChild(toggleBtn);
    wrapper.appendChild(container);

    // Replié par défaut si aucune donnée
    if (Object.keys(chartData).length === 0) {
        wrapper.classList.add('collapsed');
    }

    toggleBtn.addEventListener('click', () => {
        wrapper.classList.toggle('collapsed');
    });

    function updateToggleBadge() {
        const count = activeNotifications.size;
        const badge = toggleBtn.querySelector('.notif-count');
        badge.textContent = count > 0 ? count : '';
    }

    // Si pas de données patient, on s'arrête ici
    if (Object.keys(chartData).length === 0) return;

    // État des notifications

    // Métriques dont une notification est actuellement ouverte
    const activeNotifications = new Set();

    // Dernière valeur notifiée par métrique (évite les répétitions au polling)
    const notifiedValues = {};
    Object.keys(chartData).forEach(key => {
        notifiedValues[key] = chartData[key]?.lastValue ?? null;
    });

    // Wrapper scrollable
    const scrollable = document.createElement('div');
    scrollable.id = 'notifications-scrollable';
    scrollable.className = 'notifications-scrollable';
    container.appendChild(scrollable);

    const panelLabel = document.createElement('div');
    panelLabel.className = 'notifications-panel-label';
    panelLabel.textContent = 'Menu des alertes';
    container.insertBefore(panelLabel, scrollable);

    // Détermine le niveau d'alerte d'une mesure
    function getAlertLevel(metricKey, data) {
        const val = parseFloat(data.lastValue);
        if (isNaN(val)) return null;

        // Seuils MAX
        if (data.seuil_critique    && val >= data.seuil_critique)    return 'critique';
        if (data.seuil_urgent      && val >= data.seuil_urgent)      return 'urgent';
        if (data.seuil_preoccupant && val >= data.seuil_preoccupant) return 'préoccupant';

        // Seuils MIN
        if (data.seuil_critique_min    && val <= data.seuil_critique_min)    return 'critique';
        if (data.seuil_urgent_min      && val <= data.seuil_urgent_min)      return 'urgent';
        if (data.seuil_preoccupant_min && val <= data.seuil_preoccupant_min) return 'préoccupant';

        return null;
    }

    // Gestion du bouton "Ignorer tout"
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
                updateToggleBadge();
                btn.remove();
            });
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

    // -------------------------------------------------------------------------
    // Création d'une notification
    // -------------------------------------------------------------------------
    function createNotification(metricKey, level, value, unit) {
        // Bloquer si une notification est déjà ouverte pour cette métrique
        if (activeNotifications.has(metricKey)) return;
        activeNotifications.add(metricKey);
        updateToggleBadge();

        getOrCreateDismissAllBtn();

        const titleMap = {
            'critique':    'Seuil Critique Atteint',
            'urgent':      'Seuil Urgent Atteint',
            'préoccupant': 'Attention Requise'
        };

        const cardTitle = document.querySelector(`article[data-chart-id="${metricKey}"] .card-title`)?.innerText || metricKey;
        const cleanTitle = cardTitle.split('(')[0].trim();

        // Création sécurisée du DOM (protection XSS)
        const notif = document.createElement('div');
        notif.className = `notification-toast ${level}`;
        
        const header = document.createElement('div');
        header.className = 'notification-header';
        
        const titleSpan = document.createElement('span');
        titleSpan.className = 'notification-title';
        titleSpan.textContent = '⚠️ ' + (titleMap[level] || level);
        header.appendChild(titleSpan);
        
        const message = document.createElement('div');
        message.className = 'notification-message';
        
        const titleStrong = document.createElement('strong');
        titleStrong.textContent = cleanTitle;
        const valueStrong = document.createElement('strong');
        valueStrong.textContent = value + ' ' + unit;
        
        message.appendChild(titleStrong);
        message.appendChild(document.createTextNode(' à '));
        message.appendChild(valueStrong);
        message.appendChild(document.createTextNode('.'));
        
        const actions = document.createElement('div');
        actions.className = 'notification-actions';
        
        const viewBtn = document.createElement('button');
        viewBtn.className = 'btn-notif btn-notif-view';
        viewBtn.textContent = "Voir l'alerte";
        
        const ignoreBtn = document.createElement('button');
        ignoreBtn.className = 'btn-notif btn-notif-ignore';
        ignoreBtn.textContent = 'Ignorer';
        
        actions.appendChild(viewBtn);
        actions.appendChild(ignoreBtn);
        
        notif.appendChild(header);
        notif.appendChild(message);
        notif.appendChild(actions);

        // Voir l'alerte
        viewBtn.addEventListener('click', () => {
            activeNotifications.delete(metricKey);
            updateToggleBadge();

            // Replier le panneau pour laisser la vue libre
            wrapper.classList.add('collapsed');

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
        ignoreBtn.addEventListener('click', () => {
            activeNotifications.delete(metricKey);
            updateToggleBadge();
            closeNotification(notif);
        });

        scrollable.appendChild(notif);
    }

    // Scroll vers la carte graphique avec effet de surbrillance
    function scrollToChart(metricKey) {
        const chartCard = document.querySelector(`article[data-chart-id="${metricKey}"]`);
        if (!chartCard) return;
        chartCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
        chartCard.style.transition = 'box-shadow 0.5s';
        chartCard.style.boxShadow = '0 0 20px rgba(229, 62, 62, 0.6)';
        setTimeout(() => { chartCard.style.boxShadow = ''; }, 2000);
    }

    // Fermeture d'une notification
    function closeNotification(element) {
        element.classList.add('closing');
        setTimeout(() => {
            if (element.parentNode) element.parentNode.removeChild(element);
            removeDismissAllBtnIfEmpty();
            updateToggleBadge();
        }, 300);
    }

    // Affichage initial des alertes au chargement
    Object.keys(chartData).forEach(key => {
        const metric = chartData[key];
        const level = getAlertLevel(key, metric);
        if (level) {
            createNotification(key, level, metric.lastValue, metric.unit);
        }
    });

    // Si aucune alerte au chargement, replier le panneau
    if (activeNotifications.size === 0) {
        wrapper.classList.add('collapsed');
    }

    // Mise à jour en temps réel
    window.addEventListener('chartDataUpdated', (e) => {
        const newData = e.detail.chartData;

        Object.keys(newData).forEach(key => {
            const metric = newData[key];
            const newValue = metric.lastValue;

            if (newValue === notifiedValues[key]) return;
            notifiedValues[key] = newValue;

            const level = getAlertLevel(key, metric);
            if (level) {
                createNotification(key, level, newValue, metric.unit);
            }
        });
    });
});