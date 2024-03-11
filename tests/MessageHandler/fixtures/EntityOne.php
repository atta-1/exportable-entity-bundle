<?php

declare(strict_types=1);

namespace Atta\ExportableEntityBundle\Tests\MessageHandler\fixtures;

use Atta\ExportableEntityBundle\Attribute\Exportable;

class EntityOne
{
    public function __construct(
        #[Exportable(['entityThree.title'])]
        private readonly EntityTwo $entityTwo = new EntityTwo(),

        #[Exportable]
        private readonly string $title = 'EntityOne',
    ) {
    }

    public function getEntityTwo(): EntityTwo
    {
        return $this->entityTwo;
    }

    public function getTitle(): string
    {
        return $this->title;
    }
}
