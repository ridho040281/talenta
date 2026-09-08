<?php

namespace App\Helpers;

class Terbilang
{
    public static function make($number): string
    {
        $number = abs((int) $number);
        if ($number === 0) {
            return 'nol';
        }

        return trim(self::convert($number));
    }

    private static function convert($number): string
    {
        $huruf = ['', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh', 'sebelas'];

        if ($number < 12) {
            return ' '.$huruf[$number];
        } elseif ($number < 20) {
            return self::convert($number - 10).' belas';
        } elseif ($number < 100) {
            return self::convert((int) ($number / 10)).' puluh'.self::convert($number % 10);
        } elseif ($number < 200) {
            return ' seratus'.self::convert($number - 100);
        } elseif ($number < 1000) {
            return self::convert((int) ($number / 100)).' ratus'.self::convert($number % 100);
        } elseif ($number < 2000) {
            return ' seribu'.self::convert($number - 1000);
        } elseif ($number < 1000000) {
            return self::convert((int) ($number / 1000)).' ribu'.self::convert($number % 1000);
        } elseif ($number < 1000000000) {
            return self::convert((int) ($number / 1000000)).' juta'.self::convert($number % 1000000);
        } elseif ($number < 1000000000000) {
            return self::convert((int) ($number / 1000000000)).' milyar'.self::convert((int) fmod($number, 1000000000));
        } elseif ($number < 1000000000000000) {
            return self::convert((int) ($number / 1000000000000)).' triliun'.self::convert((int) fmod($number, 1000000000000));
        }

        return '';
    }
}
