<?php

declare(strict_types=1);

namespace webignition\BaseBasilTestCase\Enum;

enum StatementStage: string
{
    case EXECUTE = 'execute';
    case SETUP = 'setup';
}
