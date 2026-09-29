(function(){
	'use strict';

	var cfg = window.BDCG590 || {};
	var root = document.getElementById('bdc-g590-runner');
	if (!root) {
		return;
	}

	var start = root.querySelector('[data-bdc-g590-start]');
	var restart = root.querySelector('[data-bdc-g590-restart]');
	var statusEl = root.querySelector('[data-bdc-g590-status]');
	var phaseEl = root.querySelector('[data-bdc-g590-phase]');
	var progressEl = root.querySelector('[data-bdc-g590-progress]');
	var detailEl = root.querySelector('[data-bdc-g590-detail]');
	var download = root.querySelector('[data-bdc-g590-download]');
	var errorBox = root.querySelector('[data-bdc-g590-error]');
	var running = false;

	function render(state) {
		if (!state) {
			return;
		}

		statusEl.textContent = state.status_label || state.status || '';
		phaseEl.textContent = state.phase_label || state.phase || '';
		progressEl.value = Number(state.progress || 0);
		detailEl.textContent = state.detail || '';

		if (state.download_url) {
			download.href = state.download_url;
			download.hidden = false;
		} else {
			download.hidden = true;
		}

		start.disabled = running || state.status === 'complete';
		restart.disabled = running;
	}

	function showError(message) {
		errorBox.style.display = 'block';
		errorBox.querySelector('p').textContent = message;
	}

	function clearError() {
		errorBox.style.display = 'none';
		errorBox.querySelector('p').textContent = '';
	}

	function delay(ms) {
		return new Promise(function(resolve) {
			window.setTimeout(resolve, ms);
		});
	}

	async function request(action, extra) {
		var body = new URLSearchParams();
		body.set('action', action);
		body.set('nonce', cfg.nonce || '');

		Object.keys(extra || {}).forEach(function(key) {
			body.set(key, String(extra[key]));
		});

		var response = await fetch(cfg.ajaxUrl, {
			method: 'POST',
			credentials: 'same-origin',
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
			},
			body: body.toString()
		});

		var payload;
		try {
			payload = await response.json();
		} catch (error) {
			throw new Error('Resposta HTTP não-JSON (' + response.status + ').');
		}

		if (!response.ok || !payload || payload.success !== true) {
			var message = payload && payload.data && payload.data.message
				? payload.data.message
				: 'Falha HTTP ' + response.status;
			throw new Error(message);
		}

		return payload.data || {};
	}

	async function recover(jobId) {
		try {
			var state = await request(cfg.actions.status, { job_id: jobId || '' });
			render(state);

			if (state.status === 'running') {
				await delay(1200);
				return cycle(state.job_id);
			}

			return state;
		} catch (error) {
			showError(
				'Conexão interrompida. A execução é resumível; use “Iniciar / Retomar G-590”. ' +
				error.message
			);
			running = false;
			render(cfg.initial || {});
			return null;
		}
	}

	async function cycle(jobId) {
		running = true;
		clearError();

		try {
			while (true) {
				var state = await request(cfg.actions.step, { job_id: jobId });
				render(state);

				if (state.status !== 'running') {
					running = false;
					render(state);
					return state;
				}

				if (state.busy) {
					await delay(1000);
				} else {
					await delay(120);
				}
			}
		} catch (error) {
			showError(
				'A requisição atual não concluiu no navegador. Consultando estado persistido para retomar sem duplicar trabalho. ' +
				error.message
			);
			return recover(jobId);
		}
	}

	async function begin(forceRestart) {
		if (running) {
			return;
		}

		running = true;
		clearError();
		render(cfg.initial || {});

		try {
			var state = await request(cfg.actions.start, {
				restart: forceRestart ? '1' : '0'
			});
			cfg.initial = state;
			render(state);

			if (state.status === 'running') {
				return cycle(state.job_id);
			}

			running = false;
			render(state);
			return state;
		} catch (error) {
			running = false;
			showError(error.message);
			render(cfg.initial || {});
			return null;
		}
	}

	start.addEventListener('click', function() {
		begin(false);
	});

	restart.addEventListener('click', function() {
		begin(true);
	});

	render(cfg.initial || {});
})();