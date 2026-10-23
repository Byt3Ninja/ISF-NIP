/**
 * Global fix for rich-text editor popups (Summernote Link/Picture/Video
 * dialogs, TinyMCE menus/dialogs, CKEditor dialogs) used from inside a
 * Bootstrap 5 modal or offcanvas.
 *
 * Those editors mount their popups directly in <body>, outside the open modal.
 * Bootstrap's focus trap listens for `focusin` on document and pulls focus back
 * into the modal whenever anything outside it is focused, so the popup's inputs
 * can never keep focus: they look clickable but nothing can be typed, and the
 * Insert button stays disabled.
 *
 * Here we stop `focusin` from editor popups at <body>, before it bubbles up to
 * the document-level trap. Popups that are themselves a Bootstrap `.modal`
 * (e.g. the summernote-bs5 build) are left alone: they run their own trap.
 *
 * Self-installing: covers every editor on every page - no per-view wiring.
 */
(function () {
  'use strict';

  var EDITOR_POPUPS = [
    '.note-modal',
    '.note-popover',
    '.tox-tinymce-aux',
    '.cke_dialog',
    '.cke_dialog_container'
  ].join(', ');

  function install() {
    document.body.addEventListener('focusin', function (e) {
      var target = e.target;
      if (!target || !target.closest) return;

      if (target.closest(EDITOR_POPUPS) && !target.closest('.modal, .offcanvas')) {
        e.stopPropagation();
      }
    });
  }

  if (document.body) {
    install();
  } else {
    document.addEventListener('DOMContentLoaded', install);
  }
})();
