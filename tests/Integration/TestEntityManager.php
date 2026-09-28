<?php

declare(strict_types=1);

namespace AlexandreBulete\DddDoctrineBridge\Tests\Integration;

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Tools\DsnParser;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMSetup;
use Doctrine\ORM\Tools\SchemaTool;

/**
 * Entity manager on a real database, with a fresh schema for the fixtures.
 *
 * The database comes from DDD_TEST_DATABASE_URL so that CI can run the same
 * suite against PostgreSQL and MySQL — the platforms disagree on exactly the
 * kind of SQL this bridge generates. Without it, SQLite in memory: fast, but
 * blind to platform-specific failures.
 */
final class TestEntityManager
{
    public static function create(): EntityManagerInterface
    {
        $url = getenv('DDD_TEST_DATABASE_URL');

        $params = (new DsnParser([
            'pdo-sqlite' => 'pdo_sqlite',
            'postgresql' => 'pdo_pgsql',
            'mysql' => 'pdo_mysql',
        ]))->parse(is_string($url) && $url !== '' ? $url : 'pdo-sqlite:///:memory:');

        $config = ORMSetup::createAttributeMetadataConfig([__DIR__ . '/Fixture'], true);
        if (\PHP_VERSION_ID >= 80400) {
            $config->enableNativeLazyObjects(true);
        }

        $em = new EntityManager(DriverManager::getConnection($params, $config), $config);

        $metadata = $em->getMetadataFactory()->getAllMetadata();
        $schemaTool = new SchemaTool($em);
        $schemaTool->dropSchema($metadata);
        $schemaTool->createSchema($metadata);

        return $em;
    }
}
