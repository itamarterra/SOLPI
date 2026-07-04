<?php

declare(strict_types=1);

namespace SOLPI\Core;

final class Str
{
    public static function upper(
        string $text
    ): string {

        return mb_strtoupper($text);

    }

    public static function lower(
        string $text
    ): string {

        return mb_strtolower($text);

    }

    public static function contains(
        string $text,
        string $search
    ): bool {

        return str_contains(
            self::upper($text),
            self::upper($search)
        );

    }

    public static function slug(
        string $text
    ): string {

        $text = strtolower($text);

        $text = preg_replace('/[^a-z0-9]+/','-',$text);

        return trim($text,'-');

    }

    public static function random(
        int $length=16
    ): string {

        return substr(

            md5(

                uniqid(

                    '',

                    true

                )

            ),

            0,

            $length

        );

    }
}
