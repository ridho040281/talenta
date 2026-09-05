<?php

namespace App\Helpers;

class Terbilang
{
    public static function make($number)
    {
        $number = abs((int) $number);
        $huruf = ['', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh', 'sebelas'];
        $temp = '';

        if ($number < 12) {
            $temp = ' '.$huruf[$number];
        } elseif ($number < 20) {
            $temp = self::make($number - 10).' belas';
        } elseif ($number < 100) {
            $temp = self::make((int) ($number / 10)).' puluh'.self::make($number % 10);
        } elseif ($number < 200) {
            $temp = ' seratus'.self::make($number - 100);
        } elseif ($number < 1000) {
            $temp = self::make((int) ($number / 100)).' ratus'.self::make($number % 100);
        } elseif ($number < 2000) {
            $temp = ' seribu'.self::make($number - 1000);
        } elseif ($number < 1000000) {
            $temp = self::make((int) ($number / 1000)).' ribu'.self::make($number % 1000);
        } elseif ($number < 1000000000) {
            $temp = self::make((int) ($number / 1000000)).' juta'.self::make($number % 1000000);
        } elseif ($number < 1000000000000) {
            $temp = self::make((int) ($number / 1000000000)).' milyar'.self::make(fmod($number, 1000000000));
        } elseif ($number < 1000000000000000) {
            $temp = self::make((int) ($number / 1000000000000)).' trilyun'.self::make(fmod($number, 1000000000000));
        }

        return trim($temp);
    }
}
