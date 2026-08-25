<?php

namespace App\Http\Helpers;

class PhoneHelper
{

    public static function normalize(string $phone): string
    {
        $phone = preg_replace('/\D/', '', $phone);
        if (str_starts_with($phone, '07')) {
            $phone = '254' . substr($phone, 1);
        } elseif (str_starts_with($phone, '0')) {
            $phone = '254' . substr($phone, 1);
        }
        return $phone;
    }
}
