<?php

declare(strict_types=1);

namespace SOLPI\Core\Logging;

use RuntimeException;

final class FileLogger
{
    private LogFormatter $formatter;

    public function __construct()
    {
        $this->formatter = new LogFormatter();
    }

    public function write(

        string $file,

        string $level,

        string $message,

        array $context = []

    ): void {

        $directory = dirname($file);

        if (!is_dir($directory)) {

            mkdir(

                $directory,

                0775,

                true

            );

        }

        $content = $this->formatter->format(

            $level,

            $message,

            $context

        );

        $result = file_put_contents(

            $file,

            $content,

            FILE_APPEND | LOCK_EX

        );

        if ($result === false) {

            throw new RuntimeException(

                'Não foi possível gravar o arquivo de log.'

            );

        }

    }
}