param(
  [string]$SiteDir = "C:\PHPWeb\worldcup",
  [string]$NodeExe = ""
)

$ErrorActionPreference = 'Stop'

if ([string]::IsNullOrWhiteSpace($NodeExe)) {
  $NodeExe = (Get-Command node.exe -ErrorAction SilentlyContinue | Select-Object -First 1 -ExpandProperty Source)
}
if (-not $NodeExe -and (Test-Path 'C:\Program Files\nodejs\node.exe')) {
  $NodeExe = 'C:\Program Files\nodejs\node.exe'
}
if (-not (Test-Path -LiteralPath $NodeExe)) {
  throw 'Node.js was not found. Pass -NodeExe with the full node.exe path.'
}

$UpdateScript = Join-Path $SiteDir 'update_scores_espn.js'
if (-not (Test-Path -LiteralPath $UpdateScript)) {
  throw "Updater not found: $UpdateScript"
}

$TaskCommand = "`"$NodeExe`" `"$UpdateScript`""

@(
  'WorldCup FIFA Playwright 0805 SGT',
  'WorldCup FIFA Playwright 1205 SGT',
  'WorldCup FIFA Scrape 0805 SGT',
  'WorldCup FIFA Scrape 1205 SGT'
) | ForEach-Object {
  schtasks /Delete /F /TN $_ 2>$null | Out-Null
}

schtasks /Create /F /TN 'WorldCup Score Update 0805 SGT' /SC DAILY /ST 08:05 /TR $TaskCommand | Out-Host
schtasks /Create /F /TN 'WorldCup Score Update 1205 SGT' /SC DAILY /ST 12:05 /TR $TaskCommand | Out-Host

Write-Host 'Created daily score update tasks for 08:05 and 12:05 server time.' -ForegroundColor Green
Write-Host 'The server time zone should be Singapore Standard Time.'
