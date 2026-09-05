#!/usr/bin/env bash
# remote-deploy.sh — OPCIONAL. Script que se coloca en BanaHosting para que el cron de cPanel
# procese las instalaciones/migraciones tras un deploy por FTP (sin SSH).
#
# En BanaHosting FTP-only NO hay SSH, así que este script se ejecuta via CRON de cPanel.
# Lo que hace:
#   1) Backup de la BD (mysqldump) ANTES de migrar (regla CI/CD del ecosistema).
#   2) php artisan migrate --force
#   3) php artisan db:seed --force
#   4) php artisan config:clear + cache:clear
#
# Uso (cron de cPanel, 1 vez por minuto o tras deploy):
#   php /home/USER/scripts/remote-deploy.sh
# O si el hosting no permite bash, correr los comandos artisan directamente en el cron.

set -euo pipefail

# ---------------------------------------------------------------
# CONFIGURACION (ajustar por hosting)
APP_DIR="/home/USER/public_html"          # DocumentRoot de la app (public/ es DocumentRoot real)
ARTISAN="$APP_DIR/artisan"
DB_NAME="USER_quienmanda"
DB_USER="USER_quienmanda"
DB_PASS="REPLACE_ME"
BACKUP_DIR="/home/USER/backups"
# ---------------------------------------------------------------

export PATH="$PATH:/usr/local/bin:/usr/bin:/bin"

# Solo se ejecuta si existe el trigger de "deploy listo" colocado por el workflow FTP,
# o manualmente. Si quieres que corra siempre de forma idempotente, elimina el chequeo.
TRIGGER="$APP_DIR/storage/app/deploy-ready"
if [ ! -f "$TRIGGER" ]; then
    echo "Sin trigger de deploy; no se ejecuta migracion."
    exit 0
fi

mkdir -p "$BACKUP_DIR"

echo "== [1/4] Backup BD antes de migrar =="
STAMP=$(date +%Y%m%d_%H%M%S)
mysqldump --no-tablespaces -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" \
    > "$BACKUP_DIR/${DB_NAME}_${STAMP}.sql.gz.bak"
# En shared hosting usar dump directo si mysqldump no esta:
#   mysqldump -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" > "$BACKUP_DIR/${DB_NAME}_${STAMP}.sql"
echo "Backup: $BACKUP_DIR/${DB_NAME}_${STAMP}.sql"

echo "== [2/4] Migraciones =="
php "$ARTISAN" migrate --force

echo "== [3/4] Semillas =="
php "$ARTISAN" db:seed --force

echo "== [4/4] Limpieza de cache =="
php "$ARTISAN" config:clear
php "$ARTISAN" cache:clear
php "$ARTISAN" view:clear

rm -f "$TRIGGER"
echo "== OK. Deploy procesado. =="
