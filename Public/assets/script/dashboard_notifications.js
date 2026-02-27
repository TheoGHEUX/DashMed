document.addEventListener('DOMContentLoaded', function() {
    // Récupération des données injectées par PHP
    const chartData = window.patientChartData || {};
    const container = document.getElementById('notification-container');

    if (!container || Object.keys(chartData).length === 0) return;

    const priorities = {
        'critique': 3,
        'urgent': 2,
        'préoccupant': 1,
        'normal': 0
    };

    //détermine le niveau d'alerte d'une mesure
    function getAlertLevel(metricKey, data) {
        const val = parseFloat(data.lastValue);
        if (isNaN(val)) return null;

        // On vérifie les seuils MAX
        if (data.seuil_critique && val >= data.seuil_critique) return 'critique';
        if (data.seuil_urgent && val >= data.seuil_urgent) return 'urgent';
        if (data.seuil_preoccupant && val >= data.seuil_preoccupant) return 'préoccupant';

        // On vérifie les seuils MIN (si définis)
        if (data.seuil_critique_min && val <= data.seuil_critique_min) return 'critique';
        if (data.seuil_urgent_min && val <= data.seuil_urgent_min) return 'urgent';
        if (data.seuil_preoccupant_min && val <= data.seuil_preoccupant_min) return 'préoccupant';

        return null;
    }

    function createNotification(metricKey, level, value, unit) {
        const titleMap = {
            'critique': 'Seuil Critique Atteint',
            'urgent': 'Seuil Urgent Atteint',
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
			// Vérifier si le graphique est visible dans le dashboard
			const isVisible = window.dashboardIsChartVisible && window.dashboardIsChartVisible(metricKey);
			
			// Si le graphique n'est pas visible, le réajouter
			if (!isVisible && window.dashboardAddChart) {
				window.dashboardAddChart(metricKey);
				// Attendre que le graphique soit ajouté au DOM avant de scroller
				setTimeout(() => {
					const chartCard = document.querySelector(`article[data-chart-id="${metricKey}"]`);
					if (chartCard) {
						chartCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
						chartCard.style.transition = 'box-shadow 0.5s';
						chartCard.style.boxShadow = '0 0 20px rgba(229, 62, 62, 0.6)';
						setTimeout(() => { chartCard.style.boxShadow = ''; }, 2000);
					}
				}, 100);
			} else {
				// Le graphique est déjà visible, scroller directement
				const chartCard = document.querySelector(`article[data-chart-id="${metricKey}"]`);
				if (chartCard) {
					chartCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
					chartCard.style.transition = 'box-shadow 0.5s';
					chartCard.style.boxShadow = '0 0 20px rgba(229, 62, 62, 0.6)';
					setTimeout(() => { chartCard.style.boxShadow = ''; }, 2000);
				}
			}
			closeNotification(notif);
		});

		// Ignorer
		notif.querySelector('.btn-notif-ignore').addEventListener('click', () => {
            closeNotification(notif);
        });

        container.appendChild(notif);
    }

    function closeNotification(element) {
        element.classList.add('closing');
        setTimeout(() => {
            if (element.parentNode) element.parentNode.removeChild(element);
        }, 300); // Attend la fin de l'animation CSS
    }


    Object.keys(chartData).forEach(key => {
        const metric = chartData[key];
        const level = getAlertLevel(key, metric);

        if (level) {
            createNotification(key, level, metric.lastValue, metric.unit);
        }
    });
});
