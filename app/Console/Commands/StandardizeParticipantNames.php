<?php

namespace App\Console\Commands;

use App\Helpers\NameStandardizer;
use App\Models\BadmintonMatch;
use App\Models\Registration;
use App\Models\RegistrationMember;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

#[Signature('talenta:standardize-names')]
#[Description('Standarisasi seluruh nama peserta, nama official, nama sekolah, dan nama pemain di database menjadi Title Case yang rapi')]
class StandardizeParticipantNames extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('=== MEMULAI STANDARISASI NAMA PESERTA & SEKOLAH TALENTA ===');

        // 1. Standarisasi RegistrationMember (Nama Siswa & Sekolah)
        $this->line('1. Memproses data anggota peserta (registration_members)...');
        $updatedMembers = 0;
        RegistrationMember::chunk(200, function ($members) use (&$updatedMembers) {
            foreach ($members as $member) {
                $rawName = $member->getRawOriginal('full_name');
                $rawSchool = $member->getRawOriginal('school_name');

                $cleanName = NameStandardizer::format($rawName);
                $cleanSchool = ! empty($rawSchool) ? NameStandardizer::formatSchool($rawSchool) : null;

                if ($rawName !== $cleanName || $rawSchool !== $cleanSchool) {
                    DB::table('registration_members')
                        ->where('id', $member->id)
                        ->update([
                            'full_name' => $cleanName,
                            'school_name' => $cleanSchool,
                        ]);
                    $updatedMembers++;
                }
            }
        });
        $this->info(" -> {$updatedMembers} data peserta berhasil distandarkan.");

        // 2. Standarisasi Registration (Official, Team Name, Institution Name)
        $this->line('2. Memproses data pendaftaran (registrations)...');
        $updatedRegistrations = 0;
        Registration::chunk(200, function ($registrations) use (&$updatedRegistrations) {
            foreach ($registrations as $reg) {
                $rawOfficial = $reg->getRawOriginal('official_name');
                $rawTeam = $reg->getRawOriginal('team_name');
                $rawInst = $reg->getRawOriginal('institution_name');

                $cleanOfficial = ! empty($rawOfficial) ? NameStandardizer::format($rawOfficial) : null;
                $cleanTeam = ! empty($rawTeam) ? NameStandardizer::format($rawTeam) : null;
                $cleanInst = ! empty($rawInst) ? NameStandardizer::formatSchool($rawInst) : null;

                if ($rawOfficial !== $cleanOfficial || $rawTeam !== $cleanTeam || $rawInst !== $cleanInst) {
                    DB::table('registrations')
                        ->where('id', $reg->id)
                        ->update([
                            'official_name' => $cleanOfficial,
                            'team_name' => $cleanTeam,
                            'institution_name' => $cleanInst,
                        ]);
                    $updatedRegistrations++;
                }
            }
        });
        $this->info(" -> {$updatedRegistrations} data registrasi berhasil distandarkan.");

        // 3. Standarisasi BadmintonMatch (Pemain 1 & 2, Sekolah Tim 1 & 2)
        $this->line('3. Memproses pertandingan bulu tangkis (badminton_matches)...');
        $updatedMatches = 0;
        BadmintonMatch::chunk(100, function ($matches) use (&$updatedMatches) {
            foreach ($matches as $match) {
                $rawT1P1 = $match->getRawOriginal('team1_player1');
                $rawT1P2 = $match->getRawOriginal('team1_player2');
                $rawT2P1 = $match->getRawOriginal('team2_player1');
                $rawT2P2 = $match->getRawOriginal('team2_player2');
                $rawT1Sch = $match->getRawOriginal('team1_school');
                $rawT2Sch = $match->getRawOriginal('team2_school');

                $cleanT1P1 = ! empty($rawT1P1) ? NameStandardizer::format($rawT1P1) : null;
                $cleanT1P2 = ! empty($rawT1P2) ? NameStandardizer::format($rawT1P2) : null;
                $cleanT2P1 = ! empty($rawT2P1) ? NameStandardizer::format($rawT2P1) : null;
                $cleanT2P2 = ! empty($rawT2P2) ? NameStandardizer::format($rawT2P2) : null;
                $cleanT1Sch = ! empty($rawT1Sch) ? NameStandardizer::formatSchool($rawT1Sch) : null;
                $cleanT2Sch = ! empty($rawT2Sch) ? NameStandardizer::formatSchool($rawT2Sch) : null;

                if (
                    $rawT1P1 !== $cleanT1P1 || $rawT1P2 !== $cleanT1P2 ||
                    $rawT2P1 !== $cleanT2P1 || $rawT2P2 !== $cleanT2P2 ||
                    $rawT1Sch !== $cleanT1Sch || $rawT2Sch !== $cleanT2Sch
                ) {
                    DB::table('badminton_matches')
                        ->where('id', $match->id)
                        ->update([
                            'team1_player1' => $cleanT1P1,
                            'team1_player2' => $cleanT1P2,
                            'team2_player1' => $cleanT2P1,
                            'team2_player2' => $cleanT2P2,
                            'team1_school' => $cleanT1Sch,
                            'team2_school' => $cleanT2Sch,
                        ]);
                    $updatedMatches++;
                }
            }
        });
        $this->info(" -> {$updatedMatches} pertandingan bulu tangkis berhasil distandarkan.");

        $this->info('=== STANDARISASI NAMA SELESAI DENGAN SUKSES! ===');

        return Command::SUCCESS;
    }
}
