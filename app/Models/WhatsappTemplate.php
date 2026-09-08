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
                'message' => "Assalamu'alaikum Wr. Wb.\nYth. {nama_peserta} ({nama_sekolah}),\n\nTerima kasih! Formulir pendaftaran cabang lomba *{cabang_lomba}* pada TALENTA 2026 MTsN 1 Blitar telah berhasil dikirim.\n\nDetail Pendaftaran:\n• NISN: {nisn}\n• Kode Pendaftaran: {kode_pendaftaran}\n• Cabang Lomba: {cabang_lomba}\n• Asal Sekolah: {nama_sekolah}\n• Status: Menunggu Verifikasi Berkas\n\nPanitia akan segera memeriksa kelengkapan berkas Anda. Pantau status pendaftaran melalui:\n{link_login}\n\nSalam hangat,\nPanitia TALENTA 2026 MTsN 1 Blitar",
                'is_active' => true,
                'is_system' => true,
            ],
            [
                'code' => 'registration_verified',
                'name' => '3. Notifikasi Pendaftaran Terverifikasi Sah',
                'description' => 'Terkirim otomatis saat panitia/PIC lomba memverifikasi status pendaftaran menjadi Sah (Verified).',
                'message' => "Assalamu'alaikum Wr. Wb.\nYth. Official & Peserta {nama_peserta} ({nama_sekolah}),\n\nKABAR GEMBIRA! Pendaftaran Anda pada ajang TALENTA 2026 MTsN 1 Blitar cabang *{cabang_lomba}* telah dinyatakan *SAH & TERVERIFIKASI*.\n\nDetail Peserta Resmi:\n• Nomor Peserta Resmi: *{no_peserta}*\n• NISN: {nisn}\n• Kode Registrasi: {kode_pendaftaran}\n• Cabang Lomba: {cabang_lomba}\n• Status: Terverifikasi (Sah)\n\nSilakan cetak Formulir Pendaftaran & Kartu Peserta resmi di dashboard Anda:\n{link_login}\n\nPantau jadwal TM, live undian, dan scoreboard melalui:\n{link_scoreboard}\n\nSemangat berlatih dan raih prestasi terbaik!\nPanitia TALENTA 2026 MTsN 1 Blitar",
                'is_active' => true,
                'is_system' => true,
            ],
            [
                'code' => 'draw_result_picked',
                'name' => '4. Notifikasi Hasil Undian & Nomor Tampil (Spin Wheel)',
                'description' => 'Terkirim otomatis ke WhatsApp peserta saat hasil putaran undian spin wheel / hacker draw berhasil disimpan.',
                'message' => "Assalamu'alaikum Wr. Wb.\nYth. Official & Peserta {nama_peserta} ({nama_sekolah}),\n\nHASIL PENGUNDIAN NOMOR URUT TAMPIL TALENTA 2026\n\nBerdasarkan hasil undian resmi panitia pada cabang *{cabang_lomba}*, nomor urut giliran tampil Anda adalah:\n\n🎲 *NOMOR URUT TAMPIL: #{nomor_undian}*\n\nDetail Peserta:\n• Nama: {nama_peserta}\n• NISN: {nisn}\n• Asal Sekolah: {nama_sekolah}\n• No. Peserta: {no_peserta}\n• Cabang Lomba: {cabang_lomba}\n\nPantau jadwal urutan tampil lengkap dan live scoreboard melalui:\n{link_scoreboard}\n\nHarap hadir tepat waktu sesuai dengan nomor urut tampil Anda.\nPanitia TALENTA 2026 MTsN 1 Blitar",
                'is_active' => true,
                'is_system' => true,
            ],
            [
                'code' => 'pic_new_registration',
                'name' => '5. Alert PIC: Pendaftar Baru Masuk (Siap Diverifikasi)',
                'description' => 'Terkirim otomatis ke WhatsApp PIC Lomba saat ada peserta baru yang mengirim formulir pendaftaran di cabangnya.',
                'message' => "📥 *NOTIFIKASI PIC LOMBA - BERKAS MASUK & SIAP DIVERIFIKASI*\n\nAssalamu'alaikum Wr. Wb.\nYth. Bapak/Ibu PIC Lomba *{cabang_lomba}*,\n\nAda pendaftar baru yang baru saja masuk dan *SIAP UNTUK DIVERIFIKASI*:\n\n👤 *Nama Peserta:* {nama_peserta}\n🏫 *Asal Sekolah:* {nama_sekolah}\n📝 *NISN:* {nisn}\n🎯 *Cabang Lomba:* {cabang_lomba}\n📄 *Kode Registrasi:* {kode_pendaftaran}\n📱 *No. WA Pendaftar:* {no_wa}\n\nSilakan login ke Dashboard PIC untuk memeriksa kelengkapan berkas:\n{link_login}\n\n_Sistem Notifikasi Otomatis TALENTA 2026 MTsN 1 Blitar_",
                'is_active' => true,
                'is_system' => true,
            ],
            [
                'code' => 'treasurer_new_payment',
                'name' => '6. Alert Bendahara: Pembayaran Masuk (Cek Mutasi)',
                'description' => 'Terkirim otomatis ke WhatsApp Bendahara saat ada pendaftar baru yang mengirim formulir/tagihan biaya pendaftaran.',
                'message' => "💸 *NOTIFIKASI BENDAHARA - PEMBAYARAN MASUK*\n\nAssalamu'alaikum Wr. Wb.\nYth. Bendahara Panitia TALENTA 2026 MTsN 1 Blitar,\n\nAda pendaftaran baru yang masuk dan *SIAP DICEK MUTASI REKENINGNYA*:\n\n👤 *Pendaftar / Sekolah:* {nama_peserta} ({nama_sekolah})\n📝 *NISN:* {nisn}\n🏆 *Cabang Lomba:* {cabang_lomba}\n💵 *Nominal Biaya:* Rp {nominal_biaya}\n📄 *Kode Registrasi:* {kode_pendaftaran}\n\nSilakan cek mutasi rekening bank dan verifikasi status pembayarannya di dashboard admin:\n{link_login}\n\n_Sistem Keuangan Otomatis TALENTA 2026 MTsN 1 Blitar_",
                'is_active' => true,
                'is_system' => true,
            ],
            [
                'code' => 'tm_spin_invitation',
                'name' => '7. Pengumuman Jadwal Technical Meeting & Undian',
                'description' => 'Template broadcast undangan Technical Meeting dan pengundian nomor urut tampil lomba.',
                'message' => "Pemberitahuan Jadwal Technical Meeting & Spin Undian TALENTA 2026\n\nKepada Yth. Delegasi {nama_sekolah} ({nama_peserta}),\n\nTechnical meeting dan penentuan nomor urut tampil cabang {cabang_lomba} akan dilaksanakan secara live transparan.\n\nNomor Peserta: {no_peserta}\nLink Scoreboard & Undian: {link_scoreboard}\n\nMohon hadir tepat waktu.\nPanitia TALENTA MTsN 1 Blitar",
                'is_active' => true,
                'is_system' => true,
            ],
            [
                'code' => 'registration_revision',
                'name' => '8. Notifikasi Berkas Perlu Perbaikan / Revisi',
                'description' => 'Terkirim otomatis ke WhatsApp peserta & official saat panitia/PIC meminta perbaikan data atau unggah ulang berkas.',
                'message' => "Assalamu'alaikum Wr. Wb.\nYth. Official & Peserta {nama_peserta} ({nama_sekolah}),\n\n*PEMBERITAHUAN REVISI BERKAS PENDAFTARAN TALENTA 2026*\n\nBerdasarkan pemeriksaan panitia pada cabang *{cabang_lomba}*, berkas pendaftaran Anda membutuhkan *PERBAIKAN / REVISI*:\n\nDetail Pendaftaran:\n• Kode Registrasi: *{kode_pendaftaran}*\n• Cabang Lomba: {cabang_lomba}\n• Catatan Panitia: *{catatan_verifikasi}*\n\nSilakan login ke akun pendaftaran Anda untuk memperbaiki data / mengunggah ulang berkas perbaikan:\n{link_login}\n\nMohon segera diperbaiki agar pendaftaran Anda dapat segera diverifikasi sah.\nSalam hangat,\nPanitia TALENTA 2026 MTsN 1 Blitar",
                'is_active' => true,
                'is_system' => true,
            ],
            [
                'code' => 'registration_rejected',
                'name' => '9. Notifikasi Pendaftaran Ditolak',
                'description' => 'Terkirim otomatis ke WhatsApp peserta & official saat panitia/PIC menolak pendaftaran.',
                'message' => "Assalamu'alaikum Wr. Wb.\nYth. Official & Peserta {nama_peserta} ({nama_sekolah}),\n\n*PEMBERITAHUAN STATUS PENDAFTARAN TALENTA 2026*\n\nMohon maaf, pendaftaran Anda pada cabang *{cabang_lomba}* belum dapat kami terima / dinyatakan *DITOLAK*:\n\nDetail Pendaftaran:\n• Kode Registrasi: *{kode_pendaftaran}*\n• Cabang Lomba: {cabang_lomba}\n• Alasan Penolakan: *{catatan_verifikasi}*\n\nUntuk informasi lebih lanjut atau konfirmasi kendala, silakan hubungi kontak resmi panitia atau cek status pendaftaran Anda di portal:\n{link_login}\n\nSalam hormat,\nPanitia TALENTA 2026 MTsN 1 Blitar",
                'is_active' => true,
                'is_system' => true,
            ],
            [
                'code' => 'treasurer_collective_invoice',
                'name' => '10. Alert Bendahara: Pendaftaran Kolektif Masuk (Cek Mutasi)',
                'description' => 'Terkirim otomatis ke WhatsApp Bendahara saat sekolah mengirim pendaftaran kolektif rombongan beserta bukti transfer.',
                'message' => "🏢 *ALERT BENDAHARA - PENDAFTARAN KOLEKTIF MASUK*\n\nAssalamu'alaikum Wr. Wb.\nYth. Bendahara Panitia {nama_kegiatan},\n\nTelah masuk pendaftaran kolektif (rombongan sekolah) baru beserta bukti transfer bank yang *SIAP DICEK MUTASI & DIVERIFIKASI*:\n\n🏫 *Asal Sekolah / Lembaga:* {nama_sekolah}\n👤 *Official / Penanggung Jawab:* {nama_pendaftar} ({no_wa})\n👥 *Total Siswa Didaftarkan:* {jumlah_peserta} Peserta\n📑 *No. Invoice Tagihan:* {kode_pendaftaran}\n💵 *Total Nominal Transfer:* Rp {nominal_biaya}\n\n📌 Bukti transfer bank telah diunggah oleh official. Silakan periksa mutasi rekening dan klik tautan berikut untuk verifikasi invoice:\n{link_login}\n\n_Sistem Keuangan Kolektif {nama_aplikasi} {nama_instansi}_",
                'is_active' => true,
                'is_system' => true,
            ],
            [
                'code' => 'collective_invoice_verified',
                'name' => '11. Notifikasi Kolektif: Tagihan Lunas & Siswa Terverifikasi Sah',
                'description' => 'Terkirim otomatis ke WhatsApp official sekolah saat bendahara menyetujui invoice pendaftaran kolektif.',
                'message' => "Assalamu'alaikum Wr. Wb.\nYth. Official & Pembina {nama_sekolah} ({nama_pendaftar}),\n\nALHAMDULILLAH! Pembayaran pendaftaran kolektif Anda ({kode_pendaftaran}) sebesar *Rp {nominal_biaya}* untuk *{jumlah_peserta} peserta* telah dinyatakan *LUNAS & DIVERIFIKASI RESMI OLEH BENDAHARA*.\n\nSeluruh delegasi siswa Anda kini telah resmi terdaftar dan berkas pendaftaran serta kartu peserta resmi sudah dapat dicetak secara kolektif di:\n{link_login}\n\nPantau jadwal perlombaan dan live scoreboard melalui:\n{link_scoreboard}\n\nTerima kasih atas partisipasi aktif {nama_sekolah}!\nPanitia {nama_kegiatan}",
                'is_active' => true,
                'is_system' => true,
            ],
            [
                'code' => 'collective_invoice_rejected',
                'name' => '12. Notifikasi Kolektif: Pembayaran Tagihan Ditolak',
                'description' => 'Terkirim otomatis ke WhatsApp official sekolah saat bendahara menolak invoice pendaftaran kolektif.',
                'message' => "Assalamu'alaikum Wr. Wb.\nYth. Official & Pembina {nama_sekolah} ({nama_pendaftar}),\n\n*PEMBERITAHUAN STATUS TAGIHAN KOLEKTIF {nama_kegiatan}*\n\nMohon maaf, tagihan pendaftaran kolektif Anda ({kode_pendaftaran}) belum dapat disetujui / dinyatakan *DITOLAK* oleh Bendahara:\n\nDetail Tagihan:\n• No. Invoice: *{kode_pendaftaran}*\n• Asal Sekolah: {nama_sekolah}\n• Catatan Bendahara: *{catatan_verifikasi}*\n\nSilakan login ke akun Anda untuk memeriksa rincian atau mengunggah ulang bukti transfer yang sesuai:\n{link_login}\n\nSalam hormat,\nBendahara Panitia {nama_kegiatan}",
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
