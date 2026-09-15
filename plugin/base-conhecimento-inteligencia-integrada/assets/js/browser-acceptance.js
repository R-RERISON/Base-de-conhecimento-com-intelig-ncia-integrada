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
			var timer = window.setTimeout(function () { frame.remove(); reject(new Error('timeout iframe')); }, 15000);
			frame.addEventListener('load', function () {
				window.clearTimeout(timer);
				resolve({ frame: frame, doc: frame.contentDocument, win: frame.contentWindow });
			}, { once: true });
			frame.src = src;
			document.body.appendChild(frame);
		});
	}

	function closeFrame(loaded) {
		if (loaded && loaded.frame) { loaded.frame.remove(); }
	}

	function testShellKeyboard() {
		var tabs = Array.prototype.slice.call(document.querySelectorAll('[data-bdc-workspace-tabs] .bdc-kb-tab'));
		var labels = tabs.map(text);
		var expected = ['Visão geral', 'Summary', 'Classificação', 'Review & Governança', 'Histórico'];
		add('G110-B01', JSON.stringify(labels) === JSON.stringify(expected), 'Workspace expõe exatamente as cinco tabs autorizadas.', labels.join(' | '));
		add('G110-B02', !!document.querySelector('.bdc-kb-workspace-header') && !!document.querySelector('.bdc-kb-workspace-main'), 'Context Header e Main Work Area estão presentes.');
		add('G110-B03', document.body.innerText.indexOf('AI Ready') === -1 && document.body.innerText.indexOf('health score') === -1, 'Features proibidas não aparecem no Workspace.');
		if (tabs.length !== 5) { return; }
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
		add('G110-B05', style.outlineStyle !== 'none' || style.boxShadow !== 'none', 'Tab focada possui indicação visual de foco.', 'outline=' + style.outlineStyle + ', boxShadow=' + style.boxShadow);
		add('G110-B06', tabs.every(function (tab) { return tab.tagName === 'A' && !!tab.getAttribute('href'); }), 'Tabs mantêm semântica nativa de link e ativação por Enter.');
	}

	async function testDomainProjections() {
		var summary = await loadFrame(urlFor('summary'), 1200);
		var sdoc = summary.doc;
		var summaryOk = ['objective', 'escalation', 'important'].every(function (field) {
			var node = sdoc.querySelector('[name="summary[' + field + ']"]');
			return node && node.value === cfg.expected[field];
		});
		add('G110-B07', summaryOk, 'Summary renderiza os valores gravados pelo writer real.');
		add('G110-B08', !!sdoc.querySelector('form.bdc-kb-form') && !!sdoc.querySelector('label[for="bdc-kb-objective"]'), 'Summary mantém formulário e labels associados.');
		closeFrame(summary);

		var classification = await loadFrame(urlFor('classification'), 1200);
		var cdoc = classification.doc;
		var classOk = Object.keys(cfg.termIds || {}).every(function (field) {
			var select = cdoc.querySelector('[name="classification[' + field + '][]"]');
			return !!select && Array.prototype.some.call(select.options, function (option) { return option.selected && String(option.value) === String(cfg.termIds[field]); });
		});
		add('G110-B09', classOk, 'Classificação renderiza os termos gravados pelo writer real.');
		add('G110-B10', !!cdoc.querySelector('form.bdc-kb-classification-form'), 'Classificação mantém writer no contexto do Workspace.');
		closeFrame(classification);

		var review = await loadFrame(urlFor('review'), 1200);
		var rdoc = review.doc;
		add('G110-B11', text(rdoc.querySelector('.bdc-kb-state-badge')) === 'Aprovado', 'Review projeta o estado canônico final approved.');
		add('G110-B12', !!rdoc.querySelector('form.bdc-kb-review-form'), 'Reviewer autorizado recebe transições válidas no estado approved.');
		closeFrame(review);

		var history = await loadFrame(urlFor('history'), 1200);
		var hdoc = history.doc;
		var items = Array.prototype.slice.call(hdoc.querySelectorAll('.bdc-kb-history-item'));
		var hOk = items.length === 3 && text(items[0]).indexOf('Requer ajustes → Aprovado') !== -1 && text(items[1]).indexOf('Em revisão → Requer ajustes') !== -1 && text(items[1]).indexOf(cfg.expected.needsNote) !== -1 && text(items[2]).indexOf('Não revisado → Em revisão') !== -1;
		add('G110-B13', hOk, 'Histórico read-only projeta exatamente os três eventos canônicos em ordem reversa.');
		closeFrame(history);

		var denied = await loadFrame(urlFor('review', { bdc_g110_cap: 'deny_reviewer', bdc_g110_token: cfg.token, bdc_g110_sig: cfg.capabilitySig }), 1200);
		var ddoc = denied.doc;
		add('G110-B14', !ddoc.querySelector('form.bdc-kb-review-form') && text(ddoc.body).indexOf('Não há transições de governança disponíveis') !== -1, 'UI não oferece ação de reviewer quando edit_others_posts é negado por probe assinado.');
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
		var contained = !!root && root.scrollWidth <= root.clientWidth + 2 && !!tabs && tabs.getBoundingClientRect().right <= root.getBoundingClientRect().right + 2;
		add(id, contained && oneColumn, 'Reflow ' + width + 'px mantém o plugin contido' + (width <= 782 ? ' e overview em uma coluna.' : '.'), JSON.stringify({ width: width, columns: columns, rootScroll: root ? root.scrollWidth : 0, rootClient: root ? root.clientWidth : 0 }));
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
		form.submit();
	}

	async function run() {
		try {
			testShellKeyboard();
			await testDomainProjections();
			await testViewport(1440, 'G110-B15');
			await testViewport(1024, 'G110-B16');
			await testViewport(782, 'G110-B17');
			await testViewport(492, 'G110-B18');
		} catch (error) {
			add('G110-B99', false, 'Exceção durante Browser Acceptance.', error && error.message ? error.message : String(error));
		}
		finalize();
	}

	window.setTimeout(run, 300);
}());
