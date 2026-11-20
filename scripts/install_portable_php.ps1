# Portable PHP 8.3 Auto-Downloader and Configurator for Windows
$ErrorActionPreference = "SilentlyContinue"
$root = Split-Path -Parent $PSScriptRoot
$phpDir = Join-Path $root "php"
$targetPhpExe = Join-Path $phpDir "php.exe"

if (Test-Path $targetPhpExe) {
    Write-Host "[OK] Portable PHP already present at $targetPhpExe" -ForegroundColor Green
    exit 0
}

Write-Host "Installing portable PHP 8.3 for Smart Attendance..." -ForegroundColor Cyan

# 1. First try winget if available on system
$wingetCmd = Get-Command winget.exe -ErrorAction SilentlyContinue
if ($wingetCmd) {
    Write-Host "Installing via Windows Package Manager (winget)..." -ForegroundColor Yellow
    & winget install PHP.PHP.8.3 --accept-source-agreements --accept-package-agreements --silent
    $wingetPhp = "$env:LOCALAPPDATA\Microsoft\WinGet\Packages\PHP.PHP.8.3_Microsoft.Winget.Source_8wekyb3d8bbwe\php.exe"
    if (Test-Path $wingetPhp) {
        Write-Host "[SUCCESS] PHP 8.3 installed successfully via winget!" -ForegroundColor Green
        exit 0
    }
}

# 2. Fallback: Download official portable PHP zip
if (-not (Test-Path $phpDir)) { New-Item -ItemType Directory -Path $phpDir -Force | Out-Null }
$zipFile = Join-Path $root "scratch\php-portable.zip"
$zipDir = Split-Path -Parent $zipFile
if (-not (Test-Path $zipDir)) { New-Item -ItemType Directory -Path $zipDir -Force | Out-Null }

$phpUrl = "https://windows.php.net/downloads/releases/php-8.3.14-Win32-vs16-x64.zip"
Write-Host "Downloading PHP from $phpUrl..." -ForegroundColor Cyan

try {
    [Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12
    (New-Object System.Net.WebClient).DownloadFile($phpUrl, $zipFile)
} catch {
    Write-Host "Primary URL failed. Trying secondary mirror..." -ForegroundColor Yellow
    $phpUrl = "https://github.com/shivammathur/php-builder/releases/download/8.3.14/php-8.3.14-nts-Win32-vs16-x64.zip"
    try {
        (New-Object System.Net.WebClient).DownloadFile($phpUrl, $zipFile)
    } catch {
        Write-Host "[ERROR] Could not download PHP automatically. Please install PHP 8+ manually or via XAMPP." -ForegroundColor Red
        exit 1
    }
}

if (Test-Path $zipFile) {
    Write-Host "Extracting portable PHP..." -ForegroundColor Cyan
    Expand-Archive -Path $zipFile -DestinationPath $phpDir -Force
    Remove-Item $zipFile -Force

    # Configure php.ini with required extensions
    $prodIni = Join-Path $phpDir "php.ini-production"
    $targetIni = Join-Path $phpDir "php.ini"
    if (Test-Path $prodIni) {
        Copy-Item -Path $prodIni -Destination $targetIni -Force
        $iniTxt = Get-Content $targetIni -Raw
        $iniTxt = $iniTxt -replace ';extension_dir = "ext"', 'extension_dir = "ext"'
        $iniTxt = $iniTxt -replace ';extension=curl', 'extension=curl'
        $iniTxt = $iniTxt -replace ';extension=fileinfo', 'extension=fileinfo'
        $iniTxt = $iniTxt -replace ';extension=gd', 'extension=gd'
        $iniTxt = $iniTxt -replace ';extension=mbstring', 'extension=mbstring'
        $iniTxt = $iniTxt -replace ';extension=openssl', 'extension=openssl'
        $iniTxt = $iniTxt -replace ';extension=pdo_sqlite', 'extension=pdo_sqlite'
        $iniTxt = $iniTxt -replace ';extension=sqlite3', 'extension=sqlite3'
        $iniTxt = $iniTxt -replace ';extension=pdo_mysql', 'extension=pdo_mysql'
        Set-Content -Path $targetIni -Value $iniTxt -Force
    }

    if (Test-Path $targetPhpExe) {
        Write-Host "[SUCCESS] Portable PHP 8.3 configured successfully at $targetPhpExe" -ForegroundColor Green
    }
}
