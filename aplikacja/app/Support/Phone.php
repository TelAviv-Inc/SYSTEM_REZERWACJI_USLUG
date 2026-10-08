<?php

namespace App\Support;

class Phone
{
    /**
     *Strip spaces and dashes from a phone number." would be accurate. There's also an empty line at the start of the method body
     */
    public static function normalize(?string $phone): ?string
    {

        return $phone === null ? null : preg_replace('/[\s-]/', '', $phone);
    }
}
