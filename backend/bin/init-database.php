<?php
declare(strict_types=1);

use FixIt\Database;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../src/bootstrap.php';

$env = load_env(__DIR__ . '/../.env');
$pdo = Database::connect($env);

$requiredTables = [
    'users',
    'service_categories',
    'provider_profiles',
    'provider_categories',
    'jobs',
    'job_status_logs',
    'messages',
    'reviews',
];

$existingTables = [];
$stmt = $pdo->query('SHOW TABLES');
foreach ($stmt->fetchAll(PDO::FETCH_NUM) as $row) {
    $existingTables[] = (string)$row[0];
}

$existingRequired = array_values(array_intersect($requiredTables, $existingTables));

if (count($existingRequired) === count($requiredTables)) {
    fwrite(STDOUT, "Database initialization skipped: required tables already exist.\n");
    exit(0);
}

if (count($existingRequired) > 0) {
    fwrite(STDERR, "Database initialization stopped: only some required tables exist. Manual review is required.\n");
    exit(1);
}

$root = dirname(__DIR__, 2);
foreach (['database/production_schema.sql', 'database/production_seed.sql'] as $relativePath) {
    $sqlPath = $root . '/' . $relativePath;
    $sql = file_get_contents($sqlPath);
    if ($sql === false) {
        fwrite(STDERR, "Database initialization failed: missing {$relativePath}.\n");
        exit(1);
    }

    foreach (split_sql_statements($sql) as $statement) {
        $pdo->exec($statement);
    }
}

fwrite(STDOUT, "Database initialization performed: schema and seed data imported.\n");

function split_sql_statements(string $sql): array
{
    $statements = [];
    $current = '';
    $quote = null;
    $length = strlen($sql);

    for ($i = 0; $i < $length; $i++) {
        $char = $sql[$i];
        $current .= $char;

        if (($char === "'" || $char === '"') && ($i === 0 || $sql[$i - 1] !== '\\')) {
            $quote = $quote === $char ? null : ($quote ?? $char);
        }

        if ($char === ';' && $quote === null) {
            $statement = trim(substr($current, 0, -1));
            if ($statement !== '') {
                $statements[] = $statement;
            }
            $current = '';
        }
    }

    $tail = trim($current);
    if ($tail !== '') {
        $statements[] = $tail;
    }

    return $statements;
}
