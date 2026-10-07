#!/usr/bin/env bash
# ==============================================================================
# SCRIPT GIT PULL & CLEAR CACHE SERVER
# ==============================================================================
# Cara pakai di terminal server:
#   chmod +x pull.sh
#   ./pull.sh
# ==============================================================================

echo "📥 Menarik pembaruan terbaru dari GitHub..."
git pull origin main

echo "🧹 Membersihkan cache view blade..."
php artisan view:clear

echo "⚡ Membersihkan cache aplikasi & route..."
php artisan cache:clear
php artisan config:clear
php artisan route:clear

echo "✅ Update & bersihkan cache selesai!"
