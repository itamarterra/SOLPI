<?php

declare(strict_types=1);

namespace SOLPI\Core;

final class Plugin
{
    public const NAME = 'SOLPI';

    public const VERSION = '1.0.0';

    public const AUTHOR = 'Itamar Terra';

    public const LICENSE = 'GPL v3';

    public const MINIMUM_GLPI = '11.0';

    public const MINIMUM_PHP = '8.2';

    public static function information(): array
    {
        return [

            'name'=>self::NAME,

            'version'=>self::VERSION,

            'author'=>self::AUTHOR,

            'license'=>self::LICENSE,

            'min_glpi'=>self::MINIMUM_GLPI,

            'min_php'=>self::MINIMUM_PHP

        ];
    }
}
