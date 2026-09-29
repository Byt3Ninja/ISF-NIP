/* global window */
(function () {
    'use strict';

    const locale = (document.documentElement.lang || 'en').toLowerCase().startsWith('ar') ? 'ar' : 'en';
    const dict   = window.importerI18n || {};

    function get(path, replacements) {
        const parts = path.split('.');
        let node = dict;
        for (const p of parts) {
            if (!node || typeof node !== 'object') return path;
            node = node[p];
        }
        if (typeof node !== 'string') return path;
        if (!replacements) return node;
        return Object.entries(replacements).reduce(
            (s, [k, v]) => s.replaceAll(':' + k, v),
            node
        );
    }

    // Pick EN or AR from an API error object { message_en, message_ar }
    function pickLocalised(obj) {
        if (!obj) return '';
        return locale === 'ar' ? (obj.message_ar || obj.message_en || '') : (obj.message_en || '');
    }

    window.importerT = { get, pickLocalised, locale };
})();
