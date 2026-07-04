<?php
$zip = new ZipArchive();
$zipFile = __DIR__ . DIRECTORY_SEPARATOR . 'solpi-audit-phpdoc-fixes-2026-07-01.zip';
if ($zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
    echo "ERR_OPEN\n";
    exit(1);
}
$base = realpath(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..');
$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($base));
foreach ($it as $file) {
    if ($file->isDir()) continue;
    $filePath = $file->getRealPath();
    // skip .git if present
    if (strpos($filePath, DIRECTORY_SEPARATOR . '.git' . DIRECTORY_SEPARATOR) !== false) continue;
    $localPath = substr($filePath, strlen($base) + 1);
    $zip->addFile($filePath, $localPath);
}
$zip->close();
echo $zipFile . PHP_EOL;
