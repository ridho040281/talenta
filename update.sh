#!/usr/bin/env bash
# ==============================================================================
# SCRIPT UPDATE CEPAT SERVER TALENTA
# ==============================================================================
# Cara pakai di server:
#   chmod +x update.sh
#   ./update.sh
# ==============================================================================

echo "📥 Menarik pembaruan terbaru dari Git..."
git pull origin main

echo "🗄️ Menjalankan migrasi database..."
php artisan migrate --force

echo "🧹 Membersihkan view & cache..."
php artisan view:clear
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan optimize:clear

echo "🔄 Me-reload PHP-FPM / OPcache..."
if systemctl is-active --quiet php8.3-fpm; then
    sudo systemctl reload php8.3-fpm || true
elif systemctl is-active --quiet php-fpm-83; then
    sudo systemctl reload php-fpm-83 || true
elif systemctl is-active --quiet php8.2-fpm; then
    sudo systemctl reload php8.2-fpm || true
fi

echo "✅ Update server Talenta berhasil diterapkan!"