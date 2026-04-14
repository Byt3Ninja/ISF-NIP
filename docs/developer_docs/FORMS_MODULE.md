# Forms Module

The Forms Management module lets tenant admins, sub-admins, organization
managers, and founders build custom forms, publish them to programs, render
them to end users, and capture validated submissions. It powers application
forms, surveys, and any program step that needs structured data collection.

---

## Table of Contents

- [Overview](#overview)
- [For Regular Users](#for-regular-users-non-technical)
  - [Who can use this module](#who-can-use-this-module)
  - [Creating a form](#creating-a-form)
  - [Adding sections and questions](#adding-sections-and-questions)
  - [Question types](#question-types)
  - [Response logic](#response-logic-conditional-flow)
  - [Previewing and sharing a form](#previewing-and-sharing-a-form)
  - [Submitting a form (end-user view)](#submitting-a-form-end-user-view)
  - [Validation behavior](#validation-behavior)
  - [FAQ](#frequently-asked-questions-faq)
- [For Developers](#for-developers-technical)
  - [Architecture](#architecture)
  - [Routes](#routes)
  - [Controllers and actions](#controllers-and-actions)
  - [Database schema](#database-schema)
  - [Question type reference](#question-type-reference)
  - [Validation pipeline](#validation-pipeline)
  - [Data preservation on failed submission](#data-preservation-on-failed-submission)
  - [Internationalization](#internationalization)
  - [File structure](#file-structure)
  - [Extending with a new question type](#extending-with-a-new-question-type)

---

## Overview

The Forms module has three conceptual surfaces:

1. **Builder** – a drag-and-drop UI at `/dashboard/show-builder` where form
   authors compose forms from sections and questions.
2. **Renderer** – a public-facing form at `/dashboard/show-form/{id}` where
   end users answer the questions and submit their responses.
3. **Submission handler** – the backend pipeline that validates submissions,
   stores responses, and relays success/failure back to the user.

Forms are scoped to an **organization**. Which forms a user can see depends on
their role (see [Who can use this module](#who-can-use-this-module)).

---

# For Regular Users (Non-Technical)

## Who can use this module

| Role                  | Can build forms | Can see forms of                          | Can submit |
| --------------------- | :-------------: | ----------------------------------------- | :--------: |
| Admin (Super/Tenant)  |       Yes       | All organizations                         |    N/A     |
| Sub-Admin             |       Yes       | Organizations they are linked to          |    N/A     |
| Organization Manager  |       Yes       | Their own organization(s)                 |    N/A     |
| Founder (registered)  |       No        | Forms attached to programs they joined    |    Yes     |

Non-builder roles (Mentor, Judge, Service Provider) do not interact with this
module directly.

## Creating a form

1. Open **Dashboard → Form Builder** (`/dashboard/show-builder`).
2. Click **Add Form** (or **Switch Form** to open an existing one).
3. Give the form a title and pick an organization. If you only belong to one
   organization, this step is automatic.
4. Start adding sections and questions (see below).
5. Click **Save Form** when you are done. The form is ready to be referenced
   from program steps immediately.

## Adding sections and questions

A form is organized into **sections**, and each section holds one or more
**questions**. Sections are purely visual groupings — they help users see a
long form in chunks and also drive conditional flow (see below).

- **Add Section** creates a new section at the bottom of the form.
- **Add Question to Section** adds a question inside a section.
- Sections and questions can be reordered by dragging the handle on the left.
- A section can be deleted together with its questions via the three-dot menu.

Each question has:

- A **title** (the label shown to the end user).
- A **type** (see next section).
- A **Required** toggle.
- Type-specific settings (options, min/max, allowed domains, …).

## Question types

| Type               | What it captures                                               |
| ------------------ | -------------------------------------------------------------- |
| Short answer       | A single line of free text.                                    |
| Paragraph          | A multi-line text area.                                        |
| Number             | A numeric value with optional min, max, and step.              |
| Email              | An email address, with an optional "confirm email" second box. |
| URL                | A URL, with an optional list of allowed domains.               |
| Multiple choice    | One choice from a list (radio buttons).                        |
| Checkboxes         | Zero or more choices from a list.                              |
| Dropdown           | One choice from a dropdown.                                    |
| Linear scale       | A numeric rating, e.g. 1–5 or 1–10.                            |
| Date               | A calendar date (`YYYY-MM-DD`).                                |
| Time               | A time of day in 24-hour format (`HH:MM`).                     |
| File upload        | A single file attachment.                                      |

For choice-based types (multiple choice, checkboxes, dropdown) you can add a
special **"Other"** option so respondents can type a custom answer. The
"Other" text field is always optional — respondents can leave it empty.

## Response logic (conditional flow)

Each option in a multiple choice or dropdown question can jump the respondent
to a different section, or end the form ("Submit form"). Use the **Response
Logic** panel on a question to wire this up. The default is **Continue to next
section**.

## Previewing and sharing a form

From the builder, click **View Saved Form** to see what respondents will see.
This is the same URL your program steps will link to
(`/dashboard/show-form/{id}`). The preview shows section and question counts
at the top and allows authors to jump back into edit mode.

## Submitting a form (end-user view)

As a founder filling out a form, you will see one section at a time. When you
answer a question that has response logic attached, the form will scroll to
the next relevant section.

### Validation behavior

The form is validated on the client as you type, and again on the server when
you submit. The messages you will see are:

| Problem                              | English                                | Arabic                                  |
| ------------------------------------ | -------------------------------------- | --------------------------------------- |
| Required field is empty              | This field is required.                | هذا الحقل مطلوب.                        |
| Email field has an invalid address   | Please enter a valid email address.    | يرجى إدخال بريد إلكتروني صحيح.          |
| Number field has non-numeric input   | Please enter numbers only.             | يرجى إدخال أرقام فقط.                   |
| Time field has an invalid value      | Please enter a valid time.             | يرجى إدخال وقت صحيح.                    |

All error messages appear **directly underneath the relevant field**. A bad
value in one field never blocks the form from rendering or triggers a generic
"system error" page.

If your submission is rejected by the server (for example because a field is
required and you left it blank), the form reloads with **all your previously
entered values still in place** — short answers, paragraphs, selected radio
buttons, checked boxes, dropdown selections, dates, and times are all
preserved. Only uploaded files need to be re-attached (see next section).

### File upload limits

Every **File upload** question is subject to the same policy, enforced on
both the client and the server:

- **Maximum size:** 10 MB per file.
- **Allowed extensions:** `doc`, `docx`, `xls`, `xlsx`, `ppt`, `pptx`, `pdf`,
  `jpg`, `jpeg`, `png`, `webp`.
- **Anything else is blocked** — including executable and scripting
  extensions such as `exe`, `php`, `phtml`, `sh`, `bash`, `bin`, `pl`,
  `cgi`, `ksh`, `htaccess`, `htpasswd`. These are not in the whitelist and
  are never accepted by the validator.

If a selected file violates either rule, the form stays on screen, the
invalid file input is highlighted, and the inline message appears under the
field:

| Problem        | English                                                                               | Arabic                                                                           |
| -------------- | ------------------------------------------------------------------------------------- | -------------------------------------------------------------------------------- |
| File too large | File size must not exceed 10 MB.                                                      | يجب ألا يتجاوز حجم الملف 10 ميجابايت.                                            |
| Bad file type  | Supported file types are Word, Excel, PowerPoint, PDF, JPG, JPEG, PNG, and WEBP.      | أنواع الملفات المدعومة هي Word وExcel وPowerPoint وPDF وJPG وJPEG وPNG وWEBP.     |

If the web server itself rejects the upload as too large (nginx
`client_max_body_size` → HTTP 413), Laravel catches the
`PostTooLargeException` and redirects the user back to the form with a
top-of-page banner carrying the same "File size must not exceed 10 MB."
message, so founders never land on a raw error page.

### File uploads and the all-or-nothing rule

Browsers do not let a server re-populate a file input after a failed
submission — this is a security restriction outside of Incubator's control.
Because of this, forms that contain a **File upload** question follow an
**all-or-nothing** rule:

1. When you click **Submit** on such a form, you will see a confirmation
   dialog reminding you that if submission fails you will have to re-attach
   your files.
2. If you click **Yes, submit**, the form is sent in full.
3. If you click **Cancel**, nothing is sent and your values remain as they
   were.

Forms without file uploads do not show this dialog.

## Frequently Asked Questions (FAQ)

**Q: I marked "Other" as not required but the form still asks me to fill it.**
A: "Other" is always optional unless you also mark the parent question as
required AND no standard option is selected. Pick one of the standard options
or type a value into the "Other" box.

**Q: Why does my submission reset all the fields when it fails?**
A: It shouldn't — that was a bug that is now fixed. If you still see this,
make sure you are on the latest build and contact support with the form ID.

**Q: Can I attach multiple files to one question?**
A: Not yet. One file per file-upload question. Add more file-upload questions
if you need to collect several attachments.

**Q: Can I export submissions?**
A: Submissions are stored in the `form_data` table and are surfaced through
the program submissions views. Bulk export is handled by those views rather
than the Forms module itself.

---

# For Developers (Technical)

## Architecture

```
┌──────────────────────────────────────────────────────────────────────────┐
│                            FORMS MODULE                                  │
│                                                                          │
│  ┌────────────────┐    ┌────────────────┐    ┌────────────────────────┐  │
│  │  Form Builder  │───>│   Form Render  │───>│   Submission Handler   │  │
│  │  (author UI)   │    │   (end user)   │    │   (validate + store)   │  │
│  └────────────────┘    └────────────────┘    └────────────────────────┘  │
│         │                      │                        │                │
│      saveForm             showForm                handleFormRequest       │
│    (AJAX, JSON)          (Blade view)              (POST + redirect)      │
│         │                      │                        │                │
│  ┌──────▼──────────────────────▼────────────────────────▼──────────┐     │
│  │   builder_forms  ──<  builder_fields  >──  fields               │     │
│  │                  (pivot: options JSON)                          │     │
│  │                                                                 │     │
│  │                    form_data  (submitted values)                │     │
│  └─────────────────────────────────────────────────────────────────┘     │
└──────────────────────────────────────────────────────────────────────────┘
```

All three surfaces are served by a single controller,
`App\Http\Controllers\FormBuilderController`. The builder UI lives in
`resources/views/admin/settings/formbuilder/index.blade.php` with a thick JS
companion at `public/vuexy/assets/js/app-form-builder.js`. The render UI lives
in `resources/views/admin/settings/formbuilder/form.blade.php`.

## Routes

All routes are defined in `routes/web.php` under the admin group:

| Method  | URI                                 | Name                        | Action                                  |
| ------- | ----------------------------------- | --------------------------- | --------------------------------------- |
| GET     | `/dashboard/show-builder/{id?}`     | `forms.builder_view`        | `FormBuilderController@showBuilder`     |
| POST    | `/dashboard/form/{id}/save-form`    | `forms.design_save`         | `FormBuilderController@saveForm`        |
| GET     | `/dashboard/show-form/{id}`         | `forms.render_view`         | `FormBuilderController@showForm`        |
| POST    | `/dashboard/submit-form`            | `forms.submissions_create`  | `FormBuilderController@handleFormRequest` |
| DELETE  | `/dashboard/form/{id}/delete`       | `forms.delete`              | `FormBuilderController@deleteForm`      |
| DELETE  | `/dashboard/form/{id}/reorder-…`    | `forms.reorder_sections`    | `FormBuilderController@reorderSections` |
| DELETE  | `/dashboard/form/{id}/reorder-…`    | `forms.reorder_questions`   | `FormBuilderController@reorderQuestions` |

Access is gated by the standard Spatie permission on the route name, see
`database/dbFiles/_permissions.json`.

## Controllers and actions

### `showBuilder($id?)`

Loads the builder UI. Given an optional form id, it:

1. Resolves the user's accessible organizations via `$user->organizations_ids`.
2. Loads (or lazily creates) a `BuilderForm` for the current org.
3. Reads every `BuilderField` attached to the form, decodes the `options`
   JSON, and groups the data by section into a structure consumed by
   `app-form-builder.js` via the `FB_CONFIG.builderData` global.

### `saveForm($id, Request $request)`

AJAX endpoint that accepts a JSON payload built by `extractQuestionData()` on
the client. The route `{id}` is the **authoritative** form identifier: `0`
(or missing) means "create new", any positive integer means "update that
specific form, including renaming it".

It:

1. Validates the envelope (`form_fields_data.items`, `title`,
   `organization_id`).
2. If `$id > 0` and a matching `BuilderForm` exists, the form is loaded and
   its `form_name` + `organization_id` are updated in place; otherwise a new
   `BuilderForm` row is created. This replaces the legacy "look up by
   `(form_name, organization_id)`" path, which silently orphaned forms on
   rename and rewrote the wrong form on name collisions.
3. Translates each question into a `{ field_id, options }` tuple:
   - `field_id` maps the semantic type to a row in the `fields` table
     (`input`, `textarea`, `select`, `radio`, `checkbox`, `file`, `number`,
     `email`, `url`, …).
   - `options` is a JSON blob that includes the `label`, `section`,
     `description`, `validation`, `logic`, and type-specific configuration
     such as `values` (for choice types), `min/max/step` (for `number`), or
     `confirm` (for `email`).
4. Wraps the entire re-attach in a DB transaction: existing attachments are
   detached, then new rows are attached to `builder_fields`. Any
   `\Throwable` raised inside the transaction is reported via Laravel's
   `report()` helper and returned to the client as `success: false` with
   the original exception message — no more generic error pages.

Response:

```json
{
  "success": true,
  "id": 42,
  "organization_id": 7,
  "action": "updated",   // or "created"
  "message": null
}
```

The client uses `action` to pick between the "Form saved successfully." /
"تم حفظ النموذج بنجاح!" and "Form updated successfully." /
"تم تحديث النموذج بنجاح." messages, and always reloads
`/dashboard/show-builder/{id}` after a save so the form remains accessible
for further edits.

### `showForm($id)`

Renders the end-user view. The action loads `BuilderField` rows in ascending
insertion order and groups them by `options.section` exactly like
`showBuilder` does. Each question is collapsed into:

```php
[
  'id'          => 123,
  'label'       => 'Your name',
  'description' => '',
  'section'     => 'About you',
  'type'        => 'short_answer',
  'is_required' => true,
  'options'     => [...choices if any...],
  'logic'       => [...per-option jump targets...],
]
```

The Blade template (`form.blade.php`) then switches on `$question['type']` to
render the right input element. Every rendered input carries a `data-qtype`
attribute that is later read by the client-side validator.

### `handleFormRequest(Request $request)`

POST target for the rendered form. It:

1. Hard-validates the envelope: `builder_form_id` and `field_ids` must be
   present. Missing envelope → HTTP 422.
2. Iterates the comma-separated `field_ids`, loads the `BuilderField` for
   each, and builds a `rules_array` plus localized `custom_messages`:
   - `required` if `options.validation.required === 1`, else `nullable`.
   - `email` for `options.type === 'email'`.
   - `numeric` for `options.type === 'number'` or `'linear_scale'`.
   - `date_format:H:i` for `options.type === 'time'`.
   - `date` for `options.type === 'date'`.
3. Feeds the rules into a `Validator` with localized messages (EN/AR based on
   `app()->getLocale()`).
4. On failure: `redirect()->back()->withErrors($v)->withInput()` so that both
   errors and previously entered values survive into the redirect target.
5. On success: redirects back with a success flash message.

### `CyclesController@my($id)` / `CyclesController@saveForm(Request $request)`

The founder-facing submission flow does **not** go through
`FormBuilderController::handleFormRequest` — that action is only used by
the admin form preview. The real submission path is on `CyclesController`
and is reachable from the founder dashboard via
`/dashboard/cycles/my/{step_id}`:

- `my($id)` loads the `Step`, resolves its `BuilderForm`, re-groups the
  questions by section in the same shape as `showForm()`, queries
  `Formdata` for any existing answers by the current user, and flashes
  them into the session via `session()->flashInput(...)` so that
  `old($fieldName)` in the Blade view picks up the previously-submitted
  values (including file paths).
- `saveForm(Request $request)` is the POST target. It reads
  `field_ids`, pre-hydrates the request with any previously-submitted
  **file** paths (so the `required` rule on file fields passes when the
  founder re-opens an already-submitted form and does not re-upload
  anything), then runs the same per-field rule builder used by
  `FormBuilderController::handleFormRequest`: `required`, `email`,
  `numeric`, `date_format:H:i`, `date`, and the
  `file|max:10240|mimes:…` stack (the file stack is only attached when a
  fresh upload is actually present, otherwise the hydrated path string
  would be rejected by the `file` rule). Messages are localized from the
  same EN/AR map.
- On validation failure it `redirect()->back()->withErrors()->withInput()`
  — identical to the admin preview, so entered values survive a failed
  submission.
- On success it writes one `Formdata` row per field via `updateOrInsert`
  and redirects the founder back to the dashboard with a success flash.

The founder cycles view at
`resources/views/admin/settings/cycles/form.blade.php` renders file
inputs with:

- An `accept` attribute and max-size hint text, matching the policy
  defined by GV-2136.
- A `data-has-existing="1"` attribute + hidden `field_{id}_existing`
  sentinel whenever `old($fieldName)` flashes a path from a previous
  submission.
- No `required` attribute when `$hasExistingFile` is true, so the
  browser's native validation does not block re-submission without
  re-uploading.
- A "Current file: [Download]" row beneath the input that lets the
  founder download the previously uploaded file or ignore it and
  replace it by picking a new file.

The client-side `addRequiredValidator` callback returns `true`
immediately when the input's `dataset.hasExisting === '1'`, so the
FormValidation layer also treats a previously-submitted file as a
valid existing value.

### `deleteForm($id)`

Blocks deletion when the form is referenced by program steps or when any
`form_data` row points at one of its fields, and returns a bilingual JSON
error message in either case.

## Database schema

The module uses four tables:

| Table            | Purpose                                                                                                      |
| ---------------- | ------------------------------------------------------------------------------------------------------------ |
| `builder_forms`  | One row per form. Columns: `id`, `form_name`, `organization_id`, timestamps.                                 |
| `fields`         | Catalog of primitive field types (`input`, `textarea`, `select`, `radio`, `checkbox`, `file`, `number`, …). |
| `builder_fields` | Pivot between `builder_forms` and `fields`. Column `options` holds a JSON blob with label, validation, etc.  |
| `form_data`      | Submitted values, one row per answered question per submission.                                              |

The `options` JSON stored on `builder_fields` is the source of truth for
question configuration. Example:

```json
{
  "label": "Your age",
  "section": "About you",
  "description": "",
  "type": "number",
  "min": "18",
  "max": "99",
  "step": "1",
  "validation": { "required": 1 },
  "logic": []
}
```

## Question type reference

The `options.type` field tells both the renderer and the validator how to
handle the question. The canonical list is:

| `options.type`    | Underlying `fields.field_type` | HTML input                        | Validators applied                        |
| ----------------- | ------------------------------ | --------------------------------- | ----------------------------------------- |
| `short_answer`    | `input`                        | `<input type="text">`             | required                                  |
| `paragraph`       | `textarea`                     | `<textarea>`                      | required                                  |
| `number`          | `number` (fallback `input`)    | `<input type="number">`           | required, numeric                         |
| `email`           | `input`                        | `<input type="email">`            | required, email                           |
| `url`             | `input`                        | `<input type="url">`              | required, URL format (client-side)        |
| `multiple_choice` | `select`                       | `<input type="radio">`            | required                                  |
| `checkboxes`      | `radio`                        | `<input type="checkbox">`         | required                                  |
| `dropdown`        | `select`                       | `<select>`                        | required                                  |
| `linear_scale`    | `input`                        | `<select>` (1–N)                  | required, numeric                         |
| `date`            | `input`                        | `<input class="datepicker">`      | required, `date`                          |
| `time`            | `input`                        | `<input class="timepicker">`      | required, `date_format:H:i`               |
| `file`            | `file`                         | `<input type="file">`             | required, `file`, `max:10240`, `mimes:doc,docx,xls,xlsx,ppt,pptx,pdf,jpg,jpeg,png,webp`; triggers confirmation dialog |

## Validation pipeline

Validation happens in two places and both use the same error copy so the
experience is identical regardless of which layer rejects the input.

### Client side

`form.blade.php` wires up `@form-validation/bootstrap5` against every input
that has a `name`. For each field it reads `data-qtype` and attaches only the
validators that make sense for that type (required, email, numeric, time,
URL). Error messages are placed immediately after the `.application-field-control`
wrapper via the `plugins.message.placed` callback, which keeps them under the
input visually. Locale is driven by `document.documentElement.lang`.

### Server side

`handleFormRequest` builds `rules_array` and `custom_messages` per field as
described above. The localized message map is:

```php
[
    'en' => [
        'required' => 'This field is required.',
        'email'    => 'Please enter a valid email address.',
        'numeric'  => 'Please enter numbers only.',
        'time'     => 'Please enter a valid time.',
    ],
    'ar' => [
        'required' => 'هذا الحقل مطلوب.',
        'email'    => 'يرجى إدخال بريد إلكتروني صحيح.',
        'numeric'  => 'يرجى إدخال أرقام فقط.',
        'time'     => 'يرجى إدخال وقت صحيح.',
    ],
]
```

Failed submissions return via `redirect()->back()->withErrors()->withInput()`.

## File upload enforcement

File upload questions are enforced by **three** layers that all share the
same whitelist and size cap, so the same message surfaces to the user
regardless of which layer catches the problem:

1. **HTML hint** — the `<input type="file">` carries an `accept` attribute
   listing both the allowed extensions and their MIME equivalents. This is
   advisory only (browsers honor it for the file picker but will happily
   accept drag-and-drop), so the other two layers cannot be skipped.
2. **Client-side** — two `FormValidation` callbacks in `form.blade.php`
   read `field.files[0]` and check:
   - `callback_file_size` — bytes must be ≤ `10 * 1024 * 1024`.
   - `callback_file_type` — extension (case-insensitive) must be in
     `ALLOWED_FILE_EXTENSIONS`.
   These fire before the form is submitted, so the founder never leaves
   the page. Messages come from the `translations[locale]` map.
3. **Server-side** — `handleFormRequest()` attaches three rules to every
   field whose `options.type === 'file'`:
   - `file`      — must be a real uploaded file
   - `max:10240` — size in kilobytes (10 MB)
   - `mimes:doc,docx,xls,xlsx,ppt,pptx,pdf,jpg,jpeg,png,webp`

   Laravel's `mimes` rule maps extensions through `Symfony\Mime\MimeTypes`
   and compares against the actual uploaded content, so a renamed `.php`
   or `.exe` will not slip through by spoofing the extension. Dangerous
   extensions (`php`, `phtml`, `sh`, `bash`, `bin`, `pl`, `cgi`, `ksh`,
   `htaccess`, `htpasswd`, `exe`, …) are simply not in the whitelist and
   are therefore unconditionally rejected.

### 413 / PostTooLargeException

nginx and PHP both enforce a hard byte cap (`client_max_body_size`,
`post_max_size`, `upload_max_filesize`). When a payload exceeds those
limits, the request never reaches `handleFormRequest()` — it short-circuits
with an HTTP 413. Laravel raises `PostTooLargeException` in that case, and
`App\Exceptions\Handler::render()` catches it:

```php
if ($exception instanceof PostTooLargeException) {
    $locale  = app()->getLocale() === 'ar' ? 'ar' : 'en';
    $message = $locale === 'ar'
        ? 'يجب ألا يتجاوز حجم الملف 10 ميجابايت.'
        : 'File size must not exceed 10 MB.';

    if ($request->expectsJson()) {
        return response()->json(['success' => false, 'message' => $message], 413);
    }

    return redirect()->back()
        ->withErrors(new MessageBag(['upload' => $message]))
        ->withInput();
}
```

The redirect attaches the message under the `upload` error key, and the
render view displays a top-of-page banner whenever `$errors->has('upload')`:

```blade
@if($errors->has('upload'))
  <div class="alert alert-danger ...">
    <i class="ti ti-alert-triangle me-2"></i>{{ $errors->first('upload') }}
  </div>
@endif
```

Because the `upload` key is global (no specific field), the banner is the
right surface for a 413 — we don't know which file caused it, only that the
aggregate payload was too large. The client-side pre-check should catch
this in almost all cases; the banner is the safety net for requests that
bypass JS or hit PHP/nginx limits lower than 10 MB.

> **Ops note:** make sure `upload_max_filesize`, `post_max_size`
> (`php.ini`), and `client_max_body_size` (nginx) are configured to at
> least 10 MB so the legitimate case never triggers the 413 path.

## Data preservation on failed submission

Non-file inputs are preserved automatically: the Blade template reads back the
previous value from Laravel's `old($fieldName)` helper on every input type,
including the `checked`/`selected` state for radios, checkboxes and dropdowns.

**Files are an explicit exception.** Browsers do not allow any form of
server-side repopulation of an `<input type="file">`. Rather than silently
dropping files or implementing a temp-storage scheme that would silently
duplicate uploads, the Forms module enforces an **all-or-nothing** contract:

- When a rendered form contains at least one file input, the
  `DefaultSubmit` FormValidation plugin is **not** registered.
- Instead, the client-side listener for `core.form.valid` prompts the user
  with a confirmation dialog ("uploaded files cannot be recovered
  automatically if a server-side error occurs…").
- On accept, the listener calls
  `HTMLFormElement.prototype.submit.call(form)` directly, bypassing any
  further FormValidation lifecycle hooks.
- On cancel, nothing is sent.

This keeps the submission atomic from the user's perspective: either the
whole thing goes through, or nothing does — and the user is warned upfront.

## Internationalization

- **Builder UI**: strings come from `resources/lang/{en,ar}/builder.php` and
  are injected into JS via `FB_CONFIG.translations` in `index.blade.php`.
- **Render UI**: the validation messages and the submit confirmation copy
  are currently hard-coded in `form.blade.php`'s inline script (keyed on
  `document.documentElement.lang`). The same strings are **also** present in
  `builder.php` under `Validation_required`, `Validation_email`,
  `Validation_number`, `Validation_time`, `Confirm_submit_title`, and
  `Confirm_submit_message` so a future refactor can centralize them.
- **Backend**: `handleFormRequest` picks its message map based on
  `app()->getLocale()`.

If you add a new message, keep all three locations in sync.

## File structure

```
app/Http/Controllers/FormBuilderController.php           # all three actions
app/BuilderForm.php                                      # forms eloquent model
app/BuilderField.php                                     # pivot model
app/Field.php                                            # field-type catalog
database/dbFiles/fields.json                             # seed list of field types
database/seeders/FieldSeeder.php                         # seeds the catalog
resources/views/admin/settings/formbuilder/index.blade.php  # builder shell + FB_CONFIG
resources/views/admin/settings/formbuilder/form.blade.php   # render UI + client validation
public/vuexy/assets/js/app-form-builder.js               # builder interactions
resources/lang/en/builder.php                            # English strings
resources/lang/ar/builder.php                            # Arabic strings
routes/web.php                                           # module routes
```

## Extending with a new question type

To add a new question type (say, "rating stars"), touch the following points
in order:

1. **Seed a `fields` row** (if a new primitive HTML input is needed) via
   `database/dbFiles/fields.json` and re-run `FieldSeeder`.
2. **Builder JS** — add the new `<option>` to the `.question-type` dropdown
   in `createQuestionHTML()` and a matching template inside
   `updateQuestionOptions()`. Extend `extractQuestionData()` so the
   type-specific settings are serialized into the outgoing payload, and
   `populateQuestionData()` so the settings can be restored in edit mode.
3. **`saveForm()`** — add a `case` to the `switch ($field_type)` that maps
   the new type to a `field_id` and writes the right `additional_config`.
4. **`showBuilder()`** — usually nothing to do, because the default branch
   already echoes `options.type` back to the builder.
5. **`showForm()` / `form.blade.php`** — add a `@case('your_type')` inside
   the Blade switch that renders the input, sets `value="{{ old($fieldName) }}"`
   for preservation, and tags it with `data-qtype="your_type"`.
6. **Client validator** — in the inline script in `form.blade.php`, add a
   branch that reads `data-qtype === 'your_type'` and attaches the
   appropriate validator callback with a localized message.
7. **Server validator** — in `handleFormRequest()`, add a `case 'your_type'`
   inside the type switch that appends the matching Laravel rule (for
   example `regex:/.../`) and a localized error message to `$custom_messages`.
8. **Lang files** — add any new strings to both `en/builder.php` and
   `ar/builder.php`. The convention is `Validation_{key}` for error copy
   and `PascalCase` for labels.
9. **Docs** — update the [Question type reference](#question-type-reference)
   table and the [Question types](#question-types) list above.

---

## Changelog

### 2026-04-14 — Email & Number display in form view across roles (GV-2280, GV-2281, GV-2282)

**Confirmation pass — no code changes required.** This ticket family
asks that Email and Number questions render correctly in the form view
for Super Admin / Tenant Admin, Sub-Admin, and Organization Manager,
with the required indicator shown only on required questions. The
rendering pipeline was already delivered by the GV-2273 overhaul on
2026-04-13. Verified against the acceptance criteria:

- **Email renders as an email input.** The `@case('email')` branch in
  `resources/views/admin/settings/formbuilder/form.blade.php` emits
  `<input type="email" … data-qtype="email">`, so the browser treats
  the field as an email input and triggers the matching validators on
  the client (`emailAddress`) and the server (`email` rule) with the
  localized messages delivered by GV-2273.
- **Number renders as a number input.** The `@case('number')` branch
  emits `<input type="number" inputmode="decimal" …
  data-qtype="number">`, again with the matching `callback_numeric`
  validator on the client and `numeric` rule on the server.
- **Required indicator is shared across all question types.** It is
  rendered once per question via:
  ```blade
  @if($question['is_required'])
    <span class="text-danger ms-1">*</span>
  @endif
  ```
  and a secondary helper line
  `{{ $question['is_required'] ? 'Required field' : 'Optional field' }}`.
  Because these blocks live *outside* the per-type `@switch`, they
  apply uniformly to Email, Number, and every other question type —
  so a required Email or Number question shows the asterisk plus
  "Required field", and an optional one shows neither.
- **HTML `required` attribute** is also wired per case
  (`{{ $question['is_required'] ? 'required' : '' }}`) on both the
  email and number inputs, so the browser's native validation
  participates too.
- **Role access** is covered by `forms.render_view`, which
  `database/dbFiles/rolesWithDefaultPermissions.json` already grants
  to `admin`, `organizationmanager`, and `subadmin` by default.

QA can close GV-2280 through GV-2282 against the existing GV-2273
implementation — no further code changes are required. Regression
test: build a form with one required Email, one optional Email, one
required Number, and one optional Number; open the rendered form and
confirm the asterisk and "Required field" text appear only on the
required pair.

### 2026-04-14 — Email & Number question types across roles (GV-2274, GV-2275, GV-2276, GV-2277, GV-2278, GV-2279)

**Confirmation pass — no code changes required.** This family of tickets
asks for Email and Number question types in form creation and edit mode
for Super Admin / Tenant Admin, Sub-Admin, and Organization Manager.
The full pipeline was already delivered by the GV-2273 overhaul on
2026-04-13. Verified end-to-end against the acceptance criteria:

- **Dropdown options** — `<option value="number">` / `<option
  value="email">` are present in the question-type `<select>` rendered
  by `createQuestionHTML()` in
  `public/vuexy/assets/js/app-form-builder.js` (alongside `url`, which
  is also supported but not strictly required by this ticket).
- **Type-specific config UI** — the `number` template exposes
  `min` / `max` / `step` inputs; the `email` template exposes the
  "Require email confirmation" toggle. Both render in create and edit
  modes via `updateQuestionOptions()`.
- **Save path** — `FormBuilderController::saveForm()` has explicit
  `case 'number'`, `case 'email'`, and `case 'url'` branches in its
  question-type switch, writing `options.type` + the type-specific
  config (`min/max/step`, `confirm`, `allowed_domains`) into
  `builder_fields.options`. The `Required` toggle is serialized via
  `validation.required = !empty($question['required']) ? 1 : 0`.
- **Edit reload** — `FormBuilderController::showBuilder()` reads
  `options.type` back through its `default` branch, so the builder JS
  sees `type: 'number'` or `type: 'email'` on the payload and
  `populateQuestionData()` restores the dropdown selection plus the
  `number_config` / `email_config` fields in place.
- **Render + validation** — the founder-facing view has
  `@case('number'|'email'|'url')` branches that emit
  `<input type="number">`, `<input type="email">`, and
  `<input type="url">` with `data-qtype` attributes, and the
  client-side FormValidation wiring applies `emailAddress`,
  `callback_numeric`, and `callback_url` validators with localized
  messages (see the GV-2273 changelog entry below for the exact copy).
- **Role access** — `forms.builder_view`, `forms.render_view`, and
  `forms.design_save` permissions are granted by default to `admin`,
  `organizationmanager`, and `subadmin` in
  `database/dbFiles/rolesWithDefaultPermissions.json`, so all three
  target roles can reach both the create and edit flows.

QA can therefore close GV-2274 through GV-2279 against the existing
GV-2273 implementation — no further code changes are required. If a
regression is ever introduced to this feature, re-run the GV-2273 test
plan (builder dropdown → save → refresh → edit → re-save) to reproduce.

### 2026-04-14 — Preserve uploaded required files on form view (GV-2134)

- **Fixed "file is required on re-submit" bug.**
  `CyclesController::saveForm()` used to run the `required` validator
  *before* falling back to any previously-uploaded file path, so a
  founder re-opening an already-submitted form and clicking Submit
  without re-attaching the file would be rejected with "field can't be
  left blank". The action now queries the founder's existing
  `Formdata` rows up front and merges each stored file path into the
  request (`$request->merge(['field_X' => $existing_path])`) **before**
  the validator runs, so the required rule sees a valid value.
- **File rule only applies to fresh uploads.** When the hydrated value
  is a string path (not an `UploadedFile`), the `file|max|mimes` stack
  is skipped for that field; those rules are only attached when
  `$request->hasFile()` is true. This keeps the size + type enforcement
  from GV-2136 without rejecting legitimate re-submissions.
- **Unlocked the cycles form for editing.** The founder cycles view
  (`resources/views/admin/settings/cycles/form.blade.php`) used to set
  `$isReadOnly = true` as soon as any previous answer existed, which
  dropped the `<form>` wrapper and the submit button entirely — making
  it impossible to edit a submitted form. The flag was split into
  `$hasSubmission` (drives the "Submitted" status badge) and
  `$isReadOnly = false` (kept for backward compatibility but no longer
  gates rendering), so the form is always editable while still
  showing that it has been submitted.
- **File inputs now treat previously-uploaded files as valid existing
  values.** Every file input with a flashed `old()` value:
    - drops the native `required` attribute so the browser does not
      block submission,
    - sets `data-has-existing="1"` so the JS
      `addRequiredValidator` callback short-circuits with `true`,
    - emits a hidden `field_{id}_existing` sentinel that carries the
      stored path, and
    - renders a "Current file: [Download]" row letting the founder
      download the existing file or replace it by selecting a new
      one.
  Uploading a new file replaces the stored path; leaving the input
  empty keeps the old file.
- **Dashboard CTA relabel.** The founder dashboard step tile now
  shows **View** (with a `ti-eye` icon) instead of **Submit** for any
  step that already has a `form_data` row for the current user. The
  link target is unchanged — it still opens `/cycles/my/{id}` — the
  label just honestly reflects that a submission exists.
- **File policy brought to parity.** Cycles form now carries the same
  `accept` attribute, max-size hint, and client-side
  `callback_file_type` / `callback_file_size` validators that GV-2136
  added to the admin form preview. Oversized or wrong-type uploads
  are blocked inline on the cycles page too.
- **Arabic email message corrected.** The cycles form's hardcoded JS
  translation map used `… صالح`; it now matches the product spec
  `يرجى إدخال بريد إلكتروني صحيح.` for consistency with
  `FormBuilderController`.

### 2026-04-14 — Allow form editing without save error (GV-2137)

- **Fixed save-by-name bug.** `saveForm()` used to look up the target form
  by `(form_name, organization_id)` and ignored the route `{id}`
  parameter entirely. Renaming a form therefore created a *new* form and
  orphaned the original, and a name collision silently rewrote the wrong
  form. The action now uses the route id as the authoritative identifier
  and updates `form_name` + `organization_id` on the loaded model, so
  renames are persisted correctly and a fresh `BuilderForm` is only
  created when the id is `0`.
- **Fixed silent 500 on save errors.** The `catch (Exception $e)` block
  around the DB transaction was unqualified inside a namespaced class
  and `Exception` was not imported, so it resolved to
  `App\Http\Controllers\Exception` (nonexistent) and raised a fatal
  "class not found" whenever any real exception was thrown during the
  re-attach. The catch now uses `\Throwable`, reports the exception via
  `report()`, and returns `success: false` + the original message so the
  UI can show a real error instead of a generic system page. This was
  the most common source of the "some forms give an error" symptom
  described in the ticket.
- **Button relabel.** The save button inside the builder now reads
  **Update Form** / **تحديث النموذج** while in edit mode (was
  "Edit Form" / "تحرير النموذج"). Create mode still reads "Add Form".
- **Per-mode success message.** `saveForm()` returns a new `action` key
  (`created` | `updated`) and the client picks between
  `Form_saved_successfully` and the new `Form_updated_successfully`
  strings. The Arabic message matches the spec exactly:
  `تم تحديث النموذج بنجاح.`
- **Form stays accessible after save.** The client now always reloads
  `/dashboard/show-builder/{id}` after a successful save, so updated
  values survive a page refresh and the form remains editable. Edit-mode
  saves used to navigate straight to the read-only render view, which
  is what produced the "form becomes inaccessible after save" report.
- **Envelope validation.** `saveForm()` now also validates
  `form_fields_data.title` and `form_fields_data.organization_id` before
  touching the database, so malformed payloads short-circuit with a
  proper 422 instead of hitting the transaction.
- **Lang files** gained `Update_Form` and `Form_updated_successfully`
  in both `en/builder.php` and `ar/builder.php`.

### 2026-04-13 — File upload hardening (GV-2136)

- **Size cap:** every file upload question now rejects files larger than
  10 MB. Enforced on the client (byte check against
  `MAX_FILE_SIZE_BYTES = 10 * 1024 * 1024`) and on the server (Laravel
  `max:10240` rule). Inline message: `File size must not exceed 10 MB.` /
  `يجب ألا يتجاوز حجم الملف 10 ميجابايت.`
- **Extension whitelist:** only `doc`, `docx`, `xls`, `xlsx`, `ppt`,
  `pptx`, `pdf`, `jpg`, `jpeg`, `png`, `webp` are accepted. Anything else
  (including `exe`, `php`, `phtml`, `sh`, `bash`, `bin`, `pl`, `cgi`,
  `ksh`, `htaccess`, `htpasswd`) is rejected both client-side (extension
  callback) and server-side (`mimes:` rule, which verifies the real MIME
  type so a renamed `.php` file cannot pass as a `.pdf`). Inline message:
  `Supported file types are Word, Excel, PowerPoint, PDF, JPG, JPEG, PNG,
  and WEBP.` / `أنواع الملفات المدعومة هي Word وExcel وPowerPoint وPDF
  وJPG وJPEG وPNG وWEBP.`
- **HTML `accept` hint** added to the file input so the native file
  picker defaults to the allowed types.
- **Hint text** ("Max 10 MB. Allowed types: …") now shown under every
  file input in both locales.
- **413 graceful handling:** `App\Exceptions\Handler::render()` catches
  `PostTooLargeException` and redirects back to the form with the
  localized size message attached under the `upload` error key. The
  render view shows this as a top-of-page banner
  (`$errors->has('upload')`), so founders never land on a raw nginx 413
  error page.
- **Lang files:** added `Validation_file_size`, `Validation_file_type`,
  `File_upload_hint` to both `en/builder.php` and `ar/builder.php`.

### 2026-04-13 — Custom form input overhaul (GV-2273, GV-2372, GV-2376, GV-2377)

- **Number question type** is now fully supported end-to-end. It was wired in
  the builder JS already but silently dropped by `saveForm()`; it now serializes
  `min`/`max`/`step`, renders as `<input type="number">`, and is validated both
  client-side and server-side as `numeric`.
- **Email and URL question types** are now persisted by `saveForm()` with
  their type-specific config (`confirm`, `allowed_domains`) and rendered by
  `showForm()`; previously they were dropped into the `default` branch.
- **Client-side validation** in the render view no longer guesses the
  validator from the field label. Every rendered input now carries a
  `data-qtype` attribute and the FormValidation wiring reads it directly,
  producing exact messages for `required` / `email` / `numeric` / `time` /
  `url` in both English and Arabic. The Arabic email message was corrected
  from `… صالح` to `… صحيح` to match the product copy.
- **Server-side validation** in `handleFormRequest()` now builds per-field
  rules from each question's stored `options.type` (`required`, `email`,
  `numeric`, `date_format:H:i`, `date`) with localized messages driven by
  `app()->getLocale()`. It continues to use `redirect()->back()->withErrors()->withInput()`,
  so non-file inputs survive a failed submission unchanged.
- **File uploads follow an all-or-nothing contract.** Forms containing an
  `<input type="file">` no longer register the `DefaultSubmit` FormValidation
  plugin. Instead, a `core.form.valid` listener shows a confirmation dialog
  reminding the user that files cannot be automatically re-attached after a
  server-side failure, and either submits the form in full or cancels it. The
  prior (unworkable) "preserve files on failed submit" requirement has been
  removed from the spec.
- **Lang files** gained `Validation_required/_email/_number/_time`,
  `Confirm_submit_title/_message`, `Confirm_yes_submit`, and `Confirm_cancel`
  in both `en/builder.php` and `ar/builder.php`. The render view currently
  duplicates these strings inline for simplicity; a future refactor can
  centralize via `__()`.
