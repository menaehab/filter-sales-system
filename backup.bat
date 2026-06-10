@echo off

set DB_NAME=filter_sales_system
set DB_USER=root
set DB_PASS=

set BACKUP_DIR=D:\programming\PHP\laragon\www\filter-sales-system\storage\app\private\backups
if not exist "%BACKUP_DIR%" mkdir "%BACKUP_DIR%"
set MYSQLDUMP="D:\programming\PHP\laragon\bin\mysql\mysql-8.0.30-winx64\bin\mysqldump.exe"

for /f %%i in ('powershell -command "Get-Date -Format yyyy-MM-dd_HH-mm"') do set DATE=%%i

set FILE="%BACKUP_DIR%\db_%DATE%.sql"

if "%DB_PASS%"=="" (
    %MYSQLDUMP% -u %DB_USER% %DB_NAME% > %FILE%
) else (
    %MYSQLDUMP% -u %DB_USER% -p%DB_PASS% %DB_NAME% > %FILE%
)
