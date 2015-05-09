<?php

/**
 * Migration runner, usage:
 *
 * php migrate.php [version number] [environment]
 *
 * php migrate.php => upgrading db to newest version
 * php migrate.php [version number] => upgrading/downgrading database to specified version number
 * php migrate.php recreate => reset database and migrate to newest version
 *
 * @author Radosław Benkel
 * @author Marcin Kurczewski
 */

try {
    $config = include __DIR__ . '/db_config.php';
    $dsn = $config['db.adapter'] . '://' .
        $config['db.username'] . ':' .
        $config['db.password'] . '@' .
        $config['db.host'] . '/' .
        $config['db.dbname'];

    require_once __DIR__ . '/../library/Doctrine/Doctrine.php';
    spl_autoload_register(array('Doctrine', 'autoload'));
    $conn = Doctrine_Manager::connection($dsn);

    if (isset($argv[1]) and $argv[1] == 'recreate') {
        foreach ($conn->fetchColumn("SELECT tablename FROM pg_tables WHERE tableowner = '{$config['db.username']}' AND schemaname = 'public'") as $tableName) {
            $conn->exec("DROP TABLE {$tableName} CASCADE");
        }
    }

    $migration = new Doctrine_Migration(__DIR__ . '/../application/migrations/classes', $conn);
    $migration->setTableName('doctrine_migration_version');

    if (isset($argv[1]) and $argv[1] == 'recreate') {
        $classesKeys = array_keys($migration->getMigrationClasses());
        $version = array_pop($classesKeys);
    } else if (isset($argv[1])) {
        $version = intval($argv[1]);
    } else {
        $classesKeys = array_keys($migration->getMigrationClasses());
        $version = array_pop($classesKeys);
    }

    if ($migration->getCurrentVersion() == $version) {
        echo 'Already at version ' . $version . PHP_EOL;
    } else {
        $migration->migrate($version);
        $conn->close();
        echo 'Migrated succesfully to version ' . $migration->getCurrentVersion() . PHP_EOL;
    }

} catch (Exception $e) {
    die($e->getMessage());
}

