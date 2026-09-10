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
        // 1. Clean team_name for Tenis Meja (TMJ) where team_name holds sector/category text
        $tmjRegistrations = Registration::whereHas('competition', function ($q) {
            $q->where('code', 'TMJ');
        })->get();

        foreach ($tmjRegistrations as $reg) {
            $teamName = $reg->team_name;
            if (! empty($teamName)) {
                if (empty($reg->target_class)) {
                    if (stripos($teamName, '4-6') !== false || stripos($teamName, '4 - 6') !== false || stripos($teamName, 'Kat B') !== false) {
                        $reg->target_class = 'Kategori B (Kelas 4 - 6)';
                    } else {
                        $reg->target_class = 'Kategori A (Kelas 1 - 3)';
                    }
                }
                if (empty($reg->match_type)) {
                    if (stripos($teamName, 'PI') !== false || stripos($teamName, 'Putri') !== false) {
                        $reg->match_type = 'Tunggal Putri (PI)';
                    } else {
                        $reg->match_type = 'Tunggal Putra (PA)';
                    }
                }
                $reg->sub_category = $reg->target_class . ' - ' . $reg->match_type;
                $reg->team_name = null;
                $reg->saveQuietly();
            }
        }

        // 2. Also clean any individual registrations where team_name was accidentally filled with 'tunggal'
        Registration::where(function ($q) {
            $q->where('team_name', 'like', '%tunggal%')
              ->orWhere('team_name', 'like', '%Tunggal%');
        })->update(['team_name' => null]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Data cleanup
    }
};
