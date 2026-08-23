<?php

namespace App\Services;

use Illuminate\Support\Str;

class OfferName
{
    public static function matchKey(string $name, ?string $brand): string
    {
        $value = Str::ascii(mb_strtolower(trim(($brand ?? '').' '.$name)));
        $value = preg_replace('/\b(oferta|especial|promocao)\b/', '', $value);

        return trim(preg_replace('/[^a-z0-9]+/', ' ', $value));
    }
}
