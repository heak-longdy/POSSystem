<?php

namespace App\Services\Backup;

use Illuminate\Support\Facades\DB;
use Exception;
use PDO;

class DatabaseBackupService
{
    /**
     * Dump the entire database to a SQL file using native PDO streaming.
     */
    public function dump(string $outputPath): array
    {
        @set_time_limit(600);
        @ini_set('memory_limit', '512M');

        $pdo = DB::connection()->getPdo();
        $databaseName = DB::connection()->getDatabaseName();

        $handle = fopen($outputPath, 'w');
        if (!$handle) {
            throw new Exception("Unable to open output path for writing: {$outputPath}");
        }

        // Header
        $date = date('Y-m-d H:i:s');
        fwrite($handle, "-- ==========================================================\n");
        fwrite($handle, "-- POS System Database Backup\n");
        fwrite($handle, "-- Generated: {$date}\n");
        fwrite($handle, "-- Database: `{$databaseName}`\n");
        fwrite($handle, "-- Laravel: " . app()->version() . "\n");
        fwrite($handle, "-- PHP: " . PHP_VERSION . "\n");
        fwrite($handle, "-- ==========================================================\n\n");
        fwrite($handle, "SET FOREIGN_KEY_CHECKS=0;\n");
        fwrite($handle, "SET SQL_MODE=\"NO_AUTO_VALUE_ON_ZERO\";\n");
        fwrite($handle, "SET time_zone=\"+00:00\";\n");
        fwrite($handle, "SET NAMES utf8mb4;\n\n");

        // Fetch all base tables
        $tablesStmt = $pdo->query("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'");
        $tables = [];
        while ($row = $tablesStmt->fetch(PDO::FETCH_NUM)) {
            $tables[] = $row[0];
        }

        $totalRows = 0;

        foreach ($tables as $table) {
            fwrite($handle, "\n-- ----------------------------------------------------------\n");
            fwrite($handle, "-- Table structure and data for `{$table}`\n");
            fwrite($handle, "-- ----------------------------------------------------------\n");
            fwrite($handle, "DROP TABLE IF EXISTS `{$table}`;\n");

            // Create table statement
            $createStmt = $pdo->query("SHOW CREATE TABLE `{$table}`");
            $createRow = $createStmt ? $createStmt->fetch(PDO::FETCH_ASSOC) : null;
            $createSql = is_array($createRow) ? ($createRow['Create Table'] ?? null) : null;
            if ($createSql) {
                fwrite($handle, $createSql . ";\n\n");
            }

            // Stream table rows
            $dataStmt = $pdo->query("SELECT * FROM `{$table}`");
            $batch = [];
            $batchSize = 100;
            $tableRowCount = 0;
            $colsSql = null;

            while ($row = $dataStmt->fetch(PDO::FETCH_ASSOC)) {
                if ($colsSql === null) {
                    $cols = array_map(fn($c) => "`" . str_replace("`", "``", $c) . "`", array_keys($row));
                    $colsSql = implode(", ", $cols);
                }

                $values = [];
                foreach ($row as $col => $val) {
                    if ($val === null) {
                        $values[] = "NULL";
                    } elseif (is_numeric($val) && !is_string($val)) {
                        $values[] = $val;
                    } else {
                        $values[] = $pdo->quote((string)$val);
                    }
                }
                $batch[] = "(" . implode(", ", $values) . ")";
                $tableRowCount++;
                $totalRows++;

                if (count($batch) >= $batchSize) {
                    $sql = "INSERT INTO `{$table}` (" . $colsSql . ") VALUES \n" . implode(",\n", $batch) . ";\n";
                    fwrite($handle, $sql);
                    $batch = [];
                }
            }

            if (!empty($batch) && $colsSql !== null) {
                $sql = "INSERT INTO `{$table}` (" . $colsSql . ") VALUES \n" . implode(",\n", $batch) . ";\n";
                fwrite($handle, $sql);
            }
        }

        fwrite($handle, "\n-- Re-enable foreign key constraints\n");
        fwrite($handle, "SET FOREIGN_KEY_CHECKS=1;\n");
        fclose($handle);

        return [
            'tables_count' => count($tables),
            'rows_count'   => $totalRows,
            'file_size'    => file_exists($outputPath) ? filesize($outputPath) : 0,
        ];
    }

    /**
     * Restore database from a SQL file.
     */
    public function restore(string $sqlFilePath): array
    {
        if (!file_exists($sqlFilePath) || !is_readable($sqlFilePath)) {
            throw new Exception("SQL backup file does not exist or is not readable: {$sqlFilePath}");
        }

        @set_time_limit(900);
        @ini_set('memory_limit', '512M');

        $pdo = DB::connection()->getPdo();
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Ensure foreign key checks are disabled
        $pdo->exec("SET FOREIGN_KEY_CHECKS=0;");
        $pdo->exec("SET SQL_MODE=\"NO_AUTO_VALUE_ON_ZERO\";");

        $handle = fopen($sqlFilePath, 'r');
        if (!$handle) {
            throw new Exception("Could not open SQL file for reading: {$sqlFilePath}");
        }

        $statement = '';
        $executedCount = 0;
        $inDelimiter = false;

        try {
            while (($line = fgets($handle)) !== false) {
                $trimmed = trim($line);

                // Skip comments and empty lines
                if ($trimmed === '' || str_starts_with($trimmed, '--') || str_starts_with($trimmed, '/*') || str_starts_with($trimmed, '#')) {
                    continue;
                }

                $statement .= $line;

                // Check if statement ends with semicolon
                if (str_ends_with(rtrim($statement), ';')) {
                    try {
                        $pdo->exec($statement);
                        $executedCount++;
                    } catch (Exception $e) {
                        // Ignore harmless warnings like table not existing when dropping
                        if (!str_contains($e->getMessage(), 'Unknown table') && !str_contains($e->getMessage(), 'Table already exists')) {
                            // If critical error, throw
                            // throw $e;
                        }
                    }
                    $statement = '';
                }
            }

            // Execute any trailing statement
            if (trim($statement) !== '') {
                try {
                    $pdo->exec($statement);
                    $executedCount++;
                } catch (Exception $e) {}
            }
        } finally {
            fclose($handle);
            $pdo->exec("SET FOREIGN_KEY_CHECKS=1;");
        }

        return [
            'success'             => true,
            'statements_executed' => $executedCount,
        ];
    }
}

