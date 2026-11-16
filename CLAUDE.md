# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this is

A Laravel 10 / PHP 8.3 multi-tenant incubator-management platform, deployed for the Innovators Support Fund (ISF, Egypt). Server-rendered Blade on the Vuexy theme (Bootstrap 5, jQuery, DataTables), MySQL/MariaDB only. Models live directly in `app/` (there is no `app/Models`).

### ISF wording

`LOCALIZATION_PATH=isf` turns on a translation overlay (`app/Translation/OverlayFileLoader.php`, `resources/lang/{en,ar}/isf/`) and ISF-only behaviour (`isIsf()` in `app/Http/Helpers/Helpers.php`). Code names never change; only the words on screen do:

| In code | On the ISF screens |
|---|---|
| Organization | Program |
| Cycle | Sub Program |
| Company | Idea |
| organization manager | Program Manager |

## Commands

```bash
composer install                      # use Composer 2.8.x, see "Dependencies" below
php artisan migrate --force           # plain migrate works on an empty database
php artisan db:seed --force           # first install only: roles, admin user, settings row, lookups
php artisan db:seed --class=defaultRolesPermissionSeeder --force   # every deploy: syncs permissions
php artisan serve
```

There is no `.env.example`. The minimum environment is `APP_KEY` and the `DB_*` variables (`DB_CONNECTION=mysql`); add `LOCALIZATION_PATH=isf` for ISF behaviour.

Tests:

```bash
php vendor/bin/phpunit tests/Feature/Pipeline
php vendor/bin/phpunit tests/Feature/Pipeline/CompanyEnrollmentTest.php
php vendor/bin/phpunit --filter test_joining_a_cycle_opens_an_active_enrollment tests/Feature/Pipeline/CompanyEnrollmentTest.php
```

- Tests need a database that is **already migrated and seeded**. `phpunit.xml` sets no database, so they run against whatever the environment points to, inside a transaction per test.
- `tests/TestCase.php` refuses `RefreshDatabase`, `artisan migrate`, `db:seed` and any DDL inside a test. Run new migrations against the test database yourself before running tests.
- A large share of the existing suite fails on unchanged code (for example models that have a factory file but no `HasFactory` trait). Do not expect green: run with `--log-junit` before and after a change and compare test by test.
- Forms that validate `email:dns` look the address's domain up in DNS, so their tests fail offline unless the test calls `fakeEmailDns()` (`tests/Support/FakesEmailDns`), which answers that lookup from a fixed list of domains.

Nothing to build on the front end: the Vuexy assets and the old Mix output under `public/` are committed, and Mix is not part of any deploy. There is no linter configuration and indentation is mixed (tabs in some files, four spaces in others): match the file you are editing and do not reformat.

Deployment has two paths. The ISF staging stack is Coolify, defined in `.deploy/coolify/` (read its README). The older beta, testing and dev servers are updated by `.github/workflows/deploy-legacy.yml` on a push to the branch of the same name: `git pull`, `migrate --force`, then the permission seeder, with no `composer install` and no asset build. No workflow runs the tests.

### Dependencies

`composer.lock` is gitignored, so versions are resolved at install time (`minimum-stability: dev`, some `dev-*` requirements). Composer 2.9+ refuses to install any Laravel 10 release because of open security advisories; use 2.8.

## Architecture

### Routes are permissions

- Almost every staff page is a **named** route inside the single `dashboard` group in `routes/web.php` (`auth`, `verified`, `2fa`).
- `App\Http\Middleware\Permissions` (in the `web` group) requires the user to hold a permission **whose name is the route name**. Unnamed routes and everything in `routes/api.php` are not checked by it, so authorise those in the controller.
- The master list is `database/dbFiles/rolesWithDefaultPermissions.json`. A new named route needs an entry there with its `default_for` roles, or every non-exempt user gets a 401.
- `defaultRolesPermissionSeeder` runs on every deploy and rebuilds the permission set of each default role from that file. Permission changes made in the admin UI for those roles do not survive a deploy.
- Roles are matched by `roles.default_for`, never by `name`: `admin`, `subadmin`, `organizationmanager`, `registered` (a founder), `mentor`, `judger`, `serviceProvider`, `programManager`. Use `$user->hasRole()`, `ableTo()`, `allotRole()`. Admin gets no blanket allow; it must hold each permission. A new role also needs `Role::DEFAULT_FOR_VALUES` and an `admin.<key>` label in both languages.
- The staff sidebar comes from the closure in `config/navigation.php` (rendered by `partials/organized-menu`), with labels under `settings.navigation.*`. Founders get `partials/menu-startup`; mentors, judges and service providers get the hand-written `partials/menu`.
- `web.php` ends with a catch-all `{legacyFile}` route and has `/{org_slug}/...` wildcards. Declare new top-level public routes before them.

