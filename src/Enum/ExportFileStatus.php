<?php

declare(strict_types=1);

namespace Atta\ExportableEntityBundle\Enum;

enum ExportFileStatus: string
{
    case Processing = 'processing';
    case Done = 'done';
    case Error = 'error';
}
