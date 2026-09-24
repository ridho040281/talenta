<?php

namespace App\Helpers;

class NameStandardizer
{
    /**
     * Standardize a person's name into clean Title Case (EYD / PUEBI).
     */
    public static function format(?string $name): string
    {
        if ($name === null || trim($name) === '') {
            return '';
        }

        // Collapse multiple whitespace/tabs
        $clean = preg_replace('/\s+/', ' ', trim($name));

        // Convert to Title Case
        $title = mb_convert_case(mb_strtolower($clean, 'UTF-8'), MB_CASE_TITLE, 'UTF-8');

        // Fix apostrophes: Syafi'I -> Syafi'i, Ma'Ruf -> Ma'ruf, Sa'Diyah -> Sa'diyah
        $title = preg_replace_callback('/(?<=[a-zA-Z])\'([A-Za-z])/u', function ($m) {
            return "'".mb_strtolower($m[1], 'UTF-8');
        }, $title);

        return $title;
    }

    /**
     * Standardize a school / madrasah / institution name with proper acronyms.
     */
    public static function formatSchool(?string $school): string
    {
        if ($school === null || trim($school) === '') {
            return '';
        }

        // Collapse multiple whitespace
        $clean = preg_replace('/\s+/', ' ', trim($school));

        // Convert to Title Case
        $title = mb_convert_case(mb_strtolower($clean, 'UTF-8'), MB_CASE_TITLE, 'UTF-8');

        // Restore standard Indonesian school acronyms
        $replacements = [
            '/\bMtsn\b/i' => 'MTsN',
            '/\bMts\b/i' => 'MTs',
            '/\bMis\b/i' => 'MIS',
            '/\bMi\b/i' => 'MI',
            '/\bSdn\b/i' => 'SDN',
            '/\bSd\b/i' => 'SD',
            '/\bSmpn\b/i' => 'SMPN',
            '/\bSmp\b/i' => 'SMP',
            '/\bSman\b/i' => 'SMAN',
            '/\bSma\b/i' => 'SMA',
            '/\bSmkn\b/i' => 'SMKN',
            '/\bSmk\b/i' => 'SMK',
            '/\bMan\b/i' => 'MAN',
            '/\bMa\b/i' => 'MA',
        ];

        foreach ($replacements as $pattern => $rep) {
            $title = preg_replace($pattern, $rep, $title);
        }

        return $title;
    }
}
