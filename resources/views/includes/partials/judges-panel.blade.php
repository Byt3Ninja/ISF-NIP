{{--
	GV-9570: the one "Judges on this panel" list behind every judges-cell stack.

	Tooltips need hover, so on phones the stack showed nothing. Tapping (or
	clicking) a stack now opens this offcanvas: a bottom sheet below 768px, a
	side panel above. It is filled from the button's data-judges JSON, so a
	single instance serves every row -- including DataTables Responsive's
	copied child-row cells, which is why the click handler is delegated.

	Include once per page that renders includes.partials.judges-cell.
--}}
<div class="offcanvas offcanvas-end judges-panel" tabindex="-1" id="judgesPanel"
	aria-labelledby="judgesPanelTitle"
	data-count-template="{{ __('dashboard.judges_count') }}">
	<div class="offcanvas-header">
		<div class="me-3">
			<h5 class="offcanvas-title mb-1" id="judgesPanelTitle">{{ __('dashboard.Judges on this panel') }}</h5>
			<small class="text-muted" data-judges-count></small>
		</div>
		<button type="button" class="btn-close text-reset flex-shrink-0" data-bs-dismiss="offcanvas" aria-label="{{ __('admin.Close') }}"></button>
	</div>
	<div class="offcanvas-body">
		<ul class="list-unstyled mb-0" data-judges-list></ul>
	</div>
</div>

<style>
	/* The stack is a button: reset it, and give it a 44px touch target without
	   changing the row height (the negative margin cancels the padding). */
	.judges-stack {
		display: inline-flex; align-items: center; gap: .25rem;
		min-height: 44px; margin: -.375rem -.5rem; padding: .375rem .5rem;
		border: 0; border-radius: 999px; background: transparent; color: inherit;
		cursor: pointer; transition: background-color .15s ease;
	}
	.judges-stack:focus-visible { outline: 2px solid var(--bs-primary); outline-offset: 2px; }
	.judges-stack__chevron { display: none; font-size: 1rem; color: var(--bs-secondary-color); }
	html[dir="rtl"] .judges-stack__chevron { transform: scaleX(-1); }

	@media (hover: hover) {
		.judges-stack:hover { background-color: rgba(var(--bs-primary-rgb), .06); }
	}
	/* Touch screens have no hover to hint at the list: a quiet outline and a
	   chevron mark the stack as something to tap. */
	@media (hover: none) {
		.judges-stack { box-shadow: inset 0 0 0 1px var(--bs-border-color); padding-inline-end: .375rem; }
		.judges-stack__chevron { display: inline-block; }
	}

	.judges-panel { --bs-offcanvas-width: 380px; }
	.judges-panel.offcanvas-bottom {
		--bs-offcanvas-height: auto;
		max-height: min(80vh, 36rem);
		border-radius: 1.25rem 1.25rem 0 0;
		padding-bottom: env(safe-area-inset-bottom);
	}
	.judges-panel .offcanvas-header { align-items: flex-start; border-bottom: 1px solid var(--bs-border-color); }
	.judges-panel .offcanvas-body { padding-top: .25rem; padding-bottom: 1rem; }
	.judges-panel__item { display: flex; align-items: center; gap: .875rem; padding: .625rem 0; }
	.judges-panel__item + .judges-panel__item { border-top: 1px solid var(--bs-border-color); }
	.judges-panel__name { font-weight: 500; line-height: 1.35; color: var(--bs-heading-color); overflow-wrap: anywhere; }
</style>

<script>
	document.addEventListener('DOMContentLoaded', function () {
		var panelEl = document.getElementById('judgesPanel');
		if (!panelEl || !window.bootstrap) { return; }

		var panel = bootstrap.Offcanvas.getOrCreateInstance(panelEl);
		var listEl = panelEl.querySelector('[data-judges-list]');
		var countEl = panelEl.querySelector('[data-judges-count]');
		var phone = window.matchMedia('(max-width: 767.98px)');
		var placeholder = '/images/user-placeholder.jpeg';
		var lastTrigger = null;

		// Names on hover where hover exists. Delegated, so the stacks DataTables
		// copies into child rows get them too; touch screens skip them entirely
		// so a tap never leaves a tooltip stuck on screen.
		if (window.matchMedia('(hover: hover)').matches) {
			new bootstrap.Tooltip(document.body, { selector: '.judges-stack [data-judge-tip]', placement: 'top' });
		}

		function judgeItem(judge) {
			var li = document.createElement('li');
			li.className = 'judges-panel__item';

			var avatar = document.createElement('span');
			avatar.className = 'avatar flex-shrink-0';
			var initials = document.createElement('span');
			initials.className = 'avatar-initial rounded-circle bg-label-primary fw-semibold';
			initials.textContent = judge.initials || '';
			var img = document.createElement('img');
			img.className = 'rounded-circle position-relative object-fit-cover';
			img.alt = '';
			img.addEventListener('error', function onError() {
				img.removeEventListener('error', onError);
				img.src = placeholder;
			});
			img.src = judge.image || placeholder;
			avatar.append(initials, img);

			var name = document.createElement('span');
			name.className = 'judges-panel__name';
			name.textContent = judge.name;

			li.append(avatar, name);
			return li;
		}

		document.addEventListener('click', function (e) {
			var trigger = e.target.closest('.judges-stack');
			if (!trigger) { return; }

			var judges = [];
			try { judges = JSON.parse(trigger.getAttribute('data-judges') || '[]'); } catch (err) { judges = []; }
			if (!judges.length) { return; }

			var tipEl = e.target.closest('[data-judge-tip]');
			var tip = tipEl ? bootstrap.Tooltip.getInstance(tipEl) : null;
			if (tip) { tip.hide(); }

			listEl.replaceChildren.apply(listEl, judges.map(judgeItem));
			countEl.textContent = panelEl.getAttribute('data-count-template').replace(':count', judges.length);
			panelEl.classList.toggle('offcanvas-bottom', phone.matches);
			panelEl.classList.toggle('offcanvas-end', !phone.matches);

			lastTrigger = trigger;
			panel.show(trigger);
		});

		// Hand focus back to the stack that opened the list.
		panelEl.addEventListener('hidden.bs.offcanvas', function () {
			if (lastTrigger && document.body.contains(lastTrigger)) { lastTrigger.focus({ preventScroll: true }); }
		});

		// A sheet opened on a phone would sit wrong after rotating to a wide screen.
		var closeOnResize = function () { panel.hide(); };
		if (phone.addEventListener) { phone.addEventListener('change', closeOnResize); } else { phone.addListener(closeOnResize); }
	});
</script>