### Tenancy is manual

There is no global scope. `$user->organizations_ids` is the source of truth and every list filters by hand (`whereIn('organization_id', ...)`, or `whereHas` through the cycle). `managesOrganization()` is the staff check. The `OrganizationScope` trait is unused. `Company` has no `organization_id`: it is scoped through its cycle, and a founder's organization is derived from company → cycle.

### Programs, steps and where a company is

- `Organization` → `Cycle` (`type`: `General_Program` or `Hackathon`; `step_type`: `cycle` = staff move companies, `time` = the hourly `cycle:change-step` moves them by date) → `Step` (`stype` values in `app/Enums/StepType.php`, order in `arr`; the meaning of `data` and `list` depends on `stype`).
- A company's position is the pointer `companies.cycle` / `companies.step`, plus the single `companies.approved` flag. Rejection and alumni marking blank the pointer.
- `Company::cycle()` is a relation with the same name as the column, so `$company->cycle` is always the id. Use `cycledata()` / `cycleRelation()` and `stepdata()`.
- `App\CompanyEnrollment` (`company_enrollments`) is the history: one row each time a company enters a cycle. A `saved` hook on `Company` mirrors pointer changes into it. Two rules follow:
  - a bulk `Company::whereIn(...)->update(['step' => ...])` fires no model events, so call `CompanyEnrollment::moveToStep()` beside it;
  - code that ends an enrollment for a known reason calls `CompanyEnrollment::closeActive()` **before** clearing the pointer, otherwise the row closes as `withdrawn`.
