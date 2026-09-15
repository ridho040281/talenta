<?php

namespace App\Traits;

use App\Models\Competition;

trait CompetitionPoolTrait
{
    public function buildCompetitionPools(Competition $competition): array
    {
        $compCode = strtoupper($competition->code ?? '');
        $isBuluTangkis = ($compCode === 'BLT' || stripos($competition->name, 'bulu tangkis') !== false || stripos($competition->name, 'badminton') !== false);
        $isTenisMeja = ($compCode === 'TMJ' || stripos($competition->name, 'tenis meja') !== false || stripos($competition->name, 'pingpong') !== false);

        $registrations = $competition->registrations;
        $classified = [];

        if ($isBuluTangkis) {
            $poolDefs = [
                'kat_a_pa' => ['name' => 'Kategori A (Kelas 1–2) - Tunggal Putra (PA)', 'short' => '👦 Kat A (1-2) Putra', 'gender' => 'L', 'type' => 'kat_a', 'ganda' => false],
                'kat_a_pi' => ['name' => 'Kategori A (Kelas 1–2) - Tunggal Putri (PI)', 'short' => '👧 Kat A (1-2) Putri', 'gender' => 'P', 'type' => 'kat_a', 'ganda' => false],
                'kat_b_pa' => ['name' => 'Kategori B (Kelas 3–4) - Tunggal Putra (PA)', 'short' => '👦 Kat B (3-4) Putra', 'gender' => 'L', 'type' => 'kat_b', 'ganda' => false],
                'kat_b_pi' => ['name' => 'Kategori B (Kelas 3–4) - Tunggal Putri (PI)', 'short' => '👧 Kat B (3-4) Putri', 'gender' => 'P', 'type' => 'kat_b', 'ganda' => false],
                'kat_c_pa' => ['name' => 'Kategori C (Kelas 5–6) - Tunggal Putra (PA)', 'short' => '👦 Kat C (5-6) Putra', 'gender' => 'L', 'type' => 'kat_c', 'ganda' => false],
                'kat_c_pi' => ['name' => 'Kategori C (Kelas 5–6) - Tunggal Putri (PI)', 'short' => '👧 Kat C (5-6) Putri', 'gender' => 'P', 'type' => 'kat_c', 'ganda' => false],
                'ganda_pa' => ['name' => 'Ganda Putra (PA) - Semua Kelas', 'short' => '👥 Ganda Putra', 'gender' => 'L', 'type' => 'ganda', 'ganda' => true],
                'ganda_pi' => ['name' => 'Ganda Putri (PI) - Semua Kelas', 'short' => '👥 Ganda Putri', 'gender' => 'P', 'type' => 'ganda', 'ganda' => true],
                'ganda_mix' => ['name' => 'Ganda Campuran - Semua Kelas', 'short' => '👥 Ganda Campuran', 'gender' => 'M', 'type' => 'ganda', 'ganda' => true],
            ];

            foreach ($poolDefs as $key => $def) {
                $poolRegs = $registrations->filter(function ($reg) use ($def) {
                    $isGanda = $reg->isGanda() || stripos($reg->target_class ?? '', 'ganda') !== false;
                    if ($def['ganda']) {
                        if (! $isGanda) {
                            return false;
                        }
                    } else {
                        if ($isGanda) {
                            return false;
                        }
                        if ($def['type'] === 'kat_a' && ! $reg->isKatA()) {
                            return false;
                        }
                        if ($def['type'] === 'kat_b' && ! $reg->isKatB()) {
                            return false;
                        }
                        if ($def['type'] === 'kat_c' && ! $reg->isKatC()) {
                            return false;
                        }
                    }

                    $gen = $reg->primary_gender;
                    if ($def['gender'] === 'M') {
                        return $gen === 'M' || stripos($reg->match_type ?? '', 'campuran') !== false;
                    } elseif ($def['gender'] === 'P') {
                        return $gen === 'P' || stripos($reg->match_type ?? '', 'putri') !== false || stripos($reg->match_type ?? '', 'pi') !== false;
                    } else {
                        return $gen === 'L' || stripos($reg->match_type ?? '', 'putra') !== false || stripos($reg->match_type ?? '', 'pa') !== false || ($gen !== 'P' && $gen !== 'M' && stripos($reg->match_type ?? '', 'putri') === false && stripos($reg->match_type ?? '', 'campuran') === false);
                    }
                });

                if ($poolRegs->isNotEmpty()) {
                    $classified[$key] = [
                        'key' => $key,
                        'title' => $def['name'],
                        'short_title' => $def['short'],
                        'participants' => $this->formatParticipantList($poolRegs),
                    ];
                }
            }
        } elseif ($isTenisMeja) {
            $poolDefs = [
                'kat_a_pa' => ['name' => 'Kategori A (Kelas 1–3) - Tunggal Putra (PA)', 'short' => '👦 Kat A (1-3) Putra', 'gender' => 'L', 'type' => 'kat_a', 'ganda' => false],
                'kat_a_pi' => ['name' => 'Kategori A (Kelas 1–3) - Tunggal Putri (PI)', 'short' => '👧 Kat A (1-3) Putri', 'gender' => 'P', 'type' => 'kat_a', 'ganda' => false],
                'kat_b_pa' => ['name' => 'Kategori B (Kelas 4–6) - Tunggal Putra (PA)', 'short' => '👦 Kat B (4-6) Putra', 'gender' => 'L', 'type' => 'kat_b', 'ganda' => false],
                'kat_b_pi' => ['name' => 'Kategori B (Kelas 4–6) - Tunggal Putri (PI)', 'short' => '👧 Kat B (4-6) Putri', 'gender' => 'P', 'type' => 'kat_b', 'ganda' => false],
            ];

            foreach ($poolDefs as $key => $def) {
                $poolRegs = $registrations->filter(function ($reg) use ($def) {
                    if ($def['type'] === 'kat_a' && ! $reg->isKatA()) {
                        return false;
                    }
                    if ($def['type'] === 'kat_b' && ! $reg->isKatB()) {
                        return false;
                    }

                    $gen = $reg->primary_gender;
                    if ($def['gender'] === 'P') {
                        return $gen === 'P' || stripos($reg->match_type ?? '', 'Putri') !== false || stripos($reg->match_type ?? '', 'PI') !== false;
                    } else {
                        return $gen === 'L' || stripos($reg->match_type ?? '', 'Putra') !== false || stripos($reg->match_type ?? '', 'PA') !== false || ($gen !== 'P' && stripos($reg->match_type ?? '', 'Putri') === false && stripos($reg->match_type ?? '', 'PI') === false);
                    }
                });

                if ($poolRegs->isNotEmpty()) {
                    $classified[$key] = [
                        'key' => $key,
                        'title' => $def['name'],
                        'short_title' => $def['short'],
                        'participants' => $this->formatParticipantList($poolRegs),
                    ];
                }
            }
        }

        // Check for any unmatched registrations (fallback safe guarantee)
        $matchedIds = collect($classified)->pluck('participants')->flatten(1)->pluck('id')->toArray();
        $unmatched = $registrations->whereNotIn('id', $matchedIds);

        if ($unmatched->isNotEmpty()) {
            if (empty($classified)) {
                $paRegs = $unmatched->filter(fn ($r) => $r->primary_gender === 'L' || stripos($r->match_type ?? '', 'Putra') !== false);
                $piRegs = $unmatched->filter(fn ($r) => $r->primary_gender === 'P' || stripos($r->match_type ?? '', 'Putri') !== false);

                if ($paRegs->isNotEmpty() && $piRegs->isNotEmpty()) {
                    $classified['pa'] = [
                        'key' => 'pa',
                        'title' => 'Kelompok Putra (PA)',
                        'short_title' => '👦 Putra (PA)',
                        'participants' => $this->formatParticipantList($paRegs),
                    ];
                    $classified['pi'] = [
                        'key' => 'pi',
                        'title' => 'Kelompok Putri (PI)',
                        'short_title' => '👧 Putri (PI)',
                        'participants' => $this->formatParticipantList($piRegs),
                    ];
                } else {
                    $classified['all'] = [
                        'key' => 'all',
                        'title' => 'Semua Peserta',
                        'short_title' => '👥 Semua Peserta',
                        'participants' => $this->formatParticipantList($unmatched),
                    ];
                }
            } else {
                $classified['other'] = [
                    'key' => 'other',
                    'title' => 'Peserta Lainnya',
                    'short_title' => '👥 Lainnya ('.$unmatched->count().')',
                    'participants' => $this->formatParticipantList($unmatched),
                ];
            }
        }

        return array_values($classified);
    }

    public function formatParticipantList($regs): array
    {
        return $regs->map(function ($reg) {
            $firstMember = $reg->members->first();
            $pureName = $reg->team_name ?: ($firstMember?->full_name ?: 'Peserta #'.$reg->id);

            return [
                'id' => $reg->id,
                'name' => $pureName,
                'institution' => $reg->institution_name,
                'participant_number' => $reg->participant_number,
                'registration_code' => $reg->registration_code,
                'target_class' => $reg->target_class,
                'match_type' => $reg->match_type,
                'gender' => $reg->primary_gender,
                'draw_number' => $reg->draw_number,
                'is_drawn' => ! is_null($reg->draw_number),
                'seed_number' => $reg->seed_number,
                'is_seeded' => $reg->isSeeded(),
                'seed_label' => $reg->seed_label,
            ];
        })->values()->toArray();
    }
}
