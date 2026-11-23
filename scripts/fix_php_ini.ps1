# Automated PHP.ini Driver & Extension Configurator
$ErrorActionPreference = "SilentlyContinue"
$root = Split-Path -Parent $PSScriptRoot

Write-Host "Configuring PHP extensions for Smart Attendance..." -ForegroundColor Cyan

# Find PHP executable and its directory
$phpBin = ""
$phpCandidates = @(
    (Join-Path $root "php\php.exe"),
    "$env:LOCALAPPDATA\Microsoft\WinGet\Packages\PHP.PHP.8.3_Microsoft.Winget.Source_8wekyb3d8bbwe\php.exe",
    "C:\Program Files\PHP\php.exe",
    "C:\php\php.exe",
    "C:\xampp\php\php.exe"
)
foreach ($cand in $phpCandidates) {
    if (Test-Path $cand) { $phpBin = $cand; break }
}
if (-not $phpBin) {
    $cmd = Get-Command php.exe -ErrorAction SilentlyContinue
    if ($cmd) { $phpBin = $cmd.Source }
}

if (-not $phpBin -or -not (Test-Path $phpBin)) {
    Write-Host "[ERROR] Could not find php.exe. Please run Setup-New-Server.bat first." -ForegroundColor Red
    exit 1
}

$phpDir = Split-Path -Parent $phpBin
Write-Host "PHP Directory: $phpDir" -ForegroundColor Yellow

$iniFile = Join-Path $phpDir "php.ini"
$iniProd = Join-Path $phpDir "php.ini-production"
$iniDev = Join-Path $phpDir "php.ini-development"

# If php.ini does not exist, copy from production or development template
if (-not (Test-Path $iniFile)) {
    if (Test-Path $iniProd) {
        Copy-Item -Path $iniProd -Destination $iniFile -Force
        Write-Host "Created php.ini from php.ini-production" -ForegroundColor Green
    } elseif (Test-Path $iniDev) {
        Copy-Item -Path $iniDev -Destination $iniFile -Force
        Write-Host "Created php.ini from php.ini-development" -ForegroundColor Green
    } else {
        New-Item -ItemType File -Path $iniFile -Force | Out-Null
    }
}

$content = ""
if (Test-Path $iniFile) {
    $content = Get-Content $iniFile -Raw -ErrorAction SilentlyContinue
}

# Ensure extension_dir is active
$content = $content -replace ';extension_dir = "ext"', 'extension_dir = "ext"'
$content = $content -replace ';extension_dir = "./"', 'extension_dir = "ext"'

# Required extensions list
$exts = @(
    "pdo_sqlite",
    "sqlite3",
    "pdo_mysql",
    "curl",
    "mbstring",
    "fileinfo",
    "gd",
    "openssl"
)

foreach ($ext in $exts) {
    if ($content -match ";extension=$ext") {
        $content = $content -replace ";extension=$ext", "extension=$ext"
        Write-Host "[OK] Enabled extension: $ext" -ForegroundColor Green
    } elseif ($content -notmatch "extension=$ext") {
        $content += "`r`nextension=$ext"
        Write-Host "[OK] Added extension: $ext" -ForegroundColor Green
    } else {
        Write-Host "[OK] Already enabled: $ext" -ForegroundColor Gray
    }
}

# Also ensure display_errors and memory limit
if ($content -match ";display_errors = Off") {
    $content = $content -replace ";display_errors = Off", "display_errors = On"
}

Set-Content -Path $iniFile -Value $content -Force
Write-Host "`n[SUCCESS] php.ini updated successfully at $iniFile!" -ForegroundColor Green

# Verify with php -m
$modules = & $phpBin -m
if ($modules -contains "pdo_sqlite") {
    Write-Host "[VERIFIED] pdo_sqlite driver is now active and ready!" -ForegroundColor Green
} else {
    Write-Host "[NOTICE] Dynamic flag fallback will be used when launching server." -ForegroundColor Yellow
}