- A company active in several cycles keeps one enrollment per cycle, each with its own `approved` / `approved_by`. The pointer shows the one the founder has selected: `Company::switchToEnrollment()` moves it without ending the others (sidebar control in `partials/program-switcher`), and clearing the pointer selects another active enrollment if one is left. A founder joins a second cycle by submitting its application form (`CyclesController::saveForm` → `enrollIn`); `partials/other-programs` on the founder dashboards lists what they can still apply to (`Cycle::joinableBy()`). There is no limit on how many.
- Writes go through three methods on `Company`, never through `cycle` / `step` directly: `enrollIn($cycleId, $stepId)` (join, or move within a cycle, and select it; never ends another enrollment), `moveToStepIn($stepId, $approval)` (staff move in the step's own cycle; the pointer follows only if that cycle is selected) and `leaveCycle($cycleId, $status, $by)` (reject or withdraw from one cycle). A plain `update(['cycle' => $other])` still means "moved": it closes the previous enrollment as withdrawn.
- A staff request that rejects (`screening.change_step` with `step=0`) carries `cycle`, the program of the page it was sent from. Without it the selected program is used.
- Inside `Company` model hooks read `getAttributes()['cycle']`, not `$company->cycle`: on a model never given a cycle the property resolves to the relation and throws.
- One company can be active in several cycles at once. To find who is in a cycle or on a step, use the scopes on `Company` (`enrolledIn`, `enrolledInAny`, `enrolledOnStep`, `enrolledOnAnyStep`, `enrolledInOnAStep`) and, on loaded models, `isEnrolledIn()`, `isOnStep()` and `stepIdIn($cycleId)`. Do not add new `where('cycle', ...)` / `where('step', ...)` queries on companies.
- Staff lists, screening, judging, exports, the staff dashboards (`HomeController`, `DashboardService`), `ReportController`, the hackathon staff dashboard and the training results export already read enrollments. For "companies in a cycle of these organizations" in a count or chart, use `CompanyEnrollment::activeCompanyIdsSql($organizationIds)` (`companies.id IN ...`): it matches a company once however many cycles it is in, and needs no join to `cycles` or `users`. Staff writes (`changestep`, `changAll`, `changAllTL`, `cycle:change-step`) act per cycle. Founder-facing screens (dashboard, mentorship, training, messaging to peers) read the pointer on purpose: it is the founder's selection. Alumni marking works on the selected program too, so a staff page sends `cycle` and `ClientCompanyController::inRequestedProgram()` selects it for the request and restores the founder's selection afterwards.
- `companies.project_code` is assigned once in the `created` hook and never changes.

### People

- A founder is a user with the `registered` role and one company through `users.company_id`. `companies` has no `user_id`. One founder, one company is assumed throughout.
- `team_members` serves two purposes: a company's founders, shareholders and investors, and (with `TeamMember = 'hackathon'` and `team_formation_id`) seats in a hackathon team.
- A hackathon team (`team_formations`) belongs to one cycle and has no `company_id`; it reaches a company through `team_members.Company_id`. Judging is per company. A user may hold one accepted team across all cycles.
- `App\Almuni` (table `alumni`) is a frozen JSON snapshot per company and cycle.
- ISF applicants carry an education status and an `education_entity_id` (`App\EducationEntity`: universities, institutes, research centers) plus a national ID or student code. These live on the user, not on the company.

### Forms and judging

- `builder_forms` / `builder_fields` are templates. `form_fields` are per-step copies of the questions, and answers in `form_data` point at `form_fields`. `docs/developer_docs/FORMS_MODULE.md` predates this.
- Two judging systems coexist. The current one is `ScreeningEvaluation` → criteria with weights → `ScreeningEvaluationResults` (one score per judge, criterion and company). The legacy one (`Formdata.screening` JSON, `Finalscreening`, `FinalData`) is still wired into some views. Panels live in `steps.list` and `screenings` rows.

### Settings and configuration

Platform settings are the single `settings` row with id 1 (`Settings::find(1)`), with per-organization overrides in `organizations_settings`. `AppServiceProvider::boot` overwrites mail, broadcasting and social-login config from the database, so those environment variables are ignored once the row is filled. Code reads `env()` outside `config/` and `config/navigation.php` returns a closure: **never run `config:cache`**.

### Rules for new code

- **Text**: bilingual columns are `name_en` / `name_ar` (`lang('name')` picks one). Every new string goes in both `resources/lang/en` and `resources/lang/ar`; add an `isf/` entry only where ISF wording differs.
- **Time**: use `App\Services\TimezoneService`. Dates are stored in the app zone, deadlines are judged in the platform zone of the organization (`TimezoneService::platform($organizationId)`), and shown in the viewer's zone.
- **Uploads**: go through `FileManagerService::upload()`. Anything confidential goes through `ProtectedFiles::store()` with a `ProtectedFileSet` and is served only by `ProtectedFileController`; a new set needs the enum constant, an entry in `config/protected_files.php`, a path shape in `ProtectedFiles::SHAPES` and a case in `FileAccess::canView`.
- **Notifications and mail**: real notifications are hand-written classes, not the template/trigger admin module. Copy the three-level channel gate (platform, organization, user) from `ProgramCreatedNotification::via`, use the `ConfiguresSmtp` trait, and return a `message` key from `toDatabase`. Mailables use `RoutesTenantMail`. Do not set `mail.*` config directly.
- **Queue**: production runs one `database` queue worker. Jobs must not call `auth()`.
- **Soft deletes**: many tables have `deleted_at`, but `Company`, `Cycle`, `Step`, `Formdata` and others do not use the `SoftDeletes` trait. Eloquent returns those rows; existing raw SQL filters `deleted_at` by hand.
- **Migrations**: anonymous classes, guarded with `Schema::hasTable` / `hasColumn` in both directions. Column changes are native (`Schema::useNativeSchemaOperationsIfPossible()` is set in `AppServiceProvider`), so restate every attribute on `->change()`.
- **API**: `routes/api.php`, bearer tokens through the `sanctum.custom` middleware, controllers in `app/Http/Controllers/API`, responses through the `ApiResponder` trait and `app/Http/Resources`. `app/Http/Controllers/Api` (lower-case) is dead duplicate code.

### Where the weight is

`ClientCompanyController`, `HomeController`, `HackathonController` and `CyclesController::modify` are several thousand lines each and lean on raw SQL. Some flows are implemented twice, for web and for the API (mentorship booking is one). When changing a rule, search for the second copy.

## Tests for new features

- Put feature tests under `tests/Feature/<Area>/`, name methods `test_snake_case(): void`.
- Build actors and data with `tests/Support/SecurityActors` (`actor('admin')`, `organization()`, `program()`, `step()`, `startup()`, `grant()`), and call `FakesOutboundServices::fakeOutboundServices()` so Zoom, Teams, WhatsApp, mail and HTTP are faked. `BuildsScreeningFixtures` builds a final-screening program.
- Use `config(['app.localization_path' => 'isf'])` to exercise ISF behaviour.
- Check that a model has the `HasFactory` trait before calling `::factory()` on it.
- A company inserted with `DB::table('companies')->insert(...)` has no enrollment row, so enrollment-based lists will not see it. Use the `SecurityActors` / `BuildsScreeningFixtures` helpers or `Company::factory()`, or call `CompanyEnrollment::syncFromPointer($company, null)` after the insert.
- The markdown guides in `tests/` describe an older setup (`RefreshDatabase`, SQLite) that `TestCase` now forbids.
