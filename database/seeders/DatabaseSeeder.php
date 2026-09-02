<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Competition;
use App\Models\CompetitionCriterion;
use App\Models\Registration;
use App\Models\RegistrationMember;
use App\Models\Score;
use App\Models\ScoreDetail;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Super Admin
        $superadmin = User::create([
            'name' => 'Master Admin TALENTA',
            'email' => 'admin@talenta.test',
            'password' => Hash::make('password123'),
            'role' => 'superadmin',
            'phone' => '081234567890',
            'account_type' => 'individu',
            'institution_name' => 'Panitia TALENTA MTsN 1 Blitar',
        ]);

        // 2. PIC Cabang Lomba
        $picMipa = User::create([
            'name' => 'Koordinator Olimpiade MIPA',
            'email' => 'pic.mipa@talenta.test',
            'password' => Hash::make('password123'),
            'role' => 'pic_lomba',
            'phone' => '081234567891',
            'account_type' => 'individu',
            'institution_name' => 'MTsN 1 Blitar',
        ]);

        $picMtq = User::create([
            'name' => 'Koordinator MTQ & Keagamaan',
            'email' => 'pic.mtq@talenta.test',
            'password' => Hash::make('password123'),
            'role' => 'pic_lomba',
            'phone' => '081234567892',
            'account_type' => 'individu',
            'institution_name' => 'MTsN 1 Blitar',
        ]);

        $picOlahraga = User::create([
            'name' => 'Koordinator Olahraga & Catur',
            'email' => 'pic.olahraga@talenta.test',
            'password' => Hash::make('password123'),
            'role' => 'pic_lomba',
            'phone' => '081234567893',
            'account_type' => 'individu',
            'institution_name' => 'MTsN 1 Blitar',
        ]);

        $picSeni = User::create([
            'name' => 'Koordinator Seni & Pop Singer',
            'email' => 'pic.seni@talenta.test',
            'password' => Hash::make('password123'),
            'role' => 'pic_lomba',
            'phone' => '081234567894',
            'account_type' => 'individu',
            'institution_name' => 'MTsN 1 Blitar',
        ]);

        $picTekno = User::create([
            'name' => 'Koordinator Robotik & Pramuka',
            'email' => 'pic.teknologi@talenta.test',
            'password' => Hash::make('password123'),
            'role' => 'pic_lomba',
            'phone' => '081234567895',
            'account_type' => 'individu',
            'institution_name' => 'MTsN 1 Blitar',
        ]);

        // 3. Dewan Juri
        $juri1 = User::create([
            'name' => 'Dr. H. Ahmad Fauzi, M.Pd (Juri Keagamaan & Seni)',
            'email' => 'juri1@talenta.test',
            'password' => Hash::make('password123'),
            'role' => 'juri',
            'phone' => '081234567801',
            'account_type' => 'individu',
            'institution_name' => 'LPTQ Jawa Timur',
        ]);

        $juri2 = User::create([
            'name' => 'Prof. Ir. Bambang Subagio, M.Sc (Juri MIPA & Robotik)',
            'email' => 'juri2@talenta.test',
            'password' => Hash::make('password123'),
            'role' => 'juri',
            'phone' => '081234567802',
            'account_type' => 'individu',
            'institution_name' => 'Institut Teknologi',
        ]);

        $juri3 = User::create([
            'name' => 'Master Hendra Gunawan, WN (Juri Catur & Olahraga)',
            'email' => 'juri3@talenta.test',
            'password' => Hash::make('password123'),
            'role' => 'juri',
            'phone' => '081234567803',
            'account_type' => 'individu',
            'institution_name' => 'Percasi Jawa Timur',
        ]);

        // 4. Akun Peserta & Kontingen
        $userPesertaIndividu = User::create([
            'name' => 'Muhammad Azka Ar-Rasyid',
            'email' => 'azka@talenta.test',
            'password' => Hash::make('password123'),
            'role' => 'peserta',
            'phone' => '085712345671',
            'account_type' => 'individu',
            'institution_name' => 'SD Plus Rahmat',
        ]);

        $userKontingen1 = User::create([
            'name' => 'Official SD Islam Al-Falah',
            'email' => 'alfalah@talenta.test',
            'password' => Hash::make('password123'),
            'role' => 'peserta',
            'phone' => '085712345672',
            'account_type' => 'kontingen',
            'institution_name' => 'SD Islam Al-Falah Blitar',
        ]);

        $userKontingen2 = User::create([
            'name' => 'Official MIN 1 Kota Blitar',
            'email' => 'min1@talenta.test',
            'password' => Hash::make('password123'),
            'role' => 'peserta',
            'phone' => '085712345673',
            'account_type' => 'kontingen',
            'institution_name' => 'MIN 1 Kota Blitar',
        ]);

        $userKontingen3 = User::create([
            'name' => 'Official MI Miftahul Huda',
            'email' => 'miftahulhuda@talenta.test',
            'password' => Hash::make('password123'),
            'role' => 'peserta',
            'phone' => '085712345674',
            'account_type' => 'kontingen',
            'institution_name' => 'MI Miftahul Huda',
        ]);

        // 5. Kategori Lomba
        $catSains = Category::create([
            'name' => 'Akademik & Sains',
            'slug' => 'akademik-sains',
            'icon' => 'book-open',
            'description' => 'Ajang uji ketangkasan nalar, logika, dan pemahaman sains mendalam.',
            'order' => 1,
        ]);

        $catAgama = Category::create([
            'name' => 'Keagamaan & Syiar',
            'slug' => 'keagamaan-syiar',
            'icon' => 'award',
            'description' => 'Lomba pembacaan dan hafalan ayat-ayat suci Al-Qur\'an.',
            'order' => 2,
        ]);

        $catOlahraga = Category::create([
            'name' => 'Olahraga & Ketangkasan',
            'slug' => 'olahraga-ketangkasan',
            'icon' => 'trophy',
            'description' => 'Adu strategi, kecepatan, dan fisik dalam perlombaan sportivitas.',
            'order' => 3,
        ]);

        $catSeni = Category::create([
            'name' => 'Seni & Suara',
            'slug' => 'seni-suara',
            'icon' => 'mic',
            'description' => 'Unjuk bakat olah vokal, penghayatan musik religi dan pop kreatif.',
            'order' => 4,
        ]);

        $catTekno = Category::create([
            'name' => 'Teknologi & Kepanduan',
            'slug' => 'teknologi-kepanduan',
            'icon' => 'cpu',
            'description' => 'Eksplorasi inovasi robotika modern dan kekompakan kepanduan pramuka.',
            'order' => 5,
        ]);

        // 6. Cabang Perlombaan & Kriteria Penilaian
        // 6.1 MTQ
        $lombaMtq = Competition::create([
            'category_id' => $catAgama->id,
            'pic_id' => $picMtq->id,
            'name' => 'Musabaqah Tilawatil Qur\'an (MTQ)',
            'slug' => 'musabaqah-tilawatil-quran',
            'code' => 'MTQ',
            'type' => 'individu',
            'min_members' => 1,
            'max_members' => 1,
            'quota' => 40,
            'registration_fee' => 0,
            'venue' => 'Masjid Ulul Albab MTsN 1 Blitar',
            'schedule_date' => '2026-09-15',
            'schedule_time' => '08:00 - Selesai',
            'status' => 'buka',
            'rules' => "1. Peserta membawakan maqra' yang telah ditentukan.\n2. Waktu penampilan maksimal 7 menit.\n3. Memperhatikan kaidah tajwid, fashahah, dan lagu/suara.",
        ]);
        $lombaMtq->judges()->attach([$juri1->id => ['role_title' => 'Ketua Dewan Juri MTQ']]);

        CompetitionCriterion::create(['competition_id' => $lombaMtq->id, 'name' => 'Kaidah Tajwid', 'weight_percentage' => 35, 'min_score' => 0, 'max_score' => 100, 'description' => 'Makharijul huruf, sifatul huruf, ahkamul mad']);
        CompetitionCriterion::create(['competition_id' => $lombaMtq->id, 'name' => 'Fashahah & Adab', 'weight_percentage' => 35, 'min_score' => 0, 'max_score' => 100, 'description' => 'Adabut tilawah, kelancaran, waqaf & ibtida']);
        CompetitionCriterion::create(['competition_id' => $lombaMtq->id, 'name' => 'Suara & Irama', 'weight_percentage' => 30, 'min_score' => 0, 'max_score' => 100, 'description' => 'Keindahan nada, variasi lagu bayati, hijaz, saba, rost']);

        // 6.2 Tahfidz
        $lombaTahfidz = Competition::create([
            'category_id' => $catAgama->id,
            'pic_id' => $picMtq->id,
            'name' => 'Tahfidz Qur\'an Juz 30',
            'slug' => 'tahfidz-quran-juz-30',
            'code' => 'THF',
            'type' => 'individu',
            'min_members' => 1,
            'max_members' => 1,
            'quota' => 50,
            'registration_fee' => 0,
            'venue' => 'Aula Utama MTsN 1 Blitar Lt. 2',
            'schedule_date' => '2026-09-15',
            'schedule_time' => '08:30 - Selesai',
            'status' => 'buka',
            'rules' => "1. Menjawab sambung ayat dari dewan juri sebanyak 3 paket soal.\n2. Penilaian kelancaran, tajwid, dan tartil.",
        ]);
        $lombaTahfidz->judges()->attach([$juri1->id => ['role_title' => 'Dewan Juri Tahfidz']]);

        CompetitionCriterion::create(['competition_id' => $lombaTahfidz->id, 'name' => 'Kelancaran Hafalan (Tahfidz)', 'weight_percentage' => 45, 'min_score' => 0, 'max_score' => 100]);
        CompetitionCriterion::create(['competition_id' => $lombaTahfidz->id, 'name' => 'Tajwid & Makhraj', 'weight_percentage' => 35, 'min_score' => 0, 'max_score' => 100]);
        CompetitionCriterion::create(['competition_id' => $lombaTahfidz->id, 'name' => 'Adab & Fashahah', 'weight_percentage' => 20, 'min_score' => 0, 'max_score' => 100]);

        // 6.3 Olimpiade MIPA
        $lombaMipa = Competition::create([
            'category_id' => $catSains->id,
            'pic_id' => $picMipa->id,
            'name' => 'Olimpiade MIPA (Matematika & IPA Terpadu)',
            'slug' => 'olimpiade-mipa',
            'code' => 'MIPA',
            'type' => 'individu',
            'min_members' => 1,
            'max_members' => 1,
            'quota' => 100,
            'registration_fee' => 0,
            'venue' => 'Lab Komputer & CBT Center MTsN 1 Blitar',
            'schedule_date' => '2026-09-16',
            'schedule_time' => '08:00 - 11:30',
            'status' => 'buka',
            'rules' => "1. Terdiri dari babak penyisihan (CBT) dan babak final (Eksperimen & Uraian).\n2. Dilarang membawa alat hitung digital eksternal.",
        ]);
        $lombaMipa->judges()->attach([$juri2->id => ['role_title' => 'Dewan Juri Olimpiade Sains']]);

        CompetitionCriterion::create(['competition_id' => $lombaMipa->id, 'name' => 'Ketepatan Jawaban Matematika', 'weight_percentage' => 50, 'min_score' => 0, 'max_score' => 100]);
        CompetitionCriterion::create(['competition_id' => $lombaMipa->id, 'name' => 'Ketepatan Jawaban IPA / Sains', 'weight_percentage' => 50, 'min_score' => 0, 'max_score' => 100]);

        // 6.4 Catur
        $lombaCatur = Competition::create([
            'category_id' => $catOlahraga->id,
            'pic_id' => $picOlahraga->id,
            'name' => 'Catur Cepat Standar Percasi',
            'slug' => 'catur-cepat-percasi',
            'code' => 'CTR',
            'type' => 'individu',
            'min_members' => 1,
            'max_members' => 1,
            'quota' => 64,
            'registration_fee' => 0,
            'venue' => 'Ruang Multimedia MTsN 1 Blitar',
            'schedule_date' => '2026-09-16',
            'schedule_time' => '09:00 - Selesai',
            'status' => 'buka',
            'rules' => "1. Sistem Swiss 5 Babak, waktu pikir 15 menit + increment 5 detik.\n2. Mengikuti peraturan FIDE & Percasi terbaru.",
        ]);
        $lombaCatur->judges()->attach([$juri3->id => ['role_title' => 'Wasit Utama Catur']]);

        CompetitionCriterion::create(['competition_id' => $lombaCatur->id, 'name' => 'Poin Kemenangan Match', 'weight_percentage' => 70, 'min_score' => 0, 'max_score' => 100]);
        CompetitionCriterion::create(['competition_id' => $lombaCatur->id, 'name' => 'Taktik & Sportivitas', 'weight_percentage' => 30, 'min_score' => 0, 'max_score' => 100]);

        // 6.5 Tenis Meja
        $lombaTenis = Competition::create([
            'category_id' => $catOlahraga->id,
            'pic_id' => $picOlahraga->id,
            'name' => 'Tenis Meja Tunggal',
            'slug' => 'tenis-meja-tunggal',
            'code' => 'TMJ',
            'type' => 'individu',
            'min_members' => 1,
            'max_members' => 1,
            'quota' => 32,
            'registration_fee' => 0,
            'venue' => 'Gedung Olahraga (GOR) MTsN 1 Blitar',
            'schedule_date' => '2026-09-16',
            'schedule_time' => '08:00 - Selesai',
            'status' => 'buka',
            'rules' => "1. Sistem gugur tunggal, best of 5 games.\n2. Membawa bet pribadi sesuai standar PTMSI.",
        ]);
        $lombaTenis->judges()->attach([$juri3->id => ['role_title' => 'Wasit Tenis Meja']]);

        CompetitionCriterion::create(['competition_id' => $lombaTenis->id, 'name' => 'Skor Pertandingan', 'weight_percentage' => 80, 'min_score' => 0, 'max_score' => 100]);
        CompetitionCriterion::create(['competition_id' => $lombaTenis->id, 'name' => 'Fair Play & Disiplin', 'weight_percentage' => 20, 'min_score' => 0, 'max_score' => 100]);

        // 6.6 Pop Singer
        $lombaSinger = Competition::create([
            'category_id' => $catSeni->id,
            'pic_id' => $picSeni->id,
            'name' => 'Pop Singer Religi & Kreasi',
            'slug' => 'pop-singer-religi',
            'code' => 'POP',
            'type' => 'individu',
            'min_members' => 1,
            'max_members' => 1,
            'quota' => 35,
            'registration_fee' => 0,
            'venue' => 'Panggung Terbuka Seni Budaya MTsN 1 Blitar',
            'schedule_date' => '2026-09-17',
            'schedule_time' => '08:00 - Selesai',
            'status' => 'buka',
            'rules' => "1. Menyanyikan 1 lagu wajib pilihan dan 1 lagu bebas bertema religi/edukasi.\n2. Menyerahkan minus one audio saat daftar ulang.",
        ]);
        $lombaSinger->judges()->attach([$juri1->id => ['role_title' => 'Juri Vokal Seni']]);

        CompetitionCriterion::create(['competition_id' => $lombaSinger->id, 'name' => 'Materi Vokal & Pitch Control', 'weight_percentage' => 40, 'min_score' => 0, 'max_score' => 100]);
        CompetitionCriterion::create(['competition_id' => $lombaSinger->id, 'name' => 'Penghayatan & Artikulasi', 'weight_percentage' => 30, 'min_score' => 0, 'max_score' => 100]);
        CompetitionCriterion::create(['competition_id' => $lombaSinger->id, 'name' => 'Penampilan, Kostum & Stage Act', 'weight_percentage' => 30, 'min_score' => 0, 'max_score' => 100]);

        // 6.7 Robotik Transporter (Kolektif)
        $lombaRobotik = Competition::create([
            'category_id' => $catTekno->id,
            'pic_id' => $picTekno->id,
            'name' => 'Robotik Transporter & Line Follower',
            'slug' => 'robotik-transporter',
            'code' => 'ROB',
            'type' => 'kolektif',
            'min_members' => 2,
            'max_members' => 3,
            'quota' => 24,
            'registration_fee' => 0,
            'venue' => 'Arena Robotika Lt. 1 Gedung Sains',
            'schedule_date' => '2026-09-17',
            'schedule_time' => '09:00 - Selesai',
            'status' => 'buka',
            'rules' => "1. Satu tim terdiri dari 2 - 3 siswa.\n2. Robot harus memindahkan balok rintangan ke drop-zone dalam arena resmi.",
        ]);
        $lombaRobotik->judges()->attach([$juri2->id => ['role_title' => 'Juri Teknis Robotika']]);

        CompetitionCriterion::create(['competition_id' => $lombaRobotik->id, 'name' => 'Keberhasilan Misi & Poin Balok', 'weight_percentage' => 50, 'min_score' => 0, 'max_score' => 100]);
        CompetitionCriterion::create(['competition_id' => $lombaRobotik->id, 'name' => 'Kecepatan Waktu Lintasan', 'weight_percentage' => 25, 'min_score' => 0, 'max_score' => 100]);
        CompetitionCriterion::create(['competition_id' => $lombaRobotik->id, 'name' => 'Inovasi Desain Mekanik & Algoritma', 'weight_percentage' => 25, 'min_score' => 0, 'max_score' => 100]);

        // 6.8 Pramuka Pionering (Kolektif)
        $lombaPramuka = Competition::create([
            'category_id' => $catTekno->id,
            'pic_id' => $picTekno->id,
            'name' => 'Pramuka Pionering Kreatif',
            'slug' => 'pramuka-pionering',
            'code' => 'PRM',
            'type' => 'kolektif',
            'min_members' => 4,
            'max_members' => 6,
            'quota' => 20,
            'registration_fee' => 0,
            'venue' => 'Lapangan Utama MTsN 1 Blitar',
            'schedule_date' => '2026-09-17',
            'schedule_time' => '07:30 - 11:30',
            'status' => 'buka',
            'rules' => "1. Satu regu beranggotakan 4 - 6 orang.\n2. Membuat bangunan pionering bertema Menara Pandang/Jembatan dengan 30 tongkat dalam 90 menit.",
        ]);
        $lombaPramuka->judges()->attach([$juri1->id => ['role_title' => 'Juri Kepanduan']]);

        CompetitionCriterion::create(['competition_id' => $lombaPramuka->id, 'name' => 'Kekuatan & Kerapian Simpul/Ikatan', 'weight_percentage' => 40, 'min_score' => 0, 'max_score' => 100]);
        CompetitionCriterion::create(['competition_id' => $lombaPramuka->id, 'name' => 'Kreativitas & Estetika Bentuk', 'weight_percentage' => 30, 'min_score' => 0, 'max_score' => 100]);
        CompetitionCriterion::create(['competition_id' => $lombaPramuka->id, 'name' => 'Kekompakan Regu & Ketepatan Waktu', 'weight_percentage' => 30, 'min_score' => 0, 'max_score' => 100]);

        // 7. Sample Registrations
        // 7.1 Registrasi MTQ - Azka (Individu)
        $reg1 = Registration::create([
            'competition_id' => $lombaMtq->id,
            'user_id' => $userPesertaIndividu->id,
            'registration_code' => 'REG-2026-MTQ-001',
            'participant_number' => 'MTQ-01',
            'draw_number' => 3,
            'institution_name' => 'SD Plus Rahmat',
            'official_name' => 'Ust. Salman Al-Farisi',
            'official_phone' => '085712345671',
            'status' => 'verified',
            'verification_notes' => 'Berkas pendaftaran lengkap dan sah.',
            'verified_at' => now(),
            'verified_by' => $picMtq->id,
        ]);
        RegistrationMember::create([
            'registration_id' => $reg1->id,
            'full_name' => 'Muhammad Azka Ar-Rasyid',
            'nisn' => '0123456789',
            'gender' => 'L',
            'birth_place' => 'Blitar',
            'birth_date' => '2013-05-12',
            'phone' => '085712345671',
        ]);

        // 7.2 Registrasi MTQ - Al-Falah (Kontingen)
        $reg2 = Registration::create([
            'competition_id' => $lombaMtq->id,
            'user_id' => $userKontingen1->id,
            'registration_code' => 'REG-2026-MTQ-002',
            'participant_number' => 'MTQ-02',
            'draw_number' => 1,
            'institution_name' => 'SD Islam Al-Falah Blitar',
            'official_name' => 'H. Muhaimin, S.Pd.I',
            'official_phone' => '085712345672',
            'status' => 'verified',
            'verification_notes' => 'Terverifikasi.',
            'verified_at' => now(),
            'verified_by' => $picMtq->id,
        ]);
        RegistrationMember::create([
            'registration_id' => $reg2->id,
            'full_name' => 'Aisyah Nur Ramadhani',
            'nisn' => '0123456790',
            'gender' => 'P',
            'birth_place' => 'Blitar',
            'birth_date' => '2013-08-20',
            'phone' => '085712345672',
        ]);

        // 7.3 Registrasi MTQ - MIN 1 (Kontingen)
        $reg3 = Registration::create([
            'competition_id' => $lombaMtq->id,
            'user_id' => $userKontingen2->id,
            'registration_code' => 'REG-2026-MTQ-003',
            'participant_number' => 'MTQ-03',
            'draw_number' => 2,
            'institution_name' => 'MIN 1 Kota Blitar',
            'official_name' => 'Usth. Siti Aminah, S.Ag',
            'official_phone' => '085712345673',
            'status' => 'verified',
            'verification_notes' => 'Lengkap.',
            'verified_at' => now(),
            'verified_by' => $picMtq->id,
        ]);
        RegistrationMember::create([
            'registration_id' => $reg3->id,
            'full_name' => 'Fatih Zaidan Al-Farisy',
            'nisn' => '0123456791',
            'gender' => 'L',
            'birth_place' => 'Blitar',
            'birth_date' => '2013-03-15',
            'phone' => '085712345673',
        ]);

        // 7.4 Registrasi Robotik (Kolektif Tim)
        $regRobo = Registration::create([
            'competition_id' => $lombaRobotik->id,
            'user_id' => $userKontingen1->id,
            'registration_code' => 'REG-2026-ROB-001',
            'participant_number' => 'ROB-01',
            'draw_number' => 1,
            'team_name' => 'Al-Falah CyberBot',
            'institution_name' => 'SD Islam Al-Falah Blitar',
            'official_name' => 'Dwi Prasetyo, S.Kom',
            'official_phone' => '085712345672',
            'status' => 'verified',
            'verification_notes' => 'Tim lolos berkas.',
            'verified_at' => now(),
            'verified_by' => $picTekno->id,
        ]);
        RegistrationMember::create([
            'registration_id' => $regRobo->id,
            'full_name' => 'Rayhan Bintang Pratama',
            'nisn' => '0123456801',
            'gender' => 'L',
            'role_in_team' => 'Ketua Tim / Programmer',
        ]);
        RegistrationMember::create([
            'registration_id' => $regRobo->id,
            'full_name' => 'Galang Aditya Nugraha',
            'nisn' => '0123456802',
            'gender' => 'L',
            'role_in_team' => 'Mekanik Robot',
        ]);

        // 8. Sample Scores MTQ
        // Score untuk Azka
        $scoreAzka = Score::create([
            'competition_id' => $lombaMtq->id,
            'registration_id' => $reg1->id,
            'judge_id' => $juri1->id,
            'total_score' => 93.50,
            'is_locked' => true,
            'notes' => 'Fashahah sangat bagus, makharijul huruf tajam, lagu bayati syahdu.',
        ]);
        $critMtq = $lombaMtq->criteria;
        ScoreDetail::create(['score_id' => $scoreAzka->id, 'criterion_id' => $critMtq[0]->id, 'score_value' => 94]);
        ScoreDetail::create(['score_id' => $scoreAzka->id, 'criterion_id' => $critMtq[1]->id, 'score_value' => 95]);
        ScoreDetail::create(['score_id' => $scoreAzka->id, 'criterion_id' => $critMtq[2]->id, 'score_value' => 91]);

        // Score untuk Aisyah
        $scoreAisyah = Score::create([
            'competition_id' => $lombaMtq->id,
            'registration_id' => $reg2->id,
            'judge_id' => $juri1->id,
            'total_score' => 95.00,
            'is_locked' => true,
            'notes' => 'Irama suara merdu, waqaf ibtida sempurna.',
        ]);
        ScoreDetail::create(['score_id' => $scoreAisyah->id, 'criterion_id' => $critMtq[0]->id, 'score_value' => 96]);
        ScoreDetail::create(['score_id' => $scoreAisyah->id, 'criterion_id' => $critMtq[1]->id, 'score_value' => 94]);
        ScoreDetail::create(['score_id' => $scoreAisyah->id, 'criterion_id' => $critMtq[2]->id, 'score_value' => 95]);

        // Score untuk Fatih
        $scoreFatih = Score::create([
            'competition_id' => $lombaMtq->id,
            'registration_id' => $reg3->id,
            'judge_id' => $juri1->id,
            'total_score' => 89.00,
            'is_locked' => true,
            'notes' => 'Bagus, perlu peningkatan kestabilan nafas di nada tinggi.',
        ]);
        ScoreDetail::create(['score_id' => $scoreFatih->id, 'criterion_id' => $critMtq[0]->id, 'score_value' => 90]);
        ScoreDetail::create(['score_id' => $scoreFatih->id, 'criterion_id' => $critMtq[1]->id, 'score_value' => 88]);
        ScoreDetail::create(['score_id' => $scoreFatih->id, 'criterion_id' => $critMtq[2]->id, 'score_value' => 89]);
    }
}
