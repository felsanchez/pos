@echo off
setlocal enabledelayedexpansion

echo ==================================================
echo   EMPAQUETADOR PARA PRODUCCION - SISTEMA POS
echo ==================================================

:: Definir rutas
set "ORIGEN=%~dp0"
set "ESCRITORIO=%USERPROFILE%\Desktop"
set "DESTINO=%ESCRITORIO%\pos_produccion"

echo.
echo [1/4] Preparando directorio destino...
if exist "%DESTINO%" (
    rmdir /S /Q "%DESTINO%"
)
mkdir "%DESTINO%"

echo.
echo [2/4] Copiando archivos esenciales...
:: Copiar archivos sueltos en la raiz
copy /Y "%ORIGEN%index.php" "%DESTINO%\" > nul
if exist "%ORIGEN%config.php" copy /Y "%ORIGEN%config.php" "%DESTINO%\" > nul
if exist "%ORIGEN%.htaccess" copy /Y "%ORIGEN%.htaccess" "%DESTINO%\" > nul
if exist "%ORIGEN%descargar-xml.php" copy /Y "%ORIGEN%descargar-xml.php" "%DESTINO%\" > nul
if exist "%ORIGEN%descargar-xml-ds.php" copy /Y "%ORIGEN%descargar-xml-ds.php" "%DESTINO%\" > nul
if exist "%ORIGEN%descargar-xml-na.php" copy /Y "%ORIGEN%descargar-xml-na.php" "%DESTINO%\" > nul
if exist "%ORIGEN%descargar-xml-nc.php" copy /Y "%ORIGEN%descargar-xml-nc.php" "%DESTINO%\" > nul

:: Copiar carpetas esenciales usando robocopy (el /E es recursivo, los otros flags silencian la salida)
robocopy "%ORIGEN%controladores" "%DESTINO%\controladores" /E /NFL /NDL /NJH /NJS
robocopy "%ORIGEN%modelos" "%DESTINO%\modelos" /E /NFL /NDL /NJH /NJS
robocopy "%ORIGEN%vistas" "%DESTINO%\vistas" /E /NFL /NDL /NJH /NJS
robocopy "%ORIGEN%ajax" "%DESTINO%\ajax" /E /NFL /NDL /NJH /NJS
robocopy "%ORIGEN%extensiones" "%DESTINO%\extensiones" /E /NFL /NDL /NJH /NJS
robocopy "%ORIGEN%assets" "%DESTINO%\assets" /E /NFL /NDL /NJH /NJS
robocopy "%ORIGEN%storage" "%DESTINO%\storage" /E /NFL /NDL /NJH /NJS
robocopy "%ORIGEN%xml" "%DESTINO%\xml" /E /NFL /NDL /NJH /NJS

echo.
echo [3/4] Comprimiendo en formato ZIP...
set "ARCHIVO_ZIP=%ESCRITORIO%\pos_produccion.zip"
if exist "%ARCHIVO_ZIP%" (
    del /Q "%ARCHIVO_ZIP%"
)

:: Usar powershell para comprimir
powershell -Command "Compress-Archive -Path '%DESTINO%\*' -DestinationPath '%ARCHIVO_ZIP%' -Force"

echo.
echo [4/4] Limpiando archivos temporales...
rmdir /S /Q "%DESTINO%"

echo.
echo ==================================================
echo   PROCESO TERMINADO CON EXITO
echo ==================================================
echo El archivo para subir al hosting se encuentra en:
echo %ARCHIVO_ZIP%
echo.
echo Abriendo ubicacion...
explorer "%ESCRITORIO%"

pause
