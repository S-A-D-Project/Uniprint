@echo off
setlocal enabledelayedexpansion

for /f "delims=" %%F in ('dir /b *.md') do (
    if /i not "%%F"=="README.md" (
        del "%%F"
        echo Deleted: %%F
    )
)

echo.
echo Remaining markdown files:
dir /b *.md

echo.
echo Cleanup complete!
