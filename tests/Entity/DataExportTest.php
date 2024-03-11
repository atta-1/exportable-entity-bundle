<?php

declare(strict_types=1);

namespace Atta\ExportableEntityBundle\Tests\Entity;

use Atta\ExportableEntityBundle\Entity\DataExport;
use Doctrine\ORM\Mapping\Entity;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class DataExportTest extends TestCase
{
    /**
     * @covers \Atta\ExportableEntityBundle\Entity\DataExport
     */
    public function testInstance(): void
    {
        $class = new ReflectionClass(DataExport::class);
        $classAttrs = $class->getAttributes(Entity::class);

        // confirm that class has Entity attribute to call events
        self::assertCount(1, $classAttrs);
    }
}
