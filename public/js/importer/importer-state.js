/* global window */
(function () {
    'use strict';

    const KEY_PREFIX = 'importer:session:';

    function storageKey(uuid) {
        return KEY_PREFIX + uuid;
    }

    function load(uuid) {
        try {
            const raw = localStorage.getItem(storageKey(uuid));
            return raw ? JSON.parse(raw) : null;
        } catch (_e) {
            return null;
        }
    }

    function save(uuid, state) {
        try {
            localStorage.setItem(storageKey(uuid), JSON.stringify(state));
        } catch (_e) {
            // localStorage full / blocked; silently degrade
        }
    }

    function clear(uuid) {
        try { localStorage.removeItem(storageKey(uuid)); } catch (_e) { /* noop */ }
    }

    // Active-session helper
    function setActive(uuid) {
        try { localStorage.setItem('importer:active', uuid); } catch (_e) { /* noop */ }
    }
    function getActive() {
        try { return localStorage.getItem('importer:active'); } catch (_e) { return null; }
    }
    function clearActive() {
        try { localStorage.removeItem('importer:active'); } catch (_e) { /* noop */ }
    }

    window.importerState = { load, save, clear, setActive, getActive, clearActive };
})();
