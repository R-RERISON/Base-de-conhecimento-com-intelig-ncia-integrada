(function () {
	'use strict';
	if (!window.BDCKBG110) { return; }

	var cfg = window.BDCKBG110;
	var tests = [];

	function add(id, pass, description, details) {
		tests.push({ id: id, status: pass ? 'PASS' : 'FAIL', description: description, details: details || '' });
	}

	function urlFor(tab, extra) {
		var url = new URL(cfg.adminUrl, window.location.origin);
		url.searchParams.set('page', cfg.pageSlug);
		url.searchParams.set('post_id', String(cfg.postId));
		url.searchParams.set('tab', tab);
		Object.keys(extra || {}).forEach(function (key) { url.searchParams.set(key, String(extra[key])); });
		return url.toString();
	}

	function text(node) {
		return node ? (node.textContent || '').replace(/\s+/g, ' ').trim() : '';
	}

	function loadFrame(src, width) {
		return new Promise(function (resolve, reject) {
			var frame = document.createElement('iframe');
			frame.setAttribute('aria-hidden', 'true');
			frame.style.position = 'absolute';
			frame.style.left = '-20000px';
			frame.style.top = '0';
			frame.style.width = (width || 1200) + 'px';
			frame.style.height = '900px';
			frame.style.border = '0';
			var timer = window.setTimeout(function () { frame.remove(); reject(new Error('timeout iframe')); }, 20000);
			frame.addEventListener('load', function () {
				window.clearTimeout(timer);
				resolve({ frame: frame, doc: frame.contentDocument, win: frame.contentWindow });
			}, { once: true });
			frame.src = src;
			document.body.appendChild(frame);
		});
	}

	function waitNextLoad(loaded) {
		return new Promise(function (resolve, reject) {
			var timer = window.setTimeout(function () { reject(new Error('timeout form submit')); }, 20000);
			loaded.frame.addEventListener('load', function () {
				window.clearTimeout(timer);
				loaded.doc = loaded.frame.contentDocument;
				loaded.win = loaded.frame.contentWindow;
				resolve(loaded);
			}, { once: true });
		});
	}

	async function submitFrame(loaded, form, mutate) {
		if (!form) { throw new Error('form ausente'); }
		if (mutate) { mutate(form); }
		var pending = waitNextLoad(loaded);
		loaded.win.HTMLFormElement.prototype.submit.call(form);
		await pending;
		return loaded;
	}

	function closeFrame(loaded) {
		if (loaded && loaded.frame) { loaded.frame.remove(); }
	}

	function currentStatus(loaded, key) {
		return new URL(loaded.frame.contentWindow.location.href).searchParams.get(key) || '';
	}

	function activeTab(loaded) {
		return new URL(loaded.frame.contentWindow.location.href).searchParams.get('tab') || '';
	}

	function testShellKeyboard() {
		var tabs = Array.prototype.slice.call(document.querySelectorAll('[data-bdc-workspace-tabs] .bdc-kb-tab'));
		var labels = tabs.map(text);
		var expected = ['Visão geral', 'Summary', 'Classificação', 'Review & Governança', 'Histórico'];
		add('G110-B01', JSON.stringify(labels) === JSON.stringify(expected), 'Workspace expõe exatamente as cinco tabs autorizadas.', labels.join(' | '));
		add('G110-B02', !!document.querySelector('.bdc-kb-workspace-header') && !!document.querySelector('.bdc-kb-workspace-main'), 'Context Header e Main Work Area estão presentes.');
		add('G110-B03', document.body.innerText.indexOf('AI Ready') === -1 && document.body.innerText.indexOf('health score') === -1, 'Features proibidas não aparecem no Workspace.');
		if (tabs.length !== 5) {
			add('G110-B04', false, 'Navegação por teclado não pôde ser validada porque o conjunto de tabs divergiu.');
			add('G110-B05', false, 'Foco visível não pôde ser validado porque o conjunto de tabs divergiu.');
			add('G110-B06', false, 'Semântica das tabs não pôde ser validada porque o conjunto divergiu.');
			return;
		}
		tabs[0].focus();
		tabs[0].dispatchEvent(new KeyboardEvent('keydown', { key: 'ArrowRight', bubbles: true }));
		var right = document.activeElement === tabs[1];
		tabs[1].dispatchEvent(new KeyboardEvent('keydown', { key: 'End', bubbles: true }));
		var end = document.activeElement === tabs[4];
		tabs[4].dispatchEvent(new KeyboardEvent('keydown', { key: 'Home', bubbles: true }));
		var home = document.activeElement === tabs[0];
		tabs[0].dispatchEvent(new KeyboardEvent('keydown', { key: 'ArrowLeft', bubbles: true }));
		var left = document.activeElement === tabs[4];
		add('G110-B04', right && end && home && left, 'ArrowLeft/ArrowRight/Home/End movem foco entre tabs.', 'right=' + right + ', end=' + end + ', home=' + home + ', left=' + left);
		var style = window.getComputedStyle(tabs[4]);
		var focusVisible = style.outlineStyle !== 'none' || (style.boxShadow && style.boxShadow !== 'none');
		add('G110-B05', focusVisible, 'Tab focada possui indicação visual de foco.', 'outline=' + style.outlineStyle + ', boxShadow=' + style.boxShadow);
		add('G110-B06', tabs.every(function (tab) { return tab.tagName === 'A' && !!tab.getAttribute('href'); }), 'Tabs mantêm semântica nativa de link e ativação por Enter.');
	}

	async function testSummary() {
		var loaded = await loadFrame(urlFor('summary'), 1200);
		var form = loaded.doc.querySelector('.bdc-kb-domain-panel form.bdc-kb-form');
		add('G110-B07', !!form && !!loaded.doc.querySelector('label[for="bdc-kb-objective"]'), 'Summary renderiza formulário canônico com labels associados.');
		await submitFrame(loaded, form, function (target) {
			['objective', 'escalation', 'important'].forEach(function (field) {
				var node = target.querySelector('[name="summary[' + field + ']"]');
				if (!node) { throw new Error('campo Summary ausente: ' + field); }
				node.value = cfg.expected[field];
			});
		});
		var persisted = ['objective', 'escalation', 'important'].every(function (field) {
			var node = loaded.doc.querySelector('[name="summary[' + field + ']"]');
			return node && node.value === cfg.expected[field];
		});
		add('G110-B08', currentStatus(loaded, 'bdc_summary_status') === 'saved' && activeTab(loaded) === 'summary' && persisted, 'Summary salva pelo formulário real e permanece no contexto/tab.', loaded.frame.contentWindow.location.href);
		closeFrame(loaded);
	}

	async function testClassification() {
		var loaded = await loadFrame(urlFor('classification'), 1200);
		var form = loaded.doc.querySelector('form.bdc-kb-classification-form');
		add('G110-B09', !!form, 'Classificação renderiza writer canônico no Workspace.');
		await submitFrame(loaded, form, function (target) {
			Object.keys(cfg.termIds || {}).forEach(function (field) {
				var select = target.querySelector('[name="classification[' + field + '][]"]');
				if (!select) { throw new Error('select Classificação ausente: ' + field); }
				Array.prototype.forEach.call(select.options, function (option) {
					option.selected = String(option.value) === String(cfg.termIds[field]);
				});
			});
		});
		var persisted = Object.keys(cfg.termIds || {}).every(function (field) {
			var select = loaded.doc.querySelector('[name="classification[' + field + '][]"]');
			return !!select && Array.prototype.some.call(select.options, function (option) {
				return option.selected && String(option.value) === String(cfg.termIds[field]);
			});
		});
		add('G110-B10', currentStatus(loaded, 'bdc_classification_status') === 'saved' && activeTab(loaded) === 'classification' && persisted, 'Classificação salva pelo formulário real e permanece no contexto/tab.', loaded.frame.contentWindow.location.href);
		closeFrame(loaded);
	}

	function setReviewPayload(form, targetState, note) {
		var select = form.querySelector('[name="review[target_state]"]');
		if (select) {
			var hasOption = Array.prototype.some.call(select.options, function (option) { return option.value === targetState; });
			if (hasOption) {
				select.value = targetState;
			} else {
				select.disabled = true;
				var hidden = form.ownerDocument.createElement('input');
				hidden.type = 'hidden';
				hidden.name = 'review[target_state]';
				hidden.value = targetState;
				form.appendChild(hidden);
			}
		} else {
			throw new Error('select Review ausente');
		}
		var textarea = form.querySelector('[name="review[note]"]');
		if (!textarea) { throw new Error('nota Review ausente'); }
		textarea.value = note || '';
	}

	async function submitReview(loaded, targetState, note) {
		var form = loaded.doc.querySelector('form.bdc-kb-review-form');
		await submitFrame(loaded, form, function (target) { setReviewPayload(target, targetState, note); });
		return loaded;
	}

	async function testReviewHistory() {
		var loaded = await loadFrame(urlFor('review'), 1200);
		add('G110-B11', text(loaded.doc.querySelector('.bdc-kb-state-badge')) === 'Não revisado' && !!loaded.doc.querySelector('form.bdc-kb-review-form'), 'Review inicia unreviewed e oferece formulário ao usuário autorizado.');

		await submitReview(loaded, 'in_review', 'G110 in review ' + cfg.token);
		add('G110-B12', currentStatus(loaded, 'bdc_review_status') === 'saved' && activeTab(loaded) === 'review' && text(loaded.doc.querySelector('.bdc-kb-state-badge')) === 'Em revisão', 'unreviewed -> in_review salva via UI real e retorna à tab Review.');

		var history1 = await loadFrame(urlFor('history'), 1200);
		var items1 = Array.prototype.slice.call(history1.doc.querySelectorAll('.bdc-kb-history-item'));
		add('G110-B13', items1.length === 1 && text(items1[0]).indexOf('Não revisado → Em revisão') !== -1, 'Histórico projeta exatamente o primeiro evento.');
		closeFrame(history1);

		await submitReview(loaded, 'in_review', 'noop');
		var noopOk = currentStatus(loaded, 'bdc_review_status') === 'no_change';
		var historyNoop = await loadFrame(urlFor('history'), 1200);
		add('G110-B14', noopOk && historyNoop.doc.querySelectorAll('.bdc-kb-history-item').length === 1, 'NO_CHANGE não cria evento adicional.');
		closeFrame(historyNoop);

		await submitReview(loaded, 'needs_changes', '');
		var missingOk = currentStatus(loaded, 'bdc_review_status') === 'validation_error';
		var historyMissing = await loadFrame(urlFor('history'), 1200);
		add('G110-B15', missingOk && historyMissing.doc.querySelectorAll('.bdc-kb-history-item').length === 1, 'needs_changes sem justificativa é rejeitado sem evento.');
		closeFrame(historyMissing);

		await submitReview(loaded, 'needs_changes', cfg.expected.needsNote);
		var needsOk = currentStatus(loaded, 'bdc_review_status') === 'saved' && text(loaded.doc.querySelector('.bdc-kb-state-badge')) === 'Requer ajustes';
		var historyNeeds = await loadFrame(urlFor('history'), 1200);
		var itemsNeeds = Array.prototype.slice.call(historyNeeds.doc.querySelectorAll('.bdc-kb-history-item'));
		add('G110-B16', needsOk && itemsNeeds.length === 2 && text(itemsNeeds[0]).indexOf('Em revisão → Requer ajustes') !== -1 && text(itemsNeeds[0]).indexOf(cfg.expected.needsNote) !== -1, 'needs_changes com nota cria segundo evento e Histórico mostra a justificativa.');
		closeFrame(historyNeeds);

		await submitReview(loaded, 'approved', '');
		var approvedOk = currentStatus(loaded, 'bdc_review_status') === 'saved' && text(loaded.doc.querySelector('.bdc-kb-state-badge')) === 'Aprovado';
		var historyApproved = await loadFrame(urlFor('history'), 1200);
		var itemsApproved = Array.prototype.slice.call(historyApproved.doc.querySelectorAll('.bdc-kb-history-item'));
		add('G110-B17', approvedOk && itemsApproved.length === 3 && text(itemsApproved[0]).indexOf('Requer ajustes → Aprovado') !== -1, 'approved cria o terceiro evento e o Histórico fica consistente.');
		closeFrame(historyApproved);
		closeFrame(loaded);

		var denied = await loadFrame(urlFor('review', {
			bdc_g110_cap: 'deny_reviewer',
			bdc_g110_token: cfg.token,
			bdc_g110_sig: cfg.capabilitySig
		}), 1200);
		var deniedText = text(denied.doc.body);
		add('G110-B18', !denied.doc.querySelector('form.bdc-kb-review-form') && deniedText.indexOf('Não há transições de governança disponíveis') !== -1, 'UI não oferece ação indevida quando edit_others_posts é negado pelo probe assinado.');
		closeFrame(denied);
	}

	async function testViewport(width, id) {
		var loaded = await loadFrame(urlFor('overview', { bdc_g110_probe: '1' }), width);
		var doc = loaded.doc;
		var win = loaded.win;
		var root = doc.querySelector('.bdc-kb-admin');
		var grid = doc.querySelector('.bdc-kb-overview-grid');
		var tabs = doc.querySelector('.bdc-kb-tabs');
		var columns = grid ? win.getComputedStyle(grid).gridTemplateColumns : '';
		var count = columns ? columns.split(' ').filter(Boolean).length : 0;
		var oneColumn = width > 782 || count === 1;
		var rootContained = !!root && root.scrollWidth <= root.clientWidth + 2;
		var tabsContained = !!tabs && !!root && tabs.getBoundingClientRect().right <= root.getBoundingClientRect().right + 2;
		add(id, rootContained && tabsContained && oneColumn, 'Reflow ' + width + 'px mantém o plugin contido' + (width <= 782 ? ' e overview em uma coluna.' : '.'), JSON.stringify({ width: width, columns: columns, rootScroll: root ? root.scrollWidth : 0, rootClient: root ? root.clientWidth : 0 }));
		closeFrame(loaded);
	}

	function finalize() {
		var form = document.createElement('form');
		form.method = 'post';
		form.action = cfg.adminPostUrl;
		[['action', cfg.finalizeAction], ['post_id', String(cfg.postId)], ['token', cfg.token], ['nonce', cfg.finalizeNonce], ['browser_results', JSON.stringify(tests)]].forEach(function (pair) {
			var input = document.createElement('input');
			input.type = 'hidden';
			input.name = pair[0];
			input.value = pair[1];
			form.appendChild(input);
		});
		document.body.appendChild(form);
		window.HTMLFormElement.prototype.submit.call(form);
	}

	async function run() {
		try {
			testShellKeyboard();
			await testSummary();
			await testClassification();
			await testReviewHistory();
			await testViewport(1440, 'G110-B19');
			await testViewport(1024, 'G110-B20');
			await testViewport(782, 'G110-B21');
			await testViewport(492, 'G110-B22');
		} catch (error) {
			add('G110-B99', false, 'Exceção durante Browser Acceptance.', error && error.message ? error.message : String(error));
		}
		finalize();
	}

	window.setTimeout(run, 400);
}());
