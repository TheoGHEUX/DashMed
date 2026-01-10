// Lightweight placeholder charts for the dashboard (multiple clinical types)
// Pour réinitialiser la configuration et afficher tous les graphiques :
// Ouvrez la console (F12) et tapez : localStorage.removeItem('dashboardChartConfig'); location.reload();
// Ou ajoutez ?reset=1 à l'URL du dashboard
/**
 * Scripts de gestion des graphiques du tableau de bord
 *
 * Fournit des rendus Canvas 2D pour visualiser les constantes vitales des patients
 * avec seuils d'alerte, tooltips interactifs et configuration personnalisable.
 *
 * Fonctionnalités principales :
 *  * - **Seuils d'alerte** : Trois niveaux (préoccupant, urgent, critique) min et max
 *  * - **Responsive** : Adaptation devicePixelRatio pour tout type d'écrans
 *  * - **Interactivité** : Tooltips au survol, highlight des points
 *  * - **Mode édition** : Drag & drop, redimensionnement, ajout/suppression de graphiques
 *  * - **Persistance** : Configuration sauvegardée dans localStorage
 *  * - **Thème** : Adaptation automatique au mode sombre/clair
 *  * - **Auto-scroll** : Défilement automatique lors du drag près des bords
 *  * - **Log** : Envoi des actions (ajouter, supprimer, agrandir, réduire) au serveur
 *
 * @module dashboard_charts
 *  * @requires dashboard. js (pour initialisation via window.initDashboardCharts)
 *  * @requires dark-mode.js (pour l'événement 'themechange')
 *
 */

