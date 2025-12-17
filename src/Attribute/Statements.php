<?php

declare(strict_types=1);

namespace webignition\BaseBasilTestCase\Attribute;

#[\Attribute(\Attribute::TARGET_METHOD)]
readonly class Statements
{
    /**
     * @param array{'type':'action'|'assertion', 'statement':non-empty-string}[] $statements
     */
    public function __construct(public array $statements) {}
}
