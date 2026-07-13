<?php

namespace App\Service\Database;

use Doctrine\DBAL\Connection;

/**
 * Creates a MySQL/MariaDB compatible SQL dump of the current database while
 * applying the anonymisation rules defined in {@see DatabaseAnonymizer}.
 *
 * The dump is generated with the existing Doctrine connection (only SELECT
 * privileges are required) and is written straight to a file so that even a
 * large database does not have to be held in memory. The original data is
 * never modified – the anonymisation only happens on the way into the dump.
 */
class AnonymizedDatabaseDumper
{
    /** Binary column types whose content has to be written as a hex literal. */
    private const BINARY_TYPES = ['blob', 'tinyblob', 'mediumblob', 'longblob', 'binary', 'varbinary'];

    /** Numeric column types that can be written without quoting. */
    private const NUMERIC_TYPES = ['int', 'tinyint', 'smallint', 'mediumint', 'bigint', 'decimal', 'double', 'float', 'bit'];

    public function __construct(
        private readonly Connection $connection,
        private readonly DatabaseAnonymizer $anonymizer,
    ) {
    }

    /**
     * Writes the anonymised dump to $targetPath.
     */
    public function dumpTo(string $targetPath): void
    {
        $handle = fopen($targetPath, 'wb');
        if ($handle === false) {
            throw new \RuntimeException(sprintf('Could not open "%s" for writing.', $targetPath));
        }

        try {
            fwrite($handle, "-- Anonymized database dump\n");
            fwrite($handle, "SET NAMES utf8mb4;\n");
            fwrite($handle, "SET FOREIGN_KEY_CHECKS=0;\n");
            fwrite($handle, "SET SQL_MODE='NO_AUTO_VALUE_ON_ZERO';\n\n");

            foreach ($this->getBaseTables() as $table) {
                $this->dumpTable($handle, $table);
            }

            fwrite($handle, "SET FOREIGN_KEY_CHECKS=1;\n");
        } finally {
            fclose($handle);
        }
    }

    /**
     * @return string[]
     */
    private function getBaseTables(): array
    {
        $tables = [];
        foreach ($this->connection->fetchAllNumeric('SHOW FULL TABLES') as $row) {
            // $row[0] = table name, $row[1] = 'BASE TABLE' | 'VIEW'
            if (($row[1] ?? 'BASE TABLE') === 'BASE TABLE') {
                $tables[] = (string)$row[0];
            }
        }

        return $tables;
    }

    /**
     * @param resource $handle
     */
    private function dumpTable($handle, string $table): void
    {
        $quotedTable = $this->quoteIdentifier($table);

        fwrite($handle, sprintf("--\n-- Table structure for table %s\n--\n\n", $quotedTable));
        fwrite($handle, sprintf("DROP TABLE IF EXISTS %s;\n", $quotedTable));

        $createRow = $this->connection->fetchAssociative(sprintf('SHOW CREATE TABLE %s', $quotedTable));
        $createStatement = $createRow['Create Table'] ?? null;
        if ($createStatement === null) {
            // Not a normal table (e.g. a view) – skip it.
            return;
        }
        fwrite($handle, $createStatement . ";\n\n");

        $columnTypes = $this->getColumnTypes($table);

        fwrite($handle, sprintf("--\n-- Dumping data for table %s\n--\n\n", $quotedTable));

        $result = $this->connection->executeQuery(sprintf('SELECT * FROM %s', $quotedTable));
        $hasRows = false;
        foreach ($result->iterateAssociative() as $row) {
            $hasRows = true;
            $columns = [];
            $values = [];
            foreach ($row as $column => $value) {
                $value = $this->anonymizer->anonymize($table, $column, $value, $row);
                $columns[] = $this->quoteIdentifier($column);
                $values[] = $this->formatValue($value, $columnTypes[$column] ?? 'text');
            }

            fwrite($handle, sprintf(
                "INSERT INTO %s (%s) VALUES (%s);\n",
                $quotedTable,
                implode(', ', $columns),
                implode(', ', $values)
            ));
        }

        if ($hasRows) {
            fwrite($handle, "\n");
        }
    }

    /**
     * @return array<string, string> column name => lowercase data type
     */
    private function getColumnTypes(string $table): array
    {
        $rows = $this->connection->fetchAllAssociative(
            'SELECT COLUMN_NAME, DATA_TYPE FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table',
            ['table' => $table]
        );

        $types = [];
        foreach ($rows as $row) {
            $types[(string)$row['COLUMN_NAME']] = strtolower((string)$row['DATA_TYPE']);
        }

        return $types;
    }

    private function formatValue(mixed $value, string $type): string
    {
        if ($value === null) {
            return 'NULL';
        }

        $value = (string)$value;

        if (in_array($type, self::BINARY_TYPES, true)) {
            return $value === '' ? "''" : '0x' . bin2hex($value);
        }

        if (in_array($type, self::NUMERIC_TYPES, true) && is_numeric($value)) {
            return $value;
        }

        return $this->connection->quote($value);
    }

    private function quoteIdentifier(string $identifier): string
    {
        return '`' . str_replace('`', '``', $identifier) . '`';
    }
}