document.addEventListener('DOMContentLoaded', () => {
	const DPR = window.devicePixelRatio || 1;

	// Ajouter la classe initial-load pour les animations de chargement
	const grid = document.getElementById('dashboardGrid');
	if (grid) {
		grid.classList.add('initial-load');
		// Retirer après que les animations soient terminées
		setTimeout(() => {
			grid.classList.remove('initial-load');
		}, 1000);
	}

	// Reset config if requested via URL parameter
	const urlParams = new URLSearchParams(window.location.search);
	if (urlParams.get('reset') === '1') {
		localStorage.removeItem('dashboardChartConfig');
		// Remove the reset parameter from URL
		window.history.replaceState({}, '', window.location.pathname);
	}

	// Récupérer les données réelles du patient injectées par PHP
	const patientData = window.patientChartData || {};

    /**
     * Définition des données médicales disponibles.
     *
     * Chaque entrée contient :
     * - title : Titre affiché sur la carte
     * - type : Type de graphique ('area', 'bar', 'dual-line')
     * - data : Tableau de valeurs normalisées (0-1) depuis patientData
     * - color : Couleur principale de la courbe
     * - minVal/maxVal : Bornes pour la normalisation
     * - unit : Unité de mesure affichée
     * - thresholds : Seuils d'alerte (6 valeurs :  3 majorants + 3 minorants)
     * - valueId/noteId : IDs des éléments DOM pour affichage des valeurs
     * - value/note : Valeur et note affichées sous le graphique
     *
     * @const {Object. <string, Object>}
     */
    const CHART_DEFINITIONS = {
        'blood-pressure': {
            title: 'Tendance de la tension (mmHg)',
            type: 'area',
            data: patientData['blood-pressure']?.values, // Si pas de valeur pour cette mesure, mettre un tableau vide par défaut
            color: '#efcd44',
            minVal: 80,
            maxVal: 160,
            unit: 'mmHg',
            thresholds: {
                preoccupant: patientData['blood-pressure']?.seuil_preoccupant ?? null,
                urgent: patientData['blood-pressure']?.seuil_urgent ?? null,
                critique: patientData['blood-pressure']?.seuil_critique ?? null,
                preoccupant_min: patientData['blood-pressure']?.seuil_preoccupant_min ?? null,
                urgent_min: patientData['blood-pressure']?.seuil_urgent_min ?? null,
                critique_min: patientData['blood-pressure']?.seuil_critique_min ?? null,
            },
            valueId: 'value-bp',
            noteId: 'note-bp',
            value: patientData['blood-pressure']?.lastValue?.toFixed(0) || 'No Data',
            note: (patientData['blood-pressure']?.unit || 'mmHg') + ', dernière mesure'
        },
        'heart-rate': {
            title: 'Fréquence cardiaque',
            type: 'area',
            data: patientData['heart-rate']?.values,
            color: '#ff71ac',
            minVal: 35,
            maxVal: 130,
            unit: 'bpm',
            thresholds: {
                preoccupant: patientData['heart-rate']?.seuil_preoccupant ?? null,
                urgent: patientData['heart-rate']?.seuil_urgent ?? null,
                critique: patientData['heart-rate']?.seuil_critique ?? null,
                preoccupant_min: patientData['heart-rate']?.seuil_preoccupant_min ?? null,
                urgent_min: patientData['heart-rate']?.seuil_urgent_min ?? null,
                critique_min: patientData['heart-rate']?.seuil_critique_min ?? null,
            },
            valueId: 'value-hr',
            noteId: 'note-hr',
            value: patientData['heart-rate']?.lastValue?.toFixed(0) || 'No Data',
            note: (patientData['heart-rate']?.unit || 'BPM') + ', dernière mesure'
        },
        'respiration': {
            title: 'Fréquence respiratoire',
            type: 'area',
            data: patientData['respiration']?.values,
            color: '#0ea5e9',
            minVal: 0,
            maxVal: 30,
            unit: 'resp/min',
            thresholds: {
                preoccupant: patientData['respiration']?.seuil_preoccupant ?? null,
                urgent: patientData['respiration']?.seuil_urgent ?? null,
                critique: patientData['respiration']?.seuil_critique ?? null,
                preoccupant_min: patientData['respiration']?.seuil_preoccupant_min ?? null,
                urgent_min: patientData['respiration']?.seuil_urgent_min ?? null,
                critique_min: patientData['respiration']?.seuil_critique_min ?? null,
            },
            valueId: 'value-resp',
            noteId: 'note-resp',
            value: patientData['respiration']?.lastValue?.toFixed(0) || 'No Data',
            note: (patientData['respiration']?.unit || 'Resp/min')
        },
        'temperature': {
            title: 'Température corporelle',
            type: 'area',
            data: patientData['temperature']?.values,
            color: '#ffab6e',
            minVal: 31.0,
            maxVal: 42.0,
            unit: '°C',
            thresholds: {
                preoccupant: patientData['temperature']?.seuil_preoccupant ?? null,
                urgent: patientData['temperature']?.seuil_urgent ?? null,
                critique: patientData['temperature']?.seuil_critique ?? null,
                preoccupant_min: patientData['temperature']?.seuil_preoccupant_min ?? null,
                urgent_min: patientData['temperature']?.seuil_urgent_min ?? null,
                critique_min: patientData['temperature']?.seuil_critique_min ?? null,
            },
            valueId: 'value-temp',
            noteId: 'note-temp',
            value: patientData['temperature']?.lastValue?.toFixed(1) || 'No Data',
            note: (patientData['temperature']?.unit || '°C') + ', dernière mesure'
        },
        'glucose-trend': {
            title: 'Glycémie (tendance)',
            type: 'area',
            data: patientData['glucose-trend']?.values,
            color: '#ee7dff',
            minVal: 2.0,
            maxVal: 10,
            unit: 'mmol/L',
            thresholds: {
                preoccupant: patientData['glucose-trend']?.seuil_preoccupant ?? null,
                urgent: patientData['glucose-trend']?.seuil_urgent ?? null,
                critique: patientData['glucose-trend']?.seuil_critique ?? null,
                preoccupant_min: patientData['glucose-trend']?.seuil_preoccupant_min ?? null,
                urgent_min: patientData['glucose-trend']?.seuil_urgent_min ?? null,
                critique_min: patientData['glucose-trend']?.seuil_critique_min ?? null,
            },
            valueId: 'value-glucose-trend',
            noteId: 'note-glucose',
            value: patientData['glucose-trend']?.lastValue?.toFixed(1) || 'No Data',
            note: (patientData['glucose-trend']?.unit || 'mmol/L')
        },
        'weight': {
            title: 'Poids',
            type: 'area',
            data: patientData['weight']?.values,
            color: '#10b981',
            minVal: 35,
            maxVal: 110,
            unit: 'kg',
            thresholds: {
                preoccupant: patientData['weight']?.seuil_preoccupant ?? null,
                urgent: patientData['weight']?.seuil_urgent ?? null,
                critique: patientData['weight']?.seuil_critique ?? null,
                preoccupant_min: patientData['weight']?.seuil_preoccupant_min ?? null,
                urgent_min: patientData['weight']?.seuil_urgent_min ?? null,
                critique_min: patientData['weight']?.seuil_critique_min ?? null,
            },
            valueId: 'value-weight',
            noteId: 'note-weight',
            value: patientData['weight']?.lastValue?.toFixed(1) || 'No Data',
            note: (patientData['weight']?.unit || 'kg') + ', dernière mesure'
        },
        'oxygen-saturation': {
            title: 'Saturation en oxygène',
            type: 'area',
            data: patientData['oxygen-saturation']?.values,
            color: '#06b6d4',
            minVal: 72,
            maxVal: 100,
            unit: '%',
            thresholds: {
                preoccupant: patientData['oxygen-saturation']?.seuil_preoccupant ?? null,
                urgent: patientData['oxygen-saturation']?.seuil_urgent ?? null,
                critique: patientData['oxygen-saturation']?.seuil_critique ?? null,
                preoccupant_min: patientData['oxygen-saturation']?.seuil_preoccupant_min ?? null,
                urgent_min: patientData['oxygen-saturation']?.seuil_urgent_min ?? null,
                critique_min: patientData['oxygen-saturation']?.seuil_critique_min ?? null,
            },
            valueId: 'value-oxygen',
            noteId: 'note-oxygen',
            value: patientData['oxygen-saturation']?.lastValue?.toFixed(0) || 'No Data',
            note: (patientData['oxygen-saturation']?.unit || '%') + ', dernière mesure'
        }
    };

	let editMode = false;
	let chartConfig = loadChartConfig();

    /**
     * Configure un canvas pour avoir un bon rendu.
     *
     * Ajuste la taille du canvas selon le devicePixelRatio et retourne le contexte 2D
     * avec la transformation appropriée.
     *
     * @param {HTMLCanvasElement} canvas - Canvas à configurer
     * @returns {{ctx: CanvasRenderingContext2D, width: number, height: number}}
     */
	function setupCanvas(canvas) {
		const rect = canvas.getBoundingClientRect();
		canvas.width = rect.width * DPR;
		canvas.height = rect.height * DPR;
		const ctx = canvas.getContext('2d');
		ctx.setTransform(DPR, 0, 0, DPR, 0, 0);
		return {ctx, width: rect.width, height: rect.height};
	}

    /**
     * Dessine un graphique area simple (sans seuils).
     *
     * Fonctionnalités :
     * - Axes X/Y avec grilles et labels
     * - Courbe lissée avec dégradé sous la ligne
     * - Points interactifs avec highlight au survol
     * - Tooltip affichant la valeur réelle
     * - Adaptation automatique au mode sombre/clair
     *
     * @param {string} canvasId - ID du canvas
     * @param {number[]} data - Valeurs normalisées entre 0 et 1
     * @param {string} color - Couleur principale (hex)
     * @param {number} minVal - Valeur minimale de l'axe Y
     * @param {number} maxVal - Valeur maximale de l'axe Y
     * @param {string} unit - Unité de mesure
     */
	function animateArea(canvasId, data, color, minVal, maxVal, unit) {
		const canvas = document.getElementById(canvasId);
		if (!canvas) return;

		// Détection du mode sombre (via data-theme)
		const isDarkMode = document.documentElement.getAttribute('data-theme') === 'dark';
		const axisColor = isDarkMode ? 'rgba(255,255,255,0.9)' : 'rgba(100,110,120,0.4)';
		const gridColor = isDarkMode ? 'rgba(255,255,255,0.25)' : 'rgba(200,210,220,0.2)';
		const textColor = isDarkMode ? 'rgba(255,255,255,0.95)' : 'rgba(60,70,80,0.8)';
		const textColorLight = isDarkMode ? 'rgba(255,255,255,0.85)' : 'rgba(60,70,80,0.7)';

		// Fonction pour dessiner le graphique (appelée une seule fois)
		const {ctx, width, height} = setupCanvas(canvas);
		const paddingLeft = 50;
		const paddingRight = 20;
		const paddingTop = 20;
		const paddingBottom = 40;
		ctx.clearRect(0, 0, width, height);

		// Axes
		ctx.strokeStyle = axisColor;
		ctx.lineWidth = 2;

		// Axe Y (ordonnées)
		ctx.beginPath();
		ctx.moveTo(paddingLeft, paddingTop);
		ctx.lineTo(paddingLeft, height - paddingBottom);
		ctx.stroke();

		// Axe X (abscisses)
		ctx.beginPath();
		ctx.moveTo(paddingLeft, height - paddingBottom);
		ctx.lineTo(width - paddingRight, height - paddingBottom);
		ctx.stroke();

		// Grille horizontale avec labels sur l'axe Y
		ctx.strokeStyle = gridColor;
		ctx.lineWidth = 1;
		ctx.fillStyle = textColor;
		ctx.font = 'bold 11px sans-serif';
		ctx.textAlign = 'right';

		const numHorizontalLines = 5;
		for (let i = 0; i <= numHorizontalLines; i++) {
			const ratio = i / numHorizontalLines;
			const y = paddingTop + (height - paddingTop - paddingBottom) * ratio;
			const value = maxVal - (maxVal - minVal) * ratio;

			// Ligne de grille
			ctx.beginPath();
			ctx.moveTo(paddingLeft, y);
			ctx.lineTo(width - paddingRight, y);
			ctx.stroke();

			// Label sur l'axe Y
			ctx.fillStyle = textColor;
			ctx.fillText(value.toFixed(1), paddingLeft - 8, y + 4);
		}

		// Labels sur l'axe X (numéros de mesure)
		ctx.textAlign = 'center';
		ctx.font = '10px sans-serif';
		ctx.fillStyle = textColor;
		const numXLabels = Math.min(data.length, 10);
		const xLabelStep = Math.floor(data.length / numXLabels);

		for (let i = 0; i < data.length; i += xLabelStep) {
			const x = paddingLeft + ((width - paddingLeft - paddingRight) / (data.length - 1)) * i;
			const y = height - paddingBottom;

			// Tick mark
			ctx.beginPath();
			ctx.moveTo(x, y);
			ctx.lineTo(x, y + 5);
			ctx.strokeStyle = axisColor;
			ctx.lineWidth = 2;
			ctx.stroke();

			// Label
			ctx.fillStyle = textColor;
			ctx.fillText((i + 1).toString(), x, y + 18);
		}

		// Titre de l'axe X
		ctx.fillStyle = textColorLight;
		ctx.font = '11px sans-serif';
		ctx.fillText('Mesures', width / 2, height - 5);

		// Titre de l'axe Y (vertical)
		ctx.save();
		ctx.translate(15, height / 2);
		ctx.rotate(-Math.PI / 2);
		ctx.textAlign = 'center';
		ctx.fillStyle = textColorLight;
		ctx.fillText(unit || 'Valeur', 0, 0);
		ctx.restore();

		// Dessiner la courbe
		ctx.beginPath();
		const step = (width - paddingLeft - paddingRight) / (data.length - 1);

		data.forEach((v, i) => {
			const x = paddingLeft + i * step;
			const y = paddingTop + (1 - v) * (height - paddingTop - paddingBottom);

			if (i === 0) {
				ctx.moveTo(x, y);
			} else {
				ctx.lineTo(x, y);
			}
		});

		// Ligne de la courbe
		ctx.lineWidth = 2.5;
		ctx.strokeStyle = color;
		ctx.stroke();

		// Remplissage sous la courbe
		const gradient = ctx.createLinearGradient(0, paddingTop, 0, height - paddingBottom);
		gradient.addColorStop(0, color + '30');
		gradient.addColorStop(1, color + '05');

		ctx.lineTo(width - paddingRight, height - paddingBottom);
		ctx.lineTo(paddingLeft, height - paddingBottom);
		ctx.closePath();
		ctx.fillStyle = gradient;
		ctx.fill();

		// Points de données
		data.forEach((v, i) => {
			const x = paddingLeft + i * step;
			const y = paddingTop + (1 - v) * (height - paddingTop - paddingBottom);

			ctx.beginPath();
			ctx.arc(x, y, 3, 0, Math.PI * 2);
			ctx.fillStyle = '#ffffff';
			ctx.fill();
			ctx.strokeStyle = color;
			ctx.lineWidth = 2;
			ctx.stroke();

			ctx.beginPath();
			ctx.arc(x, y, 1.5, 0, Math.PI * 2);
			ctx.fillStyle = color;
			ctx.fill();
		});

		// Stocker les données de dessin pour le redraw
		const chartData = {
			data,
			color,
			minVal,
			maxVal,
			unit,
			step,
			paddingLeft,
			paddingRight,
			paddingTop,
			paddingBottom,
			width,
			height,
			axisColor,
			gridColor,
			textColor,
			textColorLight,
			numHorizontalLines
		};

		// Créer un tooltip HTML
		let tooltip = document.querySelector('.chart-tooltip');
		if (!tooltip) {
			tooltip = document.createElement('div');
			tooltip.className = 'chart-tooltip';
			tooltip.style.cssText = `
				position: fixed;
				background: rgba(0, 0, 0, 0.9);
				color: white;
				padding: 8px 12px;
				border-radius: 6px;
				font-size: 13px;
				font-weight: bold;
				pointer-events: none;
				display: none;
				z-index: 10000;
				box-shadow: 0 2px 8px rgba(0,0,0,0.3);
			`;
			document.body.appendChild(tooltip);
		}

		// Fonction pour redessiner le graphique avec highlight
		function redrawChart(highlightIndex = -1) {
			ctx.clearRect(0, 0, width, height);

			// Axes
			ctx.strokeStyle = chartData.axisColor;
			ctx.lineWidth = 2;
			ctx.beginPath();
			ctx.moveTo(chartData.paddingLeft, chartData.paddingTop);
			ctx.lineTo(chartData.paddingLeft, chartData.height - chartData.paddingBottom);
			ctx.stroke();
			ctx.beginPath();
			ctx.moveTo(chartData.paddingLeft, chartData.height - chartData.paddingBottom);
			ctx.lineTo(chartData.width - chartData.paddingRight, chartData.height - chartData.paddingBottom);
			ctx.stroke();

			// Grille
			ctx.strokeStyle = chartData.gridColor;
			ctx.lineWidth = 1;
			ctx.fillStyle = chartData.textColor;
			ctx.font = 'bold 11px sans-serif';
			ctx.textAlign = 'right';

			for (let i = 0; i <= chartData.numHorizontalLines; i++) {
				const ratio = i / chartData.numHorizontalLines;
				const y = chartData.paddingTop + (chartData.height - chartData.paddingTop - chartData.paddingBottom) * ratio;
				const value = chartData.maxVal - (chartData.maxVal - chartData.minVal) * ratio;

				ctx.beginPath();
				ctx.moveTo(chartData.paddingLeft, y);
				ctx.lineTo(chartData.width - chartData.paddingRight, y);
				ctx.stroke();

				ctx.fillStyle = chartData.textColor;
				ctx.fillText(value.toFixed(1), chartData.paddingLeft - 8, y + 4);
			}

			// Labels X
			ctx.textAlign = 'center';
			ctx.font = '10px sans-serif';
			const numXLabels = Math.min(chartData.data.length, 10);
			const xLabelStep = Math.floor(chartData.data.length / numXLabels);

			for (let i = 0; i < chartData.data.length; i += xLabelStep) {
				const x = chartData.paddingLeft + ((chartData.width - chartData.paddingLeft - chartData.paddingRight) / (chartData.data.length - 1)) * i;
				const y = chartData.height - chartData.paddingBottom;

				ctx.beginPath();
				ctx.moveTo(x, y);
				ctx.lineTo(x, y + 5);
				ctx.strokeStyle = chartData.axisColor;
				ctx.lineWidth = 2;
				ctx.stroke();

				ctx.fillStyle = chartData.textColor;
				ctx.fillText((i + 1).toString(), x, y + 18);
			}

			// Titres axes
			ctx.fillStyle = chartData.textColorLight;
			ctx.font = '11px sans-serif';
			ctx.fillText('Mesures', chartData.width / 2, chartData.height - 5);

			ctx.save();
			ctx.translate(15, chartData.height / 2);
			ctx.rotate(-Math.PI / 2);
			ctx.textAlign = 'center';
			ctx.fillText(chartData.unit || 'Valeur', 0, 0);
			ctx.restore();

			// Courbe
			ctx.beginPath();
			chartData.data.forEach((v, i) => {
				const x = chartData.paddingLeft + i * chartData.step;
				const y = chartData.paddingTop + (1 - v) * (chartData.height - chartData.paddingTop - chartData.paddingBottom);
				if (i === 0) ctx.moveTo(x, y);
				else ctx.lineTo(x, y);
			});
			ctx.lineWidth = 2.5;
			ctx.strokeStyle = chartData.color;
			ctx.stroke();

			// Remplissage
			const gradient = ctx.createLinearGradient(0, chartData.paddingTop, 0, chartData.height - chartData.paddingBottom);
			gradient.addColorStop(0, chartData.color + '30');
			gradient.addColorStop(1, chartData.color + '05');
			ctx.lineTo(chartData.width - chartData.paddingRight, chartData.height - chartData.paddingBottom);
			ctx.lineTo(chartData.paddingLeft, chartData.height - chartData.paddingBottom);
			ctx.closePath();
			ctx.fillStyle = gradient;
			ctx.fill();

			// Points
			chartData.data.forEach((v, i) => {
				const x = chartData.paddingLeft + i * chartData.step;
				const y = chartData.paddingTop + (1 - v) * (chartData.height - chartData.paddingTop - chartData.paddingBottom);
				const isHighlighted = i === highlightIndex;

				ctx.beginPath();
				ctx.arc(x, y, isHighlighted ? 5 : 3, 0, Math.PI * 2);
				ctx.fillStyle = '#ffffff';
				ctx.fill();
				ctx.strokeStyle = chartData.color;
				ctx.lineWidth = 2;
				ctx.stroke();

				ctx.beginPath();
				ctx.arc(x, y, 1.5, 0, Math.PI * 2);
				ctx.fillStyle = chartData.color;
				ctx.fill();
			});
		}

		// Event mousemove
		canvas.addEventListener('mousemove', (e) => {
			const rect = canvas.getBoundingClientRect();
			const mouseX = e.clientX - rect.left;
			const mouseY = e.clientY - rect.top;

			let closestIndex = -1;
			let closestDistance = Infinity;

			// Trouver le point le plus proche sur l'axe X
			chartData.data.forEach((v, i) => {
				const x = chartData.paddingLeft + i * chartData.step;
				const y = chartData.paddingTop + (1 - v) * (chartData.height - chartData.paddingTop - chartData.paddingBottom);
				const distance = Math.sqrt(Math.pow(mouseX - x, 2) + Math.pow(mouseY - y, 2));

				if (distance < 20 && distance < closestDistance) {
					closestDistance = distance;
					closestIndex = i;
				}
			});

			if (closestIndex !== -1) {
				canvas.style.cursor = 'pointer';

				// Redessiner avec highlight
				redrawChart(closestIndex);

				// Afficher tooltip
				const v = chartData.data[closestIndex];
				const realValue = chartData.minVal + v * (chartData.maxVal - chartData.minVal);
				const x = chartData.paddingLeft + closestIndex * chartData.step;
				const y = chartData.paddingTop + (1 - v) * (chartData.height - chartData.paddingTop - chartData.paddingBottom);

				tooltip.textContent = `${realValue.toFixed(1)} ${chartData.unit}`;
				tooltip.style.display = 'block';
				tooltip.style.left = (rect.left + x) + 'px';
				tooltip.style.top = (rect.top + y - 35) + 'px';
			} else {
				canvas.style.cursor = 'default';
				tooltip.style.display = 'none';
				redrawChart();
			}
		});

		canvas.addEventListener('mouseleave', () => {
			canvas.style.cursor = 'default';
			tooltip.style.display = 'none';
			redrawChart();
		});
	}

	// Area avec ligne de seuil (pour température) - Version médicale avec axes
    /**
     * Dessine un graphique area avec seuils d'alerte multiples.
     *
     * Fonctionnalités supplémentaires par rapport à animateArea() :
     * - Zones colorées pour seuils (préoccupant=jaune, urgent=orange, critique=rouge)
     * - Lignes pointillées pour seuils majorants
     * - Lignes pointillées plus espacées pour seuils minorants (4px, 8px gaps)
     * - Points rouges si valeur hors seuils
     * - Labels et axes identiques à animateArea()
     *
     * @param {string} canvasId - ID du canvas
     * @param {number[]} data - Valeurs normalisées entre 0 et 1
     * @param {string} color - Couleur principale (hex)
     * @param {Object} thresholds - Objet avec clés preoccupant, urgent, critique (+ _min)
     * @param {number} minVal - Valeur minimale de l'axe Y
     * @param {number} maxVal - Valeur maximale de l'axe Y
     * @param {string} unit - Unité de mesure
     */
    function animateAreaWithThreshold(canvasId, data, color, thresholds = {}, minVal = 0, maxVal = 1, unit = '') {
        const canvas = document.getElementById(canvasId);
        if (!canvas) return;

        const isDarkMode = document.documentElement.getAttribute('data-theme') === 'dark';
        const axisColor = isDarkMode ? 'rgba(255,255,255,0.9)' : 'rgba(100,110,120,0.4)';
        const gridColor = isDarkMode ? 'rgba(255,255,255,0.25)' : 'rgba(200,210,220,0.2)';
        const textColor = isDarkMode ? 'rgba(255,255,255,0.95)' : 'rgba(60,70,80,0.8)';
        const textColorLight = isDarkMode ? 'rgba(255,255,255,0.85)' : 'rgba(60,70,80,0.7)';

        const {ctx, width, height} = setupCanvas(canvas);
        const paddingLeft = 50, paddingRight = 20, paddingTop = 20, paddingBottom = 40;
        const step = (width - paddingLeft - paddingRight) / (data.length - 1);

        // Couleurs des seuils
        const seuilColors = {
            preoccupant: 'rgba(250,204,21,0.5)',
            urgent: 'rgba(249,115,22,0.5)',
            critique: 'rgba(220,38,38,0.5)'
        };

        // Stocker les données pour le redraw
        const chartData = {
            data,
            color,
            minVal,
            maxVal,
            unit,
            thresholds,
            step,
            paddingLeft,
            paddingRight,
            paddingTop,
            paddingBottom,
            width,
            height,
            axisColor,
            gridColor,
            textColor,
            textColorLight,
            seuilColors,
            numHorizontalLines: 5
        };

        // Créer un tooltip HTML (partagé entre tous les graphiques)
        let tooltip = document.querySelector('.chart-tooltip');
        if (!tooltip) {
            tooltip = document.createElement('div');
            tooltip.className = 'chart-tooltip';
            tooltip.style.cssText = `
            position: fixed;
            background: rgba(0, 0, 0, 0.9);
            color: white;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: bold;
            pointer-events: none;
            display: none;
            z-index: 10000;
            box-shadow: 0 2px 8px rgba(0,0,0,0.3);
        `;
            document.body.appendChild(tooltip);
        }

        // Fonction pour dessiner le graphique complet
        function drawChart(highlightIndex = -1) {
            ctx.clearRect(0, 0, chartData.width, chartData.height);

            // === AXES ===
            ctx.strokeStyle = chartData.axisColor;
            ctx.lineWidth = 2;
            ctx.beginPath();
            ctx.moveTo(chartData.paddingLeft, chartData.paddingTop);
            ctx.lineTo(chartData.paddingLeft, chartData.height - chartData.paddingBottom);
            ctx.stroke();
            ctx.beginPath();
            ctx.moveTo(chartData.paddingLeft, chartData.height - chartData.paddingBottom);
            ctx.lineTo(chartData.width - chartData.paddingRight, chartData.height - chartData.paddingBottom);
            ctx.stroke();

            // === GRILLE HORIZONTALE ===
            ctx.strokeStyle = chartData.gridColor;
            ctx.lineWidth = 1;
            ctx.fillStyle = chartData.textColor;
            ctx.font = 'bold 11px sans-serif';
            ctx.textAlign = 'right';

            for (let i = 0; i <= chartData.numHorizontalLines; i++) {
                const ratio = i / chartData.numHorizontalLines;
                const y = chartData.paddingTop + (chartData.height - chartData.paddingTop - chartData.paddingBottom) * ratio;
                const value = chartData.maxVal - (chartData.maxVal - chartData.minVal) * ratio;

                ctx.beginPath();
                ctx.moveTo(chartData.paddingLeft, y);
                ctx.lineTo(chartData.width - chartData.paddingRight, y);
                ctx.stroke();

                ctx.fillStyle = chartData.textColor;
                ctx.fillText(value.toFixed(1), chartData.paddingLeft - 8, y + 4);
            }

            // === SEUILS MAJORANTS (lignes pleines pointillées) ===
            for (const [key, val] of Object.entries(chartData.thresholds)) {
                if (!key.endsWith('_min') && typeof val === 'number') {
                    const yThr = chartData.paddingTop + (1 - (val - chartData.minVal) / (chartData.maxVal - chartData.minVal)) * (chartData.height - chartData.paddingTop - chartData.paddingBottom);
                    ctx.beginPath();
                    ctx.moveTo(chartData.paddingLeft, yThr);
                    ctx.lineTo(chartData.width - chartData.paddingRight, yThr);
                    ctx.strokeStyle = chartData.seuilColors[key] || '#000';
                    ctx.lineWidth = 2;
                    ctx.setLineDash([8, 4]);
                    ctx.stroke();
                    ctx.setLineDash([]);

                    ctx.fillStyle = chartData.seuilColors[key];
                }
            }

            // === SEUILS MINORANTS (lignes pointillées espacées) ===
            for (const [key, val] of Object.entries(chartData.thresholds)) {
                if (key.endsWith('_min') && typeof val === 'number') {
                    const baseKey = key.replace('_min', '');
                    const yThr = chartData.paddingTop + (1 - (val - chartData.minVal) / (chartData.maxVal - chartData.minVal)) * (chartData.height - chartData.paddingTop - chartData.paddingBottom);
                    ctx.beginPath();
                    ctx.moveTo(chartData.paddingLeft, yThr);
                    ctx.lineTo(chartData.width - chartData.paddingRight, yThr);
                    ctx.strokeStyle = chartData.seuilColors[baseKey] || '#000';
                    ctx.lineWidth = 2;
                    ctx.setLineDash([4, 8]);
                    ctx.stroke();
                    ctx.setLineDash([]);

                    ctx.fillStyle = chartData.seuilColors[baseKey];
                }
            }

            // === LABELS AXE X ===
            ctx.textAlign = 'center';
            ctx.font = '10px sans-serif';
            ctx.fillStyle = chartData.textColor;
            const numXLabels = Math.min(chartData.data.length, 10);
            const xLabelStep = Math.floor(chartData.data.length / numXLabels) || 1;

            for (let i = 0; i < chartData.data.length; i += xLabelStep) {
                const x = chartData.paddingLeft + ((chartData.width - chartData.paddingLeft - chartData.paddingRight) / (chartData.data.length - 1)) * i;
                const y = chartData.height - chartData.paddingBottom;

                ctx.beginPath();
                ctx.moveTo(x, y);
                ctx.lineTo(x, y + 5);
                ctx.strokeStyle = chartData.axisColor;
                ctx.lineWidth = 2;
                ctx.stroke();

                ctx.fillStyle = chartData.textColor;
                ctx.fillText((i + 1).toString(), x, y + 18);
            }

            // === TITRES AXES ===
            ctx.fillStyle = chartData.textColorLight;
            ctx.font = '11px sans-serif';
            ctx.fillText('Mesures', chartData.width / 2, chartData.height - 5);

            ctx.save();
            ctx.translate(15, chartData.height / 2);
            ctx.rotate(-Math.PI / 2);
            ctx.textAlign = 'center';
            ctx.fillText(chartData.unit || 'Valeur', 0, 0);
            ctx.restore();

            // === COURBE PRINCIPALE ===
            ctx.beginPath();
            chartData.data.forEach((v, i) => {
                const x = chartData.paddingLeft + i * chartData.step;
                const y = chartData.paddingTop + (1 - v) * (chartData.height - chartData.paddingTop - chartData.paddingBottom);
                if (i === 0) ctx.moveTo(x, y);
                else ctx.lineTo(x, y);
            });

            ctx.lineWidth = 2.5;
            ctx.strokeStyle = chartData.color;
            ctx.stroke();

            // === REMPLISSAGE DÉGRADÉ ===
            const gradient = ctx.createLinearGradient(0, chartData.paddingTop, 0, chartData.height - chartData.paddingBottom);
            gradient.addColorStop(0, chartData.color + '30');
            gradient.addColorStop(1, chartData.color + '05');
            ctx.lineTo(chartData.width - chartData.paddingRight, chartData.height - chartData.paddingBottom);
            ctx.lineTo(chartData.paddingLeft, chartData.height - chartData.paddingBottom);
            ctx.closePath();
            ctx.fillStyle = gradient;
            ctx.fill();

            // === POINTS ===
            chartData.data.forEach((v, i) => {
                const x = chartData.paddingLeft + i * chartData.step;
                const y = chartData.paddingTop + (1 - v) * (chartData.height - chartData.paddingTop - chartData.paddingBottom);
                const isHighlighted = i === highlightIndex;

                // Convertir v normalisé en valeur réelle
                const valeurReelle = chartData.minVal + v * (chartData.maxVal - chartData.minVal);

                // Déterminer si la valeur dépasse un seuil
                let isOutsideThreshold = false;

                // Vérifier seuils majorants
                for (const [key, val] of Object.entries(chartData.thresholds)) {
                    if (!key.endsWith('_min') && typeof val === 'number' && valeurReelle > val) {
                        isOutsideThreshold = true;
                    }
                }

                // Vérifier seuils minorants
                for (const [key, val] of Object.entries(chartData.thresholds)) {
                    if (key.endsWith('_min') && typeof val === 'number' && valeurReelle < val) {
                        isOutsideThreshold = true;
                    }
                }

                const pointColor = isOutsideThreshold ? '#dc2626' : chartData.color;
                const pointRadius = isHighlighted ? 5 : 3;

                ctx.beginPath();
                ctx.arc(x, y, pointRadius, 0, Math.PI * 2);
                ctx.fillStyle = '#ffffff';
                ctx.fill();
                ctx.strokeStyle = pointColor;
                ctx.lineWidth = 2;
                ctx.stroke();

                ctx.beginPath();
                ctx.arc(x, y, 1.5, 0, Math.PI * 2);
                ctx.fillStyle = pointColor;
                ctx.fill();
            });
        }

        // Dessin initial
        drawChart();

        // === INTERACTIVITÉ (TOOLTIP) ===
        canvas.addEventListener('mousemove', (e) => {
            const rect = canvas.getBoundingClientRect();
            const mouseX = e.clientX - rect.left;
            const mouseY = e.clientY - rect.top;

            let closestIndex = -1;
            let closestDistance = Infinity;

            // Trouver le point le plus proche
            chartData.data.forEach((v, i) => {
                const x = chartData.paddingLeft + i * chartData.step;
                const y = chartData.paddingTop + (1 - v) * (chartData.height - chartData.paddingTop - chartData.paddingBottom);
                const distance = Math.sqrt(Math.pow(mouseX - x, 2) + Math.pow(mouseY - y, 2));

                if (distance < 20 && distance < closestDistance) {
                    closestDistance = distance;
                    closestIndex = i;
                }
            });

            if (closestIndex !== -1) {
                canvas.style.cursor = 'pointer';

                // Redessiner avec highlight
                drawChart(closestIndex);

                // Afficher tooltip
                const v = chartData.data[closestIndex];
                const realValue = chartData.minVal + v * (chartData.maxVal - chartData.minVal);
                const x = chartData.paddingLeft + closestIndex * chartData.step;
                const y = chartData.paddingTop + (1 - v) * (chartData.height - chartData.paddingTop - chartData.paddingBottom);

                tooltip.textContent = `${realValue.toFixed(1)} ${chartData.unit}`;
                tooltip.style.display = 'block';
                tooltip.style.left = (rect.left + x) + 'px';
                tooltip.style.top = (rect.top + y - 35) + 'px';
            } else {
                canvas.style.cursor = 'default';
                tooltip.style.display = 'none';
                drawChart();
            }
        });

        canvas.addEventListener('mouseleave', () => {
            canvas.style.cursor = 'default';
            tooltip.style.display = 'none';
            drawChart();
        });
    }    // Bar chart - Version statique
	function animateBarChart(canvasId, data, color) {
		const canvas = document.getElementById(canvasId); if (!canvas) return;
		function draw() {
			const {ctx, width, height} = setupCanvas(canvas);
			ctx.clearRect(0, 0, width, height);
			const padding = 12; const available = width - padding * 2; const barW = available / data.length * 0.7; const gap = (available - barW * data.length) / Math.max(1, data.length - 1);
			data.forEach((v, i) => {
				const x = padding + i * (barW + gap);
				const h = (height - padding * 2) * v;
				ctx.fillStyle = color;
				roundRect(ctx, x, height - padding - h, barW, h, 4);
			});
		}
		draw();
	}

	// Dual-line chart (pour systolique/diastolique) - Version statique
	function animateDualLineChart(canvasId, seriesA, seriesB, colorA, colorB) {
		const canvas = document.getElementById(canvasId); if (!canvas) return;
		function draw() {
			const {ctx, width, height} = setupCanvas(canvas);
			const padding = 12;
			ctx.clearRect(0, 0, width, height);

			// grid
			ctx.strokeStyle = 'rgba(60,80,100,0.06)'; ctx.lineWidth = 1;
			for (let i = 0; i < 4; i++) {
				const y = padding + (height - padding * 2) * (i / 3);
				ctx.beginPath(); ctx.moveTo(padding, y); ctx.lineTo(width - padding, y); ctx.stroke();
			}

			const step = (width - padding * 2) / (Math.max(seriesA.length, seriesB.length) - 1);

			// draw series A
			ctx.beginPath(); ctx.lineWidth = 2.4; ctx.strokeStyle = colorA;
			seriesA.forEach((v, i) => {
				const x = padding + i * step; const y = padding + (1 - v) * (height - padding * 2);
				if (i === 0) ctx.moveTo(x, y); else ctx.lineTo(x, y);
			}); ctx.stroke();

			// draw series B
			ctx.beginPath(); ctx.lineWidth = 2; ctx.strokeStyle = colorB;
			seriesB.forEach((v, i) => {
				const x = padding + i * step; const y = padding + (1 - v) * (height - padding * 2);
				if (i === 0) ctx.moveTo(x, y); else ctx.lineTo(x, y);
			}); ctx.stroke();

			// small legend dots
			ctx.fillStyle = colorA; ctx.fillRect(width - padding - 90, padding, 10, 10); ctx.fillStyle = '#081e2b'; ctx.font = '12px sans-serif'; ctx.fillText('Systolique', width - padding - 72, padding + 9);
			ctx.fillStyle = colorB; ctx.fillRect(width - padding - 90, padding + 16, 10, 10); ctx.fillStyle = '#081e2b'; ctx.fillText('Diastolique', width - padding - 72, padding + 25);
		}
		draw();
	}

	// aide : rectangle arrondi (helper)
	function roundRect(ctx, x, y, w, h, r) {
		const radius = r || 0;
		ctx.beginPath();
		ctx.moveTo(x + radius, y);
		ctx.arcTo(x + w, y, x + w, y + h, radius);
		ctx.arcTo(x + w, y + h, x, y + h, radius);
		ctx.arcTo(x, y + h, x, y, radius);
		ctx.arcTo(x, y, x + w, y, radius);
		ctx.closePath();
		ctx.fill();
	}

    /**
     * Charge la configuration des graphiques depuis localStorage.
     *
     * Ajoute automatiquement les nouveaux graphiques définis dans CHART_DEFINITIONS.
     * Supprime les graphiques obsolètes non présents dans CHART_DEFINITIONS.
     *
     * @returns {{visible: string[], sizes: Object.<string, number>}}
     */
	function loadChartConfig() {
		// Liste complète de tous les graphiques disponibles
		const allCharts = ['blood-pressure', 'heart-rate', 'respiration', 'temperature', 'glucose-trend', 'weight', 'oxygen-saturation'];

		const saved = localStorage.getItem('dashboardChartConfig');
		if (saved) {
			try {
				const config = JSON.parse(saved);

				// Vérifier que tous les graphiques définis existent dans la config
				// Ajouter les graphiques manquants
				allCharts.forEach(chartId => {
					if (!config.visible.includes(chartId)) {
						config.visible.push(chartId);
					}
				});

				// Supprimer les graphiques qui n'existent plus dans CHART_DEFINITIONS
				config.visible = config.visible.filter(chartId => allCharts.includes(chartId));

				return config;
			} catch (e) {
				console.error('Failed to parse chart config', e);
			}
		}
		// Default config - tous les graphiques visibles
		return {
			visible: allCharts,
			sizes: {}
		};
	}

    /**
     * Envoie une action utilisateur au serveur pour logging.
     *
     * @param {string} action - Type d'action ('ajouter', 'supprimer', 'réduire', 'agrandir')
     * @param {string|null} chartId - ID du graphique concerné (pour récupérer id_mesure)
     */
	function logGraphiqueAction(action, chartId = null) {
		// Récupérer pt_id depuis window.activePatient
		const ptId = window.activePatient?.pt_id || null;
		
		// Récupérer id_mesure depuis les données du graphique si chartId est fourni
		let idMesure = null;
		if (chartId && window.patientChartData && window.patientChartData[chartId]) {
			idMesure = window.patientChartData[chartId].id_mesure || null;
		}

		fetch('/api/log-graph-action', {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json'
			},
			body: JSON.stringify({ 
				action: action,
				ptId: ptId,
				idMesure: idMesure
			})
		})
		.then(response => {
			if (!response.ok) {
				return response.json().then(data => {
					throw new Error(data.error || 'Erreur serveur');
				});
			}
			return response.json();
		})
		.then(data => {
			// Action loggée avec succès
		})
		.catch(err => {
			// Erreur lors du log de l'action - ignorer silencieusement
		});
	}


    //Sauvegarde la configuration dans localStorage.
	function saveChartConfig() {
		localStorage.setItem('dashboardChartConfig', JSON.stringify(chartConfig));
	}


    //Applique la configuration sauvegardée (visibilité + taille des graphiques).
	function applyChartConfig() {
		const grid = document.getElementById('dashboardGrid');
		if (!grid) return;

		// Parcourir tous les graphiques HTML
		document.querySelectorAll('.chart-card').forEach(card => {
			const chartId = card.getAttribute('data-chart-id');

			// Vérifier si le graphique existe dans CHART_DEFINITIONS
			if (!CHART_DEFINITIONS[chartId]) {
				console.warn(`Graphique ${chartId} non défini dans CHART_DEFINITIONS`);
				return;
			}

			// Vérifier si le graphique est dans la liste visible
			if (chartConfig.visible.includes(chartId)) {
				// Afficher le graphique
				card.style.display = 'block';

				// Apply column span (default 6 out of 12)
				const colSpan = chartConfig.sizes[chartId] || 6;
				card.setAttribute('data-col-span', colSpan);
			} else {
				// Masquer le graphique
				card.style.display = 'none';
			}
		});
	}

	// Toggle edit mode
	const toggleEditBtn = document.getElementById('toggleEditMode');
	const addChartPanel = document.getElementById('addChartPanel');

	if (toggleEditBtn) {
		toggleEditBtn.addEventListener('click', () => {
			editMode = !editMode;
			toggleEditBtn.classList.toggle('active', editMode);

			if (editMode) {
				toggleEditBtn.innerHTML = '<span class="icon-edit">✓</span><span class="text-edit">Terminer</span>';
			} else {
				toggleEditBtn.innerHTML = '<span class="icon-edit">✎</span><span class="text-edit">Modifier</span>';
			}

			// Show/hide resize handles, delete buttons and add edit-mode class
			document.querySelectorAll('.chart-card').forEach(card => {
				const handle = card.querySelector('.resize-handle');
				if (handle) {
					handle.style.display = editMode ? 'block' : 'none';
				}

				// Add/remove delete button
				let deleteBtn = card.querySelector('.btn-remove');
				if (editMode) {
					if (!deleteBtn) {
						deleteBtn = document.createElement('button');
						deleteBtn.className = 'btn-remove';
						deleteBtn.innerHTML = '×';
						deleteBtn.title = 'Supprimer ce graphique';

						const chartId = card.getAttribute('data-chart-id');
						deleteBtn.addEventListener('click', (e) => {
							e.stopPropagation();
							removeChart(chartId);
						});

						card.appendChild(deleteBtn);
					}
					deleteBtn.style.display = 'block';
				} else {
					if (deleteBtn) {
						deleteBtn.style.display = 'none';
					}
				}

				if (editMode) {
					card.classList.add('edit-mode');
					setupChartDrag(card);
				} else {
					card.classList.remove('edit-mode');
					card.draggable = false;
				}
			});

			// Show/hide add panel
			if (addChartPanel) {
				addChartPanel.style.display = editMode ? 'block' : 'none';
				if (editMode) {
					updateAvailableCharts();
				}
			}
		});
	}

	// Variables pour l'auto-scroll lors du drag
	let autoScrollInterval = null;

    /**
     * Configure le drag & drop
     *
     * Gère :
     * - dragstart : Ajoute classe 'dragging', bloque redraws
     * - dragend : Retire classe, débloque redraws après 150ms
     * - dragover : Calcule position drop (before/after/left/right)
     * - drop : Réorganise chartConfig.visible et DOM
     *
     * @param {HTMLElement} card - Carte graphique à rendre draggable
     */
	function setupChartDrag(card) {
		card.draggable = true;
		card.style.cursor = 'grab';

		card.addEventListener('dragstart', (e) => {
			const chartId = card.getAttribute('data-chart-id');
			e.dataTransfer.effectAllowed = 'move';
			e.dataTransfer.setData('text/plain', chartId);
			e.dataTransfer.setData('source', 'chart-card');
			card.classList.add('dragging');
			card.style.cursor = 'grabbing';

			// Bloquer les redraws pendant le drag
			isReordering = true;

			// Désactiver les animations pendant le drag
			const dashboardGrid = document.getElementById('dashboardGrid');
			if (dashboardGrid) {
				dashboardGrid.classList.add('reordering');
			}

			// Ajouter une classe pour indiquer le mode drag sur toutes les cartes
			document.querySelectorAll('.chart-card').forEach(c => {
				if (c !== card) c.classList.add('drop-target-available');
			});

			startAutoScroll();
		});

		card.addEventListener('dragend', (e) => {
			card.classList.remove('dragging');
			card.style.cursor = 'grab';

			// Débloquer les redraws après un court délai
			setTimeout(() => {
				isReordering = false;
			}, 150);

			// Réactiver les animations après un court délai
			const dashboardGrid = document.getElementById('dashboardGrid');
			if (dashboardGrid) {
				setTimeout(() => {
					dashboardGrid.classList.remove('reordering');
				}, 100);
			}

			// Retirer la classe de mode drag
			document.querySelectorAll('.chart-card').forEach(c => {
				c.classList.remove('drop-target-available', 'drop-before', 'drop-after', 'drop-left', 'drop-right');
			});

			stopAutoScroll();
		});


		card.addEventListener('dragover', (e) => {
			if (!editMode) return;
			const dragging = document.querySelector('.dragging');
			if (!dragging || dragging === card) return;

			e.preventDefault();
			e.dataTransfer.dropEffect = 'move';

			// Déterminer la direction du drop basé sur la position de la souris
			const rect = card.getBoundingClientRect();
			const midY = rect.top + rect.height / 2;
			const midX = rect.left + rect.width / 2;

			// Nettoyer toutes les classes de drop
			card.classList.remove('drop-before', 'drop-after', 'drop-left', 'drop-right');

			// Calculer la distance relative aux bords
			const distanceTop = e.clientY - rect.top;
			const distanceBottom = rect.bottom - e.clientY;
			const distanceLeft = e.clientX - rect.left;
			const distanceRight = rect.right - e.clientX;

			// Trouver le bord le plus proche
			const minDistance = Math.min(distanceTop, distanceBottom, distanceLeft, distanceRight);

			if (minDistance === distanceTop) {
				card.classList.add('drop-before');
			} else if (minDistance === distanceBottom) {
				card.classList.add('drop-after');
			} else if (minDistance === distanceLeft) {
				card.classList.add('drop-left');
			} else {
				card.classList.add('drop-right');
			}
		});

		card.addEventListener('dragleave', (e) => {
			// Retirer les indicateurs seulement si on sort vraiment de la carte
			if (!card.contains(e.relatedTarget)) {
				card.classList.remove('drop-before', 'drop-after', 'drop-left', 'drop-right');
			}
		});

		card.addEventListener('drop', (e) => {
			e.preventDefault();
			e.stopPropagation();

			const dropClasses = ['drop-before', 'drop-after', 'drop-left', 'drop-right'];
			const activeDropClass = dropClasses.find(cls => card.classList.contains(cls));

			card.classList.remove(...dropClasses);

			const source = e.dataTransfer.getData('source');
			if (source !== 'chart-card') return;

			const draggedId = e.dataTransfer.getData('text/plain');
			const targetId = card.getAttribute('data-chart-id');

			if (draggedId === targetId) return;

			// Reorder dans le tableau visible
			const draggedIndex = chartConfig.visible.indexOf(draggedId);
			const targetIndex = chartConfig.visible.indexOf(targetId);

			if (draggedIndex === -1 || targetIndex === -1) return;

			// Retirer de l'ancienne position
			chartConfig.visible.splice(draggedIndex, 1);

			// Calculer la nouvelle position en fonction de la direction du drop
			const newTargetIndex = chartConfig.visible.indexOf(targetId);

			// Pour tous les cas (gauche, droite, haut, bas), on utilise la même logique
			// drop-before/drop-left = insérer AVANT la carte cible
			// drop-after/drop-right = insérer APRÈS la carte cible
			if (activeDropClass === 'drop-before' || activeDropClass === 'drop-left') {
				// Insérer avant la carte cible
				chartConfig.visible.splice(newTargetIndex, 0, draggedId);
			} else if (activeDropClass === 'drop-after' || activeDropClass === 'drop-right') {
				// Insérer après la carte cible
				chartConfig.visible.splice(newTargetIndex + 1, 0, draggedId);
			} else {
				// Par défaut, insérer après
				chartConfig.visible.splice(newTargetIndex + 1, 0, draggedId);
			}

			saveChartConfig();
			reorderChartsInDOMSmooth();
		});
	}

    /**
     * Réorganise les cartes dans le DOM.
     *
     * Méthode sans rechargement : utilise insertBefore pour éviter
     * les disparitions de canvas pendant la réorganisation.
     */
	function reorderChartsInDOMSmooth() {
		const dashboardGrid = document.getElementById('dashboardGrid');
		if (!dashboardGrid) return;

		// Récupérer toutes les cartes visibles
		const allCards = Array.from(document.querySelectorAll('.chart-card'));

		// Construire l'ordre souhaité basé sur chartConfig.visible
		const orderedCards = [];
		chartConfig.visible.forEach(chartId => {
			const card = allCards.find(c => c.getAttribute('data-chart-id') === chartId);
			if (card) orderedCards.push(card);
		});

		// Ajouter les cartes cachées à la fin
		allCards.forEach(card => {
			const cardId = card.getAttribute('data-chart-id');
			if (!chartConfig.visible.includes(cardId)) {
				orderedCards.push(card);
			}
		});

		// Réorganiser en utilisant insertBefore pour éviter les disparitions
		orderedCards.forEach((card, index) => {
			const currentIndex = Array.from(dashboardGrid.children).indexOf(card);
			if (currentIndex !== index) {
				// Insérer la carte à la bonne position
				if (index >= dashboardGrid.children.length) {
					dashboardGrid.appendChild(card);
				} else {
					dashboardGrid.insertBefore(card, dashboardGrid.children[index]);
				}
			}
		});
	}


	function reorderChartsInDOM() {
		const dashboardGrid = document.getElementById('dashboardGrid');
		if (!dashboardGrid) return;

		// Get all chart cards
		const cards = Array.from(document.querySelectorAll('.chart-card'));

		// Sort cards based on their position in chartConfig.visible
		cards.sort((a, b) => {
			const aId = a.getAttribute('data-chart-id');
			const bId = b.getAttribute('data-chart-id');
			const aIndex = chartConfig.visible.indexOf(aId);
			const bIndex = chartConfig.visible.indexOf(bId);

			// Hidden charts go to the end
			if (aIndex === -1) return 1;
			if (bIndex === -1) return -1;

			return aIndex - bIndex;
		});

		// Reappend cards in the new order
		cards.forEach(card => {
			dashboardGrid.appendChild(card);
		});
	}

	// Auto-scroll functionality when dragging near edges
	let lastMouseY = 0;

	function startAutoScroll() {
		// Track mouse position during drag
		document.addEventListener('dragover', handleDragScroll);
	}

	function stopAutoScroll() {
		document.removeEventListener('dragover', handleDragScroll);
		if (autoScrollInterval) {
			clearInterval(autoScrollInterval);
			autoScrollInterval = null;
		}
	}

	function handleDragScroll(e) {
		lastMouseY = e.clientY;
		const scrollThreshold = 100; // pixels from edge to trigger scroll
		const scrollSpeed = 15; // pixels per interval
		const viewportHeight = window.innerHeight;

		// Clear existing interval
		if (autoScrollInterval) {
			clearInterval(autoScrollInterval);
			autoScrollInterval = null;
		}

		// Scroll up if near top
		if (e.clientY < scrollThreshold) {
			autoScrollInterval = setInterval(() => {
				window.scrollBy({
					top: -scrollSpeed,
					behavior: 'auto'
				});
			}, 16); // ~60fps
		}
		// Scroll down if near bottom
		else if (e.clientY > viewportHeight - scrollThreshold) {
			autoScrollInterval = setInterval(() => {
				window.scrollBy({
					top: scrollSpeed,
					behavior: 'auto'
				});
			}, 16); // ~60fps
		}
	}

    /**
     * Met à jour le panneau des graphiques disponibles à ajouter.
     *
     * Affiche uniquement les graphiques non visibles, avec drag & drop activé.
     */
	function updateAvailableCharts() {
		const container = document.getElementById('availableCharts');
		if (!container) return;

		container.innerHTML = '';

		Object.keys(CHART_DEFINITIONS).forEach(chartId => {
			const isVisible = chartConfig.visible.includes(chartId);
			const option = document.createElement('div');
			option.className = 'chart-option' + (isVisible ? ' disabled' : '');
			option.textContent = CHART_DEFINITIONS[chartId].title;
			option.setAttribute('data-chart-id', chartId);

			if (!isVisible) {
				// Drag & Drop functionality
				option.draggable = true;
				option.style.cursor = 'grab';

				option.addEventListener('dragstart', (e) => {
					e.dataTransfer.effectAllowed = 'move';
					e.dataTransfer.setData('text/plain', chartId);
					e.dataTransfer.setData('source', 'chart-option');
					option.style.opacity = '0.5';
					option.style.cursor = 'grabbing';
				});

				option.addEventListener('dragend', (e) => {
					option.style.opacity = '1';
					option.style.cursor = 'grab';
				});

				// Keep click functionality as fallback
				option.addEventListener('click', () => {
					addChart(chartId);
				});
			}

			container.appendChild(option);
		});
	}

    /**
     * Ajoute un graphique au dashboard.
     *
     * Actions :
     * 1. Log de l'action 'ajouter'
     * 2. Ajout à chartConfig.visible
     * 3. Sauvegarde + affichage + réorganisation
     * 4. Initialisation du rendu
     * 5. Setup drag si mode édition actif
     *
     * @param {string} chartId - ID du graphique à ajouter
     */
	function addChart(chartId) {
		if (chartConfig.visible.includes(chartId)) return;

		logGraphiqueAction('ajouter', chartId); // Log l'ajout/restauration du graphique
		chartConfig.visible.push(chartId);
		saveChartConfig();

		// Show the card
		const card = document.querySelector(`[data-chart-id="${chartId}"]`);
		if (card) {
			card.style.display = 'block';
			applyChartConfig();
			reorderChartsInDOM();
			initializeChart(chartId);

			// Setup drag for the newly added chart if in edit mode
			if (editMode) {
				setupChartDrag(card);

				// Add delete button if not already present
				let deleteBtn = card.querySelector('.btn-remove');
				if (!deleteBtn) {
					deleteBtn = document.createElement('button');
					deleteBtn.className = 'btn-remove';
					deleteBtn.innerHTML = '×';
					deleteBtn.title = 'Supprimer ce graphique';

					deleteBtn.addEventListener('click', (e) => {
						e.stopPropagation();
						removeChart(chartId);
					});

					card.appendChild(deleteBtn);
				}
				deleteBtn.style.display = 'block';
			}
		}

		updateAvailableCharts();
	}

    /**
     * Supprime un graphique du dashboard (masquage, pas suppression DOM).
     *
     * Actions :
     * 1. Log de l'action 'supprimer'
     * 2. Retrait de chartConfig.visible
     * 3. Sauvegarde + application config
     *
     * @param {string} chartId - ID du graphique à supprimer
     */
	function removeChart(chartId) {
		const index = chartConfig.visible.indexOf(chartId);
		if (index > -1) {
			logGraphiqueAction('supprimer', chartId); // Log la suppression
			chartConfig.visible.splice(index, 1);
			saveChartConfig();
			applyChartConfig();
			updateAvailableCharts();
		}
	}

    /**
     * Redimensionne un graphique (3-12 colonnes).
     *
     * Déclenche un redraw complet après 50ms pour éviter les bugs.
     *
     * @param {string} chartId - ID du graphique
     * @param {number} colSpan - Nombre de colonnes (3-12)
     */
	function resizeChart(chartId, colSpan) {
		// Clamp between 3 and 12 columns
		colSpan = Math.max(3, Math.min(12, colSpan));
		chartConfig.sizes[chartId] = colSpan;

		const card = document.querySelector(`[data-chart-id="${chartId}"]`);
		if (card) {
			card.setAttribute('data-col-span', colSpan);
		}

		saveChartConfig();
		setTimeout(() => {
			resizeAllCanvases();
			redrawAllCharts();
		}, 50);
	}

    /**
     * Configure les handles de redimensionnement pour tout les graphiques.
     *
     * Drag horizontal : calcule le deltaX en colonnes et applique en temps réel.
     * Affiche un indicateur de taille pendant le drag.
     * Log l'action 'agrandir' ou 'réduire' au mouseup selon la direction.
     */
	function setupResizeHandles() {
		document.querySelectorAll('.chart-card').forEach(card => {
			const handle = card.querySelector('.resize-handle');
			if (!handle) return;

			const chartId = card.getAttribute('data-chart-id');
			let isResizing = false;
			let startX = 0;
			let startColSpan = 6;

			handle.addEventListener('mousedown', (e) => {
				if (!editMode) return;

				isResizing = true;
				startX = e.clientX;
				startColSpan = parseInt(card.getAttribute('data-col-span')) || 6;

				// Disable transitions during resize
				card.classList.add('resizing');

				e.preventDefault();
				e.stopPropagation();

				// Change cursor for whole document
				document.body.style.cursor = 'ew-resize';
			});

			const handleMouseMove = (e) => {
				if (!isResizing) return;

				// Calculate how much we've moved
				const grid = document.getElementById('dashboardGrid');
				const gridRect = grid.getBoundingClientRect();
				const gridWidth = gridRect.width;

				// One column width in pixels (including gap)
				const colWidth = (gridWidth + 18) / 12;

				const deltaX = e.clientX - startX;
				const colChange = Math.round(deltaX / colWidth);

				// Calculate new column span
				let newColSpan = startColSpan + colChange;
				newColSpan = Math.max(3, Math.min(12, newColSpan));

				// Apply immediately for smooth feedback
				card.setAttribute('data-col-span', newColSpan);

				// Show size indicator
				showResizeIndicator(newColSpan);

				e.preventDefault();
			};

			const handleMouseUp = () => {
				if (!isResizing) return;

				isResizing = false;
				document.body.style.cursor = '';

				// Re-enable transitions
				card.classList.remove('resizing');

				// Hide size indicator
				hideResizeIndicator();

				// Save the final size
				const finalColSpan = parseInt(card.getAttribute('data-col-span')) || 6;
				
				// Log l'action selon si on a agrandi ou réduit
				if (finalColSpan > startColSpan) {
					logGraphiqueAction('agrandir', chartId);
				} else if (finalColSpan < startColSpan) {
					logGraphiqueAction('réduire', chartId);
				}
				
				chartConfig.sizes[chartId] = finalColSpan;
				saveChartConfig();

				// Update canvas sizes AND redraw charts
				setTimeout(() => {
					resizeAllCanvases();
					redrawAllCharts();
				}, 100);
			};

			document.addEventListener('mousemove', handleMouseMove);
			document.addEventListener('mouseup', handleMouseUp);
		});
	}

    /**
     * Initialise le rendu d'un graphique.
     *
     * Choisit automatiquement entre animateArea() et animateAreaWithThreshold()
     * selon la présence de seuils non-null dans la définition.
     *
     * @param {string} chartId - ID du graphique à initialiser
     */
    function initializeChart(chartId) {
        const def = CHART_DEFINITIONS[chartId];
        if (!def) {
            console.error(`Graphique ${chartId} non trouvé dans CHART_DEFINITIONS`);
            return;
        }

        // Ne dessiner que si les données existent
        if (!def.data || !Array.isArray(def.data) || def.data.length === 0) {
            console.warn(`Pas de données pour le graphique ${chartId} - graphique masqué`);
            return;
        }

        const canvasId = 'chart-' + chartId;
        const canvas = document.getElementById(canvasId);
        if (!canvas) {
            console.warn(`Canvas ${canvasId} non trouvé dans le DOM`);
            return;
        }

        if (def.type === 'area') {
            if (def.thresholds && Object.values(def.thresholds).some(v => v !== null)) {
                // Utiliser animateAreaWithThreshold si au moins un seuil existe
                animateAreaWithThreshold(
                    canvasId,
                    def.data,
                    def.color,
                    def.thresholds,
                    def.minVal,
                    def.maxVal,
                    def.unit
                );
            } else {
                // Sinon utiliser le graphique simple sans seuils
                animateArea(
                    canvasId,
                    def.data,
                    def.color,
                    def.minVal,
                    def.maxVal,
                    def.unit
                );
            }
        }
    }

    /**
     * Affiche un indicateur temporaire lors du redimensionnement.
     *
     * Format : "50% (6/12)" en overlay fixe au centre de l'écran.
     *
     * @param {number} colSpan - Nombre de colonnes actuel
     */
	let resizeIndicator = null;

	function showResizeIndicator(colSpan) {
		if (!resizeIndicator) {
			resizeIndicator = document.createElement('div');
			resizeIndicator.className = 'resize-indicator';
			document.body.appendChild(resizeIndicator);
		}

		const percentage = Math.round((colSpan / 12) * 100);
		resizeIndicator.textContent = `${percentage}% (${colSpan}/12)`;
		resizeIndicator.style.display = 'block';
	}

    // Masque l'indicateur de redimensionnement.
	function hideResizeIndicator() {
		if (resizeIndicator) {
			resizeIndicator.style.display = 'none';
		}
	}

	// Setup resize handles
	setupResizeHandles();

    // Setup drag & drop zone pour le dashboard grid
	const dashboardGrid = document.getElementById('dashboardGrid');
	if (dashboardGrid) {
		dashboardGrid.addEventListener('dragover', (e) => {
			if (!editMode) return;
			e.preventDefault();
			e.dataTransfer.dropEffect = 'move';
			dashboardGrid.classList.add('drag-over');
		});

		dashboardGrid.addEventListener('dragleave', (e) => {
			if (e.target === dashboardGrid) {
				dashboardGrid.classList.remove('drag-over');
			}
		});

		dashboardGrid.addEventListener('drop', (e) => {
			e.preventDefault();
			dashboardGrid.classList.remove('drag-over');

			const chartId = e.dataTransfer.getData('text/plain');
			const source = e.dataTransfer.getData('source');

			// Only add if it's from chart-option panel (not from moving within grid)
			if (source === 'chart-option' && chartId && !chartConfig.visible.includes(chartId)) {
				addChart(chartId);
			}
		});
	}

    // Setup drop zone pour supprimer des graphiques (drag vers panneau)
	if (addChartPanel) {
		addChartPanel.addEventListener('dragover', (e) => {
			if (!editMode) return;
			const source = e.dataTransfer.types.includes('source');
			if (source) {
				e.preventDefault();
				e.dataTransfer.dropEffect = 'move';
				addChartPanel.classList.add('delete-zone-active');
			}
		});

		addChartPanel.addEventListener('dragleave', (e) => {
			if (e.target === addChartPanel || !addChartPanel.contains(e.relatedTarget)) {
				addChartPanel.classList.remove('delete-zone-active');
			}
		});

		addChartPanel.addEventListener('drop', (e) => {
			e.preventDefault();
			addChartPanel.classList.remove('delete-zone-active');

			const chartId = e.dataTransfer.getData('text/plain');
			const source = e.dataTransfer.getData('source');

			// Only delete if it's from a chart card
			if (source === 'chart-card' && chartId && chartConfig.visible.includes(chartId)) {
				removeChart(chartId);
			}
		});
	}

	// Populate sample numeric values under each chart
	Object.keys(CHART_DEFINITIONS).forEach(chartId => {
		const def = CHART_DEFINITIONS[chartId];
		const valueEl = document.getElementById(def.valueId);
		const noteEl = document.getElementById(def.noteId);
		if (valueEl) valueEl.textContent = def.value;
		if (noteEl) noteEl.textContent = def.note;
	});

	// Redimensionne tous les canvas pour obtenir une bonne résolution.
	function resizeAllCanvases() {
		const canvases = document.querySelectorAll('.dashboard-grid canvas');
		canvases.forEach(canvas => {
			const rect = canvas.getBoundingClientRect();
			canvas.width = rect.width * DPR;
			canvas.height = rect.height * DPR;
			const ctx = canvas.getContext('2d');
			ctx.setTransform(DPR, 0, 0, DPR, 0, 0);
		});
	}

    /**
     * Helper de debounce pour limiter la fréquence d'appel d'une fonction.
     *
     * @param {Function} fn - Fonction à debouncer
     * @param {number} wait - Délai en millisecondes
     * @returns {Function} Fonction debouncée
     */
	function debounce(fn, wait) {
		let t = null;
		return (...args) => {
			clearTimeout(t);
			t = setTimeout(() => fn(...args), wait);
		};
	}

	// Apply saved configuration
	applyChartConfig();

	// Reorder charts in DOM based on saved order
	reorderChartsInDOM();

	// initial resize
	resizeAllCanvases();

	// Fonction pour redessiner tous les graphiques
	let isReordering = false;

	function redrawAllCharts() {
		// Ne pas redessiner pendant le drag/drop
		if (isReordering) return;

		document.querySelectorAll('.chart-card').forEach(card => {
			const chartId = card.getAttribute('data-chart-id');
			if (chartId && CHART_DEFINITIONS[chartId]) {
				initializeChart(chartId);
			}
		});
	}

	// start animations for all charts present in the DOM
	redrawAllCharts();

	// resize handler (debounced) to keep canvases crisp when viewport changes
	window.addEventListener('resize', debounce(() => {
		resizeAllCanvases();
		redrawAllCharts();
	}, 150));

	// Écouter les changements de mode sombre pour redessiner les graphiques
	const observer = new MutationObserver((mutations) => {
		mutations.forEach((mutation) => {
			if (mutation.attributeName === 'data-theme') {
				const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
				console.log('Mode sombre changé:', isDark);
				// Redessiner tous les graphiques avec les nouvelles couleurs
				setTimeout(() => redrawAllCharts(), 50);
			}
		});
	});

	observer.observe(document.documentElement, {
		attributes: true,
		attributeFilter: ['data-theme']
	});

	// Écouter aussi l'événement custom dispatché par dark-mode.js
	window.addEventListener('themechange', (e) => {
		console.log('Événement themechange reçu:', e.detail.theme);
		setTimeout(() => redrawAllCharts(), 50);
	});
});

