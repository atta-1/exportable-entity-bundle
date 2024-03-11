<?php

declare(strict_types=1);

namespace Atta\ExportableEntityBundle\Tests\MessageHandler;

use Atta\ExportableEntityBundle\Attribute\Exportable;
use Atta\ExportableEntityBundle\Entity\DataExport;
use Atta\ExportableEntityBundle\Enum\ExportFileStatus;
use Atta\ExportableEntityBundle\Message\EntityDataExportMessage;
use Atta\ExportableEntityBundle\MessageHandler\EntityDataExportMessageHandler;
use Atta\ExportableEntityBundle\Tests\MessageHandler\fixtures\EntityOne;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemOperator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function PHPUnit\Framework\assertEquals;

#[CoversClass(EntityDataExportMessageHandler::class)]
class EntityDataExportMessageHandlerTest extends TestCase
{
    public function testGetExportablePropertiesWhenArgIsEmpty(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $filesystemOperator = $this->createMock(FilesystemOperator::class);
        $class = new class() {
            public function __construct(
                #[Exportable]
                public readonly string $a = '',
            ) {
            }
        };
        $target = new class($entityManager, $filesystemOperator) extends EntityDataExportMessageHandler {
            public function getExportableProperties(string $className): array
            {
                return parent::getExportableProperties($className);
            }
        };
        self::assertEquals(['a'], $target->getExportableProperties($class::class));
    }

    public function testGetExportablePropertiesWhenArgIsNotEmpty(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $filesystemOperator = $this->createMock(FilesystemOperator::class);
        $class = new class() {
            public function __construct(
                #[Exportable(['b'])]
                public readonly string $a = '',
            ) {
            }
        };
        $target = new class($entityManager, $filesystemOperator) extends EntityDataExportMessageHandler {
            public function getExportableProperties(string $className): array
            {
                return parent::getExportableProperties($className);
            }
        };

        self::assertEquals(['a.b'], $target->getExportableProperties($class::class));
    }

    public function testGetExportablePropertiesWhenNamedArgUsed(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $filesystemOperator = $this->createMock(FilesystemOperator::class);
        $class = new class() {
            public function __construct(
                #[Exportable(properties: ['b'])]
                public readonly string $a = '',
            ) {
            }
        };
        $target = new class($entityManager, $filesystemOperator) extends EntityDataExportMessageHandler {
            public function getExportableProperties(string $className): array
            {
                return parent::getExportableProperties($className);
            }
        };
        self::assertEquals(['a.b'], $target->getExportableProperties($class::class));
    }

    public function testGetHeader(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $filesystemOperator = $this->createMock(FilesystemOperator::class);

        $target = new class($entityManager, $filesystemOperator) extends EntityDataExportMessageHandler {
            public function getHeaders(array $properties): array
            {
                return parent::getHeaders($properties);
            }
        };

        self::assertEquals(
            ['Id', 'EntityTwo Title', 'EntityTwo EntityThree Name'],
            $target->getHeaders(['id', 'entityTwo.title', 'entityTwo.entityThree.name']),
        );
    }

    public function testNestedPropertyAccess(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $filesystemOperator = $this->createMock(Filesystem::class);

        $target = new class($entityManager, $filesystemOperator) extends EntityDataExportMessageHandler {
            protected function writeRow($fileStream, array $data): void
            {
                static $iteration = -1;
                ++$iteration;

                if ($iteration === 0) {
                    assertEquals(['EntityTwo EntityThree Title', 'Title'], $data);
                } else {
                    assertEquals(['EntityThree', 'EntityOne'], $data);
                }
            }

            protected function save($fileStream, string $tempFile, string $fileName): void
            {
            }
        };

        $message = new EntityDataExportMessage(
            EntityOne::class,
            'SELECT * FROM EntityTwo',
            new ArrayCollection(),
            'filename',
        );

        $filesystemOperator->expects($this->once())
            ->method('publicUrl')
            ->with('filename')
            ->willReturn('http://example.com/filename')
        ;

        $entityManager->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(DataExport::class))
        ;

        $entityManager->expects($this->once())
            ->method('find')
            ->with(DataExport::class, self::anything())
            ->willReturn($entity = new DataExport())
        ;

        $entityManager->expects($this->exactly(2))->method('flush');
        $entityManager->expects($this->once())->method('clear');

        $query = $this->createMock(Query::class);

        $entityManager->expects($this->once())
            ->method('createQuery')
            ->willReturn($query)
        ;

        $entityOne = new EntityOne();

        $query->expects($this->once())
            ->method('toIterable')
            ->willReturn([$entityOne]);

        $target->__invoke($message);

        self::assertEquals(ExportFileStatus::Done, $entity->getStatus());
    }
}
