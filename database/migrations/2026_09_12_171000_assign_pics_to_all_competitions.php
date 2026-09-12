<?php

use App\Models\AppSetting;
use App\Models\Competition;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        \ = [
            'MIPA' => 'pic.mipa@talenta.test',
            'MTQ'  => 'pic.mtq@talenta.test',
            'TFID' => 'pic.mtq@talenta.test',
            'THF'  => 'pic.mtq@talenta.test',
            'CTR'  => 'pic.olahraga@talenta.test',
            'TMJ'  => 'pic.olahraga@talenta.test',
            'BLT'  => 'pic.olahraga@talenta.test',
            'POP'  => 'pic.seni@talenta.test',
            'ROB'  => 'pic.teknologi@talenta.test',
            'PRM'  => 'pic.teknologi@talenta.test',
        ];

        foreach (\ as \ => \) {
            \ = User::where('email', \)->first();
            if (! \) {
                continue;
            }

            \ = Competition::withoutGlobalScope('order')->where('code', \)->get();
            foreach (\ as \) {
                if (empty(\->pic_id)) {
                    \->pic_id = \->id;
                    \->saveQuietly();
                }

                DB::table('competition_pics')->updateOrInsert(
                    ['competition_id' => \->id, 'user_id' => \->id],
                    ['role_title' => 'Koordinator PIC', 'updated_at' => now()]
                );

                if (\ === 'BLT') {
                    foreach (['blt_pic_tunggal_pa', 'blt_pic_tunggal_pi', 'blt_pic_ganda_pa', 'blt_pic_ganda_pi'] as \) {
                        if (empty(AppSetting::get(\))) {
                            AppSetting::set(\, (string) \->id, 'general');
                        }
                    }
                }

                if (\ === 'TMJ') {
                    foreach (['tmj_pic_tunggal_pa', 'tmj_pic_tunggal_pi'] as \) {
                        if (empty(AppSetting::get(\))) {
                            AppSetting::set(\, (string) \->id, 'general');
                        }
                    }
                }
            }
        }
    }

    public function down(): void
    {
    }
};
