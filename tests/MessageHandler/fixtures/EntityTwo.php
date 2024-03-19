<?php

declare(strict_types=1);

namespace Atta\ExportableEntityBundle\Tests\MessageHandler\fixtures;

class EntityTwo
{
    public function __construct(
        private readonly EntityThree $entityThree = new EntityThree(),
        private readonly string $title = 'EntityTwo',
    ) {
    }

    public function getEntityThree(): EntityThree
    {
        return $this->entityThree;
    }

    public function getTitle(): string
    {
        return $this->title;
    }
}
