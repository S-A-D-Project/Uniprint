@echo off
REM ============================================================================
REM Markdown File Cleanup Script for Windows
REM ============================================================================
REM This script removes all .md files from the root directory except README.md
REM It includes safeguards and handles various edge cases
REM ============================================================================

setlocal enabledelayedexpansion

echo.
echo ============================================================================
echo                    Markdown File Cleanup Script
echo ============================================================================
echo.

REM Get the current directory
set "ROOT_DIR=%CD%"
echo Scanning directory: %ROOT_DIR%
echo.

REM Count total markdown files
set "TOTAL_COUNT=0"
set "DELETE_COUNT=0"
set "PRESERVE_COUNT=0"

for /f %%F in ('dir /b "%ROOT_DIR%\*.md" 2^>nul ^| find /c /v ""') do (
    set "TOTAL_COUNT=%%F"
)

REM Display scan results
echo [*] Scan Results:
echo     Total .md files found: %TOTAL_COUNT%
echo     Files to preserve: 1 (README.md)

REM Create temporary file list
set "TEMP_LIST=%TEMP%\md_files_to_delete.txt"
del /q "%TEMP_LIST%" 2>nul

REM Build list of files to delete (excluding README.md)
for /f "delims=" %%F in ('dir /b "%ROOT_DIR%\*.md" 2^>nul') do (
    set "FILENAME=%%F"
    REM Case-insensitive comparison
    if /i not "!FILENAME!"=="README.md" (
        echo !FILENAME! >> "%TEMP_LIST%"
        set /a DELETE_COUNT+=1
    ) else (
        set /a PRESERVE_COUNT+=1
    )
)

echo     Files to delete: %DELETE_COUNT%
echo.

REM Display files to be deleted
if %DELETE_COUNT% gtr 0 (
    echo [*] Files scheduled for deletion:
    echo.
    for /f "delims=" %%F in ('type "%TEMP_LIST%"') do (
        echo     [X] %%F
    )
    echo.
) else (
    echo [+] No markdown files to delete (except README.md)
    echo.
    goto :verify_readme
)

REM Verify README.md exists
:verify_readme
if exist "%ROOT_DIR%\README.md" (
    echo [+] README.md will be preserved
) else (
    echo [!] WARNING: README.md not found in directory
)
echo.

REM Check for WhatIf mode
if "%1"=="-whatif" (
    echo [*] WhatIf Mode: No files will be deleted
    goto :cleanup_temp
)

REM Confirmation prompt
echo [!] WARNING: This action cannot be undone!
set /p CONFIRM="Are you sure you want to delete these files? (yes/no): "

if /i not "%CONFIRM%"=="yes" (
    echo [-] Operation cancelled by user
    goto :cleanup_temp
)

REM Delete files
echo.
echo [*] Deleting files...
echo.

set "SUCCESS_COUNT=0"
set "FAILURE_COUNT=0"

for /f "delims=" %%F in ('type "%TEMP_LIST%"') do (
    set "FILE_PATH=%ROOT_DIR%\%%F"
    
    REM Check if file exists
    if exist "!FILE_PATH!" (
        REM Try to delete the file
        del /q "!FILE_PATH!" 2>nul
        
        if exist "!FILE_PATH!" (
            echo     [X] Failed to delete: %%F
            set /a FAILURE_COUNT+=1
        ) else (
            echo     [+] Deleted: %%F
            set /a SUCCESS_COUNT+=1
        )
    ) else (
        echo     [!] File not found: %%F
    )
)

echo.
echo ============================================================================
echo                         OPERATION SUMMARY
echo ============================================================================
echo.
echo [+] Successfully deleted: %SUCCESS_COUNT% files
if %FAILURE_COUNT% gtr 0 (
    echo [X] Failed to delete: %FAILURE_COUNT% files
)
echo.

REM Verify operation
echo [*] Verifying operation...
echo.

set "REMAINING_COUNT=0"
for /f "delims=" %%F in ('dir /b "%ROOT_DIR%\*.md" 2^>nul') do (
    if /i not "%%F"=="README.md" (
        set /a REMAINING_COUNT+=1
    )
)

if %REMAINING_COUNT% equ 0 (
    echo [+] Verification successful: All markdown files removed (except README.md)
    if exist "%ROOT_DIR%\README.md" (
        for %%F in ("%ROOT_DIR%\README.md") do (
            set "SIZE=%%~zF"
            set /a SIZE_KB=!SIZE!/1024
            echo     [+] README.md (!SIZE_KB! KB) - PRESERVED
        )
    )
    echo.
    echo [+] Cleanup completed successfully
    goto :cleanup_temp
) else (
    echo [!] Verification failed: %REMAINING_COUNT% markdown files still remain
    for /f "delims=" %%F in ('dir /b "%ROOT_DIR%\*.md" 2^>nul') do (
        if /i not "%%F"=="README.md" (
            echo     [X] %%F
        )
    )
    echo.
    echo [X] Cleanup encountered issues
    goto :cleanup_temp
)

:cleanup_temp
REM Clean up temporary files
del /q "%TEMP_LIST%" 2>nul

echo.
pause
