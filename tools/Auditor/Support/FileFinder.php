<?php

declare(strict_types=1);

namespace SOLPI\Tools\Auditor\Support;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use FilesystemIterator;

final class FileFinder
{
    public function phpFiles(
        string $directory
    ): array {

        $files = [];

        $iterator = new RecursiveIteratorIterator(

            new RecursiveDirectoryIterator(

                $directory,

                FilesystemIterator::SKIP_DOTS

            )

        );

        foreach ($iterator as $file) {

            if (!$file->isFile()) {
                continue;
            }

            if (strtolower($file->getExtension()) !== 'php') {
                continue;
            }

            $files[] = $file->getPathname();

        }

        sort($files);

        return $files;

    }

    public function files(
        string $directory
    ): array {

        $files = [];

        $iterator = new RecursiveIteratorIterator(

            new RecursiveDirectoryIterator(

                $directory,

                FilesystemIterator::SKIP_DOTS

            )

        );

        foreach ($iterator as $file) {

            if ($file->isFile()) {
                $files[] = $file->getPathname();
            }

        }

        sort($files);

        return $files;

    }
}