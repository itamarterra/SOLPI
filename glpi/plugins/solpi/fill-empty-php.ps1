$root = 'c:\zabbix\glpi\plugins\solpi'
$files = Get-ChildItem -Path $root -Recurse -Filter '*.php' | Where-Object { $_.Length -eq 0 }
Write-Host "Found $($files.Count) empty PHP files."
foreach ($f in $files) {
    $rel = $f.FullName.Substring($root.Length + 1).TrimStart('\')
    $parts = $rel -split '\\'
    $name = [IO.Path]::GetFileNameWithoutExtension($f.Name)
    $content = ''

    if ($parts[0] -eq 'src') {
        $ns = 'SOLPI'
        for ($i = 1; $i -lt $parts.Length - 1; $i++) {
            $ns += '\\' + $parts[$i]
        }
        if ($name -match 'Trait$') {
            $content = "<?php`r`ndeclare(strict_types=1);`r`n`r`nnamespace $ns;`r`n`r`ntrait $name`r`n{`r`n    // Trait placeholder`r`n}`r`n"
        } elseif ($name -match 'Interface$') {
            $content = "<?php`r`ndeclare(strict_types=1);`r`n`r`nnamespace $ns;`r`n`r`ninterface $name`r`n{`r`n    public function __invoke(mixed `$input = null): mixed;`r`n}`r`n"
        } elseif ($name -match 'Exception$') {
            $content = "<?php`r`ndeclare(strict_types=1);`r`n`r`nnamespace $ns;`r`n`r`nclass $name extends \\Exception`r`n{`r`n}`r`n"
        } else {
            $content = "<?php`r`ndeclare(strict_types=1);`r`n`r`nnamespace $ns;`r`n`r`nfinal class $name`r`n{`r`n    public function __call(string `$method, array `$arguments): mixed`r`n    {`r`n        return null;`r`n    }`r`n`r`n    public function __get(string `$name): mixed`r`n    {`r`n        return null;`r`n    }`r`n}`r`n"
        }
    } elseif ($rel -like 'tools\\Auditor\\Contracts\\*') {
        $content = "<?php`r`ndeclare(strict_types=1);`r`n`r`nnamespace SOLPI\\Tools\\Auditor\\Contracts;`r`n`r`ninterface $name`r`n{`r`n    public function analyze(string `$path): array;`r`n}`r`n"
    } elseif ($rel -like 'tools\\Auditor\\Reports\\*') {
        $content = "<?php`r`ndeclare(strict_types=1);`r`n`r`nnamespace SOLPI\\Tools\\Auditor\\Reports;`r`n`r`nclass $name`r`n{`r`n    public function output(array `$data): void`r`n    {`r`n        // Report placeholder`r`n    }`r`n}`r`n"
    } elseif ($rel -like 'tools\\Auditor\\Analyzers\\*') {
        $content = "<?php`r`ndeclare(strict_types=1);`r`n`r`nnamespace SOLPI\\Tools\\Auditor\\Analyzers;`r`n`r`nuse SOLPI\\Tools\\Auditor\\Contracts\\AnalyzerInterface;`r`n`r`nclass $name implements AnalyzerInterface`r`n{`r`n    public function analyze(string `$path): array`r`n    {`r`n        return [];`r`n    }`r`n}`r`n"
    } elseif ($rel -eq 'tools\\auditor.php') {
        $content = "<?php`r`ndeclare(strict_types=1);`r`n`r`n// SOLPI auditor entry point placeholder.`r`n"
    } elseif ($rel -like 'config\\ai\\*') {
        $content = "<?php`r`n`r`nreturn [];`r`n"
    } elseif ($rel -like 'config\\*') {
        if ($name -eq 'constants') {
            $content = "<?php`r`ndeclare(strict_types=1);`r`n`r`nreturn [`r`n    'solpi_version' => '2.0.0',`r`n    'solpi_name' => 'SOLPI Professional',`r`n];`r`n"
        } else {
            $content = "<?php`r`n`r`nreturn [];`r`n"
        }
    } elseif ($rel -like 'locales\\*') {
        if ($name -eq 'en_GB') {
            $content = "<?php`r`n`r`nreturn [`r`n    'solpi' => 'SOLPI',`r`n    'welcome' => 'Welcome to SOLPI',`r`n];`r`n"
        } else {
            $content = "<?php`r`n`r`nreturn [`r`n    'solpi' => 'SOLPI',`r`n    'welcome' => 'Bem-vindo ao SOLPI',`r`n];`r`n"
        }
    } elseif ($rel -like 'ajax\\*') {
        $content = "<?php`r`ndeclare(strict_types=1);`r`n`r`ninclude __DIR__ . '/../inc/includes.php';`r`n`r`nheader('Content-Type: application/json; charset=utf-8');`r`n`r`necho json_encode([`r`n    'status' => 'ok',`r`n    'action' => '$name',`r`n]);`r`n"
    } elseif ($rel -like 'api\\*') {
        if ($name -eq 'index') {
            $content = "<?php`r`ndeclare(strict_types=1);`r`n`r`nheader('Content-Type: application/json; charset=utf-8');`r`n`r`necho json_encode([`r`n    'api' => 'solpi',`r`n    'status' => 'ok',`r`n]);`r`n"
        } elseif ($name -eq 'middleware') {
            $content = "<?php`r`ndeclare(strict_types=1);`r`n`r`nreturn function (`$request) {`r`n    return `$request;`r`n};`r`n"
        } else {
            $content = "<?php`r`n`r`nreturn [];`r`n"
        }
    } elseif ($rel -like 'front\\*') {
        $content = "<?php`r`ndeclare(strict_types=1);`r`n`r`ninclude __DIR__ . '/../inc/includes.php';`r`n`r`nSession::checkLoginUser();`r`n`r`necho '<h1>SOLPI - $name</h1>';`r`n"
        if ($name -eq 'index') {
            $content += "echo '<p>Bem-vindo ao SOLPI Professional.</p>';`r`n"
        }
    } elseif ($rel -eq 'inc\\menu.php') {
        $content = "<?php`r`ndeclare(strict_types=1);`r`n`r`nfunction plugin_menu_solpi(array `$menus): array`r`n{`r`n    return `$menus;`r`n}`r`n`r`nfunction plugin_permissions_solpi(): array`r`n{`r`n    return [];`r`n}`r`n"
    } elseif ($rel -eq 'inc\\permissions.php') {
        $content = "<?php`r`n`r`nreturn [];`r`n"
    } else {
        $content = "<?php`r`n`r`n// Placeholder for $rel`r`n"
    }

    Set-Content -Path $f.FullName -Value $content -Encoding UTF8
    Write-Host "Wrote $rel"
}

$vendor = Join-Path $root 'vendor'
if (-not (Test-Path $vendor)) { New-Item -ItemType Directory -Path $vendor | Out-Null }
$autoload = Join-Path $vendor 'autoload.php'
if (-not (Test-Path $autoload)) {
    $content = @'
<?php

declare(strict_types=1);

spl_autoload_register(static function (string $class): void {
    $prefix = 'SOLPI\\';
    if (strncmp($prefix, $class, strlen($prefix)) !== 0) {
        return;
    }
    $relativeClass = substr($class, strlen($prefix));
    $file = __DIR__ . '/../src/' . str_replace('\\', '/', $relativeClass) . '.php';
    if (is_file($file)) {
        require $file;
    }
});

return true;
'@
    Set-Content -Path $autoload -Value $content -Encoding UTF8
    Write-Host 'Created vendor/autoload.php'
} else {
    Write-Host 'vendor/autoload.php already exists'
}
'@
Set-Content -Path $scriptPath -Value $script -Encoding UTF8
powershell -ExecutionPolicy Bypass -File $scriptPath