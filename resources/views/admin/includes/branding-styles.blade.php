{{-- Platform Branding (GV-9673). $_branding comes from the view composer (PlatformBrandingService).
     Unconfigured platform = stock theme: every derived shade below reproduces the Vuexy value for
     #7367f0 exactly (label #e9e7fd, hover #685dd8, ...), in light AND dark mode.
     Buttons are solid main color. Gradients only where the stock theme has one, with the same
     structure/opacity; with no custom gradient the stops fall back to the main color. --}}
<style id="dynamic-branding">
:root,
[data-bs-theme=light] {
  --theme-main: {{ $_branding['main'] }};
  --theme-main-rgb: {{ $_branding['main_rgb'] }};
  --theme-btn-text: {{ $_branding['button_text'] }};
  --theme-grad-start-rgb: {{ $_branding['gradient_start_rgb'] }};
  --theme-grad-end-rgb: {{ $_branding['gradient_end_rgb'] }};
  /* Card surface the label tints are mixed onto: #fff light, #2f3349 dark (see below). */
  --theme-surface: #fff;
  --theme-main-hover: color-mix(in srgb, var(--theme-main) 90%, #000);
  --theme-main-link-hover: var(--theme-main-hover);
  --theme-main-label: color-mix(in srgb, var(--theme-main) 16%, var(--theme-surface));
  --theme-main-label-hover: color-mix(in srgb, var(--theme-main) 24%, var(--theme-surface));
  --theme-main-lighter: color-mix(in srgb, var(--theme-main) 8%, var(--theme-surface));
  --theme-main-border-soft: color-mix(in srgb, var(--theme-main) 40%, var(--theme-surface));
  --theme-main-text-emphasis: color-mix(in srgb, var(--theme-main) 40%, #000);
  --theme-main-shadow: rgba(var(--theme-main-rgb), .3);
  --theme-main-16: rgba(var(--theme-main-rgb), .16);
  --theme-main-12: rgba(var(--theme-main-rgb), .12);
  /* .bg-gradient-primary: stock is 45deg main -> main mixed 50% with white (#7367f0 -> #b9b3f8). */
@if ($_branding['has_gradient'])
  --theme-gradient-45: linear-gradient(45deg, {{ $_branding['gradient_start'] }}, {{ $_branding['gradient_end'] }});
@else
  --theme-gradient-45: linear-gradient(45deg, var(--theme-main), color-mix(in srgb, var(--theme-main) 50%, #fff));
@endif
  /* Active menu item: stock is 270deg main@.7 -> main (mirrored in RTL). */
  --theme-gradient-menu: linear-gradient(270deg, rgba(var(--theme-grad-start-rgb), .7) 0%, rgb(var(--theme-grad-end-rgb)) 100%);
  --theme-gradient-menu-rtl: linear-gradient(-270deg, rgba(var(--theme-grad-start-rgb), .7) 0%, rgb(var(--theme-grad-end-rgb)) 100%);
  --bs-primary: var(--theme-main);
  --bs-primary-rgb: var(--theme-main-rgb);
  --bs-link-color: var(--theme-main);
  --bs-link-color-rgb: var(--theme-main-rgb);
  --bs-link-hover-color: var(--theme-main-hover);
  --bs-primary-bg-subtle: var(--theme-main-label);
  --bs-primary-border-subtle: var(--theme-main-border-soft);
  --bs-primary-text-emphasis: var(--theme-main-text-emphasis);
}
/* Dark mode: the template customizer sets data-style="dark" (or "system" + OS preference). */
[data-style=dark] { --theme-surface: #2f3349; }
@media (prefers-color-scheme: dark) {
  [data-style=system] { --theme-surface: #2f3349; }
}

a { color: var(--theme-main); }
a:hover { color: var(--theme-main-link-hover); }

.text-primary { color: var(--theme-main) !important; }
.breadcrumb-item,
.breadcrumb-item a,
.breadcrumb-item.active a:hover,
.breadcrumb-item.active a:focus,
.bg-footer-theme,
.bg-footer-theme .footer-link,
.bg-footer-theme .footer-link:hover,
.bg-footer-theme .footer-link:focus { color: var(--theme-main); }

.text-body[href]:hover,
.text-heading[href]:hover { color: var(--theme-main-hover) !important; }
.text-paper[href]:hover { color: var(--theme-main) !important; }

.bg-primary { background-color: var(--theme-main) !important; color: var(--theme-btn-text); }
a.bg-primary:hover,
a.bg-primary:focus { background-color: var(--theme-main-hover) !important; }
.bg-gradient-primary { background-image: var(--theme-gradient-45) !important; }
.bg-primary.bg-glow { box-shadow: 0 2px 3px 0 var(--theme-main-shadow); }

.bg-label-primary,
.badge.bg-label-primary,
.btn.bg-label-primary,
.list-group-item.bg-label-primary,
span.bg-label-primary,
div.bg-label-primary {
  background-color: var(--theme-main-label) !important;
  color: var(--theme-main) !important;
}

.bg-label-hover-primary,
.badge.bg-label-hover-primary,
.btn.bg-label-hover-primary {
  background-color: var(--theme-main-label) !important;
  color: var(--theme-main) !important;
}
.bg-label-hover-primary:hover,
.bg-label-hover-primary:focus,
.badge.bg-label-hover-primary:hover,
.btn.bg-label-hover-primary:hover {
  background-color: var(--theme-main) !important;
  color: var(--theme-btn-text) !important;
}

html:not([dir=rtl]) .border-primary,
html[dir=rtl] .border-primary,
.border-primary { border-color: var(--theme-main) !important; }

.fill-primary,
.svg-illustration svg { fill: var(--theme-main); }
.text-bg-primary { color: var(--theme-btn-text) !important; }
.btn-primary .text-white { color: var(--theme-btn-text) !important; }
.link-primary { color: var(--theme-main) !important; }
.link-primary:hover,
.link-primary:focus { color: var(--theme-main-hover) !important; }
.dropdown-notifications-item:not(.mark-as-read) .dropdown-notifications-read span { background-color: var(--theme-main); }

.btn-primary {
  color: var(--theme-btn-text) !important;
  background-color: var(--theme-main) !important;
  border-color: var(--theme-main) !important;
}
.btn-primary.btn[class*=btn-]:not([class*=btn-label-]):not([class*=btn-outline-]):not([class*=btn-text-]):not(.btn-icon):not(:disabled):not(.disabled) {
  box-shadow: 0 .125rem .375rem 0 var(--theme-main-shadow);
}
.btn-primary:hover,
.btn-check:focus + .btn-primary,
.btn-primary:focus,
.btn-primary.focus,
.btn-check:checked + .btn-primary,
.btn-check:active + .btn-primary,
.btn-primary:active,
.btn-primary.active,
.btn-primary.show.dropdown-toggle,
.show > .btn-primary.dropdown-toggle {
  color: var(--theme-btn-text) !important;
  background-color: var(--theme-main-hover) !important;
  border-color: var(--theme-main-hover) !important;
}
.btn-primary.disabled,
.btn-primary:disabled {
  color: var(--theme-btn-text) !important;
  background-color: var(--theme-main) !important;
  border-color: var(--theme-main) !important;
}
.btn-group .btn-primary,
.input-group .btn-primary {
  border-right: var(--bs-border-width) solid var(--theme-main-hover) !important;
  border-left: var(--bs-border-width) solid var(--theme-main-hover) !important;
}
.btn-group-vertical .btn-primary {
  border-top-color: var(--theme-main-hover) !important;
  border-bottom-color: var(--theme-main-hover) !important;
}

.shepherd-element .shepherd-button:not(:disabled).btn-primary {
  color: var(--theme-btn-text) !important;
  background-color: var(--theme-main) !important;
  border-color: var(--theme-main) !important;
}
.shepherd-element .shepherd-button:not(:disabled).btn-primary:hover,
.btn-check:focus + .shepherd-element .shepherd-button:not(:disabled).btn-primary,
.shepherd-element .shepherd-button:not(:disabled).btn-primary:focus,
.shepherd-element .shepherd-button:not(:disabled).btn-primary.focus,
.btn-check:checked + .shepherd-element .shepherd-button:not(:disabled).btn-primary,
.btn-check:active + .shepherd-element .shepherd-button:not(:disabled).btn-primary,
.shepherd-element .shepherd-button:not(:disabled).btn-primary:active,
.shepherd-element .shepherd-button:not(:disabled).btn-primary.active,
.shepherd-element .shepherd-button:not(:disabled).btn-primary.show.dropdown-toggle,
.show > .shepherd-element .shepherd-button:not(:disabled).btn-primary.dropdown-toggle {
  color: var(--theme-btn-text) !important;
  background-color: var(--theme-main-hover) !important;
  border-color: var(--theme-main-hover) !important;
}

.btn-label-primary {
  color: var(--theme-main) !important;
  background: var(--theme-main-label) !important;
  border-color: transparent !important;
}
.btn-label-primary:hover,
.btn-label-primary:focus,
.btn-label-primary.focus,
.btn-label-primary:active,
.btn-label-primary.active,
.btn-label-primary.show.dropdown-toggle,
.show > .btn-label-primary.dropdown-toggle {
  color: var(--theme-main) !important;
  background: var(--theme-main-label-hover) !important;
  border-color: transparent !important;
}
.btn-label-primary.disabled,
.btn-label-primary:disabled {
  color: var(--theme-main) !important;
  background: var(--theme-main-label) !important;
  border-color: transparent !important;
}
.btn-group .btn-label-primary,
.input-group .btn-label-primary,
.btn-group-vertical .btn-label-primary {
  border-color: var(--theme-main-border-soft) !important;
}

.btn-text-primary,
.btn.btn-text-primary {
  color: var(--theme-main) !important;
  background: transparent !important;
  border-color: transparent !important;
}
.btn-text-primary:hover,
.btn-text-primary:focus,
.btn-text-primary.focus,
.btn.btn-text-primary:hover,
.btn.btn-text-primary:focus {
  color: var(--theme-main) !important;
  background: var(--theme-main-lighter) !important;
  border-color: transparent !important;
}
.btn-text-primary:active,
.btn-text-primary.active,
.btn-text-primary.show.dropdown-toggle,
.show > .btn-text-primary.dropdown-toggle,
.btn-check:checked + .btn-text-primary {
  color: var(--theme-main) !important;
  background: var(--theme-main-lighter) !important;
  border-color: transparent !important;
}
.btn-text-primary:disabled,
.btn-text-primary.disabled {
  color: var(--theme-main) !important;
  background: transparent !important;
  border-color: transparent !important;
}
.btn-group .btn-text-primary,
.input-group .btn-text-primary,
.btn-group-vertical .btn-text-primary {
  border-color: var(--theme-main) !important;
}

.btn-outline-primary {
  color: var(--theme-main) !important;
  border-color: var(--theme-main) !important;
  background: transparent !important;
}
.btn-outline-primary:hover,
.btn-check:focus + .btn-outline-primary,
.btn-outline-primary:focus,
.btn-check:checked + .btn-outline-primary,
.btn-check:active + .btn-outline-primary,
.btn-outline-primary:active,
.btn-outline-primary.active,
.btn-outline-primary.dropdown-toggle.show {
  color: var(--theme-main) !important;
  background-color: var(--theme-main-lighter) !important;
  border-color: var(--theme-main) !important;
}
.btn-outline-primary.disabled,
.btn-outline-primary:disabled {
  color: var(--theme-main) !important;
  border-color: var(--theme-main) !important;
}
.btn-outline-primary .badge,
.btn-outline-primary:hover .badge,
.btn-outline-primary:active .badge,
.btn-outline-primary.active .badge,
.show > .btn-outline-primary.dropdown-toggle .badge {
  background: var(--theme-main) !important;
  border-color: var(--theme-main) !important;
  color: var(--theme-btn-text) !important;
}

.waves-effect .waves-ripple {
  background: radial-gradient(rgba(var(--theme-main-rgb), .2) 0, rgba(var(--theme-main-rgb), .3) 40%, rgba(var(--theme-main-rgb), .4) 50%, rgba(var(--theme-main-rgb), .5) 60%, rgba(255,255,255,0) 70%);
}

.bg-menu-theme.menu-vertical .menu-item.active > .menu-link:not(.menu-toggle),
.menu.bg-primary.menu-vertical .menu-item.active > .menu-link:not(.menu-toggle),
.bg-menu-theme.menu-horizontal .menu-inner > .menu-item.active > .menu-link.menu-toggle,
.menu.bg-primary.menu-horizontal .menu-inner > .menu-item.active > .menu-link.menu-toggle {
  background: var(--theme-gradient-menu);
  color: var(--theme-btn-text) !important;
  box-shadow: 0 2px 6px 0 var(--theme-main-shadow);
}
.bg-menu-theme.menu-vertical .menu-item.active > .menu-link:not(.menu-toggle).menu-toggle::after,
.bg-menu-theme.menu-horizontal .menu-inner > .menu-item.active > .menu-link.menu-toggle.menu-toggle::after,
.menu.bg-primary.menu-vertical .menu-item.active > .menu-link:not(.menu-toggle).menu-toggle::after { color: var(--theme-btn-text) !important; }
.bg-menu-theme.menu-horizontal .menu-item.active > .menu-link:not(.menu-toggle) {
  background: var(--theme-main-label);
  color: var(--theme-main) !important;
}
.layout-wrapper:not(.layout-horizontal) .bg-menu-theme .menu-inner > .menu-item.active:before { background: var(--theme-main); }

.menu.bg-primary,
.menu.bg-primary.menu-horizontal,
.menu.bg-primary.menu-horizontal .menu-inner .menu-item:not(.menu-item-closing) > .menu-sub,
.menu.bg-primary.menu-horizontal .menu-inner .menu-item.open > .menu-toggle { background: var(--theme-main) !important; }
.navbar.bg-primary { background-color: rgba(var(--theme-main-rgb), .88) !important; }
.navbar.bg-primary .search-input-wrapper .search-input,
.navbar.bg-primary .search-input-wrapper .search-toggler { background-color: var(--theme-main) !important; }
.footer.bg-primary { color: var(--theme-btn-text); }
.layout-footer-fixed .layout-horizontal .footer.bg-primary,
.layout-footer-fixed .layout-wrapper:not(.layout-horizontal) .footer.bg-primary .footer-container { background-color: var(--theme-main) !important; }

.menu-vertical .menu-item.active > .menu-link:not(.menu-toggle),
.bg-menu-theme.menu-vertical .menu-item.active > .menu-link:not(.menu-toggle) {
  background: var(--theme-gradient-menu) !important;
  color: var(--theme-btn-text) !important;
  box-shadow: 0 2px 6px 0 var(--theme-main-shadow);
}
.menu-vertical .menu-item.active > .menu-link:not(.menu-toggle) .menu-icon,
.menu-vertical .menu-item.active > .menu-link:not(.menu-toggle) div,
.menu-vertical .menu-item.active > .menu-link:not(.menu-toggle)::before {
  color: var(--theme-btn-text) !important;
}
.menu-vertical .menu-item.active > .menu-link.menu-toggle::after {
  color: var(--theme-btn-text) !important;
}

.layout-wrapper:not(.layout-horizontal) .bg-menu-theme .menu-inner > .menu-item.active:before {
  background: var(--theme-main);
}

.bg-menu-theme .menu-inner .menu-sub .menu-item.active > .menu-link::before,
.menu-light .menu-inner > .menu-item.open .menu-item.active > .menu-link::before {
  color: var(--theme-btn-text) !important;
}

.bg-menu-theme.menu-horizontal .menu-inner > .menu-item.active > .menu-link.menu-toggle,
.menu-horizontal .menu-inner > .menu-item.active > .menu-link.menu-toggle {
  background: var(--theme-gradient-menu) !important;
  color: var(--theme-btn-text) !important;
  box-shadow: 0 2px 6px 0 var(--theme-main-shadow);
}
.bg-menu-theme.menu-horizontal .menu-inner > .menu-item.active > .menu-link.menu-toggle::after {
  color: var(--theme-btn-text) !important;
}
.bg-menu-theme.menu-horizontal .menu-item.active > .menu-link:not(.menu-toggle) {
  background: var(--theme-main-label);
  color: var(--theme-main) !important;
}

.menu.bg-primary.menu-vertical .menu-item.active > .menu-link:not(.menu-toggle),
.menu.bg-primary.menu-horizontal .menu-inner > .menu-item.active > .menu-link.menu-toggle {
  background: var(--theme-gradient-menu) !important;
  color: var(--theme-btn-text) !important;
}
/* RTL: the stock theme mirrors the gradient with its own [dir=rtl] !important rules. */
[dir=rtl] .menu-vertical .menu-item.active > .menu-link:not(.menu-toggle),
[dir=rtl] .bg-menu-theme.menu-vertical .menu-item.active > .menu-link:not(.menu-toggle),
[dir=rtl] .menu.bg-primary.menu-vertical .menu-item.active > .menu-link:not(.menu-toggle),
[dir=rtl] .menu-horizontal .menu-inner > .menu-item.active > .menu-link.menu-toggle,
[dir=rtl] .bg-menu-theme.menu-horizontal .menu-inner > .menu-item.active > .menu-link.menu-toggle,
[dir=rtl] .menu.bg-primary.menu-horizontal .menu-inner > .menu-item.active > .menu-link.menu-toggle {
  background: var(--theme-gradient-menu-rtl) !important;
}

.form-control:focus,
.form-select:focus { border-color: var(--theme-main) !important; box-shadow: 0 2px 6px 0 var(--theme-main-shadow); }
.input-group:focus-within { box-shadow: 0 2px 6px 0 var(--theme-main-shadow); }
.input-group:focus-within .form-control,
.input-group:focus-within .input-group-text { border-color: var(--theme-main) !important; box-shadow: none; }
.form-control:focus ~ .form-label { border-color: var(--theme-main); }
.form-floating > .form-control:focus ~ label,
.form-floating > .form-control:focus:not(:placeholder-shown) ~ label,
.form-floating > .form-select:focus ~ label,
.form-floating > .form-select:focus:not(:placeholder-shown) ~ label { color: var(--theme-main); }

.form-check-input:checked,
.form-check-input[type=checkbox]:indeterminate {
  background-color: var(--theme-main);
  border-color: var(--theme-main);
  box-shadow: 0 2px 6px 0 var(--theme-main-shadow);
}
.custom-option.checked { border: 2px solid var(--theme-main) !important; }
.custom-option.checked .custom-option-body i,
.custom-option.checked .custom-option-header i,
.custom-option-icon.checked i,
.custom-option-icon.checked svg { color: var(--theme-main); }
.custom-option.custom-option-label.checked { background-color: var(--theme-main-12); color: var(--theme-main); }
.custom-option.custom-option-label.checked .custom-option-header span,
.custom-option.custom-option-label.checked .custom-option-title { color: var(--theme-main); }
.custom-option-image.custom-option-image-check:hover .form-check-input:checked { border-color: var(--theme-main); }
.modal-add-new-address .custom-option-icon.checked svg { stroke: var(--theme-main); }

.switch-input:checked ~ .switch-toggle-slider,
.switch-primary.switch .switch-input:checked ~ .switch-toggle-slider {
  background: var(--theme-main);
  color: var(--theme-btn-text);
  box-shadow: 0 2px 6px 0 var(--theme-main-shadow);
}

.form-range::-webkit-slider-thumb,
.form-range::-moz-range-thumb { background-color: var(--theme-main); }
.form-range::-webkit-slider-thumb:hover { box-shadow: 0 0 0 8px var(--theme-main-16); }
.form-range::-moz-range-thumb:hover { box-shadow: 0 0 0 8px var(--theme-main-16); }
.form-range::-webkit-slider-thumb:active,
.form-range::-webkit-slider-thumb:focus { box-shadow: 0 0 0 13px var(--theme-main-16); background-color: var(--theme-main); border-color: var(--theme-main); }
.form-range::-moz-range-thumb:active,
.form-range::-moz-range-thumb:focus { box-shadow: 0 0 0 13px var(--theme-main-16); background-color: var(--theme-main); border-color: var(--theme-main); }
.form-range::-webkit-slider-runnable-track,
.form-range::-moz-range-track { background-color: var(--theme-main); }

.nav-pills .nav-link.active,
.nav-pills .nav-link.active:hover,
.nav-pills .nav-link.active:focus {
  background-color: var(--theme-main);
  color: var(--theme-btn-text);
  box-shadow: 0 .125rem .375rem 0 var(--theme-main-shadow);
}
.nav-pills .nav-item .nav-link:not(.active):hover { background-color: var(--theme-main-label); }
.nav-tabs .nav-link.active,
.nav-tabs .nav-link.active:hover,
.nav-tabs .nav-link.active:focus { box-shadow: 0 -2px 0 var(--theme-main) inset; color: var(--theme-main); }
.nav-align-bottom .nav-tabs .nav-link.active { box-shadow: 0 2px 0 var(--theme-main) inset; }
.nav-align-left .nav-tabs .nav-link.active { box-shadow: -2px 0 0 var(--theme-main) inset; }
.nav-align-right .nav-tabs .nav-link.active { box-shadow: 2px 0 0 var(--theme-main) inset; }
.nav-align-top .nav-tabs .nav-link:not(.active):hover,
.nav-align-bottom .nav-tabs .nav-link:not(.active):hover,
html:not([dir=rtl]) .nav-align-left .nav-tabs .nav-link:not(.active):hover,
html:not([dir=rtl]) .nav-align-right .nav-tabs .nav-link:not(.active):hover { color: var(--theme-main) !important; }
.nav-align-top .nav-tabs .nav-link:not(.active):hover { border-bottom-color: var(--theme-main-label) !important; }
.nav-align-bottom .nav-tabs .nav-link:not(.active):hover { border-top-color: var(--theme-main-label) !important; }
.nav-tabs.widget-nav-tabs .nav-link.active { border-color: var(--theme-main); }
.nav-tabs.widget-nav-tabs .nav-link.active .badge { background-color: var(--theme-main-16) !important; color: var(--theme-main) !important; }
.nav-tabs .tab-slider { background: var(--theme-main); }

.page-item .page-link:hover,
.page-item .page-link:focus,
.pagination li > a:not(.page-link):hover,
.pagination li > a:not(.page-link):focus { background-color: var(--theme-main-label); color: var(--theme-main); }
.page-item.active .page-link,
.page-item.active .page-link:hover,
.page-item.active .page-link:focus,
.pagination li.active > a:not(.page-link) {
  border-color: var(--theme-main);
  background-color: var(--theme-main);
  color: var(--theme-btn-text);
  box-shadow: 0 .125rem .375rem 0 var(--theme-main-shadow);
}
.pagination {
  --bs-pagination-active-bg: var(--theme-main);
  --bs-pagination-active-border-color: var(--theme-main);
  --bs-pagination-active-color: var(--theme-btn-text);
}
.pagination-outline-primary .page-item.active .page-link,
.pagination-outline-primary.pagination li.active > a:not(.page-link) {
  border-color: var(--theme-main) !important;
  color: var(--theme-main) !important;
  background-color: var(--theme-main-label) !important;
  background-image: none !important;
}

.progress,
.progress-stacked { --bs-progress-bar-bg: var(--theme-main); }
.progress-bar {
  background-color: var(--theme-main);
  color: var(--theme-btn-text);
  box-shadow: 0 .125rem .375rem 0 var(--theme-main-shadow);
}
.progress-bar.bg-secondary,
.progress-bar.bg-success,
.progress-bar.bg-info,
.progress-bar.bg-warning,
.progress-bar.bg-danger,
.progress-bar.bg-dark { background-image: none; }

.alert-primary { background-color: var(--theme-main-label); border-color: var(--theme-main-label); color: var(--theme-main); }
.alert-primary .alert-link,
.alert-primary hr { color: var(--theme-main) !important; }
.alert-primary .alert-icon { background-color: var(--theme-main); }
.alert-outline-primary { border-color: var(--theme-main); color: var(--theme-main); }
.alert-outline-primary .alert-link,
.alert-outline-primary hr { color: var(--theme-main) !important; }
.alert-outline-primary .alert-icon { color: var(--theme-main) !important; background-color: var(--theme-main-label) !important; }
.alert-solid-primary { background-color: var(--theme-main); color: var(--theme-btn-text); }
.alert-solid-primary .alert-icon { color: var(--theme-main) !important; }

.list-group-item-primary { border-color: var(--theme-main); background-color: var(--theme-main-label); color: var(--theme-main) !important; }
a.list-group-item-primary,
button.list-group-item-primary { color: var(--theme-main); }
a.list-group-item-primary:hover,
a.list-group-item-primary:focus,
button.list-group-item-primary:hover,
button.list-group-item-primary:focus { border-color: var(--theme-main); background-color: var(--theme-main-label-hover); color: var(--theme-main); }
a.list-group-item-primary.active,
button.list-group-item-primary.active { border-color: var(--theme-main) !important; background-color: var(--theme-main) !important; color: var(--theme-btn-text) !important; }
.list-group-item.active { background-color: var(--theme-main-label); color: var(--theme-main); }
.list-group .list-group-item.active h1,
.list-group .list-group-item.active h2,
.list-group .list-group-item.active h3,
.list-group .list-group-item.active h4,
.list-group .list-group-item.active h5,
.list-group .list-group-item.active h6 { color: var(--theme-main); }
.list-group.list-group-timeline .list-group-timeline-primary:before { border-color: var(--theme-main); background-color: var(--theme-main); }

.dropdown-item:not(.disabled).active,
.dropdown-item:not(.disabled):active,
.dropdown-menu > li:not(.disabled) > a:not(.dropdown-item):active,
.dropdown-menu > li.active:not(.disabled) > a:not(.dropdown-item) { background-color: var(--theme-main-label); color: var(--theme-main) !important; }

.table-primary { --bs-table-bg: var(--theme-main-label); }
.table { --bs-table-active-bg: rgba(var(--theme-main-rgb), .08); }
table.dataTable.dtr-column > tbody > tr > td.control:before,
table.dataTable.dtr-column > tbody > tr > th.control:before { background-color: var(--theme-main); }

.carousel-control-prev,
.carousel-control-next,
.carousel-control-prev:hover,
.carousel-control-next:hover { color: var(--theme-main); }
.modal-onboarding .carousel-indicators [data-bs-target] { background-color: var(--theme-main); }

.tooltip-primary .tooltip-inner,
.tooltip-primary > .tooltip .tooltip-inner { background: var(--theme-main); color: var(--theme-btn-text); }
.tooltip-primary.bs-tooltip-auto[data-popper-placement=top] .tooltip-arrow::before { border-top-color: var(--theme-main); }
.tooltip-primary.bs-tooltip-auto[data-popper-placement=left] .tooltip-arrow::before { border-left-color: var(--theme-main); }
.tooltip-primary.bs-tooltip-auto[data-popper-placement=bottom] .tooltip-arrow::before { border-bottom-color: var(--theme-main); }
.tooltip-primary.bs-tooltip-auto[data-popper-placement=right] .tooltip-arrow::before { border-right-color: var(--theme-main); }
.popover-primary,
.popover-primary > .popover { background: var(--theme-main); }
.popover-primary .popover-header { border-color: var(--theme-main); color: var(--theme-btn-text); }
.popover-primary.bs-popover-auto[data-popper-placement=top] > .popover-arrow::after { border-top-color: var(--theme-main) !important; }
.popover-primary.bs-popover-auto[data-popper-placement=right] > .popover-arrow::after { border-right-color: var(--theme-main) !important; }
.popover-primary.bs-popover-auto[data-popper-placement=bottom] > .popover-arrow::after { border-bottom-color: var(--theme-main) !important; }
.popover-primary.bs-popover-auto[data-popper-placement=left] > .popover-arrow::after { border-left-color: var(--theme-main) !important; }

.card.card-border-shadow-primary::after { border-bottom-color: var(--theme-main-border-soft); }
.card.card-border-shadow-primary:hover::after { border-bottom-color: var(--theme-main); }
.card.card-hover-border-primary:hover,
.card .card-hover-border-primary:hover { border-color: var(--theme-main-border-soft); }
.timeline .timeline-point-primary,
.timeline .timeline-point { background-color: var(--theme-main) !important; outline: 3px solid var(--theme-main-12); }
.timeline.timeline-outline .timeline-point-primary,
.timeline.timeline-outline .timeline-point { border: 2px solid var(--theme-main) !important; }
.timeline .timeline-indicator-primary { background-color: var(--theme-main-label); }
.timeline .timeline-indicator-primary i { color: var(--theme-main) !important; }
.divider.divider-primary.divider-vertical:before,
.divider.divider-primary.divider-vertical:after,
.divider.divider-primary .divider-text:before,
.divider.divider-primary .divider-text:after { border-color: var(--theme-main); }

.select2-container--default .select2-results__option--highlighted[aria-selected] { background-color: var(--theme-main) !important; color: var(--theme-btn-text) !important; }
.select2-container--default.select2-container--focus .select2-selection,
.select2-container--default.select2-container--open .select2-selection { border-color: var(--theme-main) !important; }
.select2-primary .select2-container--default .select2-selection--multiple .select2-selection__choice { background: var(--theme-main-16) !important; color: var(--theme-main) !important; }
.bootstrap-select .dropdown-menu.inner a[aria-selected=true] { background: var(--theme-main) !important; color: var(--theme-btn-text) !important; }
.bootstrap-select .dropdown-toggle.show { border-color: var(--theme-main); }
.tagify--focus { border-color: var(--theme-main) !important; }
.tagify__dropdown__item--active { background: var(--theme-main) !important; color: var(--theme-btn-text) !important; }
.tt-suggestion:active,
.tt-cursor { background: var(--theme-main) !important; color: var(--theme-btn-text) !important; }

.datepicker table tr td.active,
.datepicker table tr td.active.highlighted,
.datepicker table tr td .focused,
.datepicker table tr td span.active,
.datepicker table tr td span.active.disabled,
.datepicker table tr td.range-start,
.datepicker table tr td.range-end {
  background: var(--theme-main) !important;
  color: var(--theme-btn-text) !important;
  box-shadow: 0 .125rem .375rem 0 var(--theme-main-shadow);
}
.datepicker table tr td.range,
.datepicker table tr td.range.highlighted,
.datepicker table tr td.range.today { color: var(--theme-main) !important; background: var(--theme-main-label) !important; }
.datepicker table tr td.today:not(.active),
.datepicker table tr td.today:not(.active):hover { color: var(--theme-main); background-color: var(--theme-main-label); }
.flatpickr-day.today,
.flatpickr-day.today:hover { color: var(--theme-main) !important; border-color: var(--theme-main-label); background-color: var(--theme-main-label) !important; }
.flatpickr-day.inRange,
.flatpickr-day.nextMonthDay.inRange,
.flatpickr-day.prevMonthDay.inRange,
.flatpickr-day.today.inRange {
  color: var(--theme-main) !important;
  background: var(--theme-main-label) !important;
  border-color: var(--theme-main-label) !important;
}
.flatpickr-day.selected,
.flatpickr-day.selected.inRange,
.flatpickr-day.selected:focus,
.flatpickr-day.selected:hover,
.flatpickr-day.startRange,
.flatpickr-day.startRange:focus,
.flatpickr-day.startRange:hover,
.flatpickr-day.endRange,
.flatpickr-day.endRange:focus,
.flatpickr-day.endRange:hover,
.flatpickr-day.week.selected {
  color: var(--theme-btn-text) !important;
  background: var(--theme-main) !important;
  border-color: var(--theme-main) !important;
  box-shadow: 0 .125rem .375rem 0 var(--theme-main-shadow);
}
.daterangepicker td.active:not(.off),
.daterangepicker .start-date:not(.end-date):not(.off),
.daterangepicker .end-date:not(.start-date):not(.off) { background: var(--theme-main) !important; color: var(--theme-btn-text); border: 0 !important; }
.daterangepicker .input-mini.active { border-color: var(--theme-main) !important; }
.daterangepicker td.in-range:not(.start-date):not(.end-date):not(.off),
.ranges li.active { color: var(--theme-main) !important; background-color: var(--theme-main-label) !important; }
li.ui-timepicker-selected,
.ui-timepicker-list .ui-timepicker-selected:hover { color: var(--theme-btn-text) !important; background: var(--theme-main) !important; }

.ql-snow.ql-toolbar button:hover,
.ql-snow.ql-toolbar button:focus,
.ql-snow.ql-toolbar button.ql-active,
.ql-snow.ql-toolbar .ql-picker-label:hover,
.ql-snow.ql-toolbar .ql-picker-label.ql-active,
.ql-snow.ql-toolbar .ql-picker-item:hover,
.ql-snow.ql-toolbar .ql-picker-item.ql-selected { color: var(--theme-main) !important; }
.ql-snow.ql-toolbar button:hover .ql-stroke,
.ql-snow.ql-toolbar button.ql-active .ql-stroke,
.ql-snow.ql-toolbar .ql-picker-label:hover .ql-stroke,
.ql-snow.ql-toolbar .ql-picker-label.ql-active .ql-stroke,
.ql-snow.ql-toolbar .ql-picker-item:hover .ql-stroke,
.ql-snow.ql-toolbar .ql-picker-item.ql-selected .ql-stroke { stroke: var(--theme-main) !important; }
.ql-snow.ql-toolbar button:hover .ql-fill,
.ql-snow.ql-toolbar button.ql-active .ql-fill,
.ql-snow.ql-toolbar .ql-picker-label:hover .ql-fill,
.ql-snow.ql-toolbar .ql-picker-label.ql-active .ql-fill,
.ql-snow.ql-toolbar .ql-picker-item:hover .ql-fill,
.ql-snow.ql-toolbar .ql-picker-item.ql-selected .ql-fill { fill: var(--theme-main) !important; }

.swal2-progress-steps[class] .swal2-progress-step.swal2-active-progress-step,
.swal2-progress-steps[class] .swal2-progress-step-line,
.swal2-progress-steps[class] .swal2-active-progress-step,
.swal2-progress-steps[class] .swal2-progress-step { background: var(--theme-main); color: var(--theme-btn-text); }
.swal2-progress-steps[class] .swal2-progress-step.swal2-active-progress-step ~ .swal2-progress-step,
.swal2-progress-steps[class] .swal2-progress-step.swal2-active-progress-step ~ .swal2-progress-step-line { background: var(--theme-main-label); }
.swal2-styled.swal2-confirm { background-color: var(--theme-main) !important; color: var(--theme-btn-text) !important; box-shadow: 0 .125rem .375rem 0 var(--theme-main-shadow); }
.dropzone.dz-drag-hover { border-color: var(--theme-main) !important; }
.swiper-pagination-bullet.swiper-pagination-bullet-active,
.swiper-pagination.swiper-pagination-progressbar .swiper-pagination-progressbar-fill { background: var(--theme-main) !important; }
.noUi-target:not([disabled]) { background: var(--theme-main-16); }
.noUi-target:not([disabled]) .noUi-connect { background: var(--theme-main); }
.noUi-target:not([disabled]) .noUi-handle { border-color: var(--theme-main); }
.noUi-target:not([disabled]) .noUi-handle:hover { box-shadow: 0 0 0 8px var(--theme-main-16); }
.noUi-target:not([disabled]) .noUi-handle:active,
.noUi-target:not([disabled]) .noUi-handle:focus { box-shadow: 0 0 0 13px var(--theme-main-16); }
.sk-primary.sk-plane,
.sk-primary .sk-chase-dot:before,
.sk-primary .sk-bounce-dot,
.sk-primary .sk-wave-rect,
.sk-primary.sk-pulse,
.sk-primary .sk-swing-dot,
.sk-primary .sk-circle-dot:before,
.sk-primary .sk-circle-fade-dot:before,
.sk-primary .sk-grid-cube,
.sk-primary .sk-fold-cube:before { background-color: var(--theme-main); }

.bs-stepper .step.active .bs-stepper-circle { background-color: var(--theme-main); color: var(--theme-btn-text); box-shadow: 0 .125rem .375rem 0 var(--theme-main-shadow); }
.bs-stepper .step.active .bs-stepper-icon svg { fill: var(--theme-main) !important; }
.bs-stepper .step.active .bs-stepper-icon i,
.bs-stepper .step.active .bs-stepper-label { color: var(--theme-main) !important; }
.bs-stepper .step.crossed .step-trigger .bs-stepper-circle { background-color: var(--theme-main-label) !important; color: var(--theme-main) !important; }
.bs-stepper .step.crossed .step-trigger .bs-stepper-icon svg { fill: var(--theme-main) !important; }
.bs-stepper .step.crossed .step-trigger .bs-stepper-icon i,
.bs-stepper.wizard-icons .step.crossed .bs-stepper-label,
.bs-stepper.wizard-icons .step.crossed + .line i { color: var(--theme-main) !important; }

.fc .fc-event-primary:not(.fc-list-event) { background-color: var(--theme-main-label) !important; color: var(--theme-main) !important; }
.fc .fc-event-primary.fc-list-event .fc-list-event-dot { border-color: var(--theme-main) !important; }
.fc .fc-button-primary:not(.fc-prev-button):not(.fc-next-button) { background-color: var(--theme-main-16) !important; color: var(--theme-main); border: 0; }
.fc .fc-button-primary:not(.fc-prev-button):not(.fc-next-button).fc-button-active,
.fc .fc-button-primary:not(.fc-prev-button):not(.fc-next-button):hover { background-color: rgba(var(--theme-main-rgb), .24) !important; color: var(--theme-main); }

.jstree-default .jstree-wholerow-hovered,
.jstree-default .jstree-hovered,
.jstree-default-dark .jstree-wholerow-hovered,
.jstree-default-dark .jstree-hovered { background: var(--theme-main-16); color: var(--theme-main); }
.jstree-default .jstree-wholerow-clicked,
.jstree-default .jstree-clicked,
.jstree-default-dark .jstree-wholerow-clicked,
.jstree-default-dark .jstree-clicked { background: var(--theme-main); color: var(--theme-btn-text); }
.plyr--video .plyr__control.plyr__control--overlaid,
.plyr--video .plyr__control[aria-expanded=true],
.plyr--video .plyr__controls button:hover,
.plyr--audio .plyr__control[aria-expanded=true],
.plyr--audio .plyr__controls button:hover,
.plyr__play-large { background: var(--theme-main) !important; color: var(--theme-btn-text) !important; }
.plyr__progress--played,
.plyr__volume--display,
.plyr--full-ui input[type=range] { color: var(--theme-main) !important; }
.plyr__menu__container .plyr__control[role=menuitemradio][aria-checked=true]::before { background: var(--theme-main) !important; }
.pcr-app .pcr-type.active,
.pcr-app .pcr-save { background: var(--theme-main) !important; }
.icon-card.active { outline: 1px solid var(--theme-main); }
.icon-card.active i,
.icon-card.active svg { color: var(--theme-main); }
.app-chat .sidebar-body .chat-contact-list li.active { background: var(--theme-main); }
.app-chat .app-chat-history .chat-history .chat-message.chat-message-right .chat-message-text { background-color: var(--theme-main) !important; color: var(--theme-btn-text); }
.navbar.landing-navbar .navbar-nav .show > .nav-link,
.navbar.landing-navbar .navbar-nav .active > .nav-link,
.navbar.landing-navbar .navbar-nav .nav-link.show,
.navbar.landing-navbar .navbar-nav .nav-link.active,
.navbar.landing-navbar .navbar-nav .nav-link:hover,
.navbar.landing-navbar .navbar-nav .nav-link:hover i { color: var(--theme-main) !important; }
.landing-features .features-icon-wrapper .features-icon-box .features-icon { border-color: var(--theme-main-12); }
.landing-features .features-icon-wrapper .features-icon-box:hover .features-icon { background-color: rgba(var(--theme-main-rgb), .05); }
.layout-navbar .navbar-dropdown.dropdown-notifications .dropdown-notifications-list .dropdown-notifications-item:not(.mark-as-read) .dropdown-notifications-read span { background-color: var(--theme-main); }

/* Template Customizer btn */
.template-customizer-open-btn {
  background-color: var(--theme-main) !important;
  border-color: var(--theme-main) !important;
  color: var(--theme-btn-text) !important;
  box-shadow: 0 2px 6px 0 var(--theme-main-shadow) !important;
}
.template-customizer-open-btn:hover,
.template-customizer-open-btn:focus {
  background-color: var(--theme-main-hover) !important;
  border-color: var(--theme-main-hover) !important;
  color: var(--theme-btn-text) !important;
}


/* Auth cover (login, sign up, forgot password): public/css/auth-pages.css paints this with the main
   color at .92 / 1 / .78 (dark .80 / .92 / .66). Same stops and opacity; the outer stops take the
   gradient colors, which fall back to the main color. */
.authentication-wrapper.authentication-cover .authentication-inner .auth-cover-bg {
  background-color: var(--theme-main) !important;
  background-image:
    radial-gradient(circle at 14% 14%, rgba(255, 255, 255, .20) 0%, rgba(255, 255, 255, 0) 55%),
    radial-gradient(circle at 88% 86%, rgba(255, 255, 255, .14) 0%, rgba(255, 255, 255, 0) 48%),
    linear-gradient(155deg, rgba(var(--theme-grad-start-rgb), .92) 0%, var(--theme-main) 45%, rgba(var(--theme-grad-end-rgb), .78) 100%) !important;
}
[data-style='dark'] .authentication-cover .authentication-inner .auth-cover-bg,
html.dark-style .authentication-cover .authentication-inner .auth-cover-bg {
  background-image:
    radial-gradient(circle at 14% 14%, rgba(255, 255, 255, .10) 0%, rgba(255, 255, 255, 0) 55%),
    linear-gradient(155deg, rgba(var(--theme-grad-start-rgb), .80) 0%, rgba(var(--theme-main-rgb), .92) 48%, rgba(var(--theme-grad-end-rgb), .66) 100%) !important;
}
</style>
<script>
  if (window.config && window.config.colors) {
    window.config.colors.primary = @json($_branding['main']);
    window.config.colors_label.primary = @json($_branding['main'] . '29');
  }
</script>
