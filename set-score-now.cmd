@echo off
setlocal

set "SITE_DIR=%~dp0"
set "PHP_EXE="

if exist "C:\php83\php.exe" set "PHP_EXE=C:\php83\php.exe"
if not defined PHP_EXE if exist "C:\php\php.exe" set "PHP_EXE=C:\php\php.exe"
if not defined PHP_EXE if exist "C:\PHP\php.exe" set "PHP_EXE=C:\PHP\php.exe"
if not defined PHP_EXE if exist "C:\PHPWeb\php\php.exe" set "PHP_EXE=C:\PHPWeb\php\php.exe"
if not defined PHP_EXE if exist "C:\PHPWeb\PHP\php.exe" set "PHP_EXE=C:\PHPWeb\PHP\php.exe"

if not defined PHP_EXE (
  echo PHP executable was not found.
  pause
  exit /b 1
)

echo Manual World Cup score update
echo Examples: ESP CPV 0 0   or   BEL EGY 2 1
echo.

set /p HOME_CODE=Home team code: 
set /p AWAY_CODE=Away team code: 
set /p HOME_SCORE=Home score: 
set /p AWAY_SCORE=Away score: 

echo.
"%PHP_EXE%" "%SITE_DIR%set_score.php" %HOME_CODE% %AWAY_CODE% %HOME_SCORE% %AWAY_SCORE%

echo.
echo Done. Refresh the website with Ctrl+F5.
pause
