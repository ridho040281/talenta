#!/usr/bin/env bash
set -e

# ==============================================================================
# SCRIPT DEPLOYMENT OTOMATIS TALENTA (PRODUCTION SERVER)
# ==============================================================================
# Penggunaan:
#   chmod +x deploy.sh
#   ./deploy.sh
# ==============================================================================

echo "🚀 [1/8] Memulai proses deployment TALENTA..."

# Aktifkan maintenance mode
echo "🔒 [2/8] Mengaktifkan maintenance mode..."
php artisan down --render="errors::503" --secret="talenta-deploy-bypass-2026" || true

# Pull update terbaru dari git repository
echo "📥 [3/8] Mengambil perubahan terbaru dari Git..."
git pull origin main

# Install / update dependensi Composer (Production mode)
echo "📦 [4/8] Menginstall dependensi Composer (no-dev)..."
composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

# Install dependensi NPM & Build Asset Vite
echo "🎨 [5/8] Mengompilasi frontend assets (Vite)..."
if command -v npm &> /dev/null; then
    npm ci --prefer-offline --no-audit
    npm run build
fi

# Jalankan migrasi database
echo "🗄️ [6/8] Menjalankan migrasi database..."
php artisan migrate --force

# Pastikan symbolic link storage terpasang
php artisan storage:link || true

# Optimasi Cache Laravel (Route, Config, View, Events)
echo "⚡ [7/8] Mengoptimasi cache Laravel..."
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

# Set permissions storage & bootstrap/cache
echo "🔐 [8/8] Memperbaiki permission direktori..."
chmod -R 775 storage bootstrap/cache || true
chown -R www-data:www-data storage bootstrap/cache || true

# Restart queue worker jika supervisor aktif
if command -v supervisorctl &> /dev/null; then
    supervisorctl restart talenta-worker:* || true
fi

# Restart PHP-FPM / OPcache (sesuaikan versi PHP server Anda)
if systemctl is-active --quiet php8.3-fpm; then
    sudo systemctl reload php8.3-fpm || true
elif systemctl is-active --quiet php8.2-fpm; then
    sudo systemctl reload php8.2-fpm || true
fi

# Matikan maintenance mode
php artisan up

echo "✅ ========================================================="
echo "🎉 DEPLOYMENT TALENTA BERHASIL SELESAI!"
echo "🌐 Aplikasi siap digunakan di server produksi."
echo "========================================================="