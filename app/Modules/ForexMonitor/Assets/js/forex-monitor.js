/**
 * Dashboard forex memakai polling 30 detik dan render chart ringan agar user
 * tetap mendapat nuansa realtime tanpa kebutuhan websocket tambahan.
 */
(function() {
	var state = {
		app: null,
		snapshotUrl: '',
		timeframe: '1D',
		chartGranularity: '',
		payload: {},
		indicatorVisibility: {},
		interactionMode: 'auto-fit',
		autoFitViewport: null,
		userViewport: null,
		priceChart: null,
		rsiChart: null,
		granularitySyncTimer: null,
		refreshTimer: null,
		requestInFlight: false
	};

	function formatNumber(value, digits) {
		var number = typeof value === 'number' ? value : parseFloat(value || 0);
		if (!isFinite(number)) {
			number = 0;
		}

		return new Intl.NumberFormat('id-ID', {
			minimumFractionDigits: digits,
			maximumFractionDigits: digits
		}).format(number);
	}

	function getEl(id) {
		return document.getElementById(id);
	}

	function parseBootstrapPayload() {
		var node = getEl('forex-dashboard-bootstrap');
		if (!node || !node.textContent) {
			return {};
		}

		try {
			return JSON.parse(node.textContent);
		} catch (error) {
			return {};
		}
	}

	function setText(id, text) {
		var element = getEl(id);
		if (!element) {
			return;
		}

		element.textContent = text;
	}

	/**
	 * Viewport default diambil dari payload backend agar load awal selalu
	 * langsung pas dengan high-low timeframe aktif tanpa ruang kosong.
	 */
	function getAutoFitViewport(chartPayload) {
		var axis = chartPayload && chartPayload.meta && chartPayload.meta.axis ? chartPayload.meta.axis : {};
		return {
			xMin: axis.x_min || null,
			xMax: axis.x_max || null,
			yMin: axis.y_min || null,
			yMax: axis.y_max || null
		};
	}

	/**
	 * Viewport aktif memilih hasil interaksi user bila ada, lalu fallback ke
	 * auto-fit awal agar tombol zoom dan polling membaca basis yang sama.
	 */
	function getActiveViewport(chartPayload) {
		if (state.interactionMode === 'user' && state.userViewport) {
			return {
				xMin: state.userViewport.xMin,
				xMax: state.userViewport.xMax,
				yMin: state.userViewport.yMin || (chartPayload && chartPayload.meta && chartPayload.meta.axis ? chartPayload.meta.axis.y_min : null),
				yMax: state.userViewport.yMax || (chartPayload && chartPayload.meta && chartPayload.meta.axis ? chartPayload.meta.axis.y_max : null)
			};
		}

		return getAutoFitViewport(chartPayload);
	}

	/**
	 * Snapshot viewport dibaca dari instance Apex agar tombol zoom manual bisa
	 * fokus pada area yang saat ini sedang dilihat user.
	 */
	function captureChartViewport() {
		if (!state.priceChart || !state.priceChart.w || !state.priceChart.w.globals) {
			return null;
		}

		var globals = state.priceChart.w.globals;
		var viewport = {
			xMin: globals.minX || null,
			xMax: globals.maxX || null,
			yMin: globals.minY || null,
			yMax: globals.maxY || null
		};

		if (!viewport.xMin || !viewport.xMax) {
			return null;
		}

		return viewport;
	}

	/**
	 * Setelah zoom atau pan terjadi, mode chart dipindah ke kontrol user agar
	 * polling berikutnya tidak memaksa chart kembali ke range auto-fit awal.
	 */
	function setUserViewport(viewport) {
		if (!viewport || !viewport.xMin || !viewport.xMax) {
			return;
		}

		state.userViewport = viewport;
		state.interactionMode = 'user';
		updateChartRangeBadge(viewport);
	}

	/**
	 * Reset viewport mengembalikan chart ke high-low penuh timeframe aktif dan
	 * sekaligus memulihkan perilaku auto-fit pada update berikutnya.
	 */
	function resetViewportState() {
		state.interactionMode = 'auto-fit';
		state.userViewport = null;
		state.autoFitViewport = getAutoFitViewport(state.payload.chart || {});
		updateChartRangeBadge(state.autoFitViewport);
	}

	/**
	 * Badge range diperbarui terpisah agar user langsung melihat dampak zoom
	 * atau reset tanpa menunggu polling snapshot berikutnya.
	 */
	function updateChartRangeBadge(viewport) {
		setText(
			'dashboard-chart-range',
			'Range ' + formatNumber((viewport && viewport.yMin) || 0, 4) + ' - ' + formatNumber((viewport && viewport.yMax) || 0, 4)
		);
	}

	/**
	 * Granularity chart dipilih dari lebar viewport agar zoom out menampilkan
	 * dataset lebih ringan dan zoom in otomatis meminta candle lebih detail.
	 */
	function resolveGranularityByViewport(timeframe, viewport) {
		var rangeMs = viewport && viewport.xMin && viewport.xMax ? (viewport.xMax - viewport.xMin) : 0;
		if (!rangeMs) {
			return timeframe === '1M' ? 'W1' : (timeframe === '1D' ? 'H4' : 'D1');
		}

		if (timeframe === '1D') {
			/**
			 * Daily sengaja turun bertahap dari H4 ke H1, M15, lalu M5 agar
			 * zoom makin dalam langsung membuka candle intraday yang lebih rapat.
			 */
			if (rangeMs <= (2 * 60 * 60 * 1000)) {
				return 'M5';
			}
			if (rangeMs <= (8 * 60 * 60 * 1000)) {
				return 'M15';
			}
			if (rangeMs <= (18 * 60 * 60 * 1000)) {
				return 'H1';
			}
			return 'H4';
		}

		if (timeframe === '1M') {
			return rangeMs <= (15 * 24 * 60 * 60 * 1000) ? 'D1' : 'W1';
		}

		return 'D1';
	}

	/**
	 * Sinkronisasi granularity ditunda singkat agar drag zoom dan pan tidak
	 * mengirim request beruntun setiap perubahan kecil pada viewport chart.
	 */
	function queueGranularitySync() {
		if (state.granularitySyncTimer) {
			window.clearTimeout(state.granularitySyncTimer);
		}

		state.granularitySyncTimer = window.setTimeout(function() {
			var viewport = captureChartViewport() || getActiveViewport(state.payload.chart || {});
			var nextGranularity = resolveGranularityByViewport(state.timeframe, viewport);
			if (!nextGranularity || nextGranularity === state.chartGranularity) {
				return;
			}

			state.chartGranularity = nextGranularity;
			fetchSnapshot(true);
		}, 180);
	}

	function escapeHtml(text) {
		return String(text || '')
			.replace(/&/g, '&amp;')
			.replace(/</g, '&lt;')
			.replace(/>/g, '&gt;')
			.replace(/"/g, '&quot;')
			.replace(/'/g, '&#039;');
	}

	function buildAlertHtml(alerts) {
		if (!alerts || !alerts.length) {
			return '<div class="project-suite-empty">Belum ada alert aktif untuk user ini.</div>';
		}

		return alerts.map(function(alert) {
			var title = String(alert.condition_type || 'above').toUpperCase() + ' ' + formatNumber(alert.target_price || 0, 4);
			var meta = 'Sound ' + (parseInt(alert.with_sound || 0, 10) === 1 ? 'On' : 'Off');
			if (alert.created_at) {
				meta += ' | Dibuat ' + escapeHtml(alert.created_at);
			}

			return ''
				+ '<div class="forex-alert-mini-item">'
				+ '<div class="forex-alert-mini-item__title">' + escapeHtml(title) + '</div>'
				+ '<div class="forex-alert-mini-item__meta">' + meta + '</div>'
				+ '</div>';
		}).join('');
	}

	function renderLivePrice(livePrice) {
		if (!livePrice) {
			return;
		}

		var changeAmount = parseFloat(livePrice.change_amount || 0);
		var arrow = changeAmount >= 0 ? '↑ ' : '↓ ';
		var percentText = formatNumber(livePrice.change_percent || 0, 4) + '%';

		setText('dashboard-current-price', formatNumber(livePrice.current_price || 0, 4));
		setText('dashboard-change-text', arrow + formatNumber(Math.abs(changeAmount), 4));
		setText('dashboard-change-percent', percentText);
		setText('dashboard-source', livePrice.provider || '-');
		setText('dashboard-source-type', livePrice.source_type || '-');
		setText('dashboard-quote-time', livePrice.quote_time || '-');
	}

	/**
	 * Block market context dirender ulang dari payload dinamis supaya status
	 * Daily, Weekly, dan Monthly tetap sinkron dengan rule signal backend.
	 */
	function renderMarketContext(marketContext, confluence) {
		var container = getEl('dashboard-market-context-list');
		if (!container) {
			return;
		}

		var labels = {
			daily: 'Daily',
			weekly: 'Weekly',
			monthly: 'Monthly'
		};
		var html = ['daily', 'weekly', 'monthly'].map(function(key) {
			var item = marketContext && marketContext[key] ? marketContext[key] : {};
			var breakoutText = '-';
			if ((item.breakout_up_level || 0) > 0 || (item.breakout_down_level || 0) > 0) {
				breakoutText = formatNumber(item.breakout_up_level || 0, 4) + ' / ' + formatNumber(item.breakout_down_level || 0, 4);
			}

			return ''
				+ '<div class="forex-market-context-card">'
				+ '<div class="forex-market-context-card__head">'
				+ '<div>'
				+ '<div class="forex-method-card__label">' + escapeHtml(labels[key]) + '</div>'
				+ '<div class="forex-method-card__meta">' + escapeHtml(item.date_label || '-') + '</div>'
				+ '</div>'
				+ '<span class="forex-signal-badge forex-signal-badge--' + escapeHtml(item.status_color || 'yellow') + '">' + escapeHtml(item.status_label || 'Inside Range') + '</span>'
				+ '</div>'
				+ '<div class="forex-market-context-card__grid">'
				+ '<div class="forex-market-context-card__row"><span>High / Low</span><strong>' + (((item.high_price || 0) > 0 || (item.low_price || 0) > 0) ? (formatNumber(item.high_price || 0, 4) + ' / ' + formatNumber(item.low_price || 0, 4)) : '-') + '</strong></div>'
				+ '<div class="forex-market-context-card__row"><span>Resistance</span><strong>' + escapeHtml(item.resistance_zone ? item.resistance_zone.label : '-') + '</strong></div>'
				+ '<div class="forex-market-context-card__row"><span>Support</span><strong>' + escapeHtml(item.support_zone ? item.support_zone.label : '-') + '</strong></div>'
				+ '<div class="forex-market-context-card__row"><span>Breakout Up / Down</span><strong>' + breakoutText + '</strong></div>'
				+ '</div>'
				+ '</div>';
		}).join('');

		container.innerHTML = html;
		setText('dashboard-context-confluence', confluence && confluence.label ? confluence.label : 'Belum ada confluence kuat');
		setText('dashboard-context-priority', marketContext && marketContext.combined && marketContext.combined.priority_timeframe ? ((marketContext.combined.priority_timeframe || '').charAt(0).toUpperCase() + (marketContext.combined.priority_timeframe || '').slice(1)) : '-');
		setText('dashboard-context-summary', marketContext && marketContext.combined && marketContext.combined.summary ? marketContext.combined.summary : 'Menunggu context dominan dari OHLC historis.');
	}

	function renderActiveAlerts(alerts) {
		var container = getEl('dashboard-active-alerts');
		if (!container) {
			return;
		}

		container.innerHTML = buildAlertHtml(alerts || []);
	}

	function renderTradingSignal(signal) {
		if (!signal) {
			return;
		}

		var badge = getEl('dashboard-signal-badge');
		if (badge) {
			badge.textContent = signal.signal_label || 'WAIT';
			badge.className = 'forex-signal-badge forex-signal-badge--' + (signal.signal_color || 'yellow');
		}

		setText('dashboard-signal-confidence', 'Confidence ' + ((signal.confidence || 'low').charAt(0).toUpperCase() + (signal.confidence || 'low').slice(1)));
		setText('dashboard-signal-reason', signal.reason || 'Menunggu konfirmasi harga dan indikator.');
		setText('dashboard-buy-zone', signal.buy_zone ? signal.buy_zone.label : '-');
		setText('dashboard-sell-zone', signal.sell_zone ? signal.sell_zone.label : '-');
		setText(
			'dashboard-breakout-level',
			((signal.breakout_level || 0) > 0 || (signal.breakdown_level || 0) > 0)
				? (formatNumber(signal.breakout_level || 0, 4) + ' / ' + formatNumber(signal.breakdown_level || 0, 4))
				: '-'
		);
		setText('dashboard-auto-monitor-label', signal.auto_monitor ? signal.auto_monitor.label : 'Auto-monitor nonaktif');
		setText('dashboard-signal-rsi', formatNumber((signal.indicators && signal.indicators.rsi) || 0, 2));

		if (signal.indicators && signal.indicators.bollinger) {
			setText(
				'dashboard-signal-bb',
				formatNumber(signal.indicators.bollinger.lower || 0, 4) + ' / ' + formatNumber(signal.indicators.bollinger.upper || 0, 4)
			);
		}

		if (signal.indicators && signal.indicators.fibonacci) {
			setText(
				'dashboard-signal-fib',
				formatNumber(signal.indicators.fibonacci['0.382'] || 0, 4) + ' / ' + formatNumber(signal.indicators.fibonacci['0.618'] || 0, 4)
			);
		}

		setText('dashboard-signal-note-1', signal.notes && signal.notes.length ? (signal.notes[0] || '-') : 'Menunggu konfirmasi support, resistance, atau breakout dinamis.');
		setText('dashboard-signal-note-2', signal.notes && signal.notes.length > 1 ? (signal.notes[1] || '-') : 'Pantau confluence Daily, Weekly, dan Monthly sebelum mengambil posisi.');
	}

	/**
	 * State toggle indikator dijaga di frontend agar pilihan user tetap aktif
	 * saat polling 30 detik maupun ketika timeframe chart berpindah.
	 */
	function syncIndicatorVisibility(chartPayload) {
		var toggles = chartPayload && chartPayload.indicators && chartPayload.indicators.toggles ? chartPayload.indicators.toggles : [];
		toggles.forEach(function(toggle) {
			if (typeof state.indicatorVisibility[toggle.key] === 'undefined') {
				state.indicatorVisibility[toggle.key] = toggle.enabled !== false;
			}
		});
	}

	/**
	 * Legend indikator dibuat sebagai checkbox supaya user bisa menyembunyikan
	 * band tertentu ketika chart mulai terasa terlalu padat di layar kecil.
	 */
	function renderIndicatorLegend(chartPayload) {
		var container = getEl('dashboard-indicator-legend');
		if (!container) {
			return;
		}

		var toggles = chartPayload && chartPayload.indicators && chartPayload.indicators.toggles ? chartPayload.indicators.toggles : [];
		syncIndicatorVisibility(chartPayload);
		container.innerHTML = toggles.map(function(toggle) {
			var inputId = 'indicator-toggle-' + escapeHtml(toggle.key || '');
			return ''
				+ '<label class="forex-indicator-chip" for="' + inputId + '">'
				+ '<input type="checkbox" id="' + inputId + '" data-indicator-toggle="' + escapeHtml(toggle.key || '') + '"' + (state.indicatorVisibility[toggle.key] ? ' checked' : '') + '>'
				+ '<span class="forex-indicator-chip__label">'
				+ '<span class="forex-indicator-chip__swatch" style="background:' + escapeHtml(toggle.color || '#ffffff') + ';"></span>'
				+ '<span>' + escapeHtml(toggle.label || toggle.key || 'Indicator') + '</span>'
				+ '</span>'
				+ '</label>';
		}).join('');
	}

	function buildPriceAnnotations(signalOverlays) {
		if (!signalOverlays) {
			return { yaxis: [] };
		}

		var annotations = [];
		if (signalOverlays.support_zone && signalOverlays.support_zone.length === 2) {
			annotations.push({
				y: signalOverlays.support_zone[0],
				y2: signalOverlays.support_zone[1],
				borderColor: '#16a34a',
				fillColor: 'rgba(22, 163, 74, 0.10)',
				label: { text: 'Buy Zone', style: { background: '#16a34a', color: '#fff' } }
			});
		}

		if (signalOverlays.resistance_zone && signalOverlays.resistance_zone.length === 2) {
			annotations.push({
				y: signalOverlays.resistance_zone[0],
				y2: signalOverlays.resistance_zone[1],
				borderColor: '#dc2626',
				fillColor: 'rgba(220, 38, 38, 0.10)',
				label: { text: 'Sell Zone', style: { background: '#dc2626', color: '#fff' } }
			});
		}

		if (signalOverlays.breakout_up_level) {
			annotations.push({
				y: signalOverlays.breakout_up_level,
				borderColor: '#2563eb',
				strokeDashArray: 4,
				label: { text: 'Breakout Up ' + escapeHtml(signalOverlays.timeframe || 'Daily'), style: { background: '#2563eb', color: '#fff' } }
			});
		}

		if (signalOverlays.breakout_down_level) {
			annotations.push({
				y: signalOverlays.breakout_down_level,
				borderColor: '#dc2626',
				strokeDashArray: 4,
				label: { text: 'Breakdown ' + escapeHtml(signalOverlays.timeframe || 'Daily'), style: { background: '#dc2626', color: '#fff' } }
			});
		}

		return { yaxis: annotations };
	}

	/**
	 * Seri line dirakit ulang sesuai toggle aktif agar chart tetap ringan dan
	 * user bisa fokus hanya pada band yang sedang ingin diamati.
	 */
	function buildPriceSeriesPayload(chartPayload) {
		var series = [
			{
				name: 'Candlestick',
				key: 'candlestick',
				type: 'candlestick',
				data: chartPayload.series && chartPayload.series.candlestick ? chartPayload.series.candlestick : []
			}
		];
		var colors = ['#34d399'];
		var widths = [1];
		var dashArray = [0];
		var toggles = chartPayload && chartPayload.indicators && chartPayload.indicators.toggles ? chartPayload.indicators.toggles : [];

		toggles.forEach(function(toggle) {
			if (!state.indicatorVisibility[toggle.key]) {
				return;
			}

			(toggle.series_keys || []).forEach(function(seriesKey, seriesIndex) {
				if (!chartPayload.series || !chartPayload.series[seriesKey]) {
					return;
				}

				series.push({
					name: toggle.key === 'sma_12'
						? (toggle.label || seriesKey)
						: ((toggle.label || seriesKey) + (seriesIndex === 0 ? ' Upper' : ' Lower')),
					key: seriesKey,
					type: 'line',
					data: chartPayload.series[seriesKey]
				});
				colors.push(toggle.color || '#ffffff');
				widths.push(toggle.key === 'sma_12' ? 2.6 : 1.35);
				dashArray.push(toggle.key === 'sma_12' ? 0 : (seriesIndex === 0 ? 0 : 4));
			});
		});

		return {
			series: series,
			colors: colors,
			widths: widths,
			dashArray: dashArray
		};
	}

	/**
	 * Tombol zoom manual memakai viewport aktif agar pembesaran dan pengecilan
	 * selalu berpusat pada area yang sedang dilihat user saat ini.
	 */
	function applyManualZoom(direction) {
		if (!state.priceChart) {
			return;
		}

		var baseViewport = captureChartViewport() || getActiveViewport(state.payload.chart || {});
		if (!baseViewport || !baseViewport.xMin || !baseViewport.xMax) {
			return;
		}

		var fullViewport = state.autoFitViewport || getAutoFitViewport(state.payload.chart || {});
		var currentRange = baseViewport.xMax - baseViewport.xMin;
		if (currentRange <= 0) {
			return;
		}

		var center = baseViewport.xMin + (currentRange / 2);
		var factor = direction === 'in' ? 0.55 : 1.8;
		var nextRange = currentRange * factor;
		var fullRange = fullViewport && fullViewport.xMin && fullViewport.xMax ? (fullViewport.xMax - fullViewport.xMin) : nextRange;
		if (direction === 'out' && fullRange > 0) {
			nextRange = Math.min(nextRange, fullRange);
		}

		var nextMin = center - (nextRange / 2);
		var nextMax = center + (nextRange / 2);
		if (fullViewport && fullViewport.xMin && fullViewport.xMax) {
			if (nextMin < fullViewport.xMin) {
				nextMin = fullViewport.xMin;
				nextMax = nextMin + nextRange;
			}
			if (nextMax > fullViewport.xMax) {
				nextMax = fullViewport.xMax;
				nextMin = nextMax - nextRange;
			}
		}

		setUserViewport({
			xMin: nextMin,
			xMax: nextMax,
			yMin: null,
			yMax: null
		});
		state.priceChart.zoomX(nextMin, nextMax);
		queueGranularitySync();
	}

	/**
	 * Wheel zoom memakai rasio posisi kursor agar pembesaran langsung fokus ke
	 * area harga yang sedang diarahkan pointer, bukan selalu ke tengah chart.
	 */
	function applyCursorZoom(deltaY, clientX) {
		var element = getEl('forex-live-candlestick');
		if (!state.priceChart || !element) {
			return;
		}

		var rect = element.getBoundingClientRect();
		if (!rect.width) {
			return;
		}

		var baseViewport = captureChartViewport() || getActiveViewport(state.payload.chart || {});
		if (!baseViewport || !baseViewport.xMin || !baseViewport.xMax) {
			return;
		}

		var fullViewport = state.autoFitViewport || getAutoFitViewport(state.payload.chart || {});
		var currentRange = baseViewport.xMax - baseViewport.xMin;
		if (currentRange <= 0) {
			return;
		}

		var cursorRatio = Math.min(1, Math.max(0, (clientX - rect.left) / rect.width));
		var focusX = baseViewport.xMin + (currentRange * cursorRatio);
		var factor = deltaY < 0 ? 0.78 : 1.28;
		var nextRange = currentRange * factor;
		var fullRange = fullViewport && fullViewport.xMin && fullViewport.xMax ? (fullViewport.xMax - fullViewport.xMin) : nextRange;
		nextRange = Math.min(Math.max(nextRange, currentRange * 0.18), fullRange > 0 ? fullRange : nextRange);

		var nextMin = focusX - (nextRange * cursorRatio);
		var nextMax = nextMin + nextRange;
		if (fullViewport && fullViewport.xMin && fullViewport.xMax) {
			if (nextMin < fullViewport.xMin) {
				nextMin = fullViewport.xMin;
				nextMax = nextMin + nextRange;
			}
			if (nextMax > fullViewport.xMax) {
				nextMax = fullViewport.xMax;
				nextMin = nextMax - nextRange;
			}
		}

		setUserViewport({
			xMin: nextMin,
			xMax: nextMax,
			yMin: null,
			yMax: null
		});
		state.priceChart.zoomX(nextMin, nextMax);
		queueGranularitySync();
	}

	/**
	 * Reset chart mengembalikan range ke viewport auto-fit dan melepaskan lock
	 * hasil interaksi sebelumnya agar polling lanjut tetap pakai range penuh.
	 */
	function resetChartZoom() {
		if (!state.priceChart) {
			return;
		}

		resetViewportState();
		state.priceChart.resetSeries(false, true);
		window.setTimeout(function() {
			state.chartGranularity = resolveGranularityByViewport(state.timeframe, state.autoFitViewport);
			fetchSnapshot(true);
		}, 60);
	}

	function createPriceChart(seriesPayload, chartMeta) {
		if (typeof window.ApexCharts === 'undefined') {
			return null;
		}

		var element = getEl('forex-live-candlestick');
		if (!element) {
			return null;
		}

		return new window.ApexCharts(element, {
			chart: {
				type: 'line',
				height: 420,
				selection: {
					enabled: true,
					type: 'x',
					fill: {
						color: 'rgba(78, 163, 255, 0.22)',
						opacity: 0.18
					},
					stroke: {
						width: 1,
						dashArray: 3,
						color: '#4ea3ff',
						opacity: 0.8
					}
				},
				toolbar: {
					show: true,
					tools: {
						pan: true,
						zoom: true,
						zoomin: true,
						zoomout: true,
						reset: true
					},
					autoSelected: 'zoom'
				},
				animations: {
					enabled: false
				},
				background: 'transparent',
				zoom: {
					enabled: true,
					type: 'x',
					autoScaleYaxis: true,
					allowMouseWheelZoom: false
				},
				events: {
					mounted: function() {
						state.autoFitViewport = getAutoFitViewport(state.payload.chart || {});
					},
					beforeZoom: function(chartContext, zoomInfo) {
						var xaxis = zoomInfo && zoomInfo.xaxis ? zoomInfo.xaxis : {};
						if (xaxis.min && xaxis.max) {
							setUserViewport({
								xMin: xaxis.min,
								xMax: xaxis.max,
								yMin: null,
								yMax: null
							});
						}

						return zoomInfo;
					},
					zoomed: function(chartContext, zoomInfo) {
						var viewport = captureChartViewport();
						if (viewport) {
							setUserViewport(viewport);
						}
						queueGranularitySync();
					},
					scrolled: function() {
						var viewport = captureChartViewport();
						if (viewport) {
							setUserViewport(viewport);
						}
						queueGranularitySync();
					},
					beforeResetZoom: function() {
						resetViewportState();
						return {
							xaxis: {
								min: state.autoFitViewport ? state.autoFitViewport.xMin : undefined,
								max: state.autoFitViewport ? state.autoFitViewport.xMax : undefined
							},
							yaxis: {
								min: state.autoFitViewport ? state.autoFitViewport.yMin : undefined,
								max: state.autoFitViewport ? state.autoFitViewport.yMax : undefined
							}
						};
					}
				}
			},
			series: seriesPayload.series || [],
			stroke: {
				curve: 'smooth',
				width: seriesPayload.widths || [],
				dashArray: seriesPayload.dashArray || []
			},
			colors: seriesPayload.colors || [],
			plotOptions: {
				candlestick: {
					colors: {
						upward: '#34d399',
						downward: '#f87171'
					},
					wick: {
						useFillColor: true
					}
				}
			},
			grid: {
				borderColor: 'rgba(148, 163, 184, 0.12)',
				strokeDashArray: 2,
				xaxis: {
					lines: {
						show: true
					}
				},
				yaxis: {
					lines: {
						show: true
					}
				}
			},
			xaxis: {
				type: 'datetime',
				min: chartMeta && chartMeta.axis ? chartMeta.axis.x_min : undefined,
				max: chartMeta && chartMeta.axis ? chartMeta.axis.x_max : undefined,
				range: chartMeta && chartMeta.axis && chartMeta.axis.x_min && chartMeta.axis.x_max ? (chartMeta.axis.x_max - chartMeta.axis.x_min) : undefined,
				labels: {
					style: {
						colors: 'rgba(226, 232, 240, 0.72)'
					}
				},
				axisBorder: {
					show: false
				},
				axisTicks: {
					show: false
				},
				tooltip: {
					enabled: false
				}
			},
			yaxis: {
				min: chartMeta && chartMeta.axis ? chartMeta.axis.y_min : undefined,
				max: chartMeta && chartMeta.axis ? chartMeta.axis.y_max : undefined,
				forceNiceScale: false,
				tickAmount: 6,
				labels: {
					formatter: function(value) {
						return formatNumber(value, 4);
					},
					style: {
						colors: 'rgba(226, 232, 240, 0.72)'
					}
				}
			},
			tooltip: {
				shared: true,
				theme: 'dark'
			},
			annotations: {
				yaxis: []
			},
			noData: {
				text: 'Data chart belum tersedia'
			},
			legend: {
				show: false
			}
		});
	}

	function createRsiChart(series) {
		if (typeof window.ApexCharts === 'undefined') {
			return null;
		}

		var element = getEl('forex-live-rsi');
		if (!element) {
			return null;
		}

		return new window.ApexCharts(element, {
			chart: {
				type: 'line',
				height: 140,
				toolbar: {
					show: false
				},
				animations: {
					enabled: false
				},
				background: 'transparent'
			},
			series: [
				{
					name: 'RSI',
					data: series.rsi || []
				}
			],
			colors: ['#f97316'],
			grid: {
				borderColor: 'rgba(148, 163, 184, 0.12)',
				strokeDashArray: 2
			},
			stroke: {
				curve: 'smooth',
				width: 2
			},
			xaxis: {
				type: 'datetime',
				labels: {
					show: false
				},
				tooltip: {
					enabled: false
				}
			},
			yaxis: {
				min: 0,
				max: 100,
				tickAmount: 4,
				labels: {
					formatter: function(value) {
						return Math.round(value).toString();
					},
					style: {
						colors: 'rgba(226, 232, 240, 0.72)'
					}
				}
			},
			annotations: {
				yaxis: [
					{ y: 70, borderColor: '#ef4444', strokeDashArray: 4 },
					{ y: 30, borderColor: '#22c55e', strokeDashArray: 4 }
				]
			},
			noData: {
				text: 'RSI belum tersedia'
			},
			legend: {
				show: false
			}
		});
	}

	function renderCharts(chartPayload) {
		if (!chartPayload || !chartPayload.series) {
			return;
		}

		var seriesPayload = buildPriceSeriesPayload(chartPayload);
		var autoFitViewport = getAutoFitViewport(chartPayload);
		var activeViewport = getActiveViewport(chartPayload);
		state.chartGranularity = chartPayload.meta && chartPayload.meta.granularity ? chartPayload.meta.granularity : state.chartGranularity;
		var indicatorText = (chartPayload.indicators && chartPayload.indicators.source ? chartPayload.indicators.source : 'HLCC/4')
			+ ' | SMA(' + (chartPayload.indicators ? chartPayload.indicators.ma_period : '-') + ')'
			+ ' | BB x ' + ((chartPayload.indicators && chartPayload.indicators.bollinger) ? chartPayload.indicators.bollinger.length : 0)
			+ ' | RSI(' + (chartPayload.indicators ? chartPayload.indicators.rsi_period : '-') + ')';
		if (chartPayload.meta && chartPayload.meta.granularity_label) {
			indicatorText += ' | ' + chartPayload.meta.granularity_label;
		}
		if (chartPayload.meta && chartPayload.meta.last_rsi !== null && chartPayload.meta.last_rsi !== undefined) {
			indicatorText += ' | RSI ' + formatNumber(chartPayload.meta.last_rsi, 2);
		}

		setText('dashboard-chart-provider', 'Provider chart: ' + (chartPayload.provider || '-'));
		setText('dashboard-chart-indicator', indicatorText);
		setText('dashboard-chart-points', 'Points: ' + ((chartPayload.meta && chartPayload.meta.total_points) || (chartPayload.candles || []).length || 0));
		updateChartRangeBadge(activeViewport);
		renderIndicatorLegend(chartPayload);
		state.autoFitViewport = autoFitViewport;

		if (!state.priceChart) {
			state.priceChart = createPriceChart(seriesPayload, chartPayload.meta || {});
			if (state.priceChart) {
				state.priceChart.render();
			}
		}

		if (!state.rsiChart) {
			state.rsiChart = createRsiChart(chartPayload.series);
			if (state.rsiChart) {
				state.rsiChart.render();
			}
		}

		if (state.priceChart) {
			var chartOptions = {
				annotations: buildPriceAnnotations(chartPayload.signal_overlays || {}),
				stroke: {
					curve: 'smooth',
					width: seriesPayload.widths,
					dashArray: seriesPayload.dashArray
				},
				colors: seriesPayload.colors
			};

			if (state.interactionMode !== 'user') {
				chartOptions.xaxis = {
					type: 'datetime',
					min: autoFitViewport ? autoFitViewport.xMin : undefined,
					max: autoFitViewport ? autoFitViewport.xMax : undefined,
					range: autoFitViewport && autoFitViewport.xMin && autoFitViewport.xMax ? (autoFitViewport.xMax - autoFitViewport.xMin) : undefined,
					labels: {
						style: {
							colors: 'rgba(226, 232, 240, 0.72)'
						}
					},
					axisBorder: {
						show: false
					},
					axisTicks: {
						show: false
					},
					tooltip: {
						enabled: false
					}
				};
				chartOptions.yaxis = {
					min: autoFitViewport ? autoFitViewport.yMin : undefined,
					max: autoFitViewport ? autoFitViewport.yMax : undefined,
					forceNiceScale: false,
					tickAmount: 6,
					labels: {
						formatter: function(value) {
							return formatNumber(value, 4);
						},
						style: {
							colors: 'rgba(226, 232, 240, 0.72)'
						}
					}
				};
			}

			state.priceChart.updateOptions(chartOptions, false, true);
			state.priceChart.updateSeries(seriesPayload.series, true);
		}

		if (state.rsiChart) {
			state.rsiChart.updateSeries([
				{
					name: 'RSI',
					data: chartPayload.series.rsi || []
				}
			], true);
		}
	}

	function playAlertSound() {
		if (typeof window.AudioContext === 'undefined' && typeof window.webkitAudioContext === 'undefined') {
			return;
		}

		try {
			var AudioContextClass = window.AudioContext || window.webkitAudioContext;
			var audioContext = new AudioContextClass();
			var oscillator = audioContext.createOscillator();
			var gainNode = audioContext.createGain();

			oscillator.type = 'sine';
			oscillator.frequency.setValueAtTime(880, audioContext.currentTime);
			gainNode.gain.setValueAtTime(0.001, audioContext.currentTime);
			gainNode.gain.exponentialRampToValueAtTime(0.12, audioContext.currentTime + 0.02);
			gainNode.gain.exponentialRampToValueAtTime(0.001, audioContext.currentTime + 0.35);
			oscillator.connect(gainNode);
			gainNode.connect(audioContext.destination);
			oscillator.start(audioContext.currentTime);
			oscillator.stop(audioContext.currentTime + 0.36);
		} catch (error) {
			return;
		}
	}

	function notifyTriggeredAlerts(triggeredAlerts) {
		if (!triggeredAlerts || !triggeredAlerts.length) {
			return;
		}

		var shouldPlaySound = false;
		triggeredAlerts.forEach(function(alert) {
			if (typeof window.show_toast === 'function') {
				window.show_toast(alert.message || 'Alert forex terpenuhi', 'success');
			}

			if (parseInt(alert.with_sound || 0, 10) === 1) {
				shouldPlaySound = true;
			}
		});

		if (shouldPlaySound) {
			playAlertSound();
		}
	}

	function renderPayload(payload) {
		state.payload = payload || {};
		renderLivePrice(state.payload.live_price || {});
		renderTradingSignal(state.payload.trading_signal || {});
		renderMarketContext(
			(state.payload.trading_signal && state.payload.trading_signal.market_context) || {},
			(state.payload.trading_signal && state.payload.trading_signal.confluence) || {}
		);
		renderActiveAlerts(state.payload.active_alerts || []);
		renderCharts(state.payload.chart || {});
		notifyTriggeredAlerts(state.payload.triggered_alerts || []);
	}

	function setActiveTimeframeButton() {
		var buttons = state.app.querySelectorAll('[data-forex-timeframe]');
		buttons.forEach(function(button) {
			button.classList.toggle('active', button.getAttribute('data-forex-timeframe') === state.timeframe);
		});
	}

	function fetchSnapshot(forceRefresh) {
		if (!state.snapshotUrl || state.requestInFlight) {
			return;
		}

		state.requestInFlight = true;
		var requestUrl = state.snapshotUrl
			+ '?timeframe=' + encodeURIComponent(state.timeframe)
			+ '&granularity=' + encodeURIComponent(state.chartGranularity || '')
			+ '&force=' + (forceRefresh ? '1' : '0')
			+ '&_=' + Date.now();

		window.fetch(requestUrl, {
			method: 'GET',
			headers: {
				'X-Requested-With': 'XMLHttpRequest'
			}
		}).then(function(response) {
			if (!response.ok) {
				throw new Error('Polling dashboard gagal');
			}

			return response.json();
		}).then(function(payload) {
			renderPayload(payload);
		}).catch(function() {
			return;
		}).finally(function() {
			state.requestInFlight = false;
		});
	}

	function bindTimeframeButtons() {
		var buttons = state.app.querySelectorAll('[data-forex-timeframe]');
		buttons.forEach(function(button) {
			button.addEventListener('click', function() {
				state.timeframe = button.getAttribute('data-forex-timeframe') || '1D';
				state.chartGranularity = '';
				resetViewportState();
				setActiveTimeframeButton();
				fetchSnapshot(true);
			});
		});
	}

	/**
	 * Kontrol zoom manual tetap disediakan supaya gesture touch, wheel, dan drag
	 * punya cadangan aksi eksplisit yang mudah dipakai semua user.
	 */
	function bindChartActionButtons() {
		var buttons = state.app.querySelectorAll('[data-chart-action]');
		buttons.forEach(function(button) {
			button.addEventListener('click', function() {
				var action = button.getAttribute('data-chart-action') || '';
				if (action === 'zoom-in') {
					applyManualZoom('in');
					return;
				}

				if (action === 'zoom-out') {
					applyManualZoom('out');
					return;
				}

				if (action === 'reset-zoom') {
					resetChartZoom();
				}
			});
		});
	}

	/**
	 * Listener wheel dipasang langsung pada container chart agar desktop dapat
	 * zoom in atau zoom out tanpa harus menyentuh toolbar bawaan Apex.
	 */
	function bindWheelZoom() {
		var element = getEl('forex-live-candlestick');
		if (!element) {
			return;
		}

		element.addEventListener('wheel', function(event) {
			if (!event.ctrlKey && !event.metaKey && !event.shiftKey) {
				event.preventDefault();
			}

			applyCursorZoom(event.deltaY, event.clientX);
		}, { passive: false });
	}

	/**
	 * Event delegation dipakai untuk toggle indikator agar checkbox baru hasil
	 * render ulang tetap otomatis aktif tanpa bind manual satu per satu.
	 */
	function bindIndicatorToggles() {
		state.app.addEventListener('change', function(event) {
			var target = event.target;
			if (!target || !target.matches('[data-indicator-toggle]')) {
				return;
			}

			state.indicatorVisibility[target.getAttribute('data-indicator-toggle')] = !!target.checked;
			renderCharts(state.payload.chart || {});
		});
	}

	function startPolling() {
		if (state.refreshTimer) {
			window.clearInterval(state.refreshTimer);
		}

		// Interval 30 detik mengikuti requirement agar harga terasa realtime
		// sambil tetap menghormati cache backend dan limit request provider.
		state.refreshTimer = window.setInterval(function() {
			fetchSnapshot(false);
		}, 30000);
	}

	function init() {
		state.app = getEl('forex-dashboard-app');
		if (!state.app) {
			return;
		}

		state.snapshotUrl = state.app.getAttribute('data-snapshot-url') || '';
		state.timeframe = state.app.getAttribute('data-default-timeframe') || '1D';
		state.payload = parseBootstrapPayload();
		state.chartGranularity = state.payload && state.payload.chart && state.payload.chart.meta ? (state.payload.chart.meta.granularity || '') : '';

		bindTimeframeButtons();
		bindChartActionButtons();
		bindIndicatorToggles();
		bindWheelZoom();
		setActiveTimeframeButton();
		resetViewportState();
		renderPayload(state.payload);
		startPolling();
	}

	document.addEventListener('DOMContentLoaded', init);
})();
