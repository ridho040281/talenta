#!/usr/bin/env bash
set -e

echo "======================================================="
echo "🚀 MEMULAI UPDATE SISTEM TALENTA DI SERVER"
echo "======================================================="

# 1. Cek apakah ada file ZIP update, jika ada ekstrak otomatis
if [ -f "update-server-bagan-seeded.zip" ]; then
    echo "📦 Menemukan update-server-bagan-seeded.zip, mengekstrak file..."
    unzip -o update-server-bagan-seeded.zip
    echo "✅ Ekstraksi ZIP selesai."
elif [ -d ".git" ]; then
    echo "📥 Mengambil perubahan terbaru dari Git (origin main)..."
    git pull origin main
    echo "✅ Git pull selesai."
else
    echo "⚠️ Tidak ada file ZIP dan bukan folder Git, melanjutkan proses database & cache..."
fi

# 2. Jalankan migrasi database (kolom seed_number & struktur bagan)
echo "🗄️ Menjalankan migrasi database..."
php artisan migrate --force

# 3. Bersihkan seluruh cache lama
echo "🧹 Membersihkan cache Laravel (View, Route, Config, Cache)..."
php artisan optimize:clear
php artisan view:clear
php artisan route:clear
php artisan config:clear
php artisan cache:clear

# 4. Pastikan permission direktori aman
if [ -d "storage" ]; then
    chmod -R 775 storage bootstrap/cache 2>/dev/null || true
fi

echo "======================================================="
echo "🎉 UPDATE SERVER BERHASIL SELESAI DENGAN SUKSES!"
echo "🏸 Fitur Bagan Pertandingan & Wasit Bulu Tangkis siap digunakan."
echo "======================================================="
