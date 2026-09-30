param(
    [Parameter(Mandatory = $true)]
    [string]$WpPath,

    [Parameter(Mandatory = $true)]
    [string]$PreviousZip,

    [string]$CandidateZip = "",

    [switch]$BootstrapComposerDependencies
)

Set-StrictMode -Version Latest
$ErrorActionPreference = "Stop"

$ScriptDir = Split-Path -Parent $MyInvocation.MyCommand.Path
$Root = (Resolve-Path (Join-Path $ScriptDir "..\..\..")).Path
$Dist = Join-Path $Root "dist"
$Evidence = Join-Path $Root "evidence"

$ExpectedCandidateSha = "985091a289f11c0ae449e6f93e2f4090ddd3790762df42cff4a8a97fc775c231"
$ExpectedCandidateName = "base-conhecimento-inteligencia-integrada-0.6.0-dev-p650.3.zip"

if ([string]::IsNullOrWhiteSpace($CandidateZip)) {
    $CandidateZip = Join-Path $Dist $ExpectedCandidateName
}

function Require-Command {
    param([string]$Name)
    $cmd = Get-Command $Name -ErrorAction SilentlyContinue
    if (-not $cmd) {
        throw "BLOCKED_LOCAL_TOOLING: command '$Name' not found in PATH."
    }
    return $cmd.Source
}

function Invoke-Gate {
    param(
        [string]$Name,
        [string[]]$Command
    )

    Write-Host ""
    Write-Host "=== $Name ==="
    & $Command[0] $Command[1..($Command.Length - 1)]
    $code = $LASTEXITCODE
    if ($code -ne 0) {
        throw "$Name failed with exit code $code."
    }
}

$git = Require-Command "git"
$php = Require-Command "php"
$python = Require-Command "python"
$wp = Require-Command "wp"

