<?php

declare(strict_types=1);

namespace Atta\ExportableEntityBundle\Enum;

enum ExportExcelStatus: string
{
    case Processing = 'processing';
    case Done = 'done';
    case Error = 'error';
}
