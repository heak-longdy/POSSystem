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
     * Restore database from a SQL file, completely replacing existing data.
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
        $databaseName = DB::connection()->getDatabaseName();

        // 1. Wipe or empty existing database to guarantee clean overwrite (no merging or updating)
        $this->wipeExistingDatabase($pdo, $databaseName, $sqlFilePath);

        // 2. Execute statements from SQL file
        $executedCount = $this->executeSqlFile($pdo, $sqlFilePath);

        return [
            'success'             => true,
            'statements_executed' => $executedCount,
        ];
    }

    /**
     * Wipe existing database before restore so backup data completely replaces existing data.
     */
    protected function wipeExistingDatabase(PDO $pdo, string $databaseName, string $sqlFilePath): void
    {
        $pdo->exec("SET FOREIGN_KEY_CHECKS=0;");

        $hasCreateTable = $this->hasCreateTableStatements($sqlFilePath);

        if ($hasCreateTable) {
            // Case A: SQL file contains table definitions (Full / Schema dump).
            // Drop all existing views and tables so no old tables or old records remain.
            $viewsStmt = $pdo->query("SELECT TABLE_NAME FROM information_schema.VIEWS WHERE TABLE_SCHEMA = " . $pdo->quote($databaseName));
            $views = [];
            while ($row = $viewsStmt->fetch(PDO::FETCH_NUM)) {
                $views[] = $row[0];
            }
            foreach ($views as $view) {
                $pdo->exec("DROP VIEW IF EXISTS `{$view}`;");
            }

            $tablesStmt = $pdo->query("SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = " . $pdo->quote($databaseName) . " AND TABLE_TYPE = 'BASE TABLE'");
            $tables = [];
            while ($row = $tablesStmt->fetch(PDO::FETCH_NUM)) {
                $tables[] = $row[0];
            }
            foreach ($tables as $table) {
                $pdo->exec("DROP TABLE IF EXISTS `{$table}`;");
            }
        } else {
            // Case B: SQL file is data-only (INSERTs only, no CREATE TABLE).
            // Truncate/delete all existing tables so backup data replaces existing records instead of merging.
            $tablesStmt = $pdo->query("SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = " . $pdo->quote($databaseName) . " AND TABLE_TYPE = 'BASE TABLE'");
            $tables = [];
            while ($row = $tablesStmt->fetch(PDO::FETCH_NUM)) {
                $tables[] = $row[0];
            }
            foreach ($tables as $table) {
                try {
                    $pdo->exec("TRUNCATE TABLE `{$table}`;");
                } catch (\Throwable $e) {
                    $pdo->exec("DELETE FROM `{$table}`;");
                }
            }
        }
    }

    /**
     * Check if SQL file contains CREATE TABLE statements.
     */
    protected function hasCreateTableStatements(string $sqlFilePath): bool
    {
        $handle = fopen($sqlFilePath, 'r');
        if (!$handle) {
            return false;
        }

        $hasCreate = false;
        $inBlockComment = false;

        while (($line = fgets($handle)) !== false) {
            $trimmed = trim($line);

            if ($inBlockComment) {
                if (str_contains($trimmed, '*/')) {
                    $inBlockComment = false;
                }
                continue;
            }

            if (str_starts_with($trimmed, '/*') && !str_starts_with($trimmed, '/*!')) {
                if (!str_contains($trimmed, '*/')) {
                    $inBlockComment = true;
                }
                continue;
            }

            if ($trimmed === '' || str_starts_with($trimmed, '--') || str_starts_with($trimmed, '#')) {
                continue;
            }

            if (preg_match('/\bCREATE\s+(?:TEMPORARY\s+)?TABLE\b/i', $trimmed)) {
                $hasCreate = true;
                break;
            }
        }

        fclose($handle);

        return $hasCreate;
    }

    /**
     * Parse and stream SQL statements from file with quote- and comment-awareness.
     */
    protected function executeSqlFile(PDO $pdo, string $sqlFilePath): int
    {
        $handle = fopen($sqlFilePath, 'r');
        if (!$handle) {
            throw new Exception("Could not open SQL file for reading: {$sqlFilePath}");
        }

        $pdo->exec("SET FOREIGN_KEY_CHECKS=0;");
        $pdo->exec("SET SQL_MODE=\"NO_AUTO_VALUE_ON_ZERO\";");
        $pdo->exec("SET NAMES utf8mb4;");

        $statement = '';
        $executedCount = 0;
        $inString = false;
        $stringChar = '';
        $isEscaped = false;
        $inBlockComment = false;

        try {
            while (($line = fgets($handle)) !== false) {
                $trimmed = trim($line);

                // Quick line-level comment skip when not currently inside a multi-line string or block comment
                if (!$inString && !$inBlockComment) {
                    if ($trimmed === '' || str_starts_with($trimmed, '--') || str_starts_with($trimmed, '#')) {
                        continue;
                    }
                }

                $len = strlen($line);
                for ($i = 0; $i < $len; $i++) {
                    $char = $line[$i];
                    $nextChar = ($i + 1 < $len) ? $line[$i + 1] : '';

                    if ($inBlockComment) {
                        if ($char === '*' && $nextChar === '/') {
                            $inBlockComment = false;
                            $statement .= '*/';
                            $i++;
                            continue;
                        }
                        $statement .= $char;
                        continue;
                    }

                    if ($inString) {
                        $statement .= $char;
                        if ($isEscaped) {
                            $isEscaped = false;
                        } elseif ($char === '\\') {
                            $isEscaped = true;
                        } elseif ($char === $stringChar) {
                            // Check for doubled quote escape in SQL (e.g. '' or "")
                            if ($nextChar === $stringChar) {
                                $statement .= $nextChar;
                                $i++;
                            } else {
                                $inString = false;
                                $stringChar = '';
                            }
                        }
                        continue;
                    }

                    // Check for start of comments outside strings
                    if ($char === '/' && $nextChar === '*') {
                        $statement .= '/*';
                        $inBlockComment = true;
                        $i++;
                        continue;
                    }

                    if (($char === '-' && $nextChar === '-') || $char === '#') {
                        if ($char === '#' || ($i + 2 >= $len) || $line[$i + 2] === ' ' || $line[$i + 2] === "\t" || $line[$i + 2] === "\r" || $line[$i + 2] === "\n") {
                            break;
                        }
                    }

                    // Check for start of strings
                    if ($char === "'" || $char === '"') {
                        $inString = true;
                        $stringChar = $char;
                        $isEscaped = false;
                        $statement .= $char;
                        continue;
                    }

                    // Check for statement delimiter ';'
                    if ($char === ';') {
                        $trimmedStmt = trim($statement);
                        if ($trimmedStmt !== '') {
                            $this->executeSingleStatement($pdo, $trimmedStmt, $executedCount);
                            $executedCount++;
                        }
                        $statement = '';
                        continue;
                    }

                    $statement .= $char;
                }
            }

            // Execute any remaining statement without trailing semicolon
            $trimmedStmt = trim($statement);
            if ($trimmedStmt !== '') {
                $this->executeSingleStatement($pdo, $trimmedStmt, $executedCount);
                $executedCount++;
            }
        } finally {
            fclose($handle);
            $pdo->exec("SET FOREIGN_KEY_CHECKS=1;");
        }

        return $executedCount;
    }

    /**
     * Execute a single SQL statement with error handling.
     */
    protected function executeSingleStatement(PDO $pdo, string $statement, int $statementIndex): void
    {
        try {
            $pdo->exec($statement);
        } catch (\Throwable $e) {
            $msg = $e->getMessage();
            // Ignore harmless errors like dropping a non-existent table or view
            if (str_contains($msg, 'Unknown table') || str_contains($msg, 'Unknown view') || str_contains($msg, "doesn't exist")) {
                if (stripos($statement, 'DROP TABLE') !== false || stripos($statement, 'DROP VIEW') !== false) {
                    return;
                }
            }
            throw new Exception("Database restore failed at statement #{$statementIndex}: " . $msg . " (SQL: " . substr($statement, 0, 160) . "...)");
        }
    }
}

