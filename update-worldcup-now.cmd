@echo off
setlocal
cd /d "%~dp0"

set "NODE_EXE="
for /f "delims=" %%I in ('where node.exe 2^>nul') do if not defined NODE_EXE set "NODE_EXE=%%I"
if not defined NODE_EXE if exist "C:\Program Files\nodejs\node.exe" set "NODE_EXE=C:\Program Files\nodejs\node.exe"

if not defined NODE_EXE (
  echo Node.js was not found. Please install Node.js or add node.exe to PATH.
  pause
  exit /b 1
)

echo Updating World Cup schedule and scores from ESPN...
"%NODE_EXE%" "%~dp0update_scores_espn.js"
echo.
if errorlevel 1 (
  echo Update failed. Check data\score-update-espn.log.
) else (
  echo Update completed. Refresh the website with Ctrl+F5.
)
pause
