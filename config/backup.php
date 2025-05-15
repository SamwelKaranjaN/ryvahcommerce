<?php
class BackupHandler {
    private $backupDir;
    private $maxBackups;
    private $conn;

    public function __construct($conn) {
        $this->backupDir = __DIR__ . '/../backups/';
        $this->maxBackups = 7; // Keep last 7 days of backups
        $this->conn = $conn;
        
        if (!file_exists($this->backupDir)) {
            mkdir($this->backupDir, 0755, true);
        }
    }

    public function createBackup() {
        $timestamp = date('Y-m-d_H-i-s');
        $filename = $this->backupDir . "backup_{$timestamp}.sql";
        
        // Get all tables
        $tables = [];
        $result = $this->conn->query("SHOW TABLES");
        while ($row = $result->fetch_row()) {
            $tables[] = $row[0];
        }
        
        $backup = "";
        
        // Create backup for each table
        foreach ($tables as $table) {
            $result = $this->conn->query("SELECT * FROM $table");
            $numColumns = $result->field_count;
            
            $backup .= "DROP TABLE IF EXISTS $table;";
            
            $row2 = $this->conn->query("SHOW CREATE TABLE $table")->fetch_row();
            $backup .= "\n\n" . $row2[1] . ";\n\n";
            
            while ($row = $result->fetch_row()) {
                $backup .= "INSERT INTO $table VALUES(";
                for ($j = 0; $j < $numColumns; $j++) {
                    $row[$j] = addslashes($row[$j]);
                    $row[$j] = str_replace("\n", "\\n", $row[$j]);
                    if (isset($row[$j])) {
                        $backup .= '"' . $row[$j] . '"';
                    } else {
                        $backup .= '""';
                    }
                    if ($j < ($numColumns - 1)) {
                        $backup .= ',';
                    }
                }
                $backup .= ");\n";
            }
            $backup .= "\n\n\n";
        }
        
        // Save backup file
        file_put_contents($filename, $backup);
        
        // Compress backup
        $zip = new ZipArchive();
        $zipname = $filename . '.zip';
        if ($zip->open($zipname, ZipArchive::CREATE) === TRUE) {
            $zip->addFile($filename, basename($filename));
            $zip->close();
            unlink($filename); // Remove original SQL file
        }
        
        // Clean old backups
        $this->cleanOldBackups();
        
        return $zipname;
    }

    private function cleanOldBackups() {
        $files = glob($this->backupDir . '*.zip');
        usort($files, function($a, $b) {
            return filemtime($b) - filemtime($a);
        });
        
        if (count($files) > $this->maxBackups) {
            $filesToDelete = array_slice($files, $this->maxBackups);
            foreach ($filesToDelete as $file) {
                unlink($file);
            }
        }
    }

    public function restoreBackup($backupFile) {
        if (!file_exists($backupFile)) {
            throw new Exception("Backup file not found");
        }
        
        // Extract backup
        $zip = new ZipArchive();
        if ($zip->open($backupFile) === TRUE) {
            $zip->extractTo($this->backupDir);
            $zip->close();
            
            $sqlFile = $this->backupDir . basename($backupFile, '.zip');
            
            // Read and execute SQL
            $sql = file_get_contents($sqlFile);
            $queries = explode(';', $sql);
            
            foreach ($queries as $query) {
                if (trim($query) != '') {
                    $this->conn->query($query);
                }
            }
            
            unlink($sqlFile);
            return true;
        }
        
        return false;
    }
} 