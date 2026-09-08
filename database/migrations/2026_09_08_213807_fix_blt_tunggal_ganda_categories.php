<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\Registration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Fix any BLT registrations that are actually Tunggal but have 'Ganda (Semua Kelas)' in target_class or sub_category
        $bltRegistrations = Registration::whereHas('competition', function ($q) {
            $q->where('code', 'BLT');
        })->with('members')->get();

        foreach ($bltRegistrations as $reg) {
            $memberCount = $reg->members->count();
            $matchType = $reg->match_type ?? '';
            $isExplicitTunggal = stripos($matchType, 'tunggal') !== false;
            $isExplicitGanda = stripos($matchType, 'ganda') !== false;

            // If it's a single participant or explicitly Tunggal
            if ($memberCount <= 1 || $isExplicitTunggal) {
                if ($reg->target_class === 'Ganda (Semua Kelas)' || stripos($reg->sub_category ?? '', 'ganda') !== false) {
                    $cleanTargetClass = 'Kategori A (Kelas 1 - 2)';

                    // Check if sub_category or notes mentioned Kat B or Kat C
                    $blob = strtolower(($reg->sub_category ?? '').' '.($reg->verification_notes ?? ''));
                    if (str_contains($blob, 'kat b') || str_contains($blob, 'kategori b') || str_contains($blob, 'kelas 3') || str_contains($blob, 'kelas 4')) {
                        $cleanTargetClass = 'Kategori B (Kelas 3 - 4)';
                    } elseif (str_contains($blob, 'kat c') || str_contains($blob, 'kategori c') || str_contains($blob, 'kelas 5') || str_contains($blob, 'kelas 6')) {
                        $cleanTargetClass = 'Kategori C (Kelas 5 - 6)';
                    }

                    $cleanMatchType = $matchType;
                    if (empty($cleanMatchType) || stripos($cleanMatchType, 'ganda') !== false) {
                        $cleanMatchType = ($reg->primary_gender === 'P') ? 'Tunggal Putri (PI)' : 'Tunggal Putra (PA)';
                    }

                    $reg->target_class = $cleanTargetClass;
                    $reg->match_type = $cleanMatchType;
                    $reg->sub_category = $cleanTargetClass.' - '.$cleanMatchType;
                    $reg->team_name = null;
                    $reg->saveQuietly();
                }
            } elseif ($memberCount > 1 || $isExplicitGanda) {
                // Ensure Ganda format is clean
                $cleanMatchType = $matchType;
                if (empty($cleanMatchType) || stripos($cleanMatchType, 'tunggal') !== false) {
                    $cleanMatchType = ($reg->primary_gender === 'P') ? 'Ganda Putri (PI)' : 'Ganda Putra (PA)';
                }
                $reg->target_class = 'Ganda (Semua Kelas)';
                $reg->match_type = $cleanMatchType;
                $reg->sub_category = $cleanMatchType;
                $reg->saveQuietly();
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No reverse needed for data cleanup
    }
};
