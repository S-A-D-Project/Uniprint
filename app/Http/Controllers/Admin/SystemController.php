<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Artisan;
use Carbon\Carbon;

class SystemController extends Controller
{
    /**
     * Show system settings page
     */
    public function settings()
    {
        $backups = $this->getBackupsList();
        
        return view('admin.settings', compact('backups'));
    }
    
    /**
     * Create database backup
     */
    public function createBackup()
    {
        try {
            $filename = 'backup_' . Carbon::now()->format('Y-m-d_H-i-s') . '.sql';
            $backupPath = storage_path('app/backups');
            
            // Create backups directory if it doesn't exist
            if (!file_exists($backupPath)) {
                mkdir($backupPath, 0755, true);
            }
            
            $filepath = $backupPath . '/' . $filename;
            
            // Get database configuration
            $dbHost = config('database.connections.' . config('database.default') . '.host');
            $dbName = config('database.connections.' . config('database.default') . '.database');
            $dbUser = config('database.connections.' . config('database.default') . '.username');
            $dbPass = config('database.connections.' . config('database.default') . '.password');
            
            // Check database driver
            $driver = config('database.default');
            
            if ($driver === 'mysql') {
                // MySQL backup command
                $command = sprintf(
                    'mysqldump -h %s -u %s -p%s %s > %s',
                    $dbHost,
                    $dbUser,
                    $dbPass,
                    $dbName,
                    $filepath
                );
                
                exec($command, $output, $return);
                
                if ($return !== 0) {
                    throw new \Exception('MySQL backup failed');
                }
            } else {
                // For other database drivers, use Laravel's DB::select
                $tables = $this->getAllTables();
                $sql = $this->generateSqlDump($tables);
                file_put_contents($filepath, $sql);
            }
            
            // Delete old backups (keep only last 7)
            $this->cleanOldBackups();
            
            return redirect()->back()->with('success', 'Database backup created successfully: ' . $filename);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Backup failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Download backup file
     */
    public function downloadBackup($filename)
    {
        $filepath = storage_path('app/backups/' . $filename);
        
        if (!file_exists($filepath)) {
            return redirect()->back()->with('error', 'Backup file not found');
        }
        
        return response()->download($filepath);
    }
    
    /**
     * Delete backup file
     */
    public function deleteBackup($filename)
    {
        try {
            $filepath = storage_path('app/backups/' . $filename);
            
            if (file_exists($filepath)) {
                unlink($filepath);
                return redirect()->back()->with('success', 'Backup deleted successfully');
            }
            
            return redirect()->back()->with('error', 'Backup file not found');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to delete backup: ' . $e->getMessage());
        }
    }
    
    /**
     * Reset database (WARNING: Destructive action)
     */
    public function resetDatabase(Request $request)
    {
        $request->validate([
            'confirm' => 'required|in:RESET',
        ]);
        
        try {
            // Create backup before reset
            $this->createBackup();
            
            // Clear all tables (except migrations)
            $tables = $this->getAllTables();
            
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            
            foreach ($tables as $table) {
                if ($table !== 'migrations') {
                    DB::table($table)->truncate();
                }
            }
            
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            
            // Run seeders to populate with fresh data
            Artisan::call('db:seed', ['--force' => true]);
            
            return redirect()->back()->with('success', 'Database reset successfully. A backup was created before reset.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Database reset failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Clear all caches
     */
    public function clearCache()
    {
        try {
            Artisan::call('cache:clear');
            Artisan::call('config:clear');
            Artisan::call('route:clear');
            Artisan::call('view:clear');
            
            return redirect()->back()->with('success', 'All caches cleared successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to clear caches: ' . $e->getMessage());
        }
    }
    
    /**
     * Optimize application
     */
    public function optimize()
    {
        try {
            Artisan::call('config:cache');
            Artisan::call('route:cache');
            Artisan::call('view:cache');
            
            return redirect()->back()->with('success', 'Application optimized successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Optimization failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Get list of backups
     */
    private function getBackupsList()
    {
        $backupPath = storage_path('app/backups');
        
        if (!file_exists($backupPath)) {
            return [];
        }
        
        $files = scandir($backupPath);
        $backups = [];
        
        foreach ($files as $file) {
            if ($file !== '.' && $file !== '..') {
                $filepath = $backupPath . '/' . $file;
                $backups[] = [
                    'filename' => $file,
                    'size' => filesize($filepath),
                    'date' => date('Y-m-d H:i:s', filemtime($filepath)),
                ];
            }
        }
        
        // Sort by date descending
        usort($backups, function ($a, $b) {
            return strtotime($b['date']) - strtotime($a['date']);
        });
        
        return $backups;
    }
    
    /**
     * Get all database tables
     */
    private function getAllTables()
    {
        $driver = config('database.default');
        
        if ($driver === 'mysql') {
            $tables = DB::select('SHOW TABLES');
            $key = 'Tables_in_' . config('database.connections.mysql.database');
            return array_map(function ($table) use ($key) {
                return $table->$key;
            }, $tables);
        } elseif ($driver === 'pgsql') {
            $tables = DB::select("SELECT tablename FROM pg_tables WHERE schemaname = 'public'");
            return array_map(function ($table) {
                return $table->tablename;
            }, $tables);
        }
        
        return [];
    }
    
    /**
     * Generate SQL dump for tables
     */
    private function generateSqlDump($tables)
    {
        $sql = "-- UniPrint Database Backup\n";
        $sql .= "-- Generated: " . Carbon::now()->toDateTimeString() . "\n\n";
        
        foreach ($tables as $table) {
            $sql .= "\n-- Table: $table\n";
            $sql .= "DROP TABLE IF EXISTS `$table`;\n";
            
            // Get table structure
            $createTable = DB::select("SHOW CREATE TABLE `$table`");
            $sql .= $createTable[0]->{'Create Table'} . ";\n\n";
            
            // Get table data
            $rows = DB::table($table)->get();
            
            if ($rows->count() > 0) {
                foreach ($rows as $row) {
                    $values = array_map(function ($value) {
                        return is_null($value) ? 'NULL' : "'" . addslashes($value) . "'";
                    }, (array) $row);
                    
                    $sql .= "INSERT INTO `$table` VALUES (" . implode(', ', $values) . ");\n";
                }
                $sql .= "\n";
            }
        }
        
        return $sql;
    }
    
    /**
     * Clean old backups (keep only last 7)
     */
    private function cleanOldBackups()
    {
        $backups = $this->getBackupsList();
        
        if (count($backups) > 7) {
            $toDelete = array_slice($backups, 7);
            
            foreach ($toDelete as $backup) {
                $filepath = storage_path('app/backups/' . $backup['filename']);
                if (file_exists($filepath)) {
                    unlink($filepath);
                }
            }
        }
    }
}
