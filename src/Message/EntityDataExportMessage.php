<?php

declare(strict_types=1);

namespace Atta\ExportableEntityBundle\Message;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Parameter;

final class EntityDataExportMessage
{
    /**
     * @param class-string                    $entityClass
     * @param ArrayCollection<int, Parameter> $parameters
     */
    public function __construct(
        private readonly string $entityClass,
        private readonly string $dql,
        private readonly ArrayCollection $parameters,
        private readonly string $filename,
    ) {
    }

    /** @return class-string */
    public function getEntityClass(): string
    {
        return $this->entityClass;
    }

    public function getDql(): string
    {
        return $this->dql;
    }

    /** @return ArrayCollection<int, Parameter> $parameters */
    public function getParameters(): ArrayCollection
    {
        return $this->parameters;
    }

    public function getFilename(): string
    {
        return $this->filename;
    }
}
