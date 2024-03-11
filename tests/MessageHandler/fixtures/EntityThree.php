<?php

declare(strict_types=1);

namespace Atta\ExportableEntityBundle\Tests\MessageHandler\fixtures;

class EntityThree
{
    private string $title = 'EntityThree';

    public function getTitle(): string
    {
        return $this->title;
    }
}
