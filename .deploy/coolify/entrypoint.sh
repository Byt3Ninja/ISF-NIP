#!/bin/sh
# Container start: put runtime folders on the persistent volumes, bring the
# database up to date, then start the web server, queue worker and scheduler.
set -eu

cd /var/www/html

: "${DB_HOST:?DB_HOST is required}"
: "${DB_DATABASE:?DB_DATABASE is required}"
: "${DB_USERNAME:?DB_USERNAME is required}"
: "${DB_PASSWORD:?DB_PASSWORD is required}"
: "${APP_KEY:?APP_KEY is required}"

# Build details recorded in .env, the same ones shown in the app's footer.
export APP_VERSION="${APP_VERSION:-$(cat version.txt 2>/dev/null || echo 0)}"
export APP_GIT_SHA="${APP_GIT_SHA:-$(printf '%s' "${SOURCE_COMMIT:-}" | cut -c1-8)}"

as_app() { runuser -u www-data -- "$@"; }

# PDO connection used for the few checks that must not depend on the app booting.
db_php() {
    php -r '
        $pdo = new PDO(
            "mysql:host=" . getenv("DB_HOST") . ";port=" . (getenv("DB_PORT") ?: 3306) . ";dbname=" . getenv("DB_DATABASE"),
            getenv("DB_USERNAME"), getenv("DB_PASSWORD"),
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        '"$1"
}

# --- storage volume -----------------------------------------------------------
mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views \
         storage/logs storage/app/public storage/app/private/importer storage/protected \
         storage/fonts bootstrap/cache
[ -L public/storage ] || ln -s /var/www/html/storage/app/public public/storage
[ -f storage/app/public/.htaccess ] || cp .deploy/upload-hardening/.htaccess storage/app/public/.htaccess

# --- uploads volume -----------------------------------------------------------
# These folders under public/ receive uploads at runtime. Each becomes a link to
# the uploads volume so files survive a redeploy. Files shipped in the image are
# copied across first, without replacing anything already uploaded.
for dir in 191014 pitch_decks documents formsubmissions organizations orgnizations \
           qrcodes icons/flags images/logo images/resource images/slider images/blog; do
    target="/var/www/html/uploads-data/$dir"
    mkdir -p "$target"
    if [ -d "public/$dir" ] && [ ! -L "public/$dir" ]; then
        cp -an "public/$dir/." "$target/" 2>/dev/null || true
        rm -rf "public/$dir"
    fi
    mkdir -p "$(dirname "public/$dir")"
    ln -sfn "$target" "public/$dir"
done

chown -R www-data:www-data storage bootstrap/cache uploads-data

# --- database -----------------------------------------------------------------
echo "Waiting for the database at $DB_HOST..."
tries=0
until db_php 'exit(0);' >/dev/null 2>&1; do
    tries=$((tries + 1))
    if [ "$tries" -ge 60 ]; then
        echo "The database did not become reachable within 2 minutes." >&2
        exit 1
    fi
    sleep 2
done

as_app php artisan package:discover --ansi
as_app php artisan optimize:clear
as_app php artisan migrate --force

users="$(db_php 'echo (int) $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();')"
if [ "$users" = "0" ]; then
    # First start on an empty database: load the reference data and the admin user.
    : "${ADMIN_PASSWORD:?ADMIN_PASSWORD is required on the first start}"
    as_app php artisan db:seed --force
    # AdminSeeder creates admin@growthlabs.app with a well-known default password.
    db_php '
        $hash = password_hash(getenv("ADMIN_PASSWORD"), PASSWORD_BCRYPT);
        $pdo->prepare("UPDATE users SET password = ? WHERE email = ?")->execute([$hash, "admin@growthlabs.app"]);
    '
    echo "Seeded a fresh database; admin password set from ADMIN_PASSWORD."
else
    # Keep roles and permissions up to date on every deploy.
    as_app php artisan db:seed --class=defaultRolesPermissionSeeder --force
fi

exec supervisord -n -c /etc/supervisor/app.conf
