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

echo "🧹 Membersihkan view & cache..."
php artisan view:clear
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan optimize:clear

echo "✅ Update server Talenta berhasil diterapkan!"