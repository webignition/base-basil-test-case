<?php

declare(strict_types=1);

namespace webignition\BaseBasilTestCase\Attribute;

#[\Attribute(\Attribute::TARGET_METHOD)]
readonly class StepName
{
    public function __construct(public string $name) {}
}
