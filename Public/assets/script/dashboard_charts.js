// Lightweight placeholder charts for the dashboard (multiple clinical types)
document.addEventListener('DOMContentLoaded', () => {
	const DPR = window.devicePixelRatio || 1;

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

	// Populate sample numeric values under each chart
	const sampleValues = {
		'value-bp': '122/78',
		'value-hr': '72',
		'value-resp': '16',
		'value-temp': '36.7',
		'value-glucose-trend': '5.9',
		'value-activity': '7 432'
	};
	Object.keys(sampleValues).forEach(id => { const el = document.getElementById(id); if (el) el.textContent = sampleValues[id]; });

	// datasets (normalized 0..1) and type mapping
	const charts = {
		'chart-blood-pressure': {type: 'dual', dataA: [0.7,0.72,0.74,0.73,0.76,0.75,0.74,0.77,0.79,0.78], dataB: [0.5,0.51,0.52,0.5,0.53,0.52,0.51,0.52,0.54,0.53], colorA: '#ef4444', colorB: '#0b6e4f'},
		'chart-heart-rate': {type: 'area', data: [0.5,0.55,0.53,0.6,0.62,0.58,0.6,0.63,0.59,0.57], color: '#be185d'},
		'chart-respiration': {type: 'area', data: [0.4,0.42,0.45,0.43,0.44,0.46,0.45,0.44,0.43,0.42], color: '#0ea5e9'},
		'chart-temperature': {type: 'area-threshold', data: [0.45,0.46,0.47,0.48,0.5,0.52,0.51,0.5,0.49,0.48], color: '#f97316', threshold: 0.6},
		'chart-glucose-trend': {type: 'area', data: [0.6,0.58,0.59,0.6,0.62,0.61,0.6,0.59,0.58,0.6], color: '#7c3aed'},
		'chart-activity': {type: 'bar', data: [0.3,0.4,0.45,0.5,0.48,0.55,0.6,0.58,0.62,0.65], color: '#059669'}
	};

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

	// initial resize
	resizeAllCanvases();

	// start animations for each chart type
	Object.keys(charts).forEach(id => {
		const cfg = charts[id];
		if (cfg.type === 'area') animateArea(id, cfg.data, cfg.color);
		else if (cfg.type === 'area-threshold') animateAreaWithThreshold(id, cfg.data, cfg.color, cfg.threshold);
		else if (cfg.type === 'spark') animateSparkline(id, cfg.data, cfg.color);
		else if (cfg.type === 'bar') animateBarChart(id, cfg.data, cfg.color);
		else if (cfg.type === 'donut') animateDonut(id, cfg.value, cfg.color);
		else if (cfg.type === 'gauge') animateGauge(id, cfg.value, cfg.color);
		else if (cfg.type === 'dual') animateDualLineChart(id, cfg.dataA, cfg.dataB, cfg.colorA, cfg.colorB);
	});

	// resize handler (debounced) to keep canvases crisp when viewport changes
	window.addEventListener('resize', debounce(() => {
		resizeAllCanvases();
	}, 150));

	// Expand/restore handlers for each card
	document.querySelectorAll('.card-expand').forEach(btn => {
		btn.addEventListener('click', (e) => {
			const card = e.currentTarget.closest('.card');
			if (!card) return;
			card.classList.toggle('expanded');
			// update canvases sizes after layout change
			setTimeout(() => resizeAllCanvases(), 80);
		});
	});
});

