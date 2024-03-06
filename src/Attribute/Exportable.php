<?php

declare(strict_types=1);

namespace Atta\ExportableEntityBundle\Attribute;

/**
 * This attribute should be used on associated entity property for association entity (like ProductCategory).
 * This is used to get metadata for associated entity (like column name) to insert generated association.
 */
#[\Attribute(flags: \Attribute::TARGET_PROPERTY)]
class Exportable
{
    public function __construct(
        public array $relatedEntityProperties = [],
    ) {
    }
}
