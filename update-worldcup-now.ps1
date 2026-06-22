$ErrorActionPreference = 'Stop'
$SiteDir = Split-Path -Parent $MyInvocation.MyCommand.Path
$NodeExe = (Get-Command node.exe -ErrorAction SilentlyContinue | Select-Object -First 1 -ExpandProperty Source)

if (-not $NodeExe -and (Test-Path 'C:\Program Files\nodejs\node.exe')) {
    $NodeExe = 'C:\Program Files\nodejs\node.exe'
}
if (-not $NodeExe) {
    throw 'Node.js was not found. Install Node.js or add node.exe to PATH.'
}

Write-Host 'Updating World Cup schedule and scores from ESPN...'
& $NodeExe (Join-Path $SiteDir 'update_scores_espn.js')
if ($LASTEXITCODE -ne 0) { throw 'Update failed. Check data\score-update-espn.log.' }
Write-Host 'Update completed. Refresh the website with Ctrl+F5.' -ForegroundColor Green
