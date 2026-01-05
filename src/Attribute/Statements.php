<?php

declare(strict_types=1);

namespace webignition\BaseBasilTestCase\Attribute;

#[\Attribute(\Attribute::TARGET_METHOD)]
readonly class Statements
{
    /**
     * @param string[] $statements
     */
    public function __construct(public array $statements) {}
}
