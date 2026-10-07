#!/usr/bin/env bash
# ==============================================================================
# SCRIPT PUSH GIT DARI KOMPUTER LOKAL
# ==============================================================================
# Cara pakai di Git Bash lokal:
#   ./push.sh
#   ./push.sh "Pesan update Anda"
# ==============================================================================

MSG="${1:-Update Layar TV Signage Iklan dan Sponsor}"

echo "🚀 Menyiapkan file untuk dikirim ke GitHub..."
git add .
git commit -m "$MSG"
echo "📤 Mengunggah ke GitHub..."
git push origin main
echo "✅ Berhasil di-push ke GitHub!"