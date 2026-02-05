<?php

declare(strict_types=1);

namespace webignition\BaseBasilTestCase\Attribute;

#[\Attribute(\Attribute::TARGET_METHOD)]
readonly class DataNames
{
    /**
     * @param string[] $names
     */
    public function __construct(public array $names) {}
}
