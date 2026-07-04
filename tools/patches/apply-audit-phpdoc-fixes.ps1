# Apply exported phpdoc/normalization fixes
# Run this from the plugin root: PowerShell -ExecutionPolicy Bypass -File tools/patches/apply-audit-phpdoc-fixes.ps1

$bundle = "tools/patches/audit-phpdoc-fixes"
$map = @{
    "$bundle/src-Core-Collection.php" = "src/Core/Collection.php"
    "$bundle/src-AI-AIKernel.php" = "src/AI/AIKernel.php"
    "$bundle/src-AI-Services-RAGService.php" = "src/AI/Services/RAGService.php"
    "$bundle/src-Core-BaseEntity.php" = "src/Core/BaseEntity.php"
    "$bundle/src-Assets-Services-AssetService.php" = "src/Assets/Services/AssetService.php"
    "$bundle/src-Core-BaseRepository.php" = "src/Core/BaseRepository.php"
}

foreach ($src in $map.Keys) {
    $dst = $map[$src]
    if (-Not (Test-Path $src)) {
        Write-Error "Missing exported file: $src"
        continue
    }
    Write-Host "Copying $src -> $dst"
    Copy-Item -Path $src -Destination $dst -Force
}

# Optional: create a branch and commit
if ((git rev-parse --is-inside-work-tree) -eq $null) {
    Write-Host "No git repository detected. Skipping commit."
    exit 0
}

$branch = "audit/phpdoc-fixes-apply"
Write-Host "Creating branch $branch and committing changes..."
git checkout -b $branch
git add src/Core/Collection.php src/AI/AIKernel.php src/AI/Services/RAGService.php src/Core/BaseEntity.php src/Assets/Services/AssetService.php src/Core/BaseRepository.php
git commit -m "chore(phpstan): add phpdoc value-types and normalize AI entities"

Write-Host "Done. Review changes and push with: git push -u origin $branch"
