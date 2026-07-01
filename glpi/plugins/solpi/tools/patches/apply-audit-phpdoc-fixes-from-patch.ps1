# Apply audit-phpdoc-fixes.patch format produced by this tool.
# Usage: PowerShell -ExecutionPolicy Bypass -File tools/patches/apply-audit-phpdoc-fixes-from-patch.ps1

$patch = 'tools/patches/audit-phpdoc-fixes.patch'
if (-Not (Test-Path $patch)) { Write-Error "Patch file not found: $patch"; exit 1 }

$content = Get-Content $patch -Raw -Encoding UTF8
$sections = $content -split "(?m)^--- PATH: " | Where-Object { $_ -ne '' }

foreach ($sec in $sections) {
    $firstLine = ($sec -split "\r?\n")[0]
    $path = $firstLine.Trim()
    $body = ($sec -split "(?s)\+\+\+ BEGIN\r?\n")[1] -split "\r?\n\+\+\+ END\r?\n"[0]
    if (-not $body) {
        Write-Error "Failed to parse section for $path"
        continue
    }
    $dst = Join-Path (Get-Location) $path
    $dir = Split-Path $dst -Parent
    if (-not (Test-Path $dir)) { New-Item -ItemType Directory -Path $dir -Force | Out-Null }
    Write-Host "Writing $dst"
    $body | Out-File -FilePath $dst -Encoding UTF8
}

# Optional git commit if repo present
try {
    $isGit = git rev-parse --is-inside-work-tree 2>$null
} catch {
    $isGit = $null
}

if ($isGit) {
    $branch = 'audit/phpdoc-fixes-apply'
    Write-Host "Git repo detected — creating branch $branch and committing changes"
    git checkout -b $branch
    git add src/Core/Collection.php src/AI/AIKernel.php src/AI/Services/RAGService.php src/Core/BaseEntity.php src/Assets/Services/AssetService.php src/Core/BaseRepository.php
    git commit -m "chore(phpstan): add phpdoc value-types and normalize AI entities"
    Write-Host "Committed. Push with: git push -u origin $branch"
} else {
    Write-Host "No git repository detected — files written only."
}
