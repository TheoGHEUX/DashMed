// Lightweight placeholder charts for the dashboard (multiple clinical types)
document.addEventListener('DOMContentLoaded', () => {
	const DPR = window.devicePixelRatio || 1;
	
	// Chart configuration management
	const CHART_DEFINITIONS = {
		'blood-pressure': {
			title: 'Tendance de la tension',
			type: 'dual',
			dataA: [0.7,0.72,0.74,0.73,0.76,0.75,0.74,0.77,0.79,0.78],
			dataB: [0.5,0.51,0.52,0.5,0.53,0.52,0.51,0.52,0.54,0.53],
			colorA: '#ef4444',
			colorB: '#0b6e4f',
			valueId: 'value-bp',
			noteId: 'note-bp',
			value: '122/78',
			note: 'Moyenne dernière semaine'
		},
		'heart-rate': {
			title: 'Fréquence cardiaque',
			type: 'area',
			data: [0.5,0.55,0.53,0.6,0.62,0.58,0.6,0.63,0.59,0.57],
			color: '#be185d',
			valueId: 'value-hr',
			noteId: 'note-hr',
			value: '72',
			note: 'BPM, dernière mesure'
		},
		'respiration': {
			title: 'Respiration',
			type: 'area',
			data: [0.4,0.42,0.45,0.43,0.44,0.46,0.45,0.44,0.43,0.42],
			color: '#0ea5e9',
			valueId: 'value-resp',
			noteId: 'note-resp',
			value: '16',
			note: 'Resp/min'
		},
		'temperature': {
			title: 'Température',
			type: 'area-threshold',
			data: [0.45,0.46,0.47,0.48,0.5,0.52,0.51,0.5,0.49,0.48],
			color: '#f97316',
			threshold: 0.6,
			valueId: 'value-temp',
			noteId: 'note-temp',
			value: '36.7',
			note: '°C, dernière mesure'
		},
		'glucose-trend': {
			title: 'Glycémie (tendance)',
			type: 'area',
			data: [0.6,0.58,0.59,0.6,0.62,0.61,0.6,0.59,0.58,0.6],
			color: '#7c3aed',
			valueId: 'value-glucose-trend',
			noteId: 'note-glucose',
			value: '5.9',
			note: 'mmol/L'
		},
		'activity': {
			title: 'Activité (pas)',
			type: 'bar',
			data: [0.3,0.4,0.45,0.5,0.48,0.55,0.6,0.58,0.62,0.65],
			color: '#059669',
			valueId: 'value-activity',
			noteId: 'note-activity',
			value: '7 432',
			note: "Aujourd'hui"
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
		const saved = localStorage.getItem('dashboardChartConfig');
		if (saved) {
			try {
				return JSON.parse(saved);
			} catch (e) {
				console.error('Failed to parse chart config', e);
			}
		}
		// Default config
		return {
			visible: ['blood-pressure', 'heart-rate', 'respiration', 'temperature', 'glucose-trend', 'activity'],
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
		
		// Hide non-visible charts
		document.querySelectorAll('.chart-card').forEach(card => {
			const chartId = card.getAttribute('data-chart-id');
			if (!chartConfig.visible.includes(chartId)) {
				card.style.display = 'none';
			} else {
				card.style.display = 'block';
				// Apply column span (default 6 out of 12)
				const colSpan = chartConfig.sizes[chartId] || 6;
				card.setAttribute('data-col-span', colSpan);
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
			
			// Show/hide edit controls
			document.querySelectorAll('.card-edit-controls').forEach(controls => {
				controls.style.display = editMode ? 'flex' : 'none';
			});
			
			// Show/hide resize handles and add edit-mode class
			document.querySelectorAll('.chart-card').forEach(card => {
				const handle = card.querySelector('.resize-handle');
				if (handle) {
					handle.style.display = editMode ? 'block' : 'none';
				}
				if (editMode) {
					card.classList.add('edit-mode');
				} else {
					card.classList.remove('edit-mode');
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
			
			if (!isVisible) {
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
			initializeChart(chartId);
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
		const canvasId = 'chart-' + chartId;
		
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
	
	// Setup event listeners for remove buttons
	document.querySelectorAll('.chart-card').forEach(card => {
		const chartId = card.getAttribute('data-chart-id');
		
		// Remove button
		const removeBtn = card.querySelector('.btn-remove');
		if (removeBtn) {
			removeBtn.addEventListener('click', () => {
				if (confirm('Voulez-vous vraiment supprimer ce graphique ?')) {
					removeChart(chartId);
				}
			});
		}
	});
	
	// Setup resize handles
	setupResizeHandles();
	
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
	
	// initial resize
	resizeAllCanvases();

	// start animations for all visible charts
	chartConfig.visible.forEach(chartId => {
		initializeChart(chartId);
	});

	// resize handler (debounced) to keep canvases crisp when viewport changes
	window.addEventListener('resize', debounce(() => {
		resizeAllCanvases();
	}, 150));
});

