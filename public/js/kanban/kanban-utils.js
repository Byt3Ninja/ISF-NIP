/**
 * kanban-utils.js
 * Shared utility functions — no blade/server dependencies.
 */

const FILE_EXT_ICON = {
  pdf:  'ti-file-type-pdf text-danger',
  doc:  'ti-file-type-doc text-primary',
  docx: 'ti-file-type-doc text-primary',
  xls:  'ti-file-type-xls text-success',
  xlsx: 'ti-file-type-xls text-success',
  png:  'ti-photo text-info',
  jpg:  'ti-photo text-info',
  jpeg: 'ti-photo text-info',
  gif:  'ti-photo text-info',
  webp: 'ti-photo text-info',
  zip:  'ti-file-zip text-warning',
  csv:  'ti-file-spreadsheet text-success',
  txt:  'ti-file-text text-secondary',
};

function fileIcon(name) {
  const ext = (name || '').split('.').pop().toLowerCase();
  return FILE_EXT_ICON[ext] || 'ti-file text-secondary';
}

function formatFileSize(bytes) {
  if (!bytes) return '';
  return bytes < 1024 * 1024
    ? (bytes / 1024).toFixed(1) + ' KB'
    : (bytes / (1024 * 1024)).toFixed(1) + ' MB';
}

/**
 * Build a clickable avatar <a> element.
 * @param {string} name
 * @param {string|null} imgUrl
 * @param {number|null} userId  — used to build profile link
 * @returns {string} HTML string
 */
function buildAvatar(name, imgUrl, userId) {
  const cfg  = window.KanbanConfig;
  const ri   = (name || 'U').substring(0, 2).toUpperCase();
  const inner = imgUrl
    ? `<img src="${imgUrl}" alt="${name}" class="rounded-circle" style="width:32px;height:32px;object-fit:cover;">`
    : `<span class="avatar-initial bg-label-secondary rounded-circle d-flex align-items-center justify-content-center" style="width:32px;height:32px;font-size:12px;">${ri}</span>`;
  if (!userId) {
    return `<span class="d-inline-flex" title="${name}">${inner}</span>`;
  }
  return `<a href="${cfg.profileBaseUrl}/${userId}" target="_blank" class="d-inline-flex" title="${name}">${inner}</a>`;
}

/**
 * Validate a jQuery file input against allowed types and max size.
 */
function validateFiles(fileInput, errorSelector) {
  const files = fileInput[0].files;
  const $errorDiv = $(errorSelector);
  const allowedTypes = [
    'image/jpeg','image/jpg','image/png','image/gif','image/webp','image/svg+xml',
    'application/pdf',
    'application/zip','application/x-rar-compressed','application/x-compressed','application/vnd.rar',
    'application/msword',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'application/vnd.ms-excel',
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    'application/vnd.ms-powerpoint',
    'application/vnd.openxmlformats-officedocument.presentationml.presentation',
  ];
  const maxSize  = 10 * 1024 * 1024;
  const maxFiles = 5;

  fileInput.removeClass('is-invalid');
  $errorDiv.text('');
  if (!files.length) return true;

  if (files.length > maxFiles) {
    fileInput.addClass('is-invalid');
    $errorDiv.text(`You can upload a maximum of ${maxFiles} files at a time.`);
    return false;
  }

  for (let i = 0; i < files.length; i++) {
    if (!allowedTypes.includes(files[i].type)) {
      fileInput.addClass('is-invalid');
      $errorDiv.text(`File "${files[i].name}" has an invalid type.`);
      return false;
    }
    if (files[i].size > maxSize) {
      fileInput.addClass('is-invalid');
      $errorDiv.text(`File "${files[i].name}" exceeds the 10MB limit.`);
      return false;
    }
  }
  return true;
}
