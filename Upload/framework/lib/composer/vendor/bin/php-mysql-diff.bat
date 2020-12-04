@ECHO OFF
setlocal DISABLEDELAYEDEXPANSION
SET BIN_TARGET=%~dp0/../camcima/php-mysql-diff/php-mysql-diff
php "%BIN_TARGET%" %*
