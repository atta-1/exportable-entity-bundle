<?php

declare(strict_types=1);

namespace Atta\ExportableEntityBundle\Tests\Attribute;

use Atta\ExportableEntityBundle\Attribute\Exportable;
use Attribute;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class ExportableTest extends TestCase
{
    /**
     * @covers \Atta\ExportableEntityBundle\Attribute\Exportable
     */
    public function testInstance(): void
    {
        $class = new ReflectionClass(Exportable::class);
        $classAttrs = $class->getAttributes(Attribute::class);

        // confirm that class has "Attribute" attribute
        self::assertCount(1, $classAttrs);

        $entityAttr = $classAttrs[0];
        self::assertSame($entityAttr->getArguments()['flags'], Attribute::TARGET_PROPERTY);
    }
}
