<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhatsappTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'description',
        'message',
        'is_active',
        'is_system',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_system' => 'boolean',
    ];

    /**
     * Get or create default system templates
     */
    public static function seedDefaults(): void
    {
        $defaults = [
            [
                'code' => 'account_created',
                'name' => '1. Notifikasi Pembuatan Akun Baru',
                'description' => 'Terkirim otomatis saat peserta/pendaftar baru selesai membuat akun di portal TALENTA 2026.',
                'message' => "Assalamu'alaikum Wr. Wb.\nYth. {nama_peserta} ({nama_sekolah}),\n\nSelamat! Akun pendaftaran TALENTA 2026 MTsN 1 Blitar Anda telah berhasil dibuat.\n\nDetail Akun Login:\n• NISN / ID Login: {nisn}\n• Password Default: {nisn}\n• No. WhatsApp: {no_wa}\n\nSilakan masuk ke portal TALENTA 2026 untuk memilih cabang lomba yang ingin diikuti:\n{link_login}\n\nSimpan pesan ini sebagai bukti akun resmi.\nPanitia TALENTA 2026 MTsN 1 Blitar",
                'is_active' => true,
                'is_system' => true,
            ],
            [
                'code' => 'registration_submitted',
                'name' => '2. Notifikasi Pengiriman Pendaftaran Lomba',
                'description' => 'Terkirim otomatis saat peserta mengirim formulir pendaftaran cabang lomba.',
                'message' => "Assalamu'alaikum Wr. Wb.\nYth. {nama_peserta} ({nama_sekolah}),\n\nTerima kasih! Formulir pendaftaran cabang lomba *{cabang_lomba}* pada TALENTA 2026 MTsN 1 Blitar telah berhasil dikirim.\n\nDetail Pendaftaran:\n• Kode Pendaftaran: {kode_pendaftaran}\n• Cabang Lomba: {cabang_lomba}\n• Asal Sekolah: {nama_sekolah}\n• Status: Menunggu Verifikasi Berkas\n\nPanitia akan segera memeriksa kelengkapan berkas Anda. Pantau status pendaftaran melalui:\n{link_login}\n\nSalam hangat,\nPanitia TALENTA 2026 MTsN 1 Blitar",
                'is_active' => true,
                'is_system' => true,
            ],
            [
                'code' => 'registration_verified',
                'name' => '3. Notifikasi Pendaftaran Terverifikasi Sah',
                'description' => 'Terkirim otomatis saat panitia/PIC lomba memverifikasi status pendaftaran menjadi Sah (Verified).',
                'message' => "Assalamu'alaikum Wr. Wb.\nYth. Official & Peserta {nama_peserta} ({nama_sekolah}),\n\nKABAR GEMBIRA! Pendaftaran Anda pada ajang TALENTA 2026 MTsN 1 Blitar cabang *{cabang_lomba}* telah dinyatakan *SAH & TERVERIFIKASI*.\n\nDetail Peserta Resmi:\n• Nomor Peserta Resmi: *{no_peserta}*\n• Kode Registrasi: {kode_pendaftaran}\n• Cabang Lomba: {cabang_lomba}\n• Status: Terverifikasi (Sah)\n\nSilakan cetak Formulir Pendaftaran & Kartu Peserta resmi di dashboard Anda:\n{link_login}\n\nPantau jadwal TM, live undian, dan scoreboard melalui:\n{link_scoreboard}\n\nSemangat berlatih dan raih prestasi terbaik!\nPanitia TALENTA 2026 MTsN 1 Blitar",
                'is_active' => true,
                'is_system' => true,
            ],
            [
                'code' => 'tm_spin_invitation',
                'name' => '4. Pengumuman Jadwal Technical Meeting & Undian',
                'description' => 'Template broadcast undangan Technical Meeting dan pengundian nomor urut tampil lomba.',
                'message' => "Pemberitahuan Jadwal Technical Meeting & Spin Undian TALENTA 2026\n\nKepada Yth. Delegasi {nama_sekolah} ({nama_peserta}),\n\nTechnical meeting dan penentuan nomor urut tampil cabang {cabang_lomba} akan dilaksanakan secara live transparan.\n\nNomor Peserta: {no_peserta}\nLink Scoreboard & Undian: {link_scoreboard}\n\nMohon hadir tepat waktu.\nPanitia TALENTA MTsN 1 Blitar",
                'is_active' => true,
                'is_system' => true,
            ],
        ];

        foreach ($defaults as $tmpl) {
            static::firstOrCreate(
                ['code' => $tmpl['code']],
                $tmpl
            );
        }
    }
}
