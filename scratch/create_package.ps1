$dest = "C:\Users\rampr\Downloads\SmartAttendance_Transfer_Package.zip"
if (Test-Path $dest) { Remove-Item $dest -Force }

$items = @(
    "app",
    "config",
    "database",
    "public",
    "scripts",
    "views",
    "index.php",
    ".env",
    ".env.example",
    "Start-SmartAttendance.bat",
    "start_production.bat",
    "Start-LiveSync-To-PC.bat",
    "Open-Photos-Folder.bat",
    "README.md"
)

Compress-Archive -Path $items -DestinationPath $dest -CompressionLevel Optimal

if (Test-Path $dest) {
    $bytes = (Get-Item $dest).Length
    $mb = [math]::Round($bytes / 1MB, 2)
    Write-Host "SUCCESS: Package created at $dest ($mb MB)" -ForegroundColor Green
} else {
    Write-Host "ERROR: Package could not be created" -ForegroundColor Red
}
