<?php

declare(strict_types=1);

namespace Atta\ExportableEntityBundle\Entity;

use Atta\ExportableEntityBundle\Enum\ExportFileStatus;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[
    ORM\Entity,
    ORM\Table('data_export'),
]
class DataExport
{
    #[
        ORM\Id,
        ORM\GeneratedValue(strategy: 'IDENTITY'),
        ORM\Column(name: 'data_export_id', type: Types::INTEGER, options: ['unsigned' => true]),
    ]
    private ?int $id = null;

    #[ORM\Column(name: 'download_url', type: Types::STRING, nullable: false)]
    private ?string $downloadUrl;

    #[ORM\Column(name: 'status', type: Types::STRING, length: 50, enumType: ExportFileStatus::class)]
    private ExportFileStatus $status;

    #[ORM\Column(name: 'exception_message', type: Types::TEXT, nullable: true)]
    private ?string $exceptionMessage;

    #[ORM\Column(name: 'created_at', type: Types::DATETIME_IMMUTABLE, nullable: false)]
    private ?DateTimeImmutable $createdAt;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDownloadUrl(): ?string
    {
        return $this->downloadUrl;
    }

    public function setDownloadUrl(string $downloadUrl): self
    {
        $this->downloadUrl = $downloadUrl;

        return $this;
    }

    public function getFilename(): ?string
    {
        return $this->downloadUrl === null ? null : basename($this->downloadUrl);
    }

    public function getStatus(): ExportFileStatus
    {
        return $this->status;
    }

    public function setStatus(ExportFileStatus $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getExceptionMessage(): ?string
    {
        return $this->exceptionMessage;
    }

    public function setExceptionMessage(?string $exceptionMessage): DataExport
    {
        $this->exceptionMessage = $exceptionMessage;

        return $this;
    }

    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
