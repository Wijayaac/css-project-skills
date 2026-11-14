/* Glacier West – Careers page: location filter for [gw_careers], Apply Now → form with the position preselected,
   resume upload drop zone. No dependencies; everything degrades to plain links / native controls without JS. */
(function () {
	'use strict';
	var cfg = window.gwCareers || {};
	var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
	function all(sel, root) { return Array.prototype.slice.call((root || document).querySelectorAll(sel)); }

	/* ---- location filter (buttons with aria-pressed) ---- */
	function initList(box) {
		var tabs = all('.gw-careers__tab', box), jobs = all('.gw-job', box);
		var empty = box.querySelector('.gw-careers__empty'), name = box.querySelector('[data-gw-empty-name]');
		tabs.forEach(function (tab) {
			tab.addEventListener('click', function () {
				var f = tab.getAttribute('data-filter') || '', shown = [], anyOpen = false;
				tabs.forEach(function (t) { var on = t === tab; t.classList.toggle('is-active', on); t.setAttribute('aria-pressed', on ? 'true' : 'false'); });
				jobs.forEach(function (j) {
					var ok = !f || (' ' + (j.getAttribute('data-locations') || '') + ' ').indexOf(' ' + f + ' ') > -1;
					j.hidden = !ok;
					if (ok) { shown.push(j); if (j.open) { anyOpen = true; } }
				});
				if (shown.length && !anyOpen) { shown[0].open = true; }
				if (empty) { empty.hidden = shown.length > 0; }
				if (name) { name.textContent = tab.textContent.trim(); }
			});
		});
	}
	all('[data-gw-careers]').forEach(initList);

	/* ---- Apply Now → scroll to the form and preselect ---- */
	function field(name) {
		return document.querySelector('.gw-apply-form [name="form_fields[' + name + ']"]') || document.querySelector('[name="form_fields[' + name + ']"]');
	}
	function choose(sel, value) {
		if (!sel || !value || !sel.options) { return; }
		for (var i = 0; i < sel.options.length; i++) {
			if (sel.options[i].value === value) {
				sel.selectedIndex = i;
				sel.dispatchEvent(new Event('change', { bubbles: true }));
				return;
			}
		}
	}
	function preselect(link) {
		choose(field('position'), link.getAttribute('data-gw-position'));
		choose(field('location'), link.getAttribute('data-gw-location'));
	}
	document.addEventListener('click', function (e) {
		var link = e.target.closest ? e.target.closest('[data-gw-apply]') : null;
		if (!link) { return; }
		var target = document.querySelector(link.getAttribute('data-gw-apply') || '#apply');
		if (!target) { return; } // no form on this page: let the link navigate
		e.preventDefault();
		preselect(link);
		target.scrollIntoView({ behavior: reduce ? 'auto' : 'smooth', block: 'start' });
		var first = field('first_name') || target.querySelector('input:not([type=hidden]), select, textarea');
		if (first) { window.setTimeout(function () { first.focus({ preventScroll: true }); }, reduce ? 0 : 700); }
		if (window.history && window.history.replaceState && window.URL) {
			var u = new URL(window.location.href);
			u.searchParams.set('position', link.getAttribute('data-gw-slug') || '');
			u.hash = link.getAttribute('data-gw-apply') || '#apply';
			window.history.replaceState(null, '', u.toString());
		}
	});
	// ?position=slug on load (covers pages served from a full-page cache, where the PHP preselect may be stale)
	try {
		var slug = new URLSearchParams(window.location.search).get('position');
		if (slug) {
			var link = document.querySelector('[data-gw-apply][data-gw-slug="' + slug.replace(/[^a-z0-9-]/gi, '') + '"]');
			if (link) { preselect(link); }
		}
	} catch (err) { /* old browser: PHP preselect still applies */ }

	/* ---- resume upload: drop zone around the native file input ---- */
	all('.gw-apply-form .elementor-field-type-upload').forEach(function (group) {
		var input = group.querySelector('input[type="file"]');
		if (!input || group.querySelector('.gw-upload')) { return; }
		var title = cfg.uploadTitle || 'Upload your resume', hint = cfg.uploadHint || 'PDF or DOC, up to 10 MB';
		var zone = document.createElement('span');
		zone.className = 'gw-upload';
		zone.innerHTML = '<span class="gw-upload__icon" aria-hidden="true"></span><span class="gw-upload__text" aria-hidden="true">' +
			'<span class="gw-upload__title"></span><span class="gw-upload__hint"></span></span>';
		zone.querySelector('.gw-upload__title').textContent = title;
		zone.querySelector('.gw-upload__hint').textContent = hint;
		input.parentNode.insertBefore(zone, input);
		zone.appendChild(input);
		group.classList.add('gw-has-upload');
		var label = zone.querySelector('.gw-upload__title');
		function sync() {
			var f = input.files && input.files.length ? input.files[0] : null;
			label.textContent = f ? f.name : title;
			group.classList.toggle('is-filled', !!f);
		}
		input.addEventListener('change', sync);
		['dragenter', 'dragover'].forEach(function (ev) { input.addEventListener(ev, function () { group.classList.add('is-drag'); }); });
		['dragleave', 'drop'].forEach(function (ev) { input.addEventListener(ev, function () { group.classList.remove('is-drag'); }); });
		if (input.form) { input.form.addEventListener('reset', function () { window.setTimeout(sync, 0); }); }
	});
})();
