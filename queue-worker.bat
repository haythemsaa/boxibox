@echo off
REM Queue Worker pour Windows
REM Ce script lance le worker Laravel en arrière-plan

echo Demarrage du Queue Worker Boxibox...

:loop
php artisan queue:work --sleep=3 --tries=3 --max-time=3600 --timeout=60
if errorlevel 1 (
    echo Erreur detectee, redemarrage dans 5 secondes...
    timeout /t 5 /nobreak > nul
) else (
    echo Worker arrete normalement, redemarrage dans 3 secondes...
    timeout /t 3 /nobreak > nul
)
goto loop
