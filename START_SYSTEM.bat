@echo off
title SIS-POLLERIA CRISPY - Servicios
echo ====================================================
echo   INICIANDO SERVICIOS DE SIS-POLLERIA CRISPY
echo ====================================================
echo.

:: Abrir Servidor de WebSockets (Reverb)
echo [+] Iniciando Servidor de Tiempo Real (Reverb)...
start "Reverb WebSockets" powershell -NoExit -Command "php artisan reverb:start"

:: Abrir Procesador de Colas (Queue Worker) con prioridad en Impresion
echo [+] Iniciando Procesador de Colas (Impresion)...
start "Queue Worker" powershell -NoExit -Command "php artisan queue:work --queue=printing,default --tries=3"

:: Abrir Servidor de Desarrollo (Vite)
echo [+] Iniciando Servidor de Frontend (Vite)...
start "Vite Dev" powershell -NoExit -Command "npm run dev"

:: Abrir Servidor PHP
echo [+] Iniciando Servidor Backend (Artisan Serve)...
start "Laravel Server" powershell -NoExit -Command "php artisan serve"

echo.
echo ====================================================
echo   TODOS LOS SERVICIOS SE ESTAN EJECUTANDO
echo   No cierres las ventanas de terminal.
echo ====================================================
echo.
pause
