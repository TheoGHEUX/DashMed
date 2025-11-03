// Lightweight placeholder charts for the dashboard (multiple clinical types)
// Pour réinitialiser la configuration et afficher tous les graphiques :
// Ouvrez la console (F12) et tapez : localStorage.removeItem('dashboardChartConfig'); location.reload();
// Ou ajoutez ?reset=1 à l'URL du dashboard

document.addEventListener('DOMContentLoaded', () => {
	const DPR = window.devicePixelRatio || 1;
	
	// Reset config if requested via URL parameter
	const urlParams = new URLSearchParams(window.location.search);
	if (urlParams.get('reset') === '1') {
		localStorage.removeItem('dashboardChartConfig');
		console.log('Configuration réinitialisée');
		// Remove the reset parameter from URL
		window.history.replaceState({}, '', window.location.pathname);
	}
	
	// Chart configuration management
	const CHART_DEFINITIONS = {
		'blood-pressure': {
			title: 'Tendance de la tension (mmHg)',
			type: 'dual',
			// Systolique (120-130 mmHg) et Diastolique (70-80 mmHg) - valeurs normalisées entre 0 et 1
			dataA: [0.68,0.70,0.72,0.71,0.73,0.72,0.71,0.74,0.75,0.73], // Systolique
			dataB: [0.48,0.50,0.52,0.51,0.53,0.52,0.50,0.52,0.54,0.52], // Diastolique
			colorA: '#ef4444',
			colorB: '#0b6e4f',
			valueId: 'value-bp',
			noteId: 'note-bp',
			value: '122/78',
			note: 'mmHg, dernière mesure'
		},
		'heart-rate': {
			title: 'Fréquence cardiaque',
			type: 'area',
			// 60-80 BPM (repos) - variations normales
			data: [0.48,0.52,0.50,0.55,0.58,0.54,0.56,0.60,0.55,0.53],
			color: '#be185d',
			valueId: 'value-hr',
			noteId: 'note-hr',
			value: '72',
			note: 'BPM, dernière mesure'
		},
		'respiration': {
			title: 'Fréquence respiratoire',
			type: 'area',
			// 12-20 resp/min (normal adulte) - stable avec légères variations
			data: [0.40,0.42,0.44,0.43,0.45,0.46,0.44,0.43,0.42,0.44],
			color: '#0ea5e9',
			valueId: 'value-resp',
			noteId: 'note-resp',
			value: '16',
			note: 'Resp/min'
		},
		'temperature': {
			title: 'Température corporelle',
			type: 'area-threshold',
			// 36.1-37.2°C (température normale) - très stable
			data: [0.46,0.47,0.48,0.49,0.50,0.51,0.50,0.49,0.48,0.49],
			color: '#f97316',
			threshold: 0.65, // Seuil de fièvre (38°C+)
			valueId: 'value-temp',
			noteId: 'note-temp',
			value: '36.7',
			note: '°C, dernière mesure'
		},
		'glucose-trend': {
			title: 'Glycémie (tendance)',
			type: 'area',
			// 4.0-7.0 mmol/L (glycémie à jeun normale) - variations post-repas
			data: [0.52,0.50,0.54,0.58,0.62,0.60,0.56,0.54,0.52,0.55],
			color: '#7c3aed',
			valueId: 'value-glucose-trend',
			noteId: 'note-glucose',
			value: '5.9',
			note: 'mmol/L'
		},
		'weight': {
			title: 'Poids',
			type: 'area',
			// Poids stable avec variations minimes (70-75 kg) - évolution sur plusieurs semaines
			data: [0.52,0.53,0.54,0.53,0.54,0.55,0.54,0.55,0.56,0.55],
			color: '#10b981',
			valueId: 'value-weight',
			noteId: 'note-weight',
			value: '72.5',
			note: 'kg, dernière mesure'
		},
		'oxygen-saturation': {
			title: 'Saturation en oxygène',
			type: 'area',
			// 95-100% (saturation normale) - très stable et élevée
			data: [0.96,0.97,0.98,0.97,0.98,0.99,0.98,0.97,0.98,0.98],
			color: '#06b6d4',
			valueId: 'value-oxygen',
			noteId: 'note-oxygen',
			value: '98',
			note: '%, dernière mesure'
		}
	};
	
	let editMode = false;
	let chartConfig = loadChartConfig();

	function setupCanvas(canvas) {
		const rect = canvas.getBoundingClientRect();
		canvas.width = rect.width * DPR;
		canvas.height = rect.height * DPR;
		const ctx = canvas.getContext('2d');
		ctx.setTransform(DPR, 0, 0, DPR, 0, 0);
		return {ctx, width: rect.width, height: rect.height};
	}

	// Area / line chart
	function animateArea(canvasId, data, color) {
		const canvas = document.getElementById(canvasId);
		if (!canvas) return;

		let t = 0;
		function draw() {
			const {ctx, width, height} = setupCanvas(canvas);
			const padding = 12;
			ctx.clearRect(0, 0, width, height);

			// grid
			ctx.strokeStyle = 'rgba(60,80,100,0.06)';
			ctx.lineWidth = 1;
			for (let i = 0; i < 3; i++) {
				const y = padding + (height - padding * 2) * (i / 2);
				ctx.beginPath(); ctx.moveTo(padding, y); ctx.lineTo(width - padding, y); ctx.stroke();
			}

			ctx.beginPath();
			ctx.lineWidth = 2.5; ctx.strokeStyle = color; ctx.fillStyle = color + '22';
			const step = (width - padding * 2) / (data.length - 1);
			data.forEach((v, i) => {
				const jitter = Math.sin((t + i * 7) / 15) * (height * 0.005);
				const x = padding + i * step;
				const y = padding + (1 - v) * (height - padding * 2) + jitter;
				if (i === 0) ctx.moveTo(x, y); else ctx.lineTo(x, y);
			});
			ctx.stroke();

			// fill
			ctx.lineTo(width - padding, height - padding);
			ctx.lineTo(padding, height - padding);
			ctx.closePath();
			ctx.fill();

			t += 1;
			requestAnimationFrame(draw);
		}

		draw();
	}

	// Area with threshold line (for temperature)
	function animateAreaWithThreshold(canvasId, data, color, threshold) {
		const canvas = document.getElementById(canvasId);
		if (!canvas) return;

		let t = 0;
		function draw() {
			const {ctx, width, height} = setupCanvas(canvas);
			const padding = 12;
			ctx.clearRect(0, 0, width, height);

			// grid
			ctx.strokeStyle = 'rgba(60,80,100,0.06)';
			ctx.lineWidth = 1;
			for (let i = 0; i < 3; i++) {
				const y = padding + (height - padding * 2) * (i / 2);
				ctx.beginPath(); ctx.moveTo(padding, y); ctx.lineTo(width - padding, y); ctx.stroke();
			}

			// draw threshold line
			if (typeof threshold === 'number') {
				const yThr = padding + (1 - threshold) * (height - padding * 2);
				ctx.beginPath(); ctx.moveTo(padding, yThr); ctx.lineTo(width - padding, yThr);
				ctx.strokeStyle = 'rgba(220,38,38,0.65)'; ctx.lineWidth = 1; ctx.setLineDash([6,6]); ctx.stroke(); ctx.setLineDash([]);
			}

			ctx.beginPath();
			ctx.lineWidth = 2.5; ctx.strokeStyle = color; ctx.fillStyle = color + '22';
			const step = (width - padding * 2) / (data.length - 1);
			data.forEach((v, i) => {
				const jitter = Math.sin((t + i * 7) / 15) * (height * 0.005);
				const x = padding + i * step;
				const y = padding + (1 - v) * (height - padding * 2) + jitter;
				if (i === 0) ctx.moveTo(x, y); else ctx.lineTo(x, y);
			});
			ctx.stroke();

			// fill
			ctx.lineTo(width - padding, height - padding);
			ctx.lineTo(padding, height - padding);
			ctx.closePath();
			ctx.fill();

			t += 1; requestAnimationFrame(draw);
		}
		draw();
	}

	// Sparkline (thin line, less padding)
	function animateSparkline(canvasId, data, color) {
		const canvas = document.getElementById(canvasId); if (!canvas) return;
		let t = 0;
		function draw() {
			const {ctx, width, height} = setupCanvas(canvas);
			ctx.clearRect(0, 0, width, height);
			const padding = 6;
			ctx.beginPath(); ctx.lineWidth = 1.6; ctx.strokeStyle = color;
			const step = (width - padding * 2) / (data.length - 1);
			data.forEach((v, i) => {
				const jitter = Math.sin((t + i * 5) / 10) * (height * 0.006);
				const x = padding + i * step; const y = padding + (1 - v) * (height - padding * 2) + jitter;
				if (i === 0) ctx.moveTo(x, y); else ctx.lineTo(x, y);
			});
			ctx.stroke(); t += 1; requestAnimationFrame(draw);
		}
		draw();
	}

	// Bar chart
	function animateBarChart(canvasId, data, color) {
		const canvas = document.getElementById(canvasId); if (!canvas) return;
		let t = 0;
		function draw() {
			const {ctx, width, height} = setupCanvas(canvas);
			ctx.clearRect(0, 0, width, height);
			const padding = 12; const available = width - padding * 2; const barW = available / data.length * 0.7; const gap = (available - barW * data.length) / Math.max(1, data.length - 1);
			data.forEach((v, i) => {
				const x = padding + i * (barW + gap);
				const h = (height - padding * 2) * v;
				const animH = h * (0.6 + 0.4 * (0.5 + 0.5 * Math.sin((t + i) / 10)));
				ctx.fillStyle = color;
				roundRect(ctx, x, height - padding - animH, barW, animH, 4);
			});
			t += 1; requestAnimationFrame(draw);
		}
		draw();
	}

	// Dual-line chart (for systolic/diastolic blood pressure)
	function animateDualLineChart(canvasId, seriesA, seriesB, colorA, colorB) {
		const canvas = document.getElementById(canvasId); if (!canvas) return;
		let t = 0;
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
				const jitter = Math.sin((t + i * 8) / 16) * (height * 0.006);
				const x = padding + i * step; const y = padding + (1 - v) * (height - padding * 2) + jitter;
				if (i === 0) ctx.moveTo(x, y); else ctx.lineTo(x, y);
			}); ctx.stroke();

			// draw series B
			ctx.beginPath(); ctx.lineWidth = 2; ctx.strokeStyle = colorB;
			seriesB.forEach((v, i) => {
				const jitter = Math.cos((t + i * 6) / 14) * (height * 0.004);
				const x = padding + i * step; const y = padding + (1 - v) * (height - padding * 2) + jitter;
				if (i === 0) ctx.moveTo(x, y); else ctx.lineTo(x, y);
			}); ctx.stroke();

			// small legend dots
			ctx.fillStyle = colorA; ctx.fillRect(width - padding - 90, padding, 10, 10); ctx.fillStyle = '#081e2b'; ctx.font = '12px sans-serif'; ctx.fillText('Systolique', width - padding - 72, padding + 9);
			ctx.fillStyle = colorB; ctx.fillRect(width - padding - 90, padding + 16, 10, 10); ctx.fillStyle = '#081e2b'; ctx.fillText('Diastolique', width - padding - 72, padding + 25);

			t += 1; requestAnimationFrame(draw);
		}
		draw();
	}

	// Donut chart (progress-like)
	function animateDonut(canvasId, value, color) {
		const canvas = document.getElementById(canvasId); if (!canvas) return;
		function draw() {
			const {ctx, width, height} = setupCanvas(canvas);
			ctx.clearRect(0, 0, width, height);
			const cx = width / 2; const cy = height / 2; const r = Math.min(width, height) * 0.32; const thickness = r * 0.45;
			// background ring
			ctx.beginPath(); ctx.arc(cx, cy, r, 0, Math.PI * 2); ctx.lineWidth = thickness; ctx.strokeStyle = '#eef6fb'; ctx.stroke();
			// progress
			const pct = Math.max(0, Math.min(1, value));
			ctx.beginPath(); ctx.arc(cx, cy, r, -Math.PI / 2, -Math.PI / 2 + Math.PI * 2 * pct); ctx.lineWidth = thickness; ctx.strokeStyle = color; ctx.lineCap = 'round'; ctx.stroke();
			requestAnimationFrame(draw);
		}
		draw();
	}

	// Gauge (semi-circle)
	function animateGauge(canvasId, value, color) {
		const canvas = document.getElementById(canvasId); if (!canvas) return;
		function draw() {
			const {ctx, width, height} = setupCanvas(canvas);
			ctx.clearRect(0, 0, width, height);
			const cx = width / 2; const cy = height * 0.75; const r = Math.min(width, height) * 0.4;
			// background arc
			ctx.beginPath(); ctx.arc(cx, cy, r, Math.PI, 0); ctx.lineWidth = 12; ctx.strokeStyle = '#eef6fb'; ctx.stroke();
			// value arc
			const pct = Math.max(0, Math.min(1, value));
			ctx.beginPath(); ctx.arc(cx, cy, r, Math.PI, Math.PI + Math.PI * pct); ctx.lineWidth = 12; ctx.strokeStyle = color; ctx.lineCap = 'round'; ctx.stroke();
			requestAnimationFrame(draw);
		}
		draw();
	}

	// helper: rounded rect
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

	// Load and save config from localStorage
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
	
	function saveChartConfig() {
		localStorage.setItem('dashboardChartConfig', JSON.stringify(chartConfig));
	}
	
	// Apply saved configuration
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
			
			// Show/hide resize handles and add edit-mode class
			document.querySelectorAll('.chart-card').forEach(card => {
				const handle = card.querySelector('.resize-handle');
				if (handle) {
					handle.style.display = editMode ? 'block' : 'none';
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
	
	// Auto-scroll variables
	let autoScrollInterval = null;
	
	// Setup drag functionality for chart cards
	function setupChartDrag(card) {
		card.draggable = true;
		card.style.cursor = 'grab';
		
		card.addEventListener('dragstart', (e) => {
			const chartId = card.getAttribute('data-chart-id');
			e.dataTransfer.effectAllowed = 'move';
			e.dataTransfer.setData('text/plain', chartId);
			e.dataTransfer.setData('source', 'chart-card');
			card.classList.add('dragging');
			startAutoScroll();
		});
		
		card.addEventListener('dragend', (e) => {
			card.classList.remove('dragging');
			stopAutoScroll();
		});
		
		// Setup drop zones on cards for reordering
		card.addEventListener('dragover', (e) => {
			if (!editMode) return;
			const dragging = document.querySelector('.dragging');
			if (!dragging || dragging === card) return;
			
			e.preventDefault();
			e.dataTransfer.dropEffect = 'move';
			
			// Determine if we should insert before or after based on mouse position
			const rect = card.getBoundingClientRect();
			const midY = rect.top + rect.height / 2;
			const midX = rect.left + rect.width / 2;
			
			// For grid layout, check both X and Y position
			if (e.clientY < midY) {
				card.classList.add('drop-before');
				card.classList.remove('drop-after');
			} else {
				card.classList.add('drop-after');
				card.classList.remove('drop-before');
			}
		});
		
		card.addEventListener('dragleave', (e) => {
			// Only remove if we're actually leaving the card
			if (!card.contains(e.relatedTarget)) {
				card.classList.remove('drop-before', 'drop-after');
			}
		});
		
		card.addEventListener('drop', (e) => {
			e.preventDefault();
			card.classList.remove('drop-before', 'drop-after');
			
			const source = e.dataTransfer.getData('source');
			if (source !== 'chart-card') return;
			
			const draggedId = e.dataTransfer.getData('text/plain');
			const targetId = card.getAttribute('data-chart-id');
			
			if (draggedId === targetId) return;
			
			// Reorder in the visible array
			const draggedIndex = chartConfig.visible.indexOf(draggedId);
			const targetIndex = chartConfig.visible.indexOf(targetId);
			
			if (draggedIndex === -1 || targetIndex === -1) return;
			
			// Remove from old position
			chartConfig.visible.splice(draggedIndex, 1);
			
			// Insert at new position
			const newTargetIndex = chartConfig.visible.indexOf(targetId);
			const rect = card.getBoundingClientRect();
			const midY = rect.top + rect.height / 2;
			
			if (e.clientY < midY) {
				// Insert before
				chartConfig.visible.splice(newTargetIndex, 0, draggedId);
			} else {
				// Insert after
				chartConfig.visible.splice(newTargetIndex + 1, 0, draggedId);
			}
			
			saveChartConfig();
			reorderChartsInDOM();
		});
	}
	
	// Reorder charts in the DOM based on the visible array order
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
	
	// Update available charts panel
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
	
	// Add chart
	function addChart(chartId) {
		if (chartConfig.visible.includes(chartId)) return;
		
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
			}
		}
		
		updateAvailableCharts();
	}
	
	// Remove chart
	function removeChart(chartId) {
		const index = chartConfig.visible.indexOf(chartId);
		if (index > -1) {
			chartConfig.visible.splice(index, 1);
			saveChartConfig();
			applyChartConfig();
			updateAvailableCharts();
		}
	}
	
	// Resize chart
	function resizeChart(chartId, colSpan) {
		// Clamp between 3 and 12 columns
		colSpan = Math.max(3, Math.min(12, colSpan));
		chartConfig.sizes[chartId] = colSpan;
		
		const card = document.querySelector(`[data-chart-id="${chartId}"]`);
		if (card) {
			card.setAttribute('data-col-span', colSpan);
		}
		
		saveChartConfig();
		setTimeout(() => resizeAllCanvases(), 50);
	}
	
	// Setup resize handles with smooth dragging
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
				chartConfig.sizes[chartId] = finalColSpan;
				saveChartConfig();
				
				// Update canvas sizes
				setTimeout(() => resizeAllCanvases(), 100);
			};
			
			document.addEventListener('mousemove', handleMouseMove);
			document.addEventListener('mouseup', handleMouseUp);
		});
	}
	
	// Initialize chart animations
	function initializeChart(chartId) {
		const def = CHART_DEFINITIONS[chartId];
		if (!def) {
			console.error(`Graphique ${chartId} non trouvé dans CHART_DEFINITIONS`);
			return;
		}
		
		const canvasId = 'chart-' + chartId;
		const canvas = document.getElementById(canvasId);
		if (!canvas) {
			console.warn(`Canvas ${canvasId} non trouvé dans le DOM`);
			return;
		}
		
		if (def.type === 'area') {
			animateArea(canvasId, def.data, def.color);
		} else if (def.type === 'area-threshold') {
			animateAreaWithThreshold(canvasId, def.data, def.color, def.threshold);
		} else if (def.type === 'bar') {
			animateBarChart(canvasId, def.data, def.color);
		} else if (def.type === 'dual') {
			animateDualLineChart(canvasId, def.dataA, def.dataB, def.colorA, def.colorB);
		}
	}
	
	// Resize indicator functions
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
	
	function hideResizeIndicator() {
		if (resizeIndicator) {
			resizeIndicator.style.display = 'none';
		}
	}
	
	// Setup resize handles
	setupResizeHandles();
	
	// Setup drag & drop zone for the dashboard grid
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
	
	// Setup drop zone for deleting charts
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

	// Ensure canvases are sized correctly on load and when window resizes
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

	// debounce helper
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

	// start animations for all charts present in the DOM
	document.querySelectorAll('.chart-card').forEach(card => {
		const chartId = card.getAttribute('data-chart-id');
		if (chartId && CHART_DEFINITIONS[chartId]) {
			initializeChart(chartId);
		}
	});

	// resize handler (debounced) to keep canvases crisp when viewport changes
	window.addEventListener('resize', debounce(() => {
		resizeAllCanvases();
	}, 150));
});