Push-Location $Root
try {
    $inside = & $git rev-parse --is-inside-work-tree 2>$null
    if ($LASTEXITCODE -ne 0 -or $inside.Trim() -ne "true") {
        throw "Run this script from a real Git checkout."
    }

    $pluginTree = (& $git rev-parse "HEAD:plugin/base-conhecimento-inteligencia-integrada").Trim()
    if ($LASTEXITCODE -ne 0 -or [string]::IsNullOrWhiteSpace($pluginTree)) {
        throw "Unable to resolve plugin_tree_sha."
    }

    $phpcs = Join-Path $Root "vendor\bin\phpcs.bat"
    $phpunit = Join-Path $Root "vendor\bin\phpunit.bat"
    if (-not (Test-Path $phpcs)) {
        $phpcs = Join-Path $Root "vendor\bin\phpcs"
    }
    if (-not (Test-Path $phpunit)) {
        $phpunit = Join-Path $Root "vendor\bin\phpunit"
    }

    if (-not (Test-Path $phpcs) -or -not (Test-Path $phpunit)) {
        if (-not $BootstrapComposerDependencies) {
            throw "BLOCKED_LOCAL_TOOLING: vendor/bin/phpcs or vendor/bin/phpunit missing. Run Composer explicitly or rerun with -BootstrapComposerDependencies."
        }

        $composer = Require-Command "composer"
        if (-not (Test-Path (Join-Path $Root "composer.lock"))) {
            Write-Warning "composer.lock is not versioned. Composer will resolve dependencies now; preserve/review the generated lock before treating tooling as reproducible."
        }

        Write-Host ""
        Write-Host "=== Composer dependency bootstrap ==="
        & $composer install --no-interaction --prefer-dist --no-progress
        if ($LASTEXITCODE -ne 0) {
            throw "Composer bootstrap failed."
        }
    }

    if (-not (Test-Path $CandidateZip)) {
        throw "Candidate ZIP not found: $CandidateZip"
    }

    $candidateHash = (Get-FileHash -Algorithm SHA256 $CandidateZip).Hash.ToLowerInvariant()
    if ($candidateHash -ne $ExpectedCandidateSha) {
        throw "FAIL_ARTIFACT_MISMATCH: expected $ExpectedCandidateSha, got $candidateHash."
    }

    if (-not (Test-Path $PreviousZip)) {
        throw "Previous ZIP not found: $PreviousZip"
    }

    Write-Host ""
    Write-Host "Plugin tree SHA: $pluginTree"
    Write-Host "Frozen candidate SHA-256: $candidateHash"

    Invoke-Gate "P640 local validation / p640.2" @(
        $python,
        (Join-Path $Root "tools\homologation\spec006\validate-p640-local.py")
    )

    Invoke-Gate "P650 local package quality / p650.3" @(
        $python,
        (Join-Path $Root "tools\homologation\spec006\validate-p650-local.py")
    )

    Invoke-Gate "P660 local security/privacy quality" @(
        $python,
        (Join-Path $Root "tools\homologation\spec006\validate-p660-local.py")
    )

    $pluginCheckScript = Join-Path $Root "tools\homologation\spec006\run-p650-p660-plugin-check-local.py"
    Write-Host ""
    Write-Host "=== P650/P660 official Plugin Check ==="
    & $python $pluginCheckScript --wp-path $WpPath --zip $CandidateZip
    $pluginCheckExit = $LASTEXITCODE

    $rollbackScript = Join-Path $Root "tools\homologation\spec006\run-p650-rollback-local.py"
    $rollbackOutput = Join-Path $Evidence "spec006-p650-rollback-current.json"
    Write-Host ""
    Write-Host "=== P650 rollback acceptance ==="
    & $python $rollbackScript --wp-path $WpPath --previous-zip $PreviousZip --candidate-zip $CandidateZip --output $rollbackOutput
    $rollbackExit = $LASTEXITCODE

    $summary = [ordered]@{
        schema_version = "1.0.0"
        gate = "SPEC-006-FINAL-LOCAL-GATES"
        execution_mode = "LOCAL_ONLY"
        source_commit = (& $git rev-parse HEAD).Trim()
        plugin_tree_sha = $pluginTree
        candidate = [ordered]@{
            name = (Split-Path -Leaf $CandidateZip)
            sha256 = $candidateHash
        }
        p640 = [ordered]@{
            evidence = "evidence/spec006-p640-local-validation-current.json"
            expected_build = "p640.2"
            completed = $true
        }
        p650_local = [ordered]@{
            evidence = "evidence/spec006-p650-local-package-validation-current.json"
            completed = $true
        }
        p660_local = [ordered]@{
            evidence = "evidence/spec006-p660-local-validation-current.json"
            completed = $true
        }
        plugin_check = [ordered]@{
            exit_code = $pluginCheckExit
            evidence = "evidence/spec006-p650-p660-plugin-check-current.json"
        }
        rollback = [ordered]@{
            exit_code = $rollbackExit
            evidence = "evidence/spec006-p650-rollback-current.json"
        }
        github_actions_used = $false
        cutover_authorized = $false
        retirement_authorized = $false
    }

    $summaryPath = Join-Path $Evidence "spec006-final-local-gates-summary-current.json"
    $summary | ConvertTo-Json -Depth 8 | Set-Content -Encoding UTF8 $summaryPath

    Write-Host ""
    Write-Host "=== FINAL LOCAL GATE SUMMARY ==="
    Write-Host "P640: PASS"
    Write-Host "P650 local quality: PASS"
    Write-Host "P660 local security/privacy: PASS"
    Write-Host "Plugin Check exit: $pluginCheckExit"
    Write-Host "Rollback exit: $rollbackExit"
    Write-Host "Summary: $summaryPath"

    if ($pluginCheckExit -ne 0 -or $rollbackExit -ne 0) {
        exit 1
    }

    exit 0
}
finally {
    Pop-Location
}
