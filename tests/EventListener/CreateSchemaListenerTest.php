<?php

declare(strict_types=1);

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\EntityAuditBundle\Tests\EventListener;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\MySQL80Platform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Tools\Event\GenerateSchemaTableEventArgs;
use PHPUnit\Framework\TestCase;
use SimpleThings\EntityAudit\AuditConfiguration;
use SimpleThings\EntityAudit\AuditManager;
use SimpleThings\EntityAudit\EventListener\CreateSchemaListener;

final class CreateSchemaListenerTest extends TestCase
{
    private const ENUM_TYPE = 'create_schema_listener_test_enum';

    public function testEnumConvertedToStringGetsAVarcharLength(): void
    {
        if (!Type::hasType(self::ENUM_TYPE)) {
            Type::getTypeRegistry()->register(self::ENUM_TYPE, new class extends Type {
                public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
                {
                    return "ENUM('open', 'closed')";
                }

                public function getName(): string
                {
                    return 'create_schema_listener_test_enum';
                }
            });
        }

        $platform = new MySQL80Platform();
        $config = AuditConfiguration::forEntities([\stdClass::class]);
        $config->setConvertEnumToString(true);
        $config->setDatabasePlatform($platform);

        $classMetadata = new ClassMetadata(\stdClass::class);
        $classMetadata->mapField(['fieldName' => 'id', 'type' => Types::INTEGER, 'id' => true]);
        $classMetadata->mapField(['fieldName' => 'status', 'type' => self::ENUM_TYPE]);

        $schema = new Schema();
        $table = $schema->createTable('entity');
        $table->addColumn('id', Types::INTEGER);
        $table->addColumn('status', self::ENUM_TYPE);
        $table->setPrimaryKey(['id']);

        (new CreateSchemaListener(new AuditManager($config)))->postGenerateSchemaTable(
            new GenerateSchemaTableEventArgs($classMetadata, $schema, $table)
        );

        $auditTable = $schema->getTable('entity_audit');
        static::assertSame(255, $auditTable->getColumn('status')->getLength());
        static::assertStringContainsString('status VARCHAR(255)', implode(';', $platform->getCreateTableSQL($auditTable)));
    }
}
