# Coolify staging stack

Runs the platform on Coolify as two containers: `app` (Apache + PHP 8.3, with the
queue worker and the scheduler under supervisord) and `db` (MariaDB 10.11).

## Coolify application settings

| Setting | Value |
|---|---|
| Build pack | Docker Compose |
| Base directory | `/.deploy/coolify` |
| Compose file | `/compose.yaml` |
| Domain | on the `app` service |

## Environment variables

| Variable | Required | Meaning |
|---|---|---|
| `ISF_APP_KEY` | yes | Laravel key, `base64:` followed by 32 random bytes in base64. Changing it logs everyone out and makes encrypted values unreadable. |
| `ISF_APP_URL` | yes | Public address, e.g. `https://isf.mystartpad.com`. |
| `ISF_DB_PASSWORD` | yes | Password of the `isf` database user. |
| `ISF_DB_ROOT_PASSWORD` | yes | MariaDB root password. |
| `ISF_ADMIN_PASSWORD` | yes | Password given to `admin@growthlabs.app` on the first start only. Later changes are made in the app. |
| `ISF_APP_ENV` | no | Default `beta`, which hides the same unfinished menu entries as production. |
| `ISF_APP_NAME`, `ISF_APP_TIMEZONE`, `ISF_APP_DEBUG` | no | Defaults `ISF`, `UTC`, `false`. |
| `ISF_MAIL_MAILER`, `ISF_MAIL_FROM_ADDRESS` | no | Default mailer is `log`: mail is written to the container log, not sent. |

## What a start does

1. Links the upload folders under `public/` to the `uploads` volume and prepares `storage/`.
2. Waits for the database, then runs `migrate --force`.
3. On an empty database: runs the full seeder and sets the admin password.
   Otherwise: runs `defaultRolesPermissionSeeder`, as the GitLab pipeline does.
4. Starts Apache, `queue:work` and `schedule:work`.

## Kept across deploys

Three volumes: `database` (MariaDB data), `storage` (`storage/`, including private
uploads under `storage/protected`) and `uploads` (public upload folders).

## Not included

- Live chat and notifications over websockets (`BROADCAST_DRIVER=log`).
- A committed `composer.lock`: package versions are resolved when the image is built.
- Config caching. The code reads `env()` outside `config/`, which a cached config would break.
