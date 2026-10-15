# Confidential uploads: private storage and cutover

Confidential files (registration papers, pitch/plan files, application attachments,
milestone, employee and task documents, program guideline documents) are served only
by `ProtectedFileController`, per file record (`protected_files`), after login, with
every access written to `file_access_logs`. New uploads are private as soon as this
release is deployed. Files uploaded before it stay where they are until the steps
below are run. Nothing in these steps deletes a file.

## Settings (.env)

| Key | Meaning |
|---|---|
| `PROTECTED_FILES_ROOT` | Base folder of the private disks. Outside the web root, kept across deploys, in backups, writable by the web user. Default `storage/protected`. |
| `PROTECTED_FILES_LEGACY_FALLBACK` | `true` until the cutover: a file not yet migrated keeps its old public link. `false` afterwards: no record, no link. |
| `PROTECTED_FILES_DELIVERY` | `stream` (PHP) or `x-accel` (nginx; needs the `/_protected/` location from `nginx.conf`). |

## Before deploying
- Run the two new migrations (`protected_files`, `file_access_logs`).
- Check `TrustProxies` so the logged IP is the visitor's, not the proxy's.
- If a CDN or caching proxy sits in front, make sure it does not cache these paths.

## Moving the existing files (run as the web server's user)

1. `php artisan files:protect:audit --json=audit.json` — read-only. Shows, per set: files found,
   references whose file is already missing, values that are not file paths, files several rows
   point at, and files no row refers to. Keep the report; it is the baseline.
2. `php artisan files:protect:copy` — copies each file to the private disk, checks it byte for
   byte, records it. The public files stay in place; the site can be live. Repeatable.
3. Maintenance window:
   - `php artisan down`
   - `php artisan files:protect:copy` (picks up files uploaded since step 2)
   - `php artisan files:protect:verify` — must pass. A file referenced by several owners with no
     way to tell whose it is blocks it; decide, or pass `--accept-undecided` (those get no link).
   - apply `nginx.conf` and reload nginx
   - `php artisan files:protect:retire` — moves the public copies to `{root}/_retired/` and
     unreferenced files to `{root}/_orphans/`. Try `--dry-run` first.
   - set `PROTECTED_FILES_LEGACY_FALLBACK=false` (and `PROTECTED_FILES_DELIVERY=x-accel`),
     `php artisan config:cache`, restart queue workers
   - `php artisan up`
4. Afterwards: open an old public URL as a guest (expect the login page), as the file's owner
   (expect the file) and as another startup (expect 404). `files:protect:watch` runs daily and
   warns if a file reappears in the old public folders. Rows with action `legacy` in
   `file_access_logs` show links that are still built the old way.

## Rollback
`php artisan files:protect:restore` puts every retired and orphaned file back and copies files
uploaded since the cutover to the public path the previous release expects. Then set
`PROTECTED_FILES_LEGACY_FALLBACK=true` and deploy the previous release. The owning tables were
never changed.

## Later
`{root}/_retired/`, `{root}/_orphans/` and `{root}/_trash/` (replaced or deleted files) are kept
until someone removes them by hand, after backups are confirmed.
