#Requires -Version 5.0
<#
.SYNOPSIS
    Removes all markdown files from the root directory except README.md
    
.DESCRIPTION
    This script safely removes all .md files from the root directory while
    preserving README.md. It includes safeguards against accidental deletion
    and handles various edge cases.
    
.PARAMETER RootPath
    The root directory to scan for markdown files (default: current directory)
    
.PARAMETER WhatIf
    Shows what would be deleted without actually deleting
    
.PARAMETER Force
    Skips confirmation prompts
    
.EXAMPLE
    .\cleanup-markdown-files.ps1
    
.EXAMPLE
    .\cleanup-markdown-files.ps1 -WhatIf
    
.EXAMPLE
    .\cleanup-markdown-files.ps1 -Force
#>

param(
    [string]$RootPath = (Get-Location).Path,
    [switch]$WhatIf,
    [switch]$Force
)

# Color output helper
function Write-ColorOutput {
    param(
        [string]$Message,
        [string]$Color = "White"
    )
    Write-Host $Message -ForegroundColor $Color
}

# Main script
function Remove-MarkdownFiles {
    Write-ColorOutput "`n╔════════════════════════════════════════════════════════════╗" "Cyan"
    Write-ColorOutput "║     Markdown File Cleanup Script                           ║" "Cyan"
    Write-ColorOutput "╚════════════════════════════════════════════════════════════╝`n" "Cyan"
    
    # Verify root path exists
    if (-not (Test-Path -Path $RootPath -PathType Container)) {
        Write-ColorOutput "❌ Error: Root path does not exist: $RootPath" "Red"
        return $false
    }
    
    Write-ColorOutput "📁 Scanning directory: $RootPath`n" "Yellow"
    
    # Whitelist - case-insensitive check
    $whitelist = @("readme.md")
    
    # Find all markdown files in root directory (not recursive subdirectories)
    $markdownFiles = @()
    try {
        $allMdFiles = Get-ChildItem -Path $RootPath -Filter "*.md" -File -ErrorAction Stop
        
        foreach ($file in $allMdFiles) {
            # Check if file is in whitelist (case-insensitive)
            $fileName = $file.Name.ToLower()
            
            if ($fileName -notin $whitelist) {
                $markdownFiles += $file
            }
        }
    }
    catch {
        Write-ColorOutput "❌ Error scanning directory: $_" "Red"
        return $false
    }
    
    # Display findings
    Write-ColorOutput "📊 Scan Results:`n" "Cyan"
    Write-ColorOutput "   Total .md files found: $(($allMdFiles | Measure-Object).Count)" "White"
    Write-ColorOutput "   Files to preserve: 1 (README.md)" "Green"
    Write-ColorOutput "   Files to delete: $(($markdownFiles | Measure-Object).Count)`n" "Yellow"
    
    if ($markdownFiles.Count -eq 0) {
        Write-ColorOutput "✅ No markdown files to delete (except README.md)" "Green"
        return $true
    }
    
    # Display files to be deleted
    Write-ColorOutput "📋 Files scheduled for deletion:`n" "Yellow"
    $markdownFiles | ForEach-Object {
        $size = [math]::Round($_.Length / 1KB, 2)
        Write-ColorOutput "   ❌ $($_.Name) ($size KB)" "Red"
    }
    Write-ColorOutput ""
    
    # Verify README.md is in whitelist
    $readmeExists = Get-ChildItem -Path $RootPath -Filter "README.md" -File -ErrorAction SilentlyContinue
    if ($readmeExists) {
        Write-ColorOutput "✅ README.md will be preserved" "Green"
    } else {
        Write-ColorOutput "⚠️  README.md not found in directory" "Yellow"
    }
    Write-ColorOutput ""
    
    # Show what-if mode
    if ($WhatIf) {
        Write-ColorOutput "🔍 WhatIf Mode: No files will be deleted" "Cyan"
        return $true
    }
    
    # Confirmation prompt
    if (-not $Force) {
        Write-ColorOutput "⚠️  WARNING: This action cannot be undone!" "Yellow"
        $confirmation = Read-Host "Are you sure you want to delete these files? (yes/no)"
        
        if ($confirmation -ne "yes") {
            Write-ColorOutput "❌ Operation cancelled by user" "Yellow"
            return $false
        }
    }
    
    # Delete files with error handling
    Write-ColorOutput "`n🗑️  Deleting files...`n" "Cyan"
    $successCount = 0
    $failureCount = 0
    $errors = @()
    
    foreach ($file in $markdownFiles) {
        try {
            # Check if file is a symbolic link
            $isSymlink = $file.Attributes -band [System.IO.FileAttributes]::ReparsePoint
            
            if ($isSymlink) {
                Write-ColorOutput "   ⚠️  Skipping symbolic link: $($file.Name)" "Yellow"
                continue
            }
            
            # Check file permissions
            $acl = Get-Acl -Path $file.FullName
            $currentUser = [System.Security.Principal.WindowsIdentity]::GetCurrent().User
            $hasDeletePermission = $acl.Access | Where-Object {
                $_.IdentityReference -match $currentUser -and
                $_.FileSystemRights -match "Delete" -and
                $_.AccessControlType -eq "Allow"
            }
            
            if (-not $hasDeletePermission -and -not ([Security.Principal.WindowsPrincipal][Security.Principal.WindowsIdentity]::GetCurrent()).IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)) {
                Write-ColorOutput "   ⚠️  Permission denied: $($file.Name)" "Yellow"
                $failureCount++
                $errors += "Permission denied: $($file.Name)"
                continue
            }
            
            # Delete the file
            Remove-Item -Path $file.FullName -Force -ErrorAction Stop
            Write-ColorOutput "   ✅ Deleted: $($file.Name)" "Green"
            $successCount++
        }
        catch {
            Write-ColorOutput "   ❌ Failed to delete: $($file.Name)" "Red"
            Write-ColorOutput "      Error: $_" "Red"
            $failureCount++
            $errors += "Failed to delete $($file.Name): $_"
        }
    }
    
    # Summary
    Write-ColorOutput "`n╔════════════════════════════════════════════════════════════╗" "Cyan"
    Write-ColorOutput "║                    OPERATION SUMMARY                       ║" "Cyan"
    Write-ColorOutput "╚════════════════════════════════════════════════════════════╝`n" "Cyan"
    
    Write-ColorOutput "✅ Successfully deleted: $successCount files" "Green"
    if ($failureCount -gt 0) {
        Write-ColorOutput "❌ Failed to delete: $failureCount files" "Red"
    }
    
    # Verify operation
    Write-ColorOutput "`n🔍 Verifying operation...`n" "Cyan"
    $remainingMdFiles = Get-ChildItem -Path $RootPath -Filter "*.md" -File -ErrorAction SilentlyContinue | 
        Where-Object { $_.Name.ToLower() -ne "readme.md" }
    
    if ($remainingMdFiles.Count -eq 0) {
        Write-ColorOutput "✅ Verification successful: All markdown files removed (except README.md)" "Green"
        
        # Show remaining README.md
        $readmeFile = Get-ChildItem -Path $RootPath -Filter "README.md" -File -ErrorAction SilentlyContinue
        if ($readmeFile) {
            $readmeSize = [math]::Round($readmeFile.Length / 1KB, 2)
            Write-ColorOutput "   📄 README.md ($readmeSize KB) - PRESERVED" "Green"
        }
        
        return $true
    } else {
        Write-ColorOutput "⚠️  Verification failed: $($remainingMdFiles.Count) markdown files still remain" "Yellow"
        $remainingMdFiles | ForEach-Object {
            Write-ColorOutput "   ❌ $($_.Name)" "Red"
        }
        return $false
    }
}

# Run the cleanup
$result = Remove-MarkdownFiles

# Exit with appropriate code
if ($result) {
    Write-ColorOutput "`n✅ Cleanup completed successfully`n" "Green"
    exit 0
} else {
    Write-ColorOutput "`n❌ Cleanup encountered issues`n" "Red"
    exit 1
}
