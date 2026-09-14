#!/usr/bin/env bash
set -e

# ==============================================================================
# SCRIPT UPDATE CEPAT TALENTA (PRODUCTION SERVER)
# ==============================================================================
# Penggunaan di terminal server:
#   bash update.sh
#   atau: ./update.sh
# ==============================================================================

echo "🚀 [1/4] Mengambil perubahan terbaru dari Git..."
git pull origin main

echo "🗄️ [2/4] Menjalankan migrasi database..."
php artisan migrate --force

echo "⚡ [3/4] Membersihkan cache lama..."
php artisan view:clear
php artisan config:clear
php artisan route:clear
php artisan cache:clear

echo "🔥 [4/4] Mengoptimasi cache Laravel..."
php artisan optimize

echo "========================================================="
echo "🎉 UPDATE SERVER TALENTA BERHASIL SELESAI!"
echo "========================================================="
