# Startup Profile Module

The Startup Profile is the central founder-facing record in Incubator. It is
modelled as an `App\Company` row, owned by a single founder (a `users` row with
the `registered` role), and rendered through `ClientCompanyController` and the
blades under `resources/views/admin/clients/startup/`. Every neighbouring
module — Programs, Mentorship, Screening, Alumni, Forms — reaches into this
record to resolve "what startup does this user belong to?" and "what does the
startup look like right now?", so the profile doubles as both a founder
dashboard and a reference object for role-scoped viewers (Admin, Organization
Manager, Sub Admin, Mentor, Judge).

This document is the single source of truth for the module as of EPIC GV-2049.
It covers the current state of the codebase (data model, controller map,
view layer, per-section field reference) and a per-ticket changelog mapping
each GV-#### entry to the concrete columns, methods, blades, and i18n keys it
touched.

---

## Table of Contents

- [1. Overview](#1-overview)
- [2. Data model](#2-data-model)
- [3. Controller map](#3-controller-map)
- [4. View layer](#4-view-layer)
- [5. Per-section field reference](#5-per-section-field-reference)
- [6. Epic GV-2049 changelog](#6-epic-gv-2049-changelog)

---

## 1. Overview

### 1.1 What the Startup Profile is

A **Startup Profile** is the aggregate of:

- One row in `companies` (identity, registration, description, business
  model, financial, statistics, social, system columns).
- Zero or more rows in `team_members` (founders, shareholders, investors).
- Zero or more rows in `employees` (individual employee records).
- Zero or more rows in `milestones`.
- Zero or more rows in `financial_projections` (currency-locked per-year
  valuations).
- Zero or more rows in `company_expertise` (pivot against `expertises`).
- Zero or more rows in `company_relations` (industry / sector / technology
  mappings driven by `static_categories`).
- Zero or more rows in `startup_customer_growth` (growth records).

A founder can author one profile. Other roles read it in progressively
narrower slices.

### 1.2 Who interacts with it

| Role                     | Primary interaction                                                                             | Route / permission |
| ------------------------ | ----------------------------------------------------------------------------------------------- | ------------------ |
| Founder (`registered`)   | Owns the record. Can edit every tab via `/dashboard/myCompany/{id?}`.                            | `company.profile_view_mine`, `company.profile_update_mine` |
| Admin                    | Read-write across all startups. Same edit route, `$id` is required.                              | `company.profile_view`, `company.profile_view_mine` |
| Organization Manager     | Read-write, scoped by `cycles.organization_id`. `updateOrStore` runs an org-scope SQL guard before touching the row (see `ClientCompanyController.php:1014-1034`). | `company.profile_view_mine` |
| Sub Admin                | Read-only via `/dashboard/companyProfile/{id}`. `editOrCreate` honours `$user->organizations_ids` via the shared `user_organizations` pivot when resolving scope. | `company.profile_view` |
| Judge (`judger`)         | Read-only during screening. Lands on the profile from the screening flow. Evaluation payload (`$evaluationForm`, `$formData`) is only computed when the company is attached to an evaluation the judge can reach. | `company.profile_view` |
| Mentor                   | Read-only during mentorship. Sees the same profile view but without the edit CTA.                | `company.profile_view` |

Every read view is routed through `ClientCompanyController@show` and every
write routes through `ClientCompanyController@updateOrStore`. There is no
separate "admin view" blade — the same `resources/views/admin/clients/startup/company_profile.blade.php`
is rendered for all viewing roles and hides the edit CTA for non-owners.

### 1.3 Top-level tabs

The read-only profile is organised into 7 top-level tabs driven by
`$pills = ['overview', 'milestones', 'team', 'pitch_deck', 'programs_summary',
'financial_report']` (plus `startup_growth` when growth records exist) at
`resources/views/admin/clients/startup/company_profile.blade.php:149-156`:

1. **Overview** — business profile summary, SDG icons, strategy tabs
   (`why_this_startup`, `mission`, `achievements`, `startup_expertise`),
   Registration & Offering Details card, team thumbnails. Backed by
   `resources/views/admin/clients/startup/partials/_startup_overview.blade.php`.
2. **Milestones** — all `milestones` rows grouped by visibility and cohort.
3. **Team** — employee statistics KPI cards (`$employeesKpis` computed in
   `show()` — see section 4.5), followed by founders / shareholders /
   investors thumbnails.
4. **Pitch Deck** — links to `pitch_deck`, `pitch_deck_video`, and any
   attached `documents` blob.
5. **Programs Summary** — aggregate of alumni + current cycle metrics
   (mentorship hours, training hours, final-screening averages, attendance).
6. **Financial Report** — read-only view of the 4-year financial projection
   table, currency-aware.
7. **Startup Growth** — customer-growth line chart fed by
   `startup_customer_growth`.

The **edit side** (`resources/views/admin/clients/company.blade.php`)
exposes a different set of tabs, one per form partial. These are the
authoring surface and are documented in section 4.2.

### 1.4 Neighbouring modules

- **Forms** — Founders answer forms attached to program steps. `Company::form()`
  (in `app/Company.php:393`) resolves the `BuilderForm` for the startup's
  current step. The forms module itself is documented in
  `docs/developer_docs/FORMS_MODULE.md`.
- **Programs** — A company's `cycle` column foreign-keys into `cycles`.
  `$programsSummary` in `show()` aggregates mentorship + training +
  final-screening metrics across the company's current cycle and its
  `almunies` rows.
- **Mentorship** — `MentorshipRequest` rows are counted into the
  `totalMentorshipHours`, `avgAttendanceRate`, and `mentor_connect`
  achievement card.
- **Screening** — `ScreeningEvaluation::getEvaluationData()` is called in
  `show()` to pre-populate the judges' scoring UI when a company is open at
  a screening step.
- **Alumni** — `App\Almuni` rows store per-cycle historic performance. The
  profile reads them in two places: the achievements card and the programs
  summary.

---

## 2. Data model

### 2.1 The `companies` table

The table was created in
`database/migrations/2024_04_18_000041_create_companies_table.php` and has
been extended by 25+ subsequent migrations. The full column list below is
grouped by concern. "Added in" cites the migration file that first
introduced the column.

`Schema::getColumnListing('companies')` is the authoritative check; every
column in this section resolves against one of the migrations under
`database/migrations/`.

#### 2.1.1 Identity & registration

| Column                        | Type              | Nullable | Added in                                                              | Purpose |
| ----------------------------- | ----------------- | -------- | --------------------------------------------------------------------- | ------- |
| `id`                          | bigint pk         | no       | `2024_04_18_000041_create_companies_table.php`                        | Primary key. |
| `country_id`                  | foreignId         | yes      | `2024_04_18_000041_create_companies_table.php`                        | FK → `countries.id`, on delete set null. |
| `state_id`                    | foreignId         | yes      | `2024_04_18_000041_create_companies_table.php`                        | FK → `states.id`. Renamed label to "Governorate / State" in GV-2050. |
| `city_id`                     | foreignId         | yes      | `2024_04_18_000041_create_companies_table.php`                        | FK → `cities.id`. |
| `name_en`                     | string            | no       | `2024_04_18_000041_create_companies_table.php`                        | English company name. `Company::getNameAttribute()` returns the locale-appropriate one. |
| `name_ar`                     | string            | no       | `2024_04_18_000041_create_companies_table.php`                        | Arabic company name. Auto-mirrored from `name_en` in CompanyN=1 save path. |
| `address_en`                  | string            | no       | `2024_04_18_000041_create_companies_table.php`                        | English postal/street address. |
| `address_ar`                  | string            | no       | `2024_04_18_000041_create_companies_table.php`                        | Arabic postal/street address. Auto-mirrored from `address_en`. |
| `est_date`                    | date              | yes      | `2024_04_18_000041_create_companies_table.php`                        | Establishment date. |
| `years_of_operations`         | string(50)        | yes      | `2024_12_05_090800_add_years_of_operations_to_companies_table.php`    | One of `0-2`, `2-5`, `5-10`, `10+`. |
| `product_or_service`          | string(50)        | yes      | `2024_12_05_094516_add_product_or_service_to_companies_table.php`     | `Product`, `Service`, or `Product + Service`. |
| `occupation`                  | string(75)        | yes      | `2024_12_26_024201_add_sharks_columns_to_companies_table.php`         | Free-form occupation label. |
| `CreatedTax`                  | string/flag       | yes      | (pre-epic, manipulated in CompanyN=1)                                 | `0`/`1` toggle: "Have you registered your startup?" |
| `Commercial_Register`         | string            | yes      | (pre-epic)                                                            | Uploaded PDF/image path (`disk: document`). |
| `Tax_Card`                    | string            | yes      | (pre-epic)                                                            | Uploaded PDF/image path. |
| `Country_Register_id`         | unsignedBigInt    | yes      | `2025_02_05_231351_allow_nullable__country__register_id_at_companies_table.php` | Country of legal registration. |
| `previous_incubation`         | string(1)         | yes      | `2026_04_14_104428_add_previous_incubation_and_registration_to_companies_table.php` | **GV-2050.** `'0'` / `'1'` flag. Stored as string, not enum. |
| `previous_incubator`          | string(150)       | yes      | `2026_04_14_104428_add_previous_incubation_and_registration_to_companies_table.php` | **GV-2050.** Name of the previous incubator. Server-side cleared to `null` when `previous_incubation == '0'`. |
| `company_registration_type`   | string(100)       | yes      | `2026_04_14_104428_add_previous_incubation_and_registration_to_companies_table.php` | **GV-2050.** One of `Not registered yet`, `Individual (Sole Proprietorship)`, `Limited Liability Company (LLC)`, `One Person Company (OPC)`, `Partnership`, `Shareholding`, `Other`. Stored as the exact string, not a numeric enum. |
| `products_services_description` | string(1000)    | yes      | `2026_04_14_104428_add_previous_incubation_and_registration_to_companies_table.php` | **GV-2050.** Short description of offering. |

#### 2.1.2 Description

| Column                        | Type              | Nullable | Added in                                                              | Purpose |
| ----------------------------- | ----------------- | -------- | --------------------------------------------------------------------- | ------- |
| `info_company`                | text              | yes      | `2024_10_11_210730_add_columns_to_companies_table.php`                | Short "About the Company" blurb. |
| `idea`                        | mediumText        | yes      | `2024_04_18_000041_create_companies_table.php`                        | Legacy "Startup idea" field. Unused by any GV-2049 blade. |
| `problem`                     | mediumText        | yes      | `2024_04_18_000041_create_companies_table.php`                        | Legacy problem statement. |
| `solution`                    | mediumText        | yes      | `2024_04_18_000041_create_companies_table.php`                        | Legacy solution statement. |
| `about_problem`               | longText          | yes      | `2024_07_31_142045_add_cols_about_to_companies_table.php` (promoted to longText by `2025_01_30_104212_change_columns_to_longtext_in_companies_table.php`) | "About the problem you're solving." |
| `about_market_opportunity`    | longText          | yes      | same                                                                  | Market opportunity description. |
| `about_competitors`           | longText          | yes      | same                                                                  | Competitor landscape. |
| `about_monetize`              | longText          | yes      | same                                                                  | Monetisation model description. |
| `about_challenges`            | longText          | yes      | same                                                                  | Key challenges. |
| `Sustainable_development`     | text (JSON)       | yes      | `2024_10_06_175259_add__sustainable_development_on_table_companies.php` | JSON-encoded array of selected SDG keys (`no_poverty`, `zero_hunger`, …). Label renamed to "Sustainable Development" (with a space) by GV-2051 — the column name is unchanged. |
| `achievements`                | text              | yes      | `2026_04_14_111032_add_description_fields_to_companies_table.php`     | **GV-2051.** Max 1000 chars (enforced in validation, not the column). |
| `mission`                     | text              | yes      | same                                                                  | **GV-2051.** Mission statement, max 1000 chars. |
| `why_this_startup`            | text              | yes      | same                                                                  | **GV-2051.** "Why this startup?" narrative, max 1000 chars. |
| `startup_expertise`           | text              | yes      | same                                                                  | **GV-2051.** Free-form expertise description, max 1000 chars. Distinct from the `company_expertise` pivot (GV-2053). |

> **Why TEXT and not VARCHAR(1000)?** GV-2051 chose `text` over `string(1000)`
> because `companies` already carries several `longText` columns and a number
> of wide `string` columns — adding four more 3000-byte-equivalent UTF-8 MB4
> columns risked tripping the InnoDB 65 535-byte row-size limit. `text`
> off-row storage sidesteps the problem. Max length is enforced in the
> controller's `max:1000` validation rule, not in the schema.

#### 2.1.3 Business model

| Column                        | Type              | Nullable | Added in                                                              | Purpose |
| ----------------------------- | ----------------- | -------- | --------------------------------------------------------------------- | ------- |
| `business_model`              | string            | yes      | `2024_07_30_133703_add_cols_pitch_to_companies_table.php`             | One of `Marketplace_model`, `On_demand_model`, `Disintermediation_model`, `Subscription_model`, `Freemium_model`, `Virtual_good_model`, `Reseller_model`, `Other`. Also overloaded as a file-path column in older code; in CompanyN=13 it is the selection string. |
| `other_business_model`        | string            | yes      | `2024_11_16_134634_add_other_business_model_to_companies_table.php`   | Free-text alternative when `business_model == 'Other'`. |
| `business_model_description`  | string            | yes      | `2024_07_30_133703_add_cols_pitch_to_companies_table.php`             | Short description (required, max:255). |
| `mvp_prototype_link`          | string(500)       | yes      | `2026_04_14_120000_add_mvp_prototype_link_to_companies_table.php`     | **GV-2054.** Public URL for the working prototype. Validated as URL, max:500. |
| `business_plan`               | string            | yes      | `2024_07_30_133703_add_cols_pitch_to_companies_table.php`             | Uploaded business-plan file path (`disk: pitch_deck`). |
| `business_plan_description`   | string            | yes      | same                                                                  | Short business-plan description. |
| `pitch_deck`                  | string            | yes      | same                                                                  | Uploaded pitch-deck file path. |
| `pitch_deck_description`      | string            | yes      | same                                                                  | Long pitch description (historically). |
| `pitch_deck_summary`          | string            | yes      | `2024_10_11_210730_add_columns_to_companies_table.php`                | Short pitch summary. |
| `pitch_deck_video`            | string            | yes      | same                                                                  | External video URL (YouTube / Vimeo). |
| `financial_plan`              | string            | yes      | `2024_07_30_133703_add_cols_pitch_to_companies_table.php`             | Uploaded financial-plan file path. File type and size restricted by GV-2138. |
| `financial_plan_description`  | string            | yes      | same                                                                  | Supporting notes. |
| `documents`                   | longText (JSON)   | yes      | `2024_10_11_210730_add_columns_to_companies_table.php`                | JSON array of uploaded supplementary document paths (`CompanyN=11`). |
| `business_categories`         | json              | yes      | `2024_12_05_102625_add_business_categories_to_companies_table.php`    | JSON array. Populated from `App\Company::static_categories()`. Cast to `array` in the model. |
| `business_phase`              | string(255)       | yes      | `2024_12_26_024201_add_sharks_columns_to_companies_table.php` (widened in `2025_02_19_092748_update_business_phase_length_in_companies_table.php`) | Legacy lifecycle stage indicator. |
| `business_type`               | string            | yes      | `2024_07_21_205612_add_col_business_type_to_companies_table.php`      | One of `B2C`, `B2B`, `B2B2C`, `C2C`, `B2G`, `Platform`. This column doubles as the "Offering Type" shown in the GV-2063 Registration & Offering Details section. |
| `institutions`                | text              | yes      | `2024_07_21_205612_add_col_business_type_to_companies_table.php` (nullable from `2025_02_18_221659_modify_email_phone_nullable_in_companies_table.php`) | JSON string of institutions. Exposed via `getInstitutionsAttribute`. |
| `development_stage`           | string            | yes      | `2024_07_22_214338_add_cols_to_companies_table.php`                   | Funding stage: `Pre_seed_funding_stage`, `Seed_funding_stage`, `Series_A_funding`, `Series_B_funding`, `Series_C_funding`. |
| `stage`                       | string            | yes      | `2024_04_18_000041_create_companies_table.php`                        | FK-ish into `stages` (stored by id). Resolved via `stagE()` relation. |
| `funding_stage`               | string            | yes      | `2024_11_16_180132_add__funding_stage_to_companies_table.php`         | Additional funding-stage label (non-normalised). |
| `sector`                      | string(200)       | yes      | `2024_04_18_000041_create_companies_table.php`                        | Primary sector id — also mirrored through `company_relations`. |

#### 2.1.4 Financial

| Column                        | Type              | Nullable | Added in                                                              | Purpose |
| ----------------------------- | ----------------- | -------- | --------------------------------------------------------------------- | ------- |
| `Currency`                    | string            | yes      | `2024_10_11_210730_add_columns_to_companies_table.php`                | ISO currency code, e.g. `USD`. |
| `Pre_money_Valuation`         | string            | yes      | same                                                                  | Stored as a string; validated as numeric in CompanyN=4. |
| `Funding_Target`              | float(115,2)      | no (default 0) | `2024_10_19_120835_add_new_column_in_companies.php`             | Target raise amount. |
| `raised_fund`                 | string            | yes      | `2024_04_18_000041_create_companies_table.php`                        | Amount raised so far (free-form string, numeric-validated). |
| `equity_offered`              | string(25)        | yes      | `2024_12_26_024201_add_sharks_columns_to_companies_table.php`         | Percentage of equity offered. |
| `annual_revenue`              | string(25)        | yes      | `2024_12_26_024201_add_sharks_columns_to_companies_table.php`         | Legacy annual-revenue column. Not used by the GV-2049 financial tab; kept for backward compatibility with older screening forms. |
| `revenue_generating`          | string            | yes      | `2024_07_22_214338_add_cols_to_companies_table.php`                   | Legacy yes/no flag. **Superseded by `generate_revenue` in GV-2058** but not dropped. See "known gaps". |
| `generate_revenue`            | string(3)         | yes      | `2026_04_14_134341_add_revenue_fields_to_companies_table.php`         | **GV-2058.** `yes` / `no`. Validated `required|in:yes,no` in CompanyN=4. |
| `average_annual_revenues`     | unsignedBigInteger| yes      | `2026_04_14_134341_add_revenue_fields_to_companies_table.php`         | **GV-2058.** Integer amount (max 999 999 999 999, 12 digits). Server-side cleared to `null` when `generate_revenue === 'no'`. |
| `currently_fundraising`       | string            | yes      | `2024_07_22_214338_add_cols_to_companies_table.php`                   | Yes/no flag. |
| `desired_funding_amount`      | string            | yes      | same                                                                  | Target ask for current round. |
| `targeted_market`             | string (JSON)     | yes      | `2024_10_11_210730_add_columns_to_companies_table.php`                | JSON-encoded array of country IDs. |

> **Why `unsignedBigInteger` for `average_annual_revenues`?** GV-2058's
> acceptance criteria reject decimals and negative values. Using
> `unsignedBigInteger` lets MySQL enforce both invariants at the column
> level — decimals would be truncated on insert and negative values rejected
> — without having to drag a decimal-type mismatch through the legacy
> `annual_revenue` column.

#### 2.1.5 Team & statistics

| Column                        | Type              | Nullable | Added in                                                              | Purpose |
| ----------------------------- | ----------------- | -------- | --------------------------------------------------------------------- | ------- |
| `team_num`                    | string(255)       | yes      | `2024_04_18_000041_create_companies_table.php` (widened by `2024_12_04_153104_change_team_num_companies_table=companies.php`) | Team-size bucket, one of the `startup.startup_size_1` … `startup.startup_size_9` i18n buckets. |
| `working_team_summary`        | text              | yes      | `2024_10_11_210730_add_columns_to_companies_table.php`                | Free-form team-narrative text. |
| `founder`                     | string            | yes      | `2024_04_18_000041_create_companies_table.php`                        | Legacy founder-name column. Unused by GV-2049 flows. |
| `employees`                   | string            | yes      | `2024_04_18_000041_create_companies_table.php`                        | Legacy employee-count column (a string). Still read by the "team_builder" achievement. Individual employee records live in the `employees` table. |
| `collaborators`               | text              | yes      | `2024_12_26_024201_add_sharks_columns_to_companies_table.php`         | Free-form list of collaborators. |
| `total_employees`             | unsignedInteger   | yes      | `2026_04_14_131705_add_employee_statistics_to_companies_table.php`    | **GV-2056.** Overall employee headcount, founder-reported. Distinct from `COUNT(employees)`. Cast to `integer` in the model. |
| `male_employees`              | unsignedInteger   | yes      | same                                                                  | **GV-2056.** Overall male headcount. Optional. |
| `female_employees`            | unsignedInteger   | yes      | same                                                                  | **GV-2056.** Overall female headcount. Optional. Validator enforces `male + female === total` when both are supplied. |
| `investors`                   | string            | yes      | `2024_04_18_000041_create_companies_table.php`                        | Legacy investor list. Superseded by the `team_members` rows where `TeamMember='Investor'`. |

> **Why overall statistics columns instead of `SELECT COUNT(*) FROM employees`?**
> GV-2056 explicitly models the three numbers as **self-reported totals**.
> Founders frequently list only a handful of key employees in the individual
> records tab but know their real headcount. The KPI cards in GV-2057 consume
> the three columns directly; they never re-derive totals from the
> `employees` table.

#### 2.1.6 Social

| Column                        | Type              | Nullable | Added in                                                              | Purpose |
| ----------------------------- | ----------------- | -------- | --------------------------------------------------------------------- | ------- |
| `phone`                       | string            | yes      | `2024_04_18_000041_create_companies_table.php` (nullable from `2025_02_18_221659_modify_email_phone_nullable_in_companies_table.php`) | Primary phone. |
| `country_code`                | string            | yes      | `2024_10_20_133347_add_country_code_in_companies_table.php`           | Phone country code. |
| `country_code_w_a`            | string            | yes      | `2025_01_12_201907_add_new_coulmn__country_code_w_a_in_companies_table.php` | WhatsApp country code. |
| `whatsapp`                    | integer           | yes      | `2024_10_11_210730_add_columns_to_companies_table.php`                | WhatsApp number. |
| `email`                       | string            | yes      | `2024_04_18_000041_create_companies_table.php`                        | Primary email. |
| `fax`                         | string(250)       | yes      | same                                                                  | Legacy fax. |
| `website`                     | string(250)       | yes      | same                                                                  | Public site. |
| `facebook`                    | string            | yes      | same                                                                  | URL. |
| `twitter`                     | string            | yes      | same                                                                  | URL. |
| `linkedin`                    | string            | yes      | same                                                                  | URL. |
| `youtube`                     | string            | yes      | same                                                                  | URL. |
| `google_map`                  | text              | yes      | same                                                                  | Free-form map URL / embed. |
| `instagram`                   | string            | yes      | `2025_01_26_120558_add_instagram_and_tiktok_to_companies_table.php`   | URL. |
| `tiktok`                      | string            | yes      | same                                                                  | URL. |
| `logo`                        | string(250)       | yes      | `2024_04_18_000041_create_companies_table.php`                        | Path under `public_uploads/images/logo/`. `getLogoUrlAttribute()` falls back to `/app-assets/images/logo_placeholder.png`. |
| `product_photo`               | text (array)      | yes      | `2024_12_26_024201_add_sharks_columns_to_companies_table.php`         | Cast to `array` in the model. |
| `product_video`               | string(250)       | yes      | same                                                                  | Video URL. |

#### 2.1.7 System / lifecycle

| Column                        | Type              | Nullable | Added in                                                              | Purpose |
| ----------------------------- | ----------------- | -------- | --------------------------------------------------------------------- | ------- |
| `cycle`                       | unsignedBigInt    | yes      | `2024_04_18_000041_create_companies_table.php`                        | FK → `cycles.id`. Current program. |
| `step`                        | unsignedBigInt    | yes      | same                                                                  | FK → `steps.id`. Current step within the cycle. |
| `approved`                    | integer           | yes      | same                                                                  | Approval state. |
| `approved_by`                 | unsignedBigInt    | yes      | same                                                                  | FK → `users.id`. |
| `user_id`                     | unsignedBigInt    | yes      | (mass-assignable via fillable; actual link is `users.company_id`)     | Listed in `$fillable` but in practice the founder-company link is maintained via the reverse `users.company_id` column; `Company::user()` uses `belongsTo(User::class, 'id', 'company_id')`. |
| `hear_about_us`               | text              | yes      | `2024_12_26_024201_add_sharks_columns_to_companies_table.php`         | Acquisition channel. |
| `industries`                  | string            | yes      | `2024_10_11_210730_add_columns_to_companies_table.php`                | Legacy single-industry slot. Modern classification lives in `company_relations`. |
| `technologies_used`           | — (virtual)       | —        | —                                                                     | Declared in `$fillable` for legacy code paths but not backed by a migration on the current schema. Use `company_relations.technology_id`. |
| `join_stock_company`          | string(255)       | yes      | `2025_01_02_110614_add_join_stock_company_to_companies_table.php`     | Legacy flag. |
| `created_at`                  | timestamp         | yes      | `2024_04_18_000041_create_companies_table.php`                        | |
| `updated_at`                  | timestamp         | yes      | same                                                                  | |
| `deleted_at`                  | timestamp         | yes      | same                                                                  | Soft deletes. |

### 2.2 The `team_members` table

Created by
`database/migrations/2024_10_11_201932_create_team_members_table.php`.
`App\Team_member` uses `$guarded = []` so every new column is automatically
fillable.

| Column                | Type      | Nullable | Added in                                                              | Purpose |
| --------------------- | --------- | -------- | --------------------------------------------------------------------- | ------- |
| `id`                  | bigint pk | no       | creation                                                              | |
| `TeamMember`          | string    | no       | creation                                                              | Discriminator. One of `Founder`, `Shareholder`, `Investor`. |
| `Name`                | string    | no       | creation                                                              | Display name. |
| `Email`               | string    | yes      | creation                                                              | Email (founders only). |
| `phone`               | string(20)| yes      | `2026_04_02_170927_add_phone_column_to_team_memebers_table.php`       | Added by GV-1956 (Startup Team epic) to back founder phone numbers. |
| `join_date`           | date      | yes      | `2026_04_15_214852_add_founder_fields_to_team_members_table.php`      | Founder join date (GV-1956). Cast to date on the model. |
| `founder_type`        | string(20)| yes      | `2026_04_15_214852_add_founder_fields_to_team_members_table.php`      | Founder type discriminator. GV-1956. One of `Founder`, `Co-founder`. |
| `working_commitment`  | string(20)| yes      | `2026_04_15_214852_add_founder_fields_to_team_members_table.php`      | Founder working commitment. GV-1956. One of `Full-time`, `Part-time`. |
| `LinkedIn`            | string    | yes      | creation                                                              | LinkedIn URL (founders only). |
| `RoleShereholder`     | string    | yes      | creation                                                              | Role label (shareholders only, misspelled column name preserved). |
| `PresentagShereholder`| integer   | no (def 0)| creation                                                              | Ownership percentage (shareholders only, misspelled column name preserved). |
| `investorType`        | string    | yes      | creation                                                              | Investment type (investors only). |
| `Company_id`          | unsignedBigInt | no  | creation                                                              | FK → `companies.id`, cascade delete. |
| `user_id`             | unsignedBigInt | yes | `2025_07_13_086547_add_user_id_and_team_id_in_team_members_table.php` | Optional link to a `users` row when the founder signs up. |
| `team_id`             | unsignedBigInt | yes | same                                                                  | Link to team formation. |
| `region`              | string    | yes      | `2025_08_08_105630_add_region_to_team_members_table.php`              | Geographic region label. |
| `accept_invition`     | tinyint   | yes      | `2025_07_17_112720_add_accept_invition_and_is_team_leader_to_team_members_table.php` | Invite-acceptance flag. |
| `is_team_leader`      | tinyint   | yes      | same                                                                  | Team-leader flag. |
| `code`                | string    | yes      | `2025_07_22_074852_add_code_and_expire_at_to_team_members_table.php`  | Invite code. |
| `expire_at`           | timestamp | yes      | same                                                                  | Invite expiry. |
| `gender`              | string    | yes      | `2025_11_13_113056_add_fields_to_team_members_table.php`              | Gender label. |
| `employment_type`     | string    | yes      | same                                                                  | Employment type. |
| `hiring_date`         | date      | yes      | same                                                                  | Hiring date. |
| `documents`           | json      | yes      | same                                                                  | Uploaded document bag, cast to `array`. |
| `created_at`, `updated_at`, `deleted_at` | timestamps | — | creation                                            | Soft deletes. |

> **GV-1953 landed on `users`, not `team_members`.** The epic plan originally
> proposed adding `national_id`, `date_of_birth`, and `education_status` to
> `team_members`, but the shipped migration
> `database/migrations/2026_04_14_103000_add_national_id_to_users_table.php`
> put `national_id` on `users` instead, alongside the pre-existing
> `users.academic_stat` column used for education status. Date-of-birth
> reuses `users.age` / the founder profile's existing DoB surface rather than
> duplicating on `team_members`. Founder identity fields are therefore
> sourced from the **owning user**, not the team-members row.

### 2.3 The `employees` table

Created by
`database/migrations/2024_11_17_000001_create_employees_table.php`.

| Column           | Type                                              | Nullable | Purpose |
| ---------------- | ------------------------------------------------- | -------- | ------- |
| `id`             | bigint pk                                         | no       | |
| `company_id`    | foreignId → `companies.id` (cascade)              | no       | |
| `full_name`     | string(100)                                       | no       | |
| `job_title`     | string(100)                                       | no       | |
| `gender`        | enum('male','female')                             | no       | |
| `employment_type` | enum('full-time','part-time','contractor','intern') | no    | |
| `hiring_date`   | date                                              | no       | Cast to `date`. |
| `documents`     | text (JSON)                                       | yes      | Cast to `array`. Populated via `addEmployee` / `editEmployee`. |
| `created_at`, `updated_at`, `deleted_at` | timestamps                | —        | Soft deletes. |

`App\Employee` fillables: `company_id`, `full_name`, `job_title`, `gender`,
`employment_type`, `hiring_date`, `documents`. Relation:
`company() → belongsTo(Company, 'company_id')`.

**Employees vs. overall statistics.** The `employees` table stores
individually identifiable staff. The three `total_employees` /
`male_employees` / `female_employees` columns on `companies` (GV-2056) are
unrelated: they are founder-reported overall headcounts. The KPI cards
displayed by GV-2057 read the `companies`-level columns and never
re-derive them from `COUNT(employees)`.

### 2.4 The `milestones` table

Created by
`database/migrations/2024_10_11_202321_create__milestones_table.php` and
extended by several subsequent migrations. Relevant columns (as of the
current schema):

| Column           | Type      | Nullable | Added in                                                         | Purpose |
| ---------------- | --------- | -------- | ---------------------------------------------------------------- | ------- |
| `id`             | bigint pk | no       | creation                                                         | |
| `name`           | tinyText  | yes      | creation                                                         | Milestone title. |
| `description`    | tinyText  | yes      | creation                                                         | Free-form description. |
| `Company_id`     | unsignedBigInt | no  | creation                                                         | FK → `companies.id`, cascade delete. |
| `completion_percentage` | integer | yes  | `2025_10_10_143156_add_completion_and_documents_to_milestones_table.php` | 0–100 integer. |
| `documents`      | json      | yes      | same                                                             | Attached file bag. |
| `category`, `status`, `date`, `visibility`, `cohort` | various | yes | `2025_12_09_125853_add_fields_to_milestones_table.php` | Added for the new milestones UI. |
| `plan`           | —         | —        | removed by `2025_10_10_143624_remove_plan_field_from_milestones_table.php` | Legacy column dropped. |

`Company::milestones()` is `hasMany(Milestone::class, 'Company_id')`.

### 2.5 The `company_expertise` pivot table (GV-2053)

Created by
`database/migrations/2026_04_14_120000_create_company_expertise_table.php`:

```php
Schema::create('company_expertise', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('company_id');
    $table->unsignedBigInteger('expertise_id');
    $table->timestamps();

    $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
    $table->foreign('expertise_id')->references('id')->on('expertises')->cascadeOnDelete();
    $table->unique(['company_id', 'expertise_id']);
});
```

- **Cascade on delete** from both sides — wiping a company or an expertise
  row cleans up its pivot entries automatically.
- **Unique pair** `(company_id, expertise_id)` prevents duplicate selections.
- **`id` + timestamps** — the table is a "rich pivot" so Laravel can use
  `withTimestamps()` and so a unique id is always available.

The two relations:

```php
// app/Company.php:429-437
public function expertises()
{
    return $this->belongsToMany(
        \App\Expertise::class,
        'company_expertise',
        'company_id',
        'expertise_id'
    )->withTimestamps();
}

// app/Expertise.php:36-44
public function companies()
{
    return $this->belongsToMany(
        \App\Company::class,
        'company_expertise',
        'expertise_id',
        'company_id'
    )->withTimestamps();
}
```

Only top-level `expertises` (those with `parent_id IS NULL`) are presented as
"Areas of Expertise" in the Support & Mentorship tab — see
`ClientCompanyController::editOrCreate` at `app/Http/Controllers/ClientCompanyController.php:925-944`.

### 2.6 Eloquent relationships on `App\Company`

Read `app/Company.php` directly. Every public method:

| Method                 | Relation                                              | Purpose |
| ---------------------- | ----------------------------------------------------- | ------- |
| `user()`               | `belongsTo(User, 'id', 'company_id')`                 | Non-standard keys: matches `companies.id` to `users.company_id`. |
| `resolveUser()`        | helper                                                | Returns `$this->user` or falls back to `$this->users->first()`. |
| `users()`              | `hasMany(User)`                                       | Users attached to this company. |
| `country()`, `state()`, `city()` | `belongsTo` each                            | Address foreign keys. |
| `sectoR()`             | `belongsTo(Sector, 'sector')`                         | Note the capital-R to avoid clobbering the `sector` column accessor. |
| `stepdata()`           | `belongsTo(Step, 'step')`                             | Current cycle step. |
| `cycledata()`          | `belongsTo(Cycle, 'cycle')`                           | Current cycle. |
| `cycle()`              | `belongsTo(Cycle, 'cycle', 'id')`                     | Alias used by the programs summary. |
| `cycleRelation()`      | `belongsTo(Cycle, 'cycle')`                           | Third alias for historical reasons. |
| `stagE()`              | `belongsTo(Stage, 'stage')`                           | Funding stage by id. |
| `teamMembers()`        | `hasMany(Team_member, 'Company_id')`                  | Founders + shareholders + investors. |
| `employees()`          | `hasMany(Employee, 'company_id')`                     | Individual employee records. |
| `milestones()`         | `hasMany(Milestone, 'Company_id')`                    | Milestones. |
| `expertises()`         | `belongsToMany(Expertise, 'company_expertise', …)`    | **GV-2053.** Support & mentorship tags. |
| `companyRelations()`   | `hasMany(CompanyRelation, 'company_id', 'id')`        | Industry / sector / technology pivot. |
| `finalScreening()`     | `hasMany(Finalscreening, 'company_id')`               | Screening results. |
| `finalData()`          | `hasMany(FinalData, 'company_id')`                    | Per-field final-screening rows. |
| `formData()`           | `hasMany(Formdata, 'company_id')`                     | Raw form answers. |
| `fundingRounds()`      | `hasMany(FundingRound, 'company_id')`                 | Funding history. |
| `sustainableIcons()`   | `hasMany(SustainableIcon)`                            | SDG icons. |
| `companyWeight()`, `companyJudge()`, `judges()`, `finals()`, `fsession()`, `form()` | various helpers | Judging / form-resolution utilities (not simple Eloquent relations). |
| `static_categories()`  | static method                                         | Returns the category list from `StaticCategory::all()`. |
| `business_phase()`     | static method                                         | Hard-coded business-phase id map. |

Attributes defined on the model:

- `getLogoUrlAttribute()`
- `getBusinessModelUrlAttribute()`
- `getBusinessPlanUrlAttribute()`
- `getPitchDeckUrlAttribute()`
- `getFinancialPlanUrlAttribute()`
- `getNameAttribute()` — returns `name_en` or `name_ar` based on locale.
- `getInstitutionsAttribute()` — `json_decode`s the raw column.

Casts:

```php
protected $casts = [
    'business_categories' => 'array',
    'product_photo'       => 'array',
    'total_employees'     => 'integer',
    'male_employees'      => 'integer',
    'female_employees'    => 'integer',
];
```

### 2.7 Relationships on `App\Team_member` and `App\Employee`

```php
// app/Team_member.php
class Team_member extends Model
{
    protected $table = 'team_members';
    protected $guarded = [];
    protected $casts = ['documents' => 'array'];

    public function projects()
    {
        return $this->belongsToMany(Project::class, 'project_team_member')
            ->withPivot(['role', 'created_at'])
            ->withTimestamps();
    }

    public function teamFormation()
    {
        return $this->belongsTo(TeamFormation::class, 'team_formation_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
```

```php
// app/Employee.php
class Employee extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id', 'full_name', 'job_title',
        'gender', 'employment_type', 'hiring_date', 'documents',
    ];

    protected $casts = [
        'hiring_date' => 'date',
        'documents'   => 'array',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }
}
```

---

## 3. Controller map

### 3.1 Web routes

All routes live inside the `auth`-protected admin group in
`routes/web.php`. Route names match the Spatie permission keys.

| Method  | URI                                              | Name                                     | Controller method                                  | Purpose |
| ------- | ------------------------------------------------ | ---------------------------------------- | -------------------------------------------------- | ------- |
| GET     | `/companyProfile/{id?}`                          | `company.profile_view`                   | `ClientCompanyController@show`                     | Read-only profile (all viewer roles). |
| GET     | `/myCompany/{id?}`                               | `company.profile_view_mine`              | `ClientCompanyController@editOrCreate`             | Edit form. Lazily creates a company row for founders who don't have one yet. |
| POST    | `/myCompany/{id?}`                               | `company.profile_update_mine`            | `ClientCompanyController@updateOrStore`            | Single write endpoint. Dispatches on the `CompanyN` hidden input. |
| POST    | `/AddCompanyTeams/{id?}`                         | `company.profile_teams_add`              | `ClientCompanyController@AddTeams`                 | Add a founder / shareholder / investor (AJAX). |
| POST    | `/AddCompanyEdit/{id?}`                          | `company.profile_teams_edit`             | `ClientCompanyController@EditTeams`                | Edit a team member (AJAX). |
| POST    | `/AddCompanyDelete/{id?}`                        | `company.profile_teams_delete`           | `ClientCompanyController@DeleteTeams`              | Soft-delete a team member. |
| POST    | `/AddCompanyMileStone/{id?}`                     | `company.profile_milestones_add`         | `ClientCompanyController@AddMileStone`             | Add a milestone. |
| POST    | `/EditCompanyMileStone/{id?}`                    | `company.profile_milestones_edit`        | `ClientCompanyController@EditMileStone`            | Edit a milestone. |
| POST    | `/DeleteCompanyMileStone/{id?}`                  | `company.profile_milestones_delete`      | `ClientCompanyController@DeleteMileStone`          | Delete a milestone. |
| POST    | `/delete-document`                               | `company.profile_documents_delete`       | `ClientCompanyController@deleteDocument`           | Remove an uploaded document. |
| POST    | `/delete-image/{id?}`                            | `company.profile_images_delete`          | `ClientCompanyController@deleteImage`              | Clear the logo. |
| POST    | `/save-statistics/{id?}`                         | `company.employees_statistics`           | `ClientCompanyController@saveStatistics`           | **GV-2056.** Dedicated save endpoint for the overall statistics side-form. |
| GET     | `/company/{id}/financial-projection/realtime`    | `company.financial_projection.realtime`  | `ClientCompanyController@getFinancialProjectionRealtime` | JSON read endpoint for the financial-projection table. |
| POST    | `/company/{id}/financial-projection/save`        | `company.financial_projection.save`      | `ClientCompanyController@saveFinancialProjection`  | JSON write endpoint for the financial-projection table. Supports single-field or full-form saves. |
| POST    | `/startupGrowth/add`                             | (unnamed)                                | `startupCustomerGrowthController@addRecord`        | Append a growth record. |
| GET     | `/companyProfile/{id}/alumni/{cycle_id}`         | `company.alumni_view`                    | `ClientCompanyController@companyAlumni`            | Alumni details for a past cycle. |
| GET     | `perks`                                          | `company.perks_view`                     | `ClientCompanyController@companyPerks`             | Perks listing. |
| GET     | `/employees/{id}`                                | `company.getEmployees`                   | `ClientCompanyController@getEmployees`             | Fetch employee list (AJAX). |
| POST    | `/employees/add/{id}`                            | `company.employees_add`                  | `ClientCompanyController@addEmployee`              | Add an individual employee record. |
| POST    | `/employees/edit/{id}`                           | `company.employees_edit`                 | `ClientCompanyController@editEmployee`             | Edit an individual employee record. |
| DELETE  | `/employees/delete/{id}`                         | `company.employees_delete`               | `ClientCompanyController@deleteEmployee`           | Soft-delete an employee. |
| POST    | `/AddCompanyalumni/{id}`                         | `company.addCompanyAlumni`               | `ClientCompanyController@addCompanyAlumni`         | Promote a company to alumni. |
| POST    | `/likeService`                                   | `services.like`                          | `ClientCompanyController@likeService`              | Like / unlike a service from within the profile. |
| GET     | `/companies`                                     | `users.view_companies`                   | `ClientCompanyController@companies`                | Admin startup listing. |
| GET     | `/companies/export`                              | `users.companies_export`                 | `CompanyExportController@export`                   | CSV/Excel export with query-param filters. |

### 3.2 API routes

Defined in `routes/api.php` inside the authenticated group.

| Method  | URI                                | Controller method                                   | Purpose |
| ------- | ---------------------------------- | --------------------------------------------------- | ------- |
| GET     | `/startups`                        | `API\CompanyController@index`                       | Paginated startup list. |
| GET     | `/v1/startups/{id}`                | `API\CompanyController@show`                        | Single startup read. |
| POST    | `/company/profile`                 | `API\CompanyController@update`                      | Update profile (uses `UpdateCompanyProfileRequest`). |
| POST    | `/company/founder`                 | `API\CompanyController@storeFounder`                | Add a founder `team_members` row. |
| PUT     | `/company/founder/{id}`            | `API\CompanyController@updateFounder`               | Edit a founder. |
| DELETE  | `/company/founder/{id}`            | `API\CompanyController@deleteFounder`               | Delete a founder. |
| POST    | `/company/shareholder`             | `API\CompanyController@storeShareholder`            | Add a shareholder. |
| PUT     | `/company/shareholder/{id}`        | `API\CompanyController@updateShareholder`           | Edit a shareholder. |
| DELETE  | `/company/shareholder/{id}`        | `API\CompanyController@deleteShareholder`           | Delete a shareholder. |
| POST    | `/company/investor`                | `API\CompanyController@storeInvestor`               | Add an investor. |
| PUT     | `/company/investor/{id}`           | `API\CompanyController@updateInvestor`              | Edit an investor. |
| DELETE  | `/company/investor/{id}`           | `API\CompanyController@deleteInvestor`              | Delete an investor. |
| POST    | `/company/milestones`              | `API\CompanyController@storeMilestone`              | Add a milestone. |
| PUT     | `/company/milestones/{id}`         | `API\CompanyController@updateMilestone`             | Edit a milestone. |
| DELETE  | `/company/milestones/{id}`         | `API\CompanyController@deleteMilestone`             | Delete a milestone. |
| GET     | `users/companies`                  | `UserController@companies`                          | Scoped company list (used by the mobile client). |

Each API verb maps to an explicit FormRequest class
(`StoreFounderRequest`, `UpdateFounderRequest`, `StoreShareholderRequest`,
…) located under `app/Http/Requests/`. Unlike the web surface, the API
layer does **not** use the `CompanyN` switcher — every semantic action has
its own endpoint. This is the surface that will back the forthcoming
external React app.

### 3.3 Controller methods (web surface)

#### `show(Request $request, $id)` — `app/Http/Controllers/ClientCompanyController.php:76`

- **Purpose:** render the read-only profile for any viewer role.
- **Inputs:** `$id` optional company id (falls back to the caller's own
  company via `CompanyService::getCompanyRelation`), `valuation_date` query
  param for the financial report.
- **Key side effects / computed data:**
  - Loads `team_members` grouped by `TeamMember` discriminator into
    `$founders`, `$Shareholders`, `$Investors`.
  - Filters visible milestones via `Milestone::where(...)->public()->get()`
    when the caller is not the owner.
  - Computes `$programsSummary` (mentorship hours, training hours,
    final-screening average, attendance rate) across the current cycle and
    all alumni rows.
  - Resolves the financial-projection periods (`past`, `year1`, `year2`,
    `year3`) into `$periods`, honouring `$request->valuation_date` with a
    fallback to the most-recent stored date.
  - Computes `$employeesKpis` (GV-2057) from `total_employees`,
    `male_employees`, `female_employees` with locale-aware `N/A` fallbacks
    (`app/Http/Controllers/ClientCompanyController.php:514-563`).
  - Computes `$registrationOfferingDetails` (GV-2063) from
    `company_registration_type`, `business_type`, and
    `products_services_description`, setting `has_any` to drive the
    empty-state banner (`ClientCompanyController.php:565-574`).
  - Computes `$achievements` (locked / unlocked badge cards).
- **Output:** `view('admin/clients/startup/company_profile', compact(...))`.
- **Auth:** delegates permission checks to `CompanyService::getCompanyRelation`
  which returns a `type == 'not_found'` sentinel on failure; callers are
  auth-gated via the `auth` middleware.

#### `editOrCreate(Request $request, $id = null)` — `ClientCompanyController.php:618`

- **Purpose:** render the edit form at `/dashboard/myCompany/{id?}`.
- **Inputs:** `$id` optional company id. When `null`, looks up
  `User::where('HaveCompany', 1)` for the current founder and lazily
  creates an empty company row if one is missing.
- **Scope guards:** Organization Managers and Sub Admins only see companies
  that belong to cycles whose `organization_id` sits in
  `$user->organizations_ids` (checked inline; no-permission branches
  redirect to `user.profile_view` with a flash).
- **Outputs:** `view('admin/clients/company', compact(...))` with:
  - `$countries`, `$states`, `$sectors`, `$Stages`, `$Currencies`.
  - `$founders`, `$Shareholders`, `$Investors` partitioned from
    `team_members`.
  - `$employees` (all Employee rows for the company).
  - `$milestones` (ordered by `created_at`).
  - `$industries`, `$technologies`, `$technologiesSelected`, `$data`
    (the industry → sector tree used by the classification form).
  - `$financialprojection`, `$valuation_date`, `$getCurrancy`,
    `$is_currency_disabled`, `$historicalValuationDates`, `$growth_records`,
    `$growthMetrics`.
  - **GV-2053:** `$areasOfExpertise` (array of
    `{id, name}` objects for top-level `expertises` rows), `$companyExpertiseIds`
    (currently-selected ids), and `$hasAreasOfExpertise` (boolean used to
    hide the Support & Mentorship tab when no active areas exist).

#### `updateOrStore(StoreUpdateClientCompanyRequest $request, $id = null)` — `ClientCompanyController.php:1004`

- **Purpose:** single write endpoint for every edit-tab form. Dispatches on
  the `CompanyN` hidden input that each form partial injects.
- **Inputs:** the request, optional `$id`.
- **Auth:** per-role scope guard identical to `editOrCreate` plus an extra
  SQL check for Organization Managers at lines 1014–1034. Falls through to
  the founder's own `company_id` when `$id` is null.
- **Outputs:** redirect or JSON depending on `CompanyN`. AJAX branches
  currently in use are `CompanyN == 14` (classification form) and
  `CompanyN == 16` (support & mentorship).
- **Errors:** catches `ValidationException` and returns
  `withErrors($e->validator)->withInput()` for non-AJAX, or a 422 JSON
  envelope for AJAX.

The full per-branch matrix is documented in section 3.4.

#### `AddTeams(Request $request, $id = null)` — `ClientCompanyController.php:2299`

- Dispatches on `TeamsNo` (`Founders`, `Shareholders`, `Investors`).
- Validates per-type fields and returns a JSON envelope on success/failure.
- Shareholder path enforces the total-ownership-percentage <= 100 constraint.
- Uses `new Team_member()` + `->save()`. The per-type column writes are
  exactly what `company-team-info.blade.php` + `app-team-info.js` expect.
- **GV-1956 (GV-1954):** the Founders branch now validates 7 fields (name/email/phone/linkedin/join_date/founder_type/working_commitment), enforces company-scoped email + phone duplicate checks inside a `DB::transaction` with `lockForUpdate`, and returns the saved row wrapped in `FounderResource` for in-place card rendering.

#### `EditTeams(Request $request, $id = null)` — `ClientCompanyController.php:2462`

- Loads the target `Team_member` by `$request->id`, branches on its
  discriminator column (`TeamMember`), re-runs validation, and persists.
- JSON-only (no HTML redirect branch).
- **GV-1956 (GV-1979):** the Founders branch was rewritten to mirror `AddTeams` — same validator, duplicate checks that exclude the current founder id, company-scope guard, and `FounderResource` response.

#### `DeleteTeams(Request $request, $id = null)` — `ClientCompanyController.php:2586`

- Soft-deletes a single team member by id.
- **GV-1956 (GV-1980):** added `auth()->user()` fallback when no route id is provided, company-scope guard on the `Team_member` lookup (prevents cross-company deletion), and differentiated response messages — `admin.founder_deleted` when the target is a founder, `admin.DeletedSuccessfully` otherwise.

#### `AddMileStone`, `EditMileStone`, `DeleteMileStone` — `ClientCompanyController.php:2612`, `2716`, `2930`

- Full validation stack: `name`, `description`, `completion_percentage`,
  `category`, `status`, `date`, `visibility`, `cohort`, `documents[]`.
- Accepts multiple documents via `mimes:pdf,jpg,jpeg,png,docx,xlsx,ppt,pptx|max:10240`.
- JSON responses, i18n-aware error messages from the `startup.*` namespace.

#### `addEmployee(Request $request, $id)` — `ClientCompanyController.php:3371`

- Validates `full_name`, `job_title`, `gender (male|female)`, `employment_type (full-time|part-time|contractor|intern)`, `hiring_date`, `documents[]`.
- Writes one `Employee` row, stores documents on `disk:public` under
  `employees/{id}/documents/`, returns JSON.
- Auth: permits the current user if they own the company or hold
  `Admin|organizationManager|registered`.

#### `editEmployee(Request $request, $id)` — `ClientCompanyController.php:3449`

- Same validation as `addEmployee` plus `id` (`exists:employees,id`) and
  `remove_documents` (JSON array of indices to drop).
- Physically deletes removed docs via `Storage::disk('public')->delete(...)`.

#### `deleteEmployee(Request $request, $id)` — `ClientCompanyController.php:3559`

- Reads the target employee id from either JSON body or form payload.
- Deletes associated document files and the row itself. Auth guard is the
  same as `addEmployee`.

#### `saveStatistics(Request $request, $id = null)` — `ClientCompanyController.php:4806`

**GV-2056.** Dedicated endpoint for the overall-statistics side-form inside
the Employees tab. Bypasses the `CompanyN` switcher:

- Validates `total_employees` (required, integer, min 0, max 999 999),
  `male_employees` and `female_employees` (nullable, integer, min 0, max
  999 999).
- Runs an `after` callback that asserts `male + female === total` when both
  are supplied.
- Persists `total_employees`, `male_employees`, `female_employees` on the
  company row and returns a JSON envelope with a bilingual success message
  (`admin.employee_statistics_updated`).
- Pre-GV-2056 the method body was commented out and the endpoint returned a
  stub `{"status":"success"}`.

#### `saveFinancialProjection(Request $request, int $id)` — `ClientCompanyController.php:4608`

- Full-form or single-field save into `financial_projections`.
- Validates `valuation_date` as `Y-m`, `Currency` as required string, and
  distributes the payload across the 4 year rows (offsets `-1, 0, 1, 2`).
- Recalculates derived metrics server-side (`ebitda`, `ebit`, `net_profit`,
  `working_capital`, `delta_wc`, `debt_change`, `free_cash_flow`) and
  returns them in the JSON response under `recalculated`.
- Wrapped in a DB transaction; rolls back on any exception.

#### `getFinancialProjectionRealtime(Request $request, int $id)` — `ClientCompanyController.php:4479`

- Read-only JSON endpoint backing the financial-projection table.
- Resolves the requested `valuation_date` (query param) or falls back to
  the most-recent historical date.
- Returns `{ success, valuation_date, currency, is_currency_locked,
  historical_dates, years: [{ year, label, data: { rev, cogs, … } }, …] }`.
- Auth: a registered user may only access their own company.

#### `companyAlumni($request, $id, $cycle_id)` — `ClientCompanyController.php:3076`

- Renders a read-only view of the startup's alumni record for a given past
  cycle.

### 3.4 The `CompanyN` switcher convention

Every form partial under `resources/views/partials/forms/company/` injects
a hidden `<input name="CompanyN" value="N">`. `updateOrStore` then branches
on the integer to route the save to the correct per-tab handler. Known
values (grepped from
`app/Http/Controllers/ClientCompanyController.php` and cross-referenced
against the blades):

| CompanyN | Tab / form                           | Owning blade                                                                   | Ticket        |
| -------: | ------------------------------------ | ------------------------------------------------------------------------------ | ------------- |
| 1        | Startup Info                         | `resources/views/partials/forms/company/company-info-form.blade.php`           | GV-2050       |
| 2        | Contact Info                         | `resources/views/partials/forms/company/company-contact.blade.php`             | pre-epic      |
| 3        | About / Description                  | `resources/views/partials/forms/company/company-desc.blade.php`                | GV-2051       |
| 4        | Financial Info                       | `resources/views/partials/forms/company/company-financial.blade.php`           | GV-2058, GV-2138 |
| 5        | Team size / summary                  | (shared input) — writes `team_num` + `working_team_summary`                    | pre-epic      |
| 6        | Pitch Deck info                      | `resources/views/partials/forms/company/company-pitch-deck.blade.php` (header) | pre-epic      |
| 8        | Social / website                     | `resources/views/partials/forms/company/company-social.blade.php`              | pre-epic      |
| 9        | Pitch Deck file upload               | `resources/views/partials/forms/company/_pitch_deck.blade.php` / `_pitch_deck_video.blade.php` / `company-pitch-deck.blade.php` | pre-epic |
| 10       | Pitch Deck video link                | `resources/views/partials/forms/company/company-pitch-deck.blade.php`          | pre-epic      |
| 11       | Supplementary documents upload       | `resources/views/partials/forms/company/company-pitch-deck.blade.php`          | pre-epic      |
| 12       | About / Problem (legacy form)        | `resources/views/partials/forms/company/about_problem.blade.php`               | pre-epic      |
| 13       | Business Model / MVP                 | `resources/views/partials/forms/company/business_model.blade.php`              | GV-2054       |
| 14       | Industry / Sectors / Classification  | `resources/views/partials/forms/company/classification_form.blade.php`         | pre-epic      |
| 15       | Financial Projection                 | `resources/views/partials/forms/company/company-financial-projection.blade.php`| pre-epic      |
| 16       | Support & Mentorship                 | `resources/views/partials/forms/company/company-support.blade.php`             | GV-2053       |

Notes:

- **There is no `CompanyN == 7`.** The switcher skips it and nothing in the
  blades references it — safe to ignore.
- **`CompanyN == 3` vs `CompanyN == 12`.** Both tabs are about "About /
  Problem" surfaces. `company-desc.blade.php` is the modern GV-2051 form
  that carries the new description fields. `about_problem.blade.php` is a
  legacy standalone form that reuses `CompanyN=12` to write the same
  `about_*` columns without touching the new fields. Both remain live;
  only the description tab is wired into the edit-side tab list.
- **`CompanyN == 16` was chosen to avoid colliding with `CompanyN == 14`
  (the classification form).** GV-2053 landed after GV-14 had already
  established the AJAX envelope pattern; reusing the same number would
  have broken the JSON / HTML dispatch logic.
- **`CompanyN == 14` is validated twice in the controller** — once inside
  its own `if` branch (industry, sectors, tech_tags) and again in the
  envelope validator (`validate(['industry_id' => [...]])` at line 1991),
  because the classification flow writes to a separate pivot table
  (`company_relations`) rather than the `companies` row itself.

### 3.5 Endpoints that bypass the CompanyN switcher

- **`company.employees_statistics`** (POST `/save-statistics/{id?}` →
  `saveStatistics`, GV-2056). Dedicated because the save is partial — only
  3 columns touched — and because the AJAX contract is a separate save
  button in a secondary tab rather than the primary form submit.
- **`company.financial_projection.realtime`** (GET, pre-epic). JSON read.
- **`company.financial_projection.save`** (POST, pre-epic). JSON write
  supporting both single-field and full-form modes. Bypassed from
  CompanyN=15 mainly so per-cell inline edits don't have to round-trip the
  entire `<form>`.

### 3.6 External API surface — `App\Http\Controllers\API\CompanyController`

All methods are defined on
`app/Http/Controllers/API/CompanyController.php` and reach 882 lines.
Constructor injects `CompanyService`. Public surface:

| Method signature                                              | Purpose |
| ------------------------------------------------------------- | ------- |
| `__construct(CompanyService $companyService)`                 | DI. |
| `index(Request $request)`                                     | Paginated list of startups. |
| `show($id)`                                                   | Single startup read — returns the profile as JSON. |
| `update(UpdateCompanyProfileRequest $request)`                | Profile write. |
| `storeFounder(StoreFounderRequest $request)`                  | Add a founder. |
| `updateFounder(UpdateFounderRequest $request, $id)`           | Edit a founder. |
| `deleteFounder($id)`                                          | Delete a founder. |
| `storeShareholder(StoreShareholderRequest $request)`          | Add a shareholder. |
| `updateShareholder(UpdateShareholderRequest $request, $id)`   | Edit a shareholder. |
| `deleteShareholder($id)`                                      | Delete a shareholder. |
| `storeInvestor(StoreInvestorRequest $request)`                | Add an investor. |
| `updateInvestor(UpdateInvestorRequest $request, $id)`         | Edit an investor. |
| `deleteInvestor($id)`                                         | Delete an investor. |
| `storeMilestone(StoreMilestoneRequest $request)`              | Add a milestone. |
| `updateMilestone(UpdateMilestoneRequest $request, $id)`       | Edit a milestone. |
| `deleteMilestone($id)`                                        | Delete a milestone. |

This API is **not** used by the incubator's Blade-rendered web UI — the
web surface uses `ClientCompanyController` — but it will back the external
React client the product team plans to ship against the same database.
Anyone extending the module should keep the two surfaces in lock-step:
when a new column appears in a CompanyN branch, it should also be wired
through `API\CompanyController::update` and its FormRequest.

---

## 4. View layer

### 4.1 Read-only profile blade

The profile surface is
`resources/views/admin/clients/startup/company_profile.blade.php` (1419
lines). It composes the following partials from
`resources/views/admin/clients/startup/partials/`:

| Partial                                 | Rendered inside tab               | Purpose |
| --------------------------------------- | --------------------------------- | ------- |
| `_startup_info.blade.php`               | (above the tab bar)               | Header card: logo, name, location, founders row, "View Prototype" button (GV-2055). |
| `_startup_badges.blade.php`             | Overview                          | Achievement badge cards (`$achievements`). |
| `_startup_overview.blade.php`           | Overview tab                      | Strategy tabs (why / mission / achievements / expertise, GV-2052), Registration & Offering Details card (GV-2063), business profile summary, SDG icons, profile-completion meter. |
| `_startup_about.blade.php`              | Overview / legacy                 | Legacy about section. |
| `_startup_milestones.blade.php`         | Milestones tab                    | Milestones timeline. |
| `_startup_founders.blade.php`           | Overview / Team                   | Founders thumbnails. GV-1956 (GV-1981) rewrote this partial to consume the real `$founders` collection from the controller, prepended a virtual owner card (first position, read-only), renders DD-MM-YYYY join dates, hides optional fields when empty, and shows a LinkedIn icon that opens in a new tab. |
| `_startup_shareholders.blade.php`       | Overview / Team                   | Shareholders thumbnails. |
| `_startup_investors.blade.php`          | Overview / Team                   | Investors thumbnails. |
| `_working_team.blade.php`               | Team                              | Legacy working-team summary block. |
| `_startup_pitch_deck.blade.php`         | Pitch Deck tab                    | Pitch deck viewer + download card. |
| `_startup_programs.blade.php`           | Programs Summary tab              | Aggregated program metrics from `$programsSummary`. |
| `_startup_financial_report.blade.php`   | Financial Report tab              | 4-year read-only financial projection table. Uses the "EBIT (Operating Income)" label from GV-2059. |
| `_startup_growth.blade.php`             | Startup Growth tab (conditional)  | Line chart of `growth_records`. |
| `_startup_screening_canvas.blade.php`   | (role-gated)                      | Judge screening sidebar. |
| `_session_modal.blade.php`, `_mutiple_session_modal.blade.php` | (modals) | Mentorship session booking. |

The 7 top-level tabs are computed in the blade's `@php` header:

```blade
@php
  $pills = ['overview', 'milestones', 'team', 'pitch_deck', 'programs_summary', 'financial_report'];
  if ($hasGrowthRecords) { $pills[] = 'startup_growth'; }
@endphp
```

### 4.2 Edit blade

`resources/views/admin/clients/company.blade.php` (534 lines) is the
authoring surface rendered by `editOrCreate`. Its tab list (collected at
lines 45–62) is:

```php
$tabs = collect([
    ['company-info',            'ti-building-skyscraper', 'Startup Info'],
    ['industry-sectors',        'ti-apps',                'Industry / Sectors'],
    ['company-contact',         'ti-phone',               'Contact Info'],
    ['company-desc',            'ti-file-description',    __('admin.about_or_description')],
    ['company-financial-info',  'ti-report-money',        'Financial Info'],
    ['company-financial-projection', 'ti-database-dollar', 'Financial Projection'],
    ['team-info',               'ti-briefcase',           __('startup.team_info')],
    ['employees',               'ti-users-group',         __('startup.employees_statistics')],
    ['pitch-deck',              'ti-presentation',        'Pitch Deck'],
    ['company-milestones',      'ti-flag',                'Milestones'],
    ['business_model',          'ti-cash-banknote',       'Business Model / MVP'],
    ['customer_growth',         'ti-trending-up',         'Customer Growth'],
]);
if ($showSupportTab) {
    $tabs->push(['support_mentorship', 'ti-brand-my-oppo', 'Support & Mentorship']);
}
```

`$showSupportTab` is bound to `$hasAreasOfExpertise` coming from the
controller, so the tab is hidden entirely when `expertises` has no
top-level rows (GV-2053 AC).

Tab → partial mapping:

| Tab target                     | Partial                                                                         | CompanyN   |
| ------------------------------ | ------------------------------------------------------------------------------- | ---------- |
| `company-info`                 | `partials/forms/company/company-info-form.blade.php`                            | 1          |
| `industry-sectors`             | `partials/forms/company/classification_form.blade.php`                          | 14         |
| `company-contact`              | `partials/forms/company/company-contact.blade.php`                              | 2          |
| `company-desc`                 | `partials/forms/company/company-desc.blade.php`                                 | 3          |
| `company-financial-info`       | `partials/forms/company/company-financial.blade.php`                            | 4          |
| `company-financial-projection` | `partials/forms/company/company-financial-projection.blade.php`                 | 15 (legacy) + dedicated JSON endpoints |
| `team-info`                    | `partials/forms/company/company-team-info.blade.php`                            | (dedicated team routes) |
| `employees`                    | `partials/forms/company/company-employees.blade.php`                            | (dedicated employee routes + `save-statistics`) |
| `pitch-deck`                   | `partials/forms/company/company-pitch-deck.blade.php`                           | 6, 9, 10, 11 |
| `company-milestones`           | `partials/forms/company/company-milestones.blade.php`                           | (dedicated milestone routes) |
| `business_model`               | `partials/forms/company/business_model.blade.php`                               | 13         |
| `customer_growth`              | `partials/forms/company/company-customers.blade.php`                            | (startupGrowth controller) |
| `support_mentorship`           | `partials/forms/company/company-support.blade.php`                              | 16         |

### 4.3 Shared JS validation — `public/vuexy/assets/js/pages-edit-compnay.js`

537 lines. Owns the FormValidation wiring for every CompanyN form that
needs client-side rules:

- **Per-tab validator instances.** Each form gets its own instance:
  - `editInfoFV` — `#edit-info-form` (Startup Info / CompanyN=1).
  - `editDescFV` — `#desc-form` (About / CompanyN=3).
  - `mvpFV` — `#mvp-form` (Business Model / CompanyN=13).
  - `financialFV` — `#financial-form` (Financial Info / CompanyN=4).
- **Per-field translations.** A top-of-file `translations` map provides
  `{ en, ar }` copies for required / numeric / URL / file-size /
  file-type errors. `mvp_prototype_link.invalid_url` is the GV-2054
  message.
- **Conditional show/hide.** The Startup Info validator wires
  `#previous_incubation` change events to toggle `#previousIncubatorWrapper`
  visibility (line 356). The Financial validator toggles
  `#average_annual_revenues_wrapper` on `#generate_revenue` change and
  revalidates the dependent field.
- **`submitFormWithFetch({ form, submitBtnId, btnTextId, btnSpinnerId,
  successMessage })`.** Replaces `form.submit()` with a `fetch(...)` POST
  carrying a `X-Requested-With: XMLHttpRequest` header. On `success: true`
  it shows a toast and re-enables the submit button. On server-side 422 it
  drops the errors into the validator's `updateFieldStatus` surface so
  they render inline. On network failure or on a non-AJAX server redirect
  it falls through to a native `form.submit()` so the server-side
  `redirect()->back()->withErrors()->withInput()` path still applies and
  founders never lose their unsaved data.
- **`core.form.valid`.** Each validator hooks this event to call
  `submitFormWithFetch` — this is why the final submit step happens after
  client-side rules pass, not on the `<form>`'s native `submit`.

Non-AJAX failure modes surface via Laravel's `old()` helper in the Blade
templates, exactly the same pattern used by the forms module.

### 4.4 Team management widget — `company-team-info.blade.php` + `app-team-info.js`

`resources/views/partials/forms/company/company-team-info.blade.php` renders three tabs (Founders / Shareholders / Investors). **GV-1956 replaced the founder modal with a right-side Bootstrap offcanvas drawer** (`#offcanvas-founder`) and wired it to the new fields added in Ticket 1. Shareholder and Investor still use the original centred modals. `public/vuexy/assets/js/app-team-info.js` owns the CRUD flow with a type-driven config:

```js
const TYPE_CONFIG = {
  founder: { teamsNo: 'Founders', fieldMap: { 
    name: 'founder_name', 
    email: 'founder_email', 
    linkedin: 'founder_linkedin',
    phone: 'founder_phone',
    type: 'founder_type',
    join_date: 'founder_join_date',
    commitment: 'founder_commitment'
  } },
  investor: { teamsNo: 'Investors', fieldMap: { name: 'investor_name', type: 'investment_type' } },
  shareholder: { teamsNo: 'Shareholders', fieldMap: { name: 'shareholder_name', role: 'shareholder_role', percentage: 'shareholder_ownership_percentage' } }
};

const VALIDATION_RULES = {
  founder: { name: { required: true }, email: { required: true, email: true }, type: { required: true }, phone: { intlTel: true }, linkedin: { url: true }, join_date: { pastOrToday: true } },
  investor: { name: { required: true, errorId: 'name-investor' }, type: { errorId: 'type-investor' } },
  shareholder: { name: { required: true, errorId: 'name-shareholder' }, role: {}, percentage: { min: 0, max: 100 } }
};

const SERVER_ERROR_MAP = {
  founder_name: { errorId: 'name', fieldName: 'name' },
  founder_email: { errorId: 'email', fieldName: 'email' },
  founder_phone: { errorId: 'phone', fieldName: 'phone' },
  founder_linkedin: { errorId: 'linkedin', fieldName: 'linkedin' },
  founder_type: { errorId: 'type', fieldName: 'type' },
  founder_join_date: { errorId: 'join_date', fieldName: 'join_date' },
  founder_commitment: { errorId: 'commitment', fieldName: 'commitment' },
  investor_name: { errorId: 'name-investor', fieldName: 'name' },
  investment_type: { errorId: 'type-investor', fieldName: 'type' },
  shareholder_name: { errorId: 'name-shareholder', fieldName: 'name' },
  shareholder_role: { errorId: 'role', fieldName: 'role' },
  shareholder_ownership_percentage: { errorId: 'percentage', fieldName: 'percentage' }
};
```

- The AJAX payload is submitted with a `TeamsNo` discriminator that the
  controller's `AddTeams` / `EditTeams` methods read to branch the save.
- Server-side errors come back keyed by the raw form-field name and are
  routed through `SERVER_ERROR_MAP` to the per-type error slot in the
  modal.
- This flow is **entirely separate** from the `CompanyN` switcher — team
  CRUD goes to the `company.profile_teams_{add,edit,delete}` routes, not
  `company.profile_update_mine`.
- **GV-1956**: `intl-tel-input` v17.0.8 is now self-hosted at `public/vuexy/assets/vendor/libs/intl-tel-input/` (no CDN dependency). The drawer initialises flatpickr on `#founder_join_date` with `maxDate: 'today'`. Delete uses SweetAlert2 with `preConfirm` so failures keep the modal open with an inline validation message.

### 4.5 Support & mentorship widget — `company-support.blade.php`

`resources/views/partials/forms/company/company-support.blade.php` (246
lines) renders a chip-style multi-select built directly in the blade with
a small co-located `<script>`:

- Reads `$areasOfExpertise` and `$companyExpertiseIds` out of the
  controller-supplied `@json(...)` globals.
- **XSS-safe DOM construction.** Chips are assembled via
  `document.createElement('div')` + `textContent = option.name` rather than
  string concatenation into `innerHTML`. GV-2053 rewrote this after an
  initial innerHTML-based implementation was flagged during review.
- The serialised ids are funnelled into a hidden `<input type="hidden"
  name="expertise_tags">` (JSON array) which the controller decodes in
  the `CompanyN == 16` branch.
- Duplicate-selection and empty-selection errors are rendered inline from
  the `MSG_DUPLICATE` / `MSG_REQUIRED` bundles.

### 4.6 Computed arrays out of the controller

Per the "no logic in blades" convention, the following arrays are
prepared in `ClientCompanyController` and consumed by the Blade views:

- **`$employeesKpis`** (GV-2057) — computed in `show()` at
  `ClientCompanyController.php:514-563`. Shape:

  ```php
  [
    [
      'title'       => __('startup.total_employees'),
      'value'       => $hasTotal ? (string) $totalEmployees : __('admin.N/A'),
      'na'          => !$hasTotal,
      'icon'        => 'ti-users-group',
      'color'       => 'success',
      'type'        => 'split',
      'has_male'    => …,
      'has_female'  => …,
      'male_value'  => …,
      'female_value'=> …,
      'male_pct'    => …,
      'female_pct'  => …,
      'show_bar'    => $showDistributionBar,
    ],
    [/* Male card */],
    [/* Female card */],
  ]
  ```

  The view at `company_profile.blade.php:253` iterates via
  `@foreach ($employeesKpis as $kpi)` and dispatches on `$kpi['type']`
  (`split` vs `pct`) to render either the distribution bar or the plain
  percentage card.

- **`$registrationOfferingDetails`** (GV-2063) — computed at
  `ClientCompanyController.php:565-574`. Shape:

  ```php
  [
    'registration_type' => $companyRegistrationType ?: null,
    'offering_type'     => $businessType            ?: null,
    'products_desc'     => $productsServicesDesc    ?: null,
    'has_any'           => (bool) (… any of the three set …),
  ]
  ```

  `_startup_overview.blade.php` consumes `has_any` to decide between
  rendering the card with live values vs the bilingual empty-state
  message "No registration and offering details added yet." (`startup.*`
  namespace). Individually null fields are hidden in-place so the card
  never shows blank rows or dashes.

- **`$areasOfExpertise` / `$companyExpertiseIds` / `$hasAreasOfExpertise`**
  (GV-2053) — computed in `editOrCreate` at
  `ClientCompanyController.php:925-944`. `$areasOfExpertise` is a flat
  array of `{id, name}` objects (name is locale-resolved: Arabic from
  `title_ar` when present, falling back to `title`). It is only populated
  from the top-level `Expertise::whereNull('parent_id')` query so child
  "skills" never leak into the multi-select. `$hasAreasOfExpertise` is the
  hide-the-tab flag.

---

## 5. Per-section field reference

Each subsection covers one tab of the edit side. The tables list every
rendered field with its i18n keys, DB column, type, nullability, validation
rule (from the matching CompanyN branch), UI element, and the ticket that
introduced or touched it.

### 5.1 Startup Info (`CompanyN == 1`)

Blade: `resources/views/partials/forms/company/company-info-form.blade.php`.
Controller branch: `ClientCompanyController.php:1064-1230`.

| Label (EN)                       | Label (AR)                      | DB column                      | Type              | Nullable | Validation rule                                                                                     | UI element            | Ticket   |
| -------------------------------- | ------------------------------- | ------------------------------ | ----------------- | -------- | ---------------------------------------------------------------------------------------------------- | --------------------- | -------- |
| Company Name                     | اسم الشركة                      | `name_en` (mirrored to `name_ar`) | string         | no       | `required\|string\|max:255`                                                                          | `<input type="text">` | pre-epic |
| Country                          | الدولة                          | `country_id`                   | foreignId         | yes      | `required\|exists:countries,id`                                                                      | Select2               | pre-epic |
| Governorate / State              | المحافظة / الولاية              | `state_id`                     | foreignId         | yes      | `required\|exists:states,id`                                                                         | Select2               | GV-2050 (renamed label) |
| Categories                       | الفئات                          | `business_categories` (JSON)   | json              | yes      | `nullable\|array`                                                                                    | Checkbox group        | pre-epic |
| Other? Please Specify            | أخرى؟ يرجى التحديد              | (appended to `business_categories`) | —           | yes      | `nullable\|string\|max:255`                                                                          | `<input type="text">` | pre-epic |
| English Address                  | العنوان الإنجليزي               | `address_en` (mirrored to `address_ar`) | string  | no       | `required\|string\|max:500`                                                                          | `<textarea>`          | pre-epic |
| Startup Size                     | حجم الشركة                      | `team_num`                     | string(255)       | yes      | (none in this branch — validated under `CompanyN=5`)                                                 | Select                | pre-epic |
| Establishment Date               | تاريخ التأسيس                   | `est_date`                     | date              | yes      | `required\|date`                                                                                     | Flatpickr text        | pre-epic |
| Have Registered Startup          | هل سجلت شركتك؟                  | `CreatedTax`                   | string flag       | yes      | (implicit `0`/`1`)                                                                                   | Select                | pre-epic |
| Country of Registration          | دولة التسجيل                    | `Country_Register_id`          | unsignedBigInt    | yes      | (conditional on `CreatedTax == 1`)                                                                   | Select2               | pre-epic |
| Commercial Register              | السجل التجاري                   | `Commercial_Register`          | string (path)     | yes      | `nullable\|file\|mimes:jpg,jpeg,png,pdf\|max:10240` + conditional `required_if:CreatedTax,1`         | `<input type="file">` | pre-epic |
| Tax Card                         | البطاقة الضريبية                | `Tax_Card`                     | string (path)     | yes      | same shape as Commercial Register                                                                    | `<input type="file">` | pre-epic |
| Years of Operations              | سنوات التشغيل                   | `years_of_operations`          | string(50)        | yes      | `required\|string`                                                                                   | Select                | pre-epic |
| Product or Service               | منتج أم خدمة                    | `product_or_service`           | string(50)        | yes      | `required\|string\|in:Product,Service,Product + Service`                                             | Select                | pre-epic |
| Company Registration Type        | نوع تسجيل الشركة                | `company_registration_type`    | string(100)       | yes      | `required\|in:Not registered yet,Individual (Sole Proprietorship),Limited Liability Company (LLC),One Person Company (OPC),Partnership,Shareholding,Other` | Select | **GV-2050** |
| Previous Incubation              | الاحتضان السابق                 | `previous_incubation`          | string(1)         | yes      | `required\|in:0,1`                                                                                   | Select (Yes/No)       | **GV-2050** |
| Previous Incubator               | المحتضن السابق                  | `previous_incubator`           | string(150)       | yes      | `nullable\|required_if:previous_incubation,1\|string\|max:150`; cleared to `null` server-side when `previous_incubation == '0'` | `<input type="text">` | **GV-2050** |
| Products / Services Description  | وصف المنتجات / الخدمات          | `products_services_description`| string(1000)      | yes      | `nullable\|string\|max:1000`                                                                         | `<textarea>` w/ char counter | **GV-2050** |

### 5.2 About / Description (`CompanyN == 3`)

Blade: `resources/views/partials/forms/company/company-desc.blade.php`.
Controller branch: `ClientCompanyController.php:1290-1321`.

| Label (EN)                          | Label (AR)                                 | DB column                   | Type        | Nullable | Validation rule                          | UI element   | Ticket   |
| ----------------------------------- | ------------------------------------------ | --------------------------- | ----------- | -------- | ----------------------------------------- | ------------ | -------- |
| About the Company                   | عن الشركة                                  | `info_company`              | text        | yes      | `nullable\|string\|max:500`               | `<textarea>` | pre-epic |
| Sustainable Development             | التنمية المستدامة                          | `Sustainable_development`   | text (JSON) | yes      | `nullable\|array`                         | Select2 multi| GV-2051 (label rename, was `Sustainable_development`) |
| About the Problem                   | عن المشكلة                                 | `about_problem`             | longText    | yes      | `nullable`                                | `<textarea>` | pre-epic |
| Market Opportunity                  | فرصة السوق                                 | `about_market_opportunity`  | longText    | yes      | `nullable`                                | `<textarea>` | pre-epic |
| Competitors                         | المنافسون                                  | `about_competitors`         | longText    | yes      | `nullable`                                | `<textarea>` | pre-epic |
| How You Monetise                    | كيفية تحقيق الإيرادات                      | `about_monetize`            | longText    | yes      | `nullable`                                | `<textarea>` | pre-epic |
| Challenges                          | التحديات                                   | `about_challenges`          | longText    | yes      | `nullable`                                | `<textarea>` | pre-epic |
| Achievements                        | الإنجازات                                  | `achievements`              | text        | yes      | `nullable\|string\|max:1000`              | `<textarea>` | **GV-2051** |
| Mission                             | المهمة                                     | `mission`                   | text        | yes      | `nullable\|string\|max:1000`              | `<textarea>` | **GV-2051** |
| Why this startup?                   | لماذا هذه الشركة الناشئة؟                  | `why_this_startup`          | text        | yes      | `nullable\|string\|max:1000`              | `<textarea>` | **GV-2051** |
| Startup Expertise                   | خبرة الشركة الناشئة                        | `startup_expertise`         | text        | yes      | `nullable\|string\|max:1000`              | `<textarea>` | **GV-2051** |

> GV-2051 also removed the `*` asterisk from the five legacy `about_*`
> fields in this blade, reflecting the fact that they are optional in the
> back-end validation. The legacy `CompanyN == 12` form
> (`about_problem.blade.php`) keeps the `*` markers and the
> `required` rules because it is a standalone narrow form, not the
> description tab.

### 5.3 Financial Info (`CompanyN == 4`)

Blade: `resources/views/partials/forms/company/company-financial.blade.php`.
Controller branch: `ClientCompanyController.php:1322-1393`.

| Label (EN)                               | Label (AR)                     | DB column                   | Type               | Nullable | Validation rule                                                                                                                           | UI element            | Ticket         |
| ---------------------------------------- | ------------------------------ | --------------------------- | ------------------ | -------- | ----------------------------------------------------------------------------------------------------------------------------------------- | --------------------- | -------------- |
| Currency                                 | العملة                         | `Currency`                  | string             | yes      | `nullable`                                                                                                                                | Select                | pre-epic       |
| Pre-money Valuation                      | التقييم قبل الجولة             | `Pre_money_Valuation`       | string             | yes      | `nullable\|numeric\|min:0`                                                                                                                | `<input type="number">` | pre-epic     |
| Raised Fund                              | التمويل المحصَّل              | `raised_fund`               | string             | yes      | `nullable\|numeric\|min:0`                                                                                                                | `<input type="number">` | pre-epic     |
| Funding Target                           | الهدف المالي                   | `Funding_Target`            | float(115,2)       | no (def 0)| `nullable\|numeric\|min:0`                                                                                                               | `<input type="number">` | pre-epic     |
| Does your business generate revenue?     | هل تحقق شركتك إيرادات؟         | `generate_revenue`          | string(3)          | yes      | `required\|in:yes,no`                                                                                                                     | Select (Yes/No)       | **GV-2058**    |
| Average Annual Revenues                  | متوسط الإيرادات السنوية        | `average_annual_revenues`   | unsignedBigInteger | yes      | `required_if:generate_revenue,yes\|nullable\|integer\|min:0\|max:999999999999`; cleared to `null` server-side when `generate_revenue === 'no'` | `<input type="number">` (12-digit mask) | **GV-2058** |
| Development Stage                        | مرحلة التطوير                  | `stage`                     | string             | yes      | `nullable`                                                                                                                                | Select                | pre-epic       |
| Funding Stage                            | مرحلة التمويل                  | `development_stage`         | string             | yes      | `nullable`                                                                                                                                | Select                | pre-epic       |
| Business Type                            | نوع الأعمال                    | `business_type`             | string             | yes      | `nullable`                                                                                                                                | Select (B2C/B2B/…)    | pre-epic       |
| Targeted Market                          | السوق المستهدف                 | `targeted_market` (JSON)    | string             | yes      | `nullable` per-value                                                                                                                      | Select2 multi         | pre-epic       |
| Financial Plan Description               | وصف الخطة المالية              | `financial_plan_description`| string             | yes      | `nullable`                                                                                                                                | `<textarea>`          | pre-epic       |
| Financial Plan Attachment                | مرفق الخطة المالية             | `financial_plan`            | string (path)      | yes      | `nullable\|file\|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx\|max:10240`                                                                          | `<input type="file">` | **GV-2138**    |

### 5.4 Business Model / MVP (`CompanyN == 13`)

Blade: `resources/views/partials/forms/company/business_model.blade.php`.
Controller branch: `ClientCompanyController.php:1547-1587`.

| Label (EN)                | Label (AR)                 | DB column                    | Type       | Nullable | Validation rule                                                | UI element           | Ticket         |
| ------------------------- | -------------------------- | ---------------------------- | ---------- | -------- | --------------------------------------------------------------- | -------------------- | -------------- |
| Business Model            | نموذج العمل                | `business_model`             | string     | yes      | `required`                                                      | Select (enum list)   | pre-epic       |
| Other Business Model      | نموذج عمل آخر              | `other_business_model`       | string     | yes      | (conditional on `business_model == 'Other'`)                    | `<input type="text">`| pre-epic       |
| Business Model Description| وصف نموذج العمل            | `business_model_description` | string     | yes      | `required\|string\|max:255`                                     | `<textarea>`         | pre-epic       |
| MVP / Prototype Link      | رابط النموذج الأولي        | `mvp_prototype_link`         | string(500)| yes      | `nullable\|url\|max:500`                                        | `<input type="url">` (maxlength=500) | **GV-2054** |
| Business Plan Attachment  | مرفق خطة العمل             | `business_plan`              | string     | yes      | `nullable\|file\|mimes:pdf,doc,docx,ppt,pptx\|max:20480`        | `<input type="file">`| pre-epic       |

Section header label was renamed from `business_model` to
`business_model_mvp` in the i18n files as part of GV-2054.

### 5.5 Industry / Sectors (`CompanyN == 14`, pre-epic)

Blade: `resources/views/partials/forms/company/classification_form.blade.php`.
Controller branch: `ClientCompanyController.php:1645-2120`.

This form writes to `company_relations`, not to the `companies` row
itself. It is the only CompanyN branch that uses an AJAX-first envelope
(`$request->ajax() ? response()->json(...) : redirect()`).

| Label (EN)      | Label (AR)       | Target table (column)            | Type   | Nullable | Validation rule                                                       | UI element       | Ticket   |
| --------------- | ---------------- | -------------------------------- | ------ | -------- | --------------------------------------------------------------------- | ---------------- | -------- |
| Industry        | الصناعة          | `company_relations.industry_id`  | fk     | no       | `required\|exists:static_categories,id`                               | Select           | pre-epic |
| Sectors         | القطاعات         | `company_relations.sector_id`    | fk     | no       | `required` (array)                                                    | Select2 multi    | pre-epic |
| Tech Tags       | علامات التقنيات  | `technologies.name` (via pivot)  | string | yes      | `nullable\|array`; each `nullable\|string\|max:50`                    | Tag input        | pre-epic |
| Other Sector    | قطاع آخر         | `sectors.name_en`                | string | yes      | `nullable\|regex:/^[A-Za-z0-9\s\-\.]+$/\|max:255`                     | `<input>`        | pre-epic |
| Other Tech      | تقنية أخرى       | `technologies.name`              | string | yes      | `nullable\|regex:/^[A-Za-z0-9\s\-\.]+$/\|max:50`                      | `<input>`        | pre-epic |

### 5.6 Financial Projection (`CompanyN == 15`, pre-epic)

Blade: `resources/views/partials/forms/company/company-financial-projection.blade.php`.
Two complementary controller methods:

- `CompanyN == 15` branch (`ClientCompanyController.php:2122-2193`) —
  legacy full-form save (4 rows into `financial_projections`).
- `saveFinancialProjection` JSON endpoint (`ClientCompanyController.php:4608-4804`) —
  modern per-cell + full-form save with server-side recalculation.
- `getFinancialProjectionRealtime` — matching read endpoint.

Fields are replicated for each of the 4 years (`past`, `year1`, `year2`,
`year3`). Columns on `financial_projections`: `revenues`, `cogs`,
`salaries`, `other_op_expenses`, `ebitda`, `depreciation_amortization`,
`ebit`, `interest`, `taxes`, `net_profit`, `receivables`, `inventory`,
`payables`, `working_capital`, `change_in_wc`, `capital_expenditures`,
`debt_end_year`, `debt_change`, `free_cash_flow` plus
`{company_id, user_id, valuation_date, year, currency}`.

**GV-2059** is a label-only change: the EBIT row now shows as
"EBIT (Operating Income)" / "الأرباح قبل الفوائد والضرائب (الدخل التشغيلي)"
via the i18n bundle. No schema or formula change.

### 5.7 Support & Mentorship (`CompanyN == 16`)

Blade: `resources/views/partials/forms/company/company-support.blade.php`.
Controller branch: `ClientCompanyController.php:1593-1643`.

| Label (EN)             | Label (AR)                  | Target                               | Type         | Nullable | Validation rule                                                            | UI element                   | Ticket   |
| ---------------------- | --------------------------- | ------------------------------------ | ------------ | -------- | --------------------------------------------------------------------------- | ---------------------------- | -------- |
| Areas of Expertise     | مجالات الخبرة               | `company_expertise` (pivot)          | pivot        | —        | `required\|array\|min:1`; each `integer\|exists:expertises,id`; extra guard that only top-level (`parent_id IS NULL`) `expertises` rows with `status == 'active'` (when the column exists) are accepted | Custom chip multi-select     | **GV-2053** |

Save logic:

```php
$activeIdsQuery = Expertise::whereNull('parent_id')->whereIn('id', $expertiseIds);
if (Schema::hasColumn('expertises', 'status')) {
    $activeIdsQuery->where('status', 'active');
}
$activeIds = $activeIdsQuery->pluck('id')->map(fn($v) => (int) $v)->toArray();

$result->expertises()->sync($activeIds);
```

Post-save, the branch returns JSON when AJAX and redirects to
`company.profile_view_mine` otherwise, carrying a localised
`admin.startup_profile_updated` success flash.

### 5.8 Team (founders, shareholders, investors)

Blade: `resources/views/partials/forms/company/company-team-info.blade.php`
plus the two shareholder/investor modal partials `_shareholder_team.blade.php` and `_investor_team.blade.php` (the legacy `_founder_team.blade.php` was removed by GV-1956; founder CRUD now lives entirely in the `#offcanvas-founder` drawer inside `company-team-info.blade.php`). Writes go
through the dedicated `company.profile_teams_add` /
`company.profile_teams_edit` / `company.profile_teams_delete` routes
(`AddTeams`, `EditTeams`, `DeleteTeams` — section 3.3).

#### Founders (`TeamsNo == 'Founders'`)

| Label (EN)          | Label (AR)            | DB column           | Type       | Nullable        | Validation rule                                                                                          | UI element                         | Ticket              |
| ------------------- | --------------------- | ------------------- | ---------- | --------------- | -------------------------------------------------------------------------------------------------------- | ---------------------------------- | ------------------- |
| Founder Name        | اسم المؤسس            | `Name`              | string     | no              | `required\|string\|max:100`                                                                              | `<input type="text">`              | GV-1956 (GV-1954)   |
| Founder Email       | البريد الإلكتروني     | `Email`             | string     | no              | `required\|email\|max:254` (company-scoped uniqueness via controller)                                    | `<input type="email">`             | GV-1956 (GV-1954)   |
| Founder Phone       | رقم الهاتف            | `phone`             | string(20) | yes             | `nullable\|string\|max:20` (company-scoped uniqueness via controller; `intl-tel-input` client-side)      | `<input type="tel">` + intlTelInput| GV-1956 (GV-1954)   |
| LinkedIn Profile    | رابط LinkedIn         | `LinkedIn`          | string     | yes             | `nullable\|url\|max:500`                                                                                 | `<input type="url">`               | GV-1956 (GV-1954)   |
| Join Date           | تاريخ الانضمام        | `join_date`         | date       | yes             | `nullable\|date\|before_or_equal:today`                                                                  | `<input type="text">` + flatpickr  | GV-1956 (GV-1954)   |
| Founder Type        | نوع المؤسس            | `founder_type`      | string(20) | no (business rule) | `required\|in:Founder,Co-founder`                                                                     | `<select>`                         | GV-1956 (GV-1954)   |
| Working Commitment  | نوع الالتزام          | `working_commitment`| string(20) | yes             | `nullable\|in:Full-time,Part-time`                                                                       | `<select>`                         | GV-1956 (GV-1954)   |

> **GV-1953** initially added `national_id`, `date_of_birth`, and
> `education_status` fields to this modal. The persistence target landed
> on the `users` table (see section 2.2) instead of `team_members`, so
> these founder-identity values follow the owning user record and are
> read/written via the user-profile surfaces, not through `AddTeams` /
> `EditTeams`.

#### Shareholders (`TeamsNo == 'Shareholders'`)

| Label (EN)          | Label (AR)               | DB column               | Type    | Nullable | Validation rule                                                           | UI element           |
| ------------------- | ------------------------ | ----------------------- | ------- | -------- | -------------------------------------------------------------------------- | -------------------- |
| Full Name           | الاسم الكامل             | `Name`                  | string  | no       | `required\|string\|max:255`                                               | `<input>`            |
| Role                | الدور                    | `RoleShereholder`       | string  | yes      | `required\|max:255`                                                       | `<input>`            |
| Ownership (%)       | نسبة الملكية             | `PresentagShereholder`  | integer | no       | `required`; total across the company must not exceed 100 (server check)   | `<input type="number" min="0" max="100">` |

#### Investors (`TeamsNo == 'Investors'`)

| Label (EN)      | Label (AR)       | DB column       | Type   | Nullable | Validation rule            | UI element |
| --------------- | ---------------- | --------------- | ------ | -------- | -------------------------- | ---------- |
| Name            | الاسم            | `Name`          | string | no       | `required\|string\|max:255`| `<input>`  |
| Investment Type | نوع الاستثمار    | `investorType`  | string | yes      | `required\|max:255`        | Select     |

### 5.9 Employees

Blade: `resources/views/partials/forms/company/company-employees.blade.php`
(1146 lines). Split into two nav pills:

1. **Overall Employees Statistics** — writes to the three `companies`
   columns via `saveStatistics`. See section 5.10 below.
2. **Employees** — CRUD on individual `employees` rows via
   `company.employees_add`, `company.employees_edit`,
   `company.employees_delete`.

| Label (EN)       | Label (AR)         | DB column          | Type                                                             | Nullable | Validation rule                                     | UI element            | Ticket |
| ---------------- | ------------------ | ------------------ | ---------------------------------------------------------------- | -------- | --------------------------------------------------- | --------------------- | ------ |
| Full Name        | الاسم الكامل       | `full_name`        | string(100)                                                      | no       | `required\|string\|max:255`                         | `<input type="text">` | pre-epic |
| Job Title / Role | المسمى الوظيفي     | `job_title`        | string(100)                                                      | no       | `required\|string\|max:255`                         | `<input>`             | pre-epic |
| Gender           | النوع              | `gender`           | enum(male,female)                                                | no       | `required\|in:male,female`                          | Select                | pre-epic |
| Employment Type  | نوع التوظيف        | `employment_type`  | enum(full-time,part-time,contractor,intern)                      | no       | `required\|in:full-time,part-time,contractor,intern`| Select                | pre-epic |
| Hiring Date      | تاريخ التعيين      | `hiring_date`      | date                                                             | no       | `required\|date`                                    | Flatpickr             | pre-epic |
| Documents        | المستندات          | `documents` (JSON) | text                                                             | yes      | `documents.*` `nullable\|file\|mimes:pdf,jpg,jpeg,png,docx\|max:10240` | File input | pre-epic |

### 5.10 Overall Employee Statistics (dedicated route, GV-2056)

Blade: upper half of
`resources/views/partials/forms/company/company-employees.blade.php`.
Endpoint: `company.employees_statistics` → `saveStatistics`.

| Label (EN)         | Label (AR)                 | DB column          | Type             | Nullable | Validation rule                                                           | UI element |
| ------------------ | -------------------------- | ------------------ | ---------------- | -------- | -------------------------------------------------------------------------- | ---------- |
| Total Employees    | إجمالي الموظفين            | `total_employees`  | unsignedInteger  | yes      | `required\|integer\|min:0\|max:999999`                                     | `<input type="number" min="0" maxlength="6">` |
| Male Employees     | الموظفون الذكور            | `male_employees`   | unsignedInteger  | yes      | `nullable\|integer\|min:0\|max:999999`                                     | `<input type="number" min="0" maxlength="6">` |
| Female Employees   | الموظفات الإناث            | `female_employees` | unsignedInteger  | yes      | `nullable\|integer\|min:0\|max:999999`                                     | `<input type="number" min="0" maxlength="6">` |

Additional server-side rule: when both `male_employees` and
`female_employees` are supplied, their sum must equal `total_employees`.
Failure message: `admin.employee_gender_sum_mismatch` (bilingual).

### 5.11 Display-only overview

`resources/views/admin/clients/startup/partials/_startup_overview.blade.php`
is the richest consumer of the GV-2049 data. Sections (with hide-when-empty
rules):

| Section                           | Pulled from                                                       | Hidden when                                                                          | Ticket  |
| --------------------------------- | ----------------------------------------------------------------- | ------------------------------------------------------------------------------------ | ------- |
| Strategy tabs (why / mission / achievements / expertise) | `$company->why_this_startup / mission / achievements / startup_expertise` | Per-tab: the underlying column is empty / whitespace. The whole strategy card renders whichever subset has values. | **GV-2052** |
| Registration & Offering Details   | `$registrationOfferingDetails`                                    | Per-row: each of `registration_type`, `offering_type`, `products_desc` is hidden individually when null. Whole card shows an empty-state banner when `has_any == false`. | **GV-2063** |
| Business profile summary          | pre-existing                                                      | n/a                                                                                  | pre-epic |
| Founders thumbnails               | `$founders`                                                       | empty                                                                                | pre-epic |
| Shareholders thumbnails           | `$Shareholders`                                                   | empty                                                                                | pre-epic |
| Investors thumbnails              | `$Investors`                                                      | empty                                                                                | pre-epic |

The Team tab's Employee Statistics KPI cards (GV-2057) live in
`company_profile.blade.php` itself (not the overview partial) and consume
`$employeesKpis` as described in section 4.6. The "View Prototype" button
(GV-2055) renders inside `_startup_info.blade.php`'s header when
`trim($company->mvp_prototype_link)` is non-empty.

---

## 6. Epic GV-2049 changelog

### GV-1953 — Add Identity Details to Founder Profile

- **What it added:**
  - `national_id` column on `users` via
    `database/migrations/2026_04_14_103000_add_national_id_to_users_table.php`.
  - Phone column on `team_members` via the pre-existing untracked
    `2026_04_02_170927_add_phone_column_to_team_memebers_table.php` migration.
  - Founder modal inputs for National ID, Date of Birth, Education Status
    under `resources/views/partials/forms/company/_founder_team.blade.php`
    and their i18n keys under `resources/lang/{en,ar}/admin.php`.
  - Country input enforced as `required` alongside the identity fields.
- **Key decisions:**
  - **Identity fields landed on `users`, not `team_members`.** The epic
    plan proposed the team_members route, but the shipped migration put
    `national_id` on `users` and delegated `date_of_birth` /
    `education_status` to the pre-existing `users.academic_stat` (with age
    reused for DoB) so that the founder identity remains tied to the
    user — not the team membership row — and survives team re-shuffles.
  - Future-date DoB validation and per-country `national_id` regex
    checks are enforced in the Request class layer rather than in-line in
    `AddTeams` / `EditTeams`.

### GV-2050 — Add Previous Incubation and Registration Details

- **What it added:**
  - Migration `2026_04_14_104428_add_previous_incubation_and_registration_to_companies_table.php`
    adding `previous_incubation` (string(1)), `previous_incubator`
    (string(150)), `company_registration_type` (string(100)), and
    `products_services_description` (string(1000)).
  - All four columns appended to `Company::$fillable`.
  - Validation block and `$result->...` writes under the
    `CompanyN == 1` branch in `ClientCompanyController::updateOrStore`
    (lines 1119-1122 for the rules, 1224-1229 for the persistence).
  - New form inputs under
    `resources/views/partials/forms/company/company-info-form.blade.php`
    at lines 273-351, including the conditional `#previousIncubatorWrapper`
    wrapper and a 1000-char text counter for the description.
  - JS show/hide wiring in
    `public/vuexy/assets/js/pages-edit-compnay.js:356` toggling
    `$('#previousIncubatorWrapper').toggle(isYes)`.
- **Key decisions:**
  - `previous_incubation` stored as **`string(1)` with `'0'`/`'1'` values**
    rather than an enum. The blade-level select uses `value="1"` /
    `value="0"` and the controller validates `in:0,1`. Server-side, when
    the flag equals `'0'`, `previous_incubator` is force-reset to `null`
    so a stale value from an earlier save cannot linger.
  - `company_registration_type` persisted as the **verbatim English
    string** (including the parenthesised clarifiers like
    `"Individual (Sole Proprietorship)"`) rather than a numeric code.
    This keeps the overview read path trivial — no mapping table lookup
    required in the GV-2063 display card — and keeps the validation rule
    list self-documenting.
  - Label "State" renamed to "Governorate / State" and "other" renamed to
    "Other" across `resources/lang/en/admin.php` and the Arabic mirror.

### GV-2051 — Add Startup Description Fields

- **What it added:**
  - Migration `2026_04_14_111032_add_description_fields_to_companies_table.php`
    adding `achievements`, `mission`, `why_this_startup`,
    `startup_expertise` as `text` columns.
  - All four columns appended to `Company::$fillable`.
  - Validation and persistence lines in the `CompanyN == 3` branch of
    `updateOrStore` (lines 1299-1302, 1317-1320).
  - New "Additional Info" subsection in
    `resources/views/partials/forms/company/company-desc.blade.php` at
    lines 102-134, iterating over `$optionalFields`.
  - Removal of the `*` asterisks from the pre-existing `about_*`
    textareas in the same blade, because those fields are optional on the
    server side.
  - i18n rename of `"Sustainable_development"` to
    `"Sustainable Development"` in the admin language files. The column
    name stays `Sustainable_development`.
- **Key decisions:**
  - Columns typed as **`text`, not `varchar(1000)`**. The `companies`
    table was already carrying multiple `longText` and wide-`string`
    columns; adding four more MB4-aware fixed-width slots risked bumping
    into InnoDB's 65 535-byte row-size cap. `text` off-row storage sidesteps
    that. Max length is enforced in validation (`max:1000`), not the
    schema.
  - The four fields are rendered in the **same blade (CompanyN=3)** as
    the legacy `about_*` columns. There is no second form; keeping them
    together was the simplest way to preserve the existing "save
    description" UX and avoid proliferating more CompanyN values.

### GV-2052 — Display Description Fields in Overview

- **What it added:**
  - Conditional rendering of the four GV-2051 fields in the Overview tab's
    strategy-tabs surface at
    `resources/views/admin/clients/startup/partials/_startup_overview.blade.php:95-189`.
  - Each strategy tab entry now renders only when its underlying column
    is non-empty; long text wraps via CSS so it cannot break the layout.
- **Key decisions:**
  - No DB or controller changes — `$company` is already passed to the
    view. The fix is blade-only: `@if (!empty(trim($value)))`-style
    guards inline.

### GV-2053 — Support & Mentorship Tab

- **What it added:**
  - Migration `2026_04_14_120000_create_company_expertise_table.php`
    creating the `company_expertise` pivot with cascade delete and a
    unique `(company_id, expertise_id)` pair.
  - `Company::expertises()` belongsToMany relation and the inverse
    `Expertise::companies()`.
  - Controller wiring: `editOrCreate` builds
    `$areasOfExpertise` + `$companyExpertiseIds` + `$hasAreasOfExpertise`
    (lines 925-944); `updateOrStore` handles the `CompanyN == 16` save
    path (lines 1593-1643) with explicit server-side sanitisation of the
    incoming `expertise_tags` payload (supports both JSON string and
    array inputs).
  - `resources/views/partials/forms/company/company-support.blade.php` —
    the frontend developer's original hardcoded 11-item array was deleted
    and replaced with a DOM-built chip widget fed from `@json($areasOfExpertise)`.
  - Tab hidden in `resources/views/admin/clients/company.blade.php:60-62`
    when `$hasAreasOfExpertise === false`.
- **Key decisions:**
  - **`CompanyN == 16`, not `15`, to avoid colliding with the existing
    financial-projection branch.** `CompanyN == 14` was the only pre-epic
    AJAX envelope path, and GV-2053 is the second; reusing 14 or 15 would
    have required untangling the classification form's bespoke branch
    logic. 16 is the first free integer after 15.
  - **XSS-hardened chip rendering.** Chips are constructed via
    `document.createElement('div') + textContent`, not string
    concatenation into `innerHTML`. This was rewritten during review to
    close the surface before ship.
  - Only top-level `Expertise::whereNull('parent_id')` rows qualify, so
    child "skills" cannot be selected by accident. When the
    `expertises.status` column exists the save path additionally filters
    to `status == 'active'`, matching the spec's "inactive options are
    excluded from future selections" AC.

### GV-2054 — Add MVP / Prototype Link

- **What it added:**
  - Migration `2026_04_14_120000_add_mvp_prototype_link_to_companies_table.php`
    adding `mvp_prototype_link` (string(500)).
  - `Company::$fillable` updated.
  - Validation rule `nullable|url|max:500` in the `CompanyN == 13`
    branch (`ClientCompanyController.php:1552`, localised via
    `startup.mvp_prototype_link_invalid_url`).
  - New form input in
    `resources/views/partials/forms/company/business_model.blade.php:62-78`
    with `maxlength="500"`.
  - FormValidation wiring in
    `public/vuexy/assets/js/pages-edit-compnay.js:429` using the
    `uri: { message: translations[lang].mvp_prototype_link.invalid_url }`
    validator.
  - i18n section label rename: `business_model` →
    `business_model_mvp` (tab heading only; the column name is
    unchanged).
- **Key decisions:**
  - Field is **optional**. Earlier drafts had `required` on the input
    and a duplicate stray `value=""` attribute; both were removed to
    match the ticket's AC.

### GV-2055 — "View Prototype" Button

- **What it added:**
  - Conditional "View Prototype" anchor in
    `resources/views/admin/clients/startup/company_profile.blade.php:203-208`,
    wired to `$company->mvp_prototype_link`, opening in a new tab with
    `target="_blank" rel="noopener noreferrer"`.
- **Key decisions:**
  - **No DB or controller change.** The old `href="https://www.google.com"`
    placeholder was replaced and the `@if` was tightened to
    `@if ($mvpLink !== '')` so the button disappears entirely when no link
    is saved.

### GV-2056 — Fill Overall Employee Statistics

- **What it added:**
  - Migration `2026_04_14_131705_add_employee_statistics_to_companies_table.php`
    adding `total_employees`, `male_employees`, `female_employees`
    (`unsignedInteger`, nullable).
  - Model casts all three to `integer`.
  - `ClientCompanyController::saveStatistics` fully implemented
    (`ClientCompanyController.php:4806-4888`): validation, sum-rule
    enforcement via a closure, persistence, JSON envelope with the
    `admin.employee_statistics_updated` success key.
  - Form surface in
    `resources/views/partials/forms/company/company-employees.blade.php:22-123`.
- **Key decisions:**
  - **Self-reported totals, not `SELECT COUNT(*) FROM employees`.**
    Founders fill individual employee records selectively; the three
    totals columns exist so the founder can report the real headcount
    once, without having to enumerate every employee.
  - **Dedicated route**, not a CompanyN branch. The save-statistics
    button lives in a secondary nav pill inside the Employees tab and
    only writes three columns; treating it as its own endpoint kept the
    existing `CompanyN == 1..15` dispatch matrix untouched.
  - Pre-GV-2056 the `saveStatistics` method body was a commented-out
    stub returning `{"status":"success"}`. That dead code was removed.

### GV-2057 — Display Employee Statistics KPI Cards

- **What it added:**
  - `$employeesKpis` array built in `ClientCompanyController::show` at
    lines 514-563.
  - KPI card rendering in
    `resources/views/admin/clients/startup/company_profile.blade.php:253-281`
    consuming the controller-built array.
- **Key decisions:**
  - **Blade `@php` block deleted** in favour of the controller-supplied
    array. This pulls presentation logic out of the template — the same
    pattern forthcoming tickets should apply.
  - **N/A handling.** The array's `na` flag per card lets the Blade
    decide between rendering the value or the bilingual
    `admin.N/A` label, and the `show_bar` flag hides the distribution
    bar when total is zero/null or when both male and female are
    missing.

### GV-2058 — Fill Revenue Information

- **What it added:**
  - Migration `2026_04_14_134341_add_revenue_fields_to_companies_table.php`
    adding `generate_revenue` (string(3)) and `average_annual_revenues`
    (`unsignedBigInteger`) with idempotent `Schema::hasColumn` guards.
  - Both columns appended to `Company::$fillable`.
  - Validation + persistence in the `CompanyN == 4` branch
    (`ClientCompanyController.php:1332-1392`), including the
    server-side null-out when `generate_revenue === 'no'`.
  - Form inputs in
    `resources/views/partials/forms/company/company-financial.blade.php:87-128`.
  - Show/hide and revalidation wiring in
    `public/vuexy/assets/js/pages-edit-compnay.js:471-521`.
- **Key decisions:**
  - **Column type `unsignedBigInteger`** for
    `average_annual_revenues`. The spec disallows decimals and negative
    values; MySQL's unsigned integer handling gives that invariant at
    the column level without relying solely on validation. The existing
    legacy `annual_revenue` (string(25)) column is left untouched.
  - **Conditional required.** `average_annual_revenues` is
    `required_if:generate_revenue,yes` — the "no" branch is a no-op
    which both passes validation and wipes any stale value.

### GV-2059 — Clarify Operating Income Label

- **What it added:**
  - i18n-only change renaming the EBIT row label to
    "EBIT (Operating Income)" in `resources/lang/en/admin.php` and the
    Arabic mirror. Consumed by
    `resources/views/partials/forms/company/company-financial-projection.blade.php`
    and `resources/views/admin/clients/startup/partials/_startup_financial_report.blade.php`.
- **Key decisions:**
  - **No new row, no formula change.** Existing `ebit` column,
    calculations, and server-side recalc logic in
    `saveFinancialProjection` stay identical.

### GV-2063 — Display Registration & Offering Details in Overview

- **What it added:**
  - `$registrationOfferingDetails` array built in
    `ClientCompanyController::show` at lines 565-574.
  - New "Registration & Offering Details" card in
    `resources/views/admin/clients/startup/partials/_startup_overview.blade.php:199-204`
    with per-field hide-when-empty rules and a bilingual empty-state
    banner when `has_any === false`.
  - New i18n keys under `resources/lang/{en,ar}/startup.php` for the
    card title, subtitle, and empty-state copy.
- **Key decisions:**
  - **"Offering type" is sourced from `business_type`.** No new column
    is added; `business_type` (B2C, B2B, …) already carries the semantic
    meaning the card needs, and reusing it keeps the overview in sync
    with the Financial Info tab automatically.
  - **Per-field hide before per-card hide.** Empty fields are collapsed
    individually so the card can show a partial view rather than
    everything-or-nothing. The empty-state banner is only shown when
    every field is empty.

### GV-2138 — Restrict Financial Plan Attachment

- **What it added:**
  - Validation rule
    `nullable|file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx|max:10240` on
    `financial_plan` inside the `CompanyN == 4` branch
    (`ClientCompanyController.php:1334`).
  - Bilingual error keys `admin.financial_plan_invalid_type` and
    `admin.financial_plan_too_large`.
  - `accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx"` attribute on the
    file input at
    `resources/views/partials/forms/company/company-financial.blade.php:235`.
- **Key decisions:**
  - Mirrors the three-layer enforcement pattern documented in
    `docs/developer_docs/FORMS_MODULE.md` for the forms module:
    HTML `accept` hint (advisory), client-side size/type check in
    `pages-edit-compnay.js`, and Laravel `mimes` + `max:10240` validation
    on the server. The nginx `client_max_body_size` bump from GV-2136 is
    already in place, so a >10 MB payload short-circuits to the
    `PostTooLargeException` handler and surfaces the same bilingual
    "File size must not exceed 10 MB" banner.

### Known gaps / follow-ups

- **Legacy `revenue_generating` column is still present.** GV-2058 added
  the new `generate_revenue` column but did not drop its predecessor.
  Both columns now exist on `companies` and are independently writable;
  the old column is unreferenced by any GV-2049 blade. A follow-up
  migration should drop it once we can confirm nothing in older forms or
  reports still reads it.
- **Legacy `CompanyN == 12` about-form overlaps with `CompanyN == 3`.**
  Both branches write `about_problem`, `about_market_opportunity`,
  `about_competitors`, `about_monetize`, `about_challenges`. The newer
  description tab is the canonical surface; the standalone
  `about_problem.blade.php` could be retired.
- **Legacy `employees`, `founder`, `investors` string columns on
  `companies`.** Pre-dating the `employees` and `team_members` tables.
  Still read by the "team_builder" badge card but otherwise dead.
- **`technologies_used` in `Company::$fillable` is unbacked.** No
  migration on the current schema creates it as a column; it exists only
  in the fillable list for historical reasons. Writes will silently no-op.
- **Test database isolation.** `phpunit.xml` now uses SQLite `:memory:`,
  but any contributor who removes that override risks the
  `RefreshDatabase` trait running `migrate:fresh` against the dev
  database. Keep the SQLite override in place.
- **`show()` still does heavy `@php` computation.** GV-2049 pulled
  `$employeesKpis` and `$registrationOfferingDetails` up into the
  controller, but a number of other computed values (mentorship hours
  aggregation, alumni program flattening) are still materialised inline
  inside `show()`. These could be extracted to `CompanyService` in a
  future refactor.
- **The `CompanyN` switcher is fundamentally fragile.** Every new tab
  needs a free integer and a new `if ($data['CompanyN'] == N)` branch,
  and the `updateOrStore` method is now ~1250 lines long. A follow-up
  refactor could dispatch on a named handler (`startup_info`,
  `description`, `financial_info`, …) and split each branch into its own
  method on `CompanyService`, mirroring the API surface. Out of scope
  for GV-2049.
- **Per-country `national_id` regex (GV-1953 AC).** Currently enforced
  only as a generic `alpha_num|max:50` rule. Country-specific patterns
  (e.g. 14-digit Egyptian NID) are documented in the ticket but not yet
  shipped.
- **Edit-tab Arabic labels for a few new inputs are hardcoded in blades**
  (see the Employees statistics card: `$l == 'ar' ? 'إحصائيات …' : …`).
  These should move into `resources/lang/ar/startup.php` for
  consistency.

### 6.14 GV-1956 — Startup Team

Full founder CRUD upgrade for the Edit Startup Profile → Team → Founders tab, replacing the legacy `_founder_team.blade.php` modal with a right-side drawer, self-hosted `intl-tel-input`, and a richer data model. Shipped across 6 commits.

**Tickets:**
- **GV-1954** (Add new founder) + **GV-1979** (Edit founder) — merged commit. Schema additions (`phone`, `join_date`, `founder_type`, `working_commitment`). `ClientCompanyController::AddTeams` and `EditTeams` Founders branches rewritten with widened validator, company-scoped duplicate checks inside `DB::transaction` with `lockForUpdate`, and `FounderResource` responses. Drawer UI replaces the modal; intl-tel-input self-hosted at `public/vuexy/assets/vendor/libs/intl-tel-input/`.
- **GV-1955** (Empty state) — bilingual empty-state copy (`No founders added yet` / `لم تتم إضافة مؤسسين بعد`) with an "Add Founder" action routed through `TeamInfo.openFounderDrawer()`.
- **GV-1978** (Display added founders) — rewrote `buildCard('founder')` in `app-team-info.js`: conditional rows (no "N/A" placeholders), LinkedIn icon with `target="_blank" rel="noopener"`, commitment badge, collapsing footer when both commitment and LinkedIn are empty.
- **GV-1980** (Delete founder) — `DeleteTeams` gained an `auth()->user()` fallback, company-scope guard, and differentiated success message. JS `deleteItem` for founders uses `Swal.fire` with `preConfirm` + `showValidationMessage` so failures keep the modal open with an inline error.
- **GV-1981** (View founders on profile) — `_startup_founders.blade.php` now consumes the real `$founders` collection, prepends a virtual owner card (first position, no Edit/Delete, join date = company `created_at`), formats join date as DD-MM-YYYY, and hides optional fields when empty. LinkedIn icon opens in a new tab.

**Files touched:**
- Migrations: `2026_04_02_170927_add_phone_column_to_team_memebers_table.php`, `2026_04_15_214852_add_founder_fields_to_team_members_table.php`
- Controller: `app/Http/Controllers/ClientCompanyController.php` (AddTeams, EditTeams, DeleteTeams; read-only profile show method for virtual owner card)
- Model: `app/Team_member.php` (`join_date` cast, `scopeFounders`)
- Requests: `app/Http/Requests/StoreFounderRequest.php`
- Resource: `app/Http/Resources/FounderResource.php`
- Repository: `app/Repositories/CompanyRepository.php` (`createFounder`)
- Blade (edit): `resources/views/partials/forms/company/company-team-info.blade.php`
- Blade (profile): `resources/views/admin/clients/startup/partials/_startup_founders.blade.php`
- JS: `public/vuexy/assets/js/app-team-info.js`
- i18n: `resources/lang/en/admin.php`, `resources/lang/ar/admin.php`
- Assets: self-hosted `public/vuexy/assets/vendor/libs/intl-tel-input/` (v17.0.8)
- Removed: `resources/views/partials/forms/company/_founder_team.blade.php`

**Virtual owner card.** On the read-only profile (`GET /dashboard/companyProfile/{id}`), the first Founders card is always built from `$company->resolveUser()` — name from the User accessor, email from the User row, phone from `users.phone`, join date from `companies.created_at`, type hardcoded to `Founder`. It is not editable, has no LinkedIn icon (the `users` table has no linkedin column), and does not hit the database for a `team_members` row. Real founders follow the virtual card in creation order.

**Duplicate-detection contract.** Email and phone uniqueness are enforced per-company and per-type (`TeamMember='Founder'`) via direct queries inside a `DB::transaction` that holds a `lockForUpdate` on the founders rows of the company. This narrows the TOCTOU window but does not eliminate it — a unique index on `(Company_id, phone)` scoped to founders would need a partial index, which MySQL 8 supports only via a generated column. Deferred as a follow-up.
