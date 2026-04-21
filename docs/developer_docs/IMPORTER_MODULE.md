# Importer Module

The Importer is a guided, three-step wizard that bulk-imports Programs,
Startup Profiles, Founders, and Co-Founders from a CSV file. It is the
canonical path for administrators and organization managers to onboard
cohorts in a single operation, and it writes through the same services
the rest of the platform uses — so data landed by the importer is
indistinguishable from data entered through the normal UI.

This document is the single source of truth for the module as of epic
**GV-2064**. It covers the current state of the codebase (data model,
service map, HTTP API, field reference) and a per-ticket changelog
mapping each GV-#### entry to the concrete files, methods, and
permissions it touched.

---

## Table of Contents

1. [Overview](#1-overview)
2. [User-facing flow](#2-user-facing-flow)
3. [Data model](#3-data-model)
4. [Service layer](#4-service-layer)
5. [Field reference](#5-field-reference)
6. [Validator catalog](#6-validator-catalog)
7. [HTTP API](#7-http-api)
8. [Integration points](#8-integration-points)
9. [Operations](#9-operations)
10. [Per-ticket changelog (GV-2064)](#10-per-ticket-changelog-gv-2064)

---

## 1. Overview

### 1.1 What the Importer is

The Importer is a **stateful 3-step wizard**:

1. **Upload** — user uploads a `.csv` file that is validated for
   extension, size, MIME, magic bytes, null bytes, encoding, emptiness,
   duplicate headers, and row cap. The file is persisted on a private
   disk keyed by a server-owned session UUID.
2. **Map + Validate** — user maps each of the ~58 application fields to
   a column in the uploaded file (or "Don't Import"). Auto-map fills the
   obvious matches. Row data is then validated server-side against
   per-field and cross-field rules. System-conflict warnings (founder
   already enrolled, program with different dates, etc.) are surfaced
   before advancing.
3. **Review & Import** — user selects the target organization (Admin
   and Sub Admin only; Org Managers auto-select their single org),
   reviews the mapping summary and a sample preview, then starts the
   import. Each row imports in its own DB transaction; credential
   emails dispatch after commit; a result summary reports per-row
   outcomes.

### 1.2 Who uses it

| Role | What they can do | Org dropdown at Step 3 |
|---|---|---|
| Admin | Import into any organization | Shown — required fallback |
| Organization Manager | Import into the single organization they manage | Hidden — auto-selected |
| Sub Admin | Import into any organization their `user_organizations` links them to | Shown — required fallback |

Role scoping is enforced via `User::organizations_ids` at the `execute`
endpoint. Founders, Mentors, Judges, and Service Providers cannot
access the importer.

The org chosen at Step 3 is treated as a **fallback**. Each row's
program is first matched (case-insensitive name + type + exact start &
end dates) across **every organization the user can write to**; if an
exact match is found, that cycle is reused and its organization is
inherited for that row. Only rows whose program cannot self-resolve to
an existing cycle fall back to the dropdown org (and a new cycle is
created there). Admins are unscoped (`reachableOrgIds = null`); all
other roles are restricted to `User::organizations_ids`.

### 1.3 Where it lives

- **Web UI**: `/dashboard/importer` (Blade + vanilla JS, Bootstrap 4.6).
- **JSON API**: `/api/v1/importer/*` (Sanctum token auth).
- **Services**: `app/Services/Importer/*` (self-contained module).
- **Blade views**: `resources/views/admin/importer/v2/` (the `v2/`
  subfolder is an internal directory name kept for organization; the
  URL contract is `/dashboard/importer` with named routes `importer.*`).
- **Canonical service integrations**: `CompanyService`,
  `CompanyRepository`, `ProgramService`, `FinancialProjectionService`,
  and founder/co-founder team-member upserts.

The legacy importer was fully removed in PR 7. There is only one
importer today and its routes are unversioned at the URL level.

---

## 2. User-facing flow

```
    +----------------------------------------------------------------+
    |  Step 1  -->  Step 2 (Map + Validate + Conflicts)  -->  Step 3 |
    |  Upload                                                  Review|
    +------------------------------+---------------------------------+
                                   |
                              Start Import
                                   v
                           +-------------+
                           |  Execute    |
                           |  (per-row)  |
                           +------+------+
                                  v
                         Credentials / Enrolled
                                emails
                                  v
                          Result summary
```

Session state machine (`importer_sessions.status`):

```
uploaded -> validated -> conflicts_reviewed -> importing -> completed
    |           |                                              |
    v           v                                              v
discarded   discarded                                       failed
```

- If `ConflictDetector` returns zero warnings the UI may transition
  from `validated` to `importing` without an explicit
  `conflicts_reviewed` stop.
- `completed`, `failed`, and `discarded` are **terminal**
  (`ImporterSession::TERMINAL_STATUSES`). The upload client auto-
  recovers from a terminal or missing session by transparently creating
  a fresh session and retrying the upload once.

---

## 3. Data model

### 3.1 `importer_sessions` table

Source: `database/migrations/2026_04_20_215518_create_importer_sessions_table.php`.

| Column | Type | Purpose |
|---|---|---|
| `id` | bigint PK | |
| `uuid` | uuid (unique) | Public identifier used in URLs |
| `user_id` | FK users, cascade on delete | Session owner |
| `status` | string(32), default `uploaded` | See §2 state machine |
| `original_filename` | string | Visible in UI during replace |
| `file_path` | string, nullable | Nulled when the underlying file is purged |
| `file_size` | unsigned bigint, default 0 | |
| `file_hash` | char(64), nullable | sha256 for audit |
| `row_count` | unsigned int, nullable | Populated after parse |
| `headers` | json, nullable | Cached after parse |
| `mappings` | json, nullable | Persisted on successful validate |
| `organization_id` | FK organizations, nullable, null on delete | Fallback org chosen at Step 3 |
| `conflict_warnings` | json, nullable | Snapshot of surfaced / accepted warnings |
| `import_result` | json, nullable | Written after execute |
| `expires_at` | timestamp, indexed | 24h from last action |
| `created_at / updated_at` | timestamp | |

### 3.2 Team-member import fields

Source: `database/migrations/2026_04_21_030131_add_import_fields_to_team_members_table.php`.

`team_members.TeamMember` already accepts `Co-founder`; the importer
writes both `Founder` and `Co-founder` rows through
`CompanyRepository::upsertFounderFromImport` /
`upsertCoFounderFromImport`.

### 3.3 File storage layout

`storage/app/private/importer/{uuid}/source.csv` on the `importer`
disk. Not web-accessible. Deleted on completion, failure, discard,
replace, or hourly purge of expired sessions.

### 3.4 Retention

- **Active session files**: 24 hours, refreshed on every valid action.
- **Row audit trail**: the session row persists after the file is
  removed; `file_path` is nulled.
- **Scheduled purge**: hourly via `php artisan importer:purge-expired`
  (registered in `App\Console\Kernel` with `->hourly()->withoutOverlapping()`).

---

## 4. Service layer

All services live under `app/Services/Importer/` unless otherwise
noted.

| Service | Responsibility | Notes |
|---|---|---|
| `ImporterFieldSchema` | Single source of truth for all fields | `all()`, `required()`, `find()`, `templateHeaders()`, `templateSampleRows()` |
| `ImporterSession` | Eloquent model for `importer_sessions` | Status + terminal-status constants |
| `ImporterSessionService` | Session CRUD + state machine | Transition guards |
| `CsvFileValidator` | File-level security + structural checks | Returns `FileError[]` |
| `CsvParser` | Stream-parse CSV; BOM strip; width normalisation | Pure function over file path |
| `CsvTemplateGenerator` | Schema-driven template CSV | Uses `ImporterFieldSchema` |
| `HeaderAutoMapper` | Alias-based header → field matching | Reads `auto_map_aliases` |
| `RowValidator` + `ValidationContext` | Per-field + cross-field validation | Returns `RowValidationResult` of `RowError[]` |
| `ConflictDetector` | 5 batched queries for system conflicts | Returns `ConflictWarning[]` |
| `ImportOrchestrator` | Execute-time coordination | Drives the `importing` transition |
| `RowImporter` | Single-row write path (per-row transaction) | Delegates to canonical services |
| `UserProvisioner` | Founder user upsert + password generation | `upsertFromImport(array $founderData, int $companyId): array` |
| `ImporterEmailDispatcher` | Post-commit email dispatch | Credentials vs. Enrolled |
| `FinancialProjectionService` | Per-year financial row upsert | `upsertYearSeriesFromImport(Company $company, array $data, int $userId)` — `user_id` is required |
| `HackathonEnrollmentService` | Founder → team-leader enrollment for `Hackathon`-type cycles | `resolveStepId` requires the cycle to have at least one step, otherwise the row fails with a clear message (no cross-cycle fallback) |

**Company address handling.** `companies.address_en` and
`companies.address_ar` are nullable as of migration
`2026_04_21_042923_make_companies_address_nullable.php`. When the CSV
`address` cell is blank, `RowImporter` writes `NULL` rather than a
placeholder string. Downstream consumers should treat the columns as
optional.

Canonical (non-importer) services extended during the epic:

| Method | Location | Purpose |
|---|---|---|
| `ProgramService::findOrCreateForImport(int $fallbackOrgId, array $data, ?array $reachableOrgIds = null): Cycle` | `app/Services/ProgramService.php` | Title (case-insensitive) + type + start + end lookup scoped to reachable orgs; exact match reused, else new cycle created in fallback org |
| `CompanyService::upsertFromImport(array $data, ?int $existingCompanyId = null): Company` | `app/Services/CompanyService.php` | Pure persistence — no auth / no request state |
| `CompanyRepository::upsertFounderFromImport(Company $company, array $data, int $userId): Team_member` | `app/Repositories/CompanyRepository.php` | Founder team_member upsert |
| `CompanyRepository::upsertCoFounderFromImport(Company $company, array $data): Team_member` | `app/Repositories/CompanyRepository.php` | Co-founder team_member upsert (no user account) |

Each service declares plain-array / DTO public methods. None take
`Request` or `Auth` — they are callable from the current Blade
controller, a future React API, or CLI commands.

---

## 5. Field reference

The table below is generated from `ImporterFieldSchema::all()` via
`php artisan importer:docs`. **Do not edit by hand** — re-run the
command after any schema change.

<!-- BEGIN: AUTO-FIELDS -->

| Key | Section | Required | EN Label | AR Label | Validator | Target |
|---|---|---|---|---|---|---|
| `program_name` | program | yes | Program Name | اسم البرنامج | `required_string` | `cycles.title` |
| `program_type` | program | yes | Program Type | نوع البرنامج | `program_type_enum` | `cycles.type` |
| `program_start_date` | program | yes | Program Start Date | تاريخ بداية البرنامج | `date_mdy` | `cycles.start` |
| `program_end_date` | program | yes | Program End Date | تاريخ نهاية البرنامج | `date_mdy` | `cycles.end` |
| `founder_first_name` | founders | yes | Founder First Name | الاسم الأول للمؤسس | `required_string` | `users.first_name_en` |
| `founder_last_name` | founders | yes | Founder Last Name | اسم العائلة للمؤسس | `required_string` | `users.last_name_en` |
| `founder_email` | founders | yes | Founder Email | البريد الإلكتروني للمؤسس | `email` | `users.email` |
| `founder_gender` | founders | yes | Founder Gender | نوع المؤسس | `gender_enum` | `users.gender` |
| `founder_phone` | founders | yes | Founder Phone | رقم هاتف المؤسس | `phone` | `users.phone` |
| `founder_country_code` | founders | yes | Country Code | رمز الدولة | `country_code` | `users.country_code` |
| `founder_working_commitment` | founders | no | Founder Working Commitment | درجة التفرغ للمؤسس | `working_commitment_enum` | `team_members.working_commitment [TeamMember=Founder]` |
| `founder_national_id` | founders | no | Founder National ID | الرقم القومي للمؤسس | `string` | `team_members.national_id [TeamMember=Founder]` |
| `founder_educational_status` | founders | no | Founder Educational Status | الحالة التعليمية للمؤسس | `educational_status_enum` | `team_members.educational_status [TeamMember=Founder]` |
| `founder_date_of_birth` | founders | no | Founder Date of Birth | تاريخ ميلاد المؤسس | `date_mdy_past` | `team_members.date_of_birth [TeamMember=Founder]` |
| `founder_join_date` | founders | no | Founder's Join Date | تاريخ انضمام المؤسس | `date_mdy` | `team_members.join_date [TeamMember=Founder]` |
| `cofounder_name` | co_founders | no | Co-Founder Name | اسم الشريك المؤسس | `string` | `team_members.Name [TeamMember=Co-founder]` |
| `cofounder_email` | co_founders | no | Co-Founder Email | البريد الإلكتروني للشريك المؤسس | `email` | `team_members.Email [TeamMember=Co-founder]` |
| `cofounder_phone` | co_founders | no | Co-Founder Phone Number | رقم هاتف الشريك المؤسس | `phone` | `team_members.phone [TeamMember=Co-founder]` |
| `cofounder_working_commitment` | co_founders | no | Co-Founder Working Commitment | درجة التفرغ للشريك المؤسس | `working_commitment_enum` | `team_members.working_commitment [TeamMember=Co-founder]` |
| `cofounder_educational_status` | co_founders | no | Co-Founder Educational Status | الحالة التعليمية للشريك المؤسس | `educational_status_enum` | `team_members.educational_status [TeamMember=Co-founder]` |
| `startup_name` | company | yes | Startup Name | اسم الشركة الناشئة | `required_string` | `companies.name_en` |
| `country` | company | yes | Country | الدولة | `country_name` | `companies.country_id` |
| `governorate` | company | no | Governorate/State | المحافظة/الولاية | `state_name_in_country` | `companies.state_id` |
| `industry` | company | no | Industry | الصناعة | `string` | `companies.industries` |
| `sector` | company | no | Sector | القطاع | `sector_name` | `companies.sector` |
| `address` | company | no | Address | العنوان | `string` | `companies.address_en` |
| `establishment_date` | company | no | Establishment Date | تاريخ التأسيس | `date_mdy` | `companies.est_date` |
| `years_of_operations` | company | no | Years of Operations | سنوات التشغيل | `string` | `companies.years_of_operations` |
| `target_markets` | company | no | Target Markets | الأسواق المستهدفة | `target_markets` | `companies.targeted_market` |
| `company_registration_type` | company | no | Company Registration Type | نوع تسجيل الشركة | `string` | `companies.company_registration_type` |
| `company_email` | company | no | Company Email | البريد الإلكتروني للشركة | `email` | `companies.email` |
| `company_website` | company | no | Company Website | موقع الشركة | `url` | `companies.website` |
| `company_facebook` | company | no | Company Facebook Page | صفحة فيسبوك للشركة | `url` | `companies.facebook` |
| `company_logo` | company | no | Company Logo | شعار الشركة | `image_url` | `companies.logo` |
| `is_previously_incubated` | company | no | Is Previously Incubated | سبق الاحتضان | `yes_no` | `companies.previous_incubation` |
| `previous_incubator` | company | no | Previous Incubator | الحاضنة السابقة | `string` | `companies.previous_incubator` |
| `achievements` | company | no | Achievements | الإنجازات | `string` | `companies.achievements` |
| `mission` | company | no | Mission | الرسالة | `string` | `companies.mission` |
| `product_or_service` | company | no | Product or Service | المنتج أو الخدمة | `products_services_enum` | `companies.product_or_service` |
| `product_service_description` | company | no | Product/Service Description | وصف المنتج/الخدمة | `string` | `companies.products_services_description` |
| `why_this_startup` | company | no | Why This Startup | لماذا هذه الشركة الناشئة | `string` | `companies.why_this_startup` |
| `startup_expertise` | company | no | Startup Expertise | خبرة الشركة الناشئة | `string` | `companies.startup_expertise` |
| `key_areas` | company | no | Key Areas Needing Support/Mentorship Help | مجالات الدعم/الإرشاد المطلوبة | `string` | `companies.hear_about_us` |
| `mvp_link` | company | no | MVP/Prototype Link | رابط النموذج الأولي | `url` | `companies.mvp_prototype_link` |
| `total_employees` | employees | no | Total Employees | إجمالي الموظفين | `integer` | `companies.total_employees` |
| `male_employees` | employees | no | Male Employees | الموظفون الذكور | `integer` | `companies.male_employees` |
| `female_employees` | employees | no | Female Employees | الموظفات الإناث | `integer` | `companies.female_employees` |
| `currency` | financials | no | Currency | العملة | `currency_code` | `financial_projections.currency [year+0]` |
| `generate_revenue` | financials | no | Does Business Generate Revenue | هل يحقق النشاط إيرادات | `yes_no` | `companies.generate_revenue` |
| `average_annual_revenue` | financials | no | Average Annual Revenue | متوسط الإيرادات السنوية | `numeric` | `companies.average_annual_revenues` |
| `current_operating_income` | financials | no | Current Operating Income (EBIT) | الدخل التشغيلي الحالي | `numeric` | `financial_projections.ebit [year+0]` |
| `forecast_operating_income_y1` | financials | no | Forecast Operating Income Year 1 (EBIT) | توقع الدخل التشغيلي للسنة 1 | `numeric` | `financial_projections.ebit [year+1]` |
| `forecast_operating_income_y2` | financials | no | Forecast Operating Income Year 2 (EBIT) | توقع الدخل التشغيلي للسنة 2 | `numeric` | `financial_projections.ebit [year+2]` |
| `forecast_operating_income_y3` | financials | no | Forecast Operating Income Year 3 (EBIT) | توقع الدخل التشغيلي للسنة 3 | `numeric` | `financial_projections.ebit [year+3]` |
| `current_revenue` | financials | no | Current Revenue | الإيرادات الحالية | `numeric` | `financial_projections.revenues [year+0]` |
| `forecast_revenue_y1` | financials | no | Forecast Revenue Year 1 | توقع الإيرادات للسنة 1 | `numeric` | `financial_projections.revenues [year+1]` |
| `forecast_revenue_y2` | financials | no | Forecast Revenue Year 2 | توقع الإيرادات للسنة 2 | `numeric` | `financial_projections.revenues [year+2]` |
| `forecast_revenue_y3` | financials | no | Forecast Revenue Year 3 | توقع الإيرادات للسنة 3 | `numeric` | `financial_projections.revenues [year+3]` |

<!-- END: AUTO-FIELDS -->

### 5.1 Cross-field rules

Defined in `RowValidator::validateCrossFields()`.

| Rule | Trigger | Error code |
|---|---|---|
| Program dates order | `program_start_date > program_end_date` | `program_dates_order` |
| Co-founder requires name | any co-founder field set, name empty | `cofounder_name_required` |
| Co-founder requires email | any co-founder field set, email empty | `cofounder_email_required` |
| Co-founder email distinct | co-founder email equals founder email (case-insensitive) | `cofounder_email_distinct` |
| State belongs to country | governorate is not a state of the row's country | `state_belongs_to_country` |
| Previous incubator requires flag | `previous_incubator` set while `is_previously_incubated` = No | `prev_incubator_requires_flag` |
| Male employees ≤ total | `male_employees > total_employees` | `male_employees_gt_total` |
| Female employees ≤ total | `female_employees > total_employees` | `female_employees_gt_total` |
| Male + female ≤ total | `male + female > total_employees` | `male_plus_female_gt_total` |
| Financials require currency | any financial amount set, currency empty | `currency_required` |
| Founder DOB not future | `founder_date_of_birth` is after today | `founder_dob_not_future` |

---

## 6. Validator catalog

Named rules dispatched from `ImporterFieldSchema.validator` inside
`RowValidator::validateField()`:

| Rule | Passes when | Blocks when |
|---|---|---|
| `required_string` | value is non-empty after trim | empty |
| `string` | always (sanity-only) | — |
| `email` | `FILTER_VALIDATE_EMAIL` passes | invalid |
| `phone` | matches `/^\+?[0-9\s\-()]{6,20}$/` | non-matching |
| `country_code` | matches `/^\+?[0-9]{1,4}$/` | non-matching |
| `date_mdy` | exact `M/D/YYYY` with `n/j/Y` parse and no calendar rollover | any other format |
| `date_mdy_past` | valid `date_mdy` AND ≤ today | future |
| `numeric` | `is_numeric` | non-numeric |
| `integer` | `ctype_digit` on sign-stripped value | decimals or letters |
| `url`, `image_url` | `FILTER_VALIDATE_URL` passes | invalid |
| `yes_no` | value in `{Yes, No}` (case-insensitive) | anything else |
| `country_name` | resolves via `ValidationContext::countryIdFor()` | unknown country |
| `state_name_in_country` | always passes at per-field level; cross-field rule validates | — |
| `sector_name` | always passes (auto-creates at import) | — |
| `currency_code` | in curated whitelist (see `ValidationContext::isKnownCurrency()`) | outside list |
| `target_markets` | comma-separated; every entry a known country | any unknown |
| `program_type_enum`, `gender_enum`, `working_commitment_enum`, `educational_status_enum`, `products_services_enum` | value is in `allowed_values` | anything else |

Empty values bypass every rule except `required_string`, which is the
sole gate for requiredness.

Error codes are mapped per-field via `RowValidator::errorCodeFor()` so
a single rule can produce distinct lang keys for different fields
(e.g., `email` → `founder_email_invalid` for the founder,
`company_email_invalid` for the company).

---

## 7. HTTP API

### 7.1 Web routes (session-auth)

All prefixed under `/dashboard/importer`, named `importer.*`, guarded
by `auth`. Defined in `routes/web.php`.

| Method | Route | Name | Purpose |
|---|---|---|---|
| `GET` | `/dashboard/importer` | `importer.index` | Render wizard shell |
| `GET` | `/dashboard/importer/template` | `importer.template` | Download schema-generated CSV template |
| `GET` | `/dashboard/importer/schema` | `importer.schema` | JSON of `ImporterFieldSchema` |
| `GET` | `/dashboard/importer/organizations` | `importer.organizations` | Role-scoped org list |
| `POST` | `/dashboard/importer/sessions` | `importer.sessions.create` | Create session |
| `GET` | `/dashboard/importer/sessions/{uuid}` | `importer.sessions.show` | Current state |
| `DELETE` | `/dashboard/importer/sessions/{uuid}` | `importer.sessions.discard` | Discard session |
| `POST` | `/dashboard/importer/sessions/{uuid}/file` | `importer.sessions.file.upload` | Upload + validate file |
| `DELETE` | `/dashboard/importer/sessions/{uuid}/file` | `importer.sessions.file.delete` | Clear file, keep session |
| `POST` | `/dashboard/importer/sessions/{uuid}/automap` | `importer.sessions.automap` | Auto-match headers |
| `POST` | `/dashboard/importer/sessions/{uuid}/validate` | `importer.sessions.validate` | Row-level validation |
| `POST` | `/dashboard/importer/sessions/{uuid}/conflicts` | `importer.sessions.conflicts` | System-conflict check + optional accept |
| `GET` | `/dashboard/importer/sessions/{uuid}/preview` | `importer.sessions.preview` | First N mapped rows |
| `POST` | `/dashboard/importer/sessions/{uuid}/execute` | `importer.sessions.execute` | Run import |

All endpoints mirror under `/api/v1/importer/*` (name prefix
`api.v1.importer.*`) behind `auth:sanctum` for future React frontend
consumption.

### 7.2 Rate limits

Configured in `RouteServiceProvider::boot()`:

- `importer-upload` — **60 uploads / hour / user**. Applied only to the
  file-upload route (`POST /sessions/{uuid}/file`).
- `importer-general` — **60 requests / minute / user**. Applied to
  every other session-scoped route.

Both limiters key on `user->id` (falling back to IP for unauthenticated
edge cases).

### 7.3 Error envelope

```json
{
  "success": false,
  "errors": [
    {
      "code": "duplicate_headers",
      "message_en": "...",
      "message_ar": "...",
      "params": { "headers": ["Email"] }
    }
  ]
}
```

Errors are always an **array**; the spec requires all detected problems
to be surfaced, not just the first one.

---

## 8. Integration points

| Dependency | Why | Table / class |
|---|---|---|
| `App\Cycle` | Program records | `cycles` |
| `App\Company` | Startup profile | `companies` |
| `App\User` | Founder account | `users` |
| `App\Team_member` | Founder + co-founder records | `team_members` (`TeamMember ∈ {Founder, Co-founder}`) |
| `App\FinancialProjection` | Per-year financials | `financial_projections` |
| `App\Country`, `App\State`, `App\Sector` | Lookups; sector auto-created on import | read-through |
| `App\Organization` | Role scoping and fallback-org lookup | read-only here |

Mail:
- `App\Mail\ImporterFounderCredentialsMail` — first-time founder (temporary password included)
- `App\Mail\ImporterFounderEnrolledMail` — existing founder (no password)

Both are dispatched **after** the per-row DB transaction commits, from
`ImporterEmailDispatcher`.

Permissions are seeded through
`database/dbFiles/rolesWithDefaultPermissions.json` and the
`defaultRolesPermissionSeeder`. The **Importer** permission group has
14 entries (`importer.index`, `importer.template`, `importer.schema`,
`importer.organizations`, and the 10 session-scoped routes) attached
to the `admin`, `organizationmanager`, and `subadmin` roles.

---

## 9. Operations

### 9.1 Cleanup triggers

| Trigger | Action |
|---|---|
| `execute` success | delete file, status → `completed` |
| `execute` fatal failure | delete file, status → `failed` |
| `DELETE /sessions/{uuid}` | delete file + folder, status → `discarded` |
| `DELETE /sessions/{uuid}/file` | delete file, keep session + mappings |
| Replace-upload (new `POST .../file`) | delete old, overwrite |
| Hourly `importer:purge-expired` | purge sessions past `expires_at` |

### 9.2 Stale-session recovery

If the UI attempts to act on a missing or terminal session, the
upload client silently creates a fresh session and retries the upload
once. No user-visible error is surfaced for this case — users see a
single, successful upload. Recovery is scoped to upload only; other
endpoints return a 4xx that surfaces the session state.

### 9.3 Manually discard a stuck session

```bash
php artisan tinker
>>> $s = App\Services\Importer\ImporterSession::where('uuid', '...')->first();
>>> app(App\Services\Importer\ImporterSessionService::class)->discard($s);
```

### 9.4 Regenerate field-reference docs

```bash
php artisan importer:docs
```

Edit any prose in `IMPORTER_MODULE.md`, re-run the command, and
commit.

### 9.5 Purge expired sessions now

```bash
php artisan importer:purge-expired
```

Runs automatically hourly; safe to invoke manually.

---

## 10. Per-ticket changelog (GV-2064)

| Ticket | Title | PR | Key artifacts |
|---|---|---|---|
| GV-2065 | Expand CSV template (Super Admin) | PR 2 | `CsvTemplateGenerator`, `ImporterFieldSchema::templateHeaders/SampleRows` |
| GV-2066 | Expand CSV template (Org Manager) | PR 2 | Same; single template, role-agnostic |
| GV-2067 | File upload validation (Super Admin) | PR 2 | `CsvFileValidator`, 10-check matrix, `FileError` DTO |
| GV-2068 | File upload validation (Org Manager) | PR 2 | Same |
| GV-2069 | Mapping fields (Super Admin) | PR 3 | `_step_mapping.blade.php`, mapping JS, 6-section UI |
| GV-2072 | Mapping fields (Org Manager) | PR 3 | Same |
| GV-2073 | Auto-map columns (Super Admin) | PR 3 | `HeaderAutoMapper`, full alias table in `ImporterFieldSchema` |
| GV-2074 | Auto-map columns (Org Manager) | PR 3 | Same |
| GV-2075 | Row validation (Super Admin) | PR 4 | `RowValidator`, `ValidationContext`, `RowError` |
| GV-2076 | Row validation (Org Manager) | PR 4 | Same |
| GV-2077 | Conflict warnings (Super Admin) | PR 5 | `ConflictDetector` (5 batched queries), Accept-and-next modal |
| GV-2078 | Conflict warnings (Org Manager) | PR 5 | Same |
| GV-2079 | Review Import Setup (Super Admin) | PR 6 | `_step_review.blade.php`, preview endpoint, org-dropdown behaviour |
| GV-2080 | Review Import Setup (Org Manager) | PR 6 | Same; org dropdown auto-hidden for single-org users |
| GV-2081 | Import + notify (Super Admin) | PR 7 | `ImportOrchestrator`, `RowImporter`, `UserProvisioner`, mailables, service extensions |
| GV-2082 | Import + notify (Org Manager) | PR 7 | Same |
| — | Documentation | PR 8 | `docs/developer_docs/IMPORTER_MODULE.md`, `php artisan importer:docs` |

---

## Appendix A: QA checklist (manual)

- [ ] Download template, edit in Excel, re-save, upload → valid.
- [ ] Upload as Admin, Org Manager, Sub Admin → Admin/Sub Admin see the org dropdown, Org Manager does not.
- [ ] Switch language EN ↔ AR between steps → all messages re-localise without losing state.
- [ ] Start import, navigate away mid-import → result visible on return.
- [ ] 1 conflict, 1 warning, 5 errors in same file → each behaves per spec.
- [ ] Responsive at 320px, 768px, 1440px on every step.
- [ ] Replace file after error → new upload proceeds; mappings preserved if headers match.
- [ ] Rate-limit: burst > 60 uploads in 1h → 429.
- [ ] Hackathon program type → founder becomes team leader, co-founder becomes team member.
- [ ] General program type → `companies.cycle` set; no team formation written.
- [ ] New founder email → credentials mail queued.
- [ ] Existing founder email → enrolled mail queued (no password).
- [ ] Same program name + type + dates exists in another reachable org → row reuses it and inherits its org; different dates → new cycle created in dropdown org.
- [ ] Discard / complete a session, then upload a new file in the same UI → client auto-creates a fresh session and upload succeeds.
