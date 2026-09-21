#!/usr/bin/env bash
# ==============================================================================
# SCRIPT PUSH GIT DARI KOMPUTER LOKAL
# ==============================================================================
# Cara pakai di Git Bash lokal:
#   ./push.sh
# ==============================================================================

echo "🚀 Menyiapkan file untuk dikirim ke GitHub..."
git add .
git commit -m "Update perbaikan dokumen cetak, bagan landscape, dan TTD QR Code"
echo "📤 Mengunggah ke GitHub..."
git push origin main
echo "✅ Berhasil di-push ke GitHub!"