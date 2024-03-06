<?php

declare(strict_types=1);

namespace Atta\ExportableEntityBundle\MessageHandler;

use Atta\ExportableEntityBundle\Attribute\Exportable;
use Atta\ExportableEntityBundle\Entity\DataExport;
use Atta\ExportableEntityBundle\Enum\ExportFileStatus;
use Atta\ExportableEntityBundle\Helper\ReflectionHelper;
use Atta\ExportableEntityBundle\Message\EntityDataExportMessage;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\Proxy;
use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\String\UnicodeString;

#[AsMessageHandler(handles: EntityDataExportMessage::class)]
class EntityDataExportMessageHandler
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        #[Autowire('@export_files')]
        private readonly FilesystemOperator $azureBlobFiles,
    ) {
    }

    public function __invoke(EntityDataExportMessage $message): void
    {
        $dataExport = $this->createDataExport($message->getFilename());
        $properties = $this->getExportableProperties($message->getEntityClass());

        $header = array_map(
            static function (string $property) {
                $relationPropertyParts = explode('.', $property);
                /** @var string $property */
                $property = end($relationPropertyParts);

                /** @var string[] $propertyAsWords */
                $propertyAsWords = preg_split(
                    '/(?=[A-Z])/',
                    (new UnicodeString($property))->title(true)->toString(),
                );

                return implode(' ', array_filter($propertyAsWords));
            },
            $properties,
        );

        $tmpFileName = $this->getTempFileName();
        $fileStream = $this->createFileStream($tmpFileName);

        try {
            $this->writeRow($fileStream, $header);

            $distinctQuery = str_replace('SELECT', 'SELECT DISTINCT', $message->getDql());
            $query = $this->entityManager->createQuery($distinctQuery);
            $query->setParameters($message->getParameters());
            foreach ($query->toIterable() as $entityObject) {
                $entityRowData = [];
                $propertyAccessor = PropertyAccess::createPropertyAccessor();
                foreach ($properties as $property) {
                    $value = $propertyAccessor->getValue($entityObject, $property);
                    // $value = ReflectionHelper::getPropertyValue($entityObject, $property);
                    if ($value instanceof \DateTimeImmutable) {
                        $value = $value->format('Y-m-d H:i:s');
                    }
                    if (is_bool($value)) {
                        $value = $value ? 'Yes' : 'No';
                    }
                    if (is_object($value)) {
                        $value = method_exists($value, '__toString') ? $value->__toString() : null;
                    }
                    if (is_array($value)) {
                        $value = print_r($value, true);
                    }
                    if ($value === null) {
                        $value = '';
                    }

                    $entityRowData[] = $value;
                }

                $this->writeRow($fileStream, $entityRowData);

                $this->entityManager->clear();
            }

            /** @var string $fileName */
            $fileName = $dataExport->getFilename();
            $this->save($fileStream, $tmpFileName, $fileName);

            $this->changeDataExportStatus($dataExport, ExportFileStatus::Done);
        } catch (\Throwable $exception) {
            $this->changeDataExportStatus($dataExport, ExportFileStatus::Error, $exception->getMessage());
            throw $exception;
        }
    }

    private function createDataExport(string $fileName): DataExport
    {
        $entity = (new DataExport())
            ->setStatus(ExportFileStatus::Processing)
            ->setCreatedAt(new \DateTimeImmutable())
            ->setDownloadUrl($this->azureBlobFiles->publicUrl($fileName));

        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        return $entity;
    }

    private function changeDataExportStatus(
        DataExport $dataExportObject,
        ExportFileStatus $status,
        ?string $exceptionMessage = null,
    ): void {
        /** @var DataExport $dataExport */
        $dataExport = $this->entityManager->find(DataExport::class, $dataExportObject->getId());
        $dataExport->setStatus($status);
        $dataExport->setExceptionMessage($exceptionMessage);

        $this->entityManager->flush();
    }

    /**
     * @param class-string $className
     *
     * @return string[]
     */
    private function getExportableProperties(string $className): array
    {
        $return = [];
        $reflectorClass = new \ReflectionClass($className);
        $reflectorProperties = $reflectorClass->getProperties();
        foreach ($reflectorProperties as $reflectorProperty) {
            if ($reflectorProperty->getAttributes(Exportable::class) === []) {
                continue;
            }

            $relationProperties = $reflectorProperty->getAttributes(Exportable::class)[0]->getArguments();
            if (empty($relationProperties)) {
                $return[] = $reflectorProperty->getName();
            } else {
                foreach ($relationProperties['relatedEntityProperties'] as $relationProperty) {
                    $return[] = $reflectorProperty->getName().'.'.$relationProperty;
                }
            }
        }

        return $return;
    }

    /**
     * @param resource                          $fileStream
     * @param array<int, array<string, string>> $data
     */
    private function writeRow($fileStream, array $data): void
    {
        $result = fputcsv($fileStream, $data);

        if ($result === false) {
            throw new \RuntimeException('Cannot write to file stream');
        }
    }

    /**
     * @param resource $fileStream
     *
     * @throws FilesystemException
     */
    private function save($fileStream, string $tempFile, string $fileName): void
    {
        fclose($fileStream);

        $fileStreamNew = fopen($tempFile, 'rb');
        if ($fileStreamNew === false) {
            throw new \RuntimeException('Cannot create file stream');
        }

        $this->azureBlobFiles->writeStream($fileName, $fileStreamNew);

        unlink($tempFile);
    }

    /**
     * @return resource
     */
    private function createFileStream(string $fileName)
    {
        $fileStream = fopen($fileName, 'wb');
        if ($fileStream === false) {
            throw new \RuntimeException('Cannot create file stream');
        }

        return $fileStream;
    }

    private function getTempFileName(): string
    {
        $filename = tempnam(sys_get_temp_dir(), 'entity-data-export');
        if ($filename === false) {
            throw new \RuntimeException('Cannot create temp file');
        }

        return $filename;
    }
}
