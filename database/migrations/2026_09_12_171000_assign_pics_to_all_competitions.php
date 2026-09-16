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
        $picMap = [
            'MIPA' => 'pic.mipa@talenta.test',
            'MTQ' => 'pic.mtq@talenta.test',
            'TFID' => 'pic.mtq@talenta.test',
            'THF' => 'pic.mtq@talenta.test',
            'CTR' => 'pic.olahraga@talenta.test',
            'TMJ' => 'pic.olahraga@talenta.test',
            'BLT' => 'pic.olahraga@talenta.test',
            'POP' => 'pic.seni@talenta.test',
            'ROB' => 'pic.teknologi@talenta.test',
            'PRM' => 'pic.teknologi@talenta.test',
        ];

        foreach ($picMap as $code => $email) {
            $user = User::where('email', $email)->first();
            if (! $user) {
                continue;
            }

            $comps = Competition::withoutGlobalScope('order')->where('code', $code)->get();
            foreach ($comps as $comp) {
                if (empty($comp->pic_id)) {
                    $comp->pic_id = $user->id;
                    $comp->saveQuietly();
                }

                DB::table('competition_pics')->updateOrInsert(
                    ['competition_id' => $comp->id, 'user_id' => $user->id],
                    ['role_title' => 'Koordinator PIC', 'updated_at' => now()]
                );

                if ($code === 'BLT') {
                    foreach (['blt_pic_tunggal_pa', 'blt_pic_tunggal_pi', 'blt_pic_ganda_pa', 'blt_pic_ganda_pi'] as $key) {
                        if (empty(AppSetting::get($key))) {
                            AppSetting::set($key, (string) $user->id, 'general');
                        }
                    }
                }

                if ($code === 'TMJ') {
                    foreach (['tmj_pic_tunggal_pa', 'tmj_pic_tunggal_pi'] as $key) {
                        if (empty(AppSetting::get($key))) {
                            AppSetting::set($key, (string) $user->id, 'general');
                        }
                    }
                }
            }
        }
    }

    public function down(): void {}
};
