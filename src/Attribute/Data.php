<?php

declare(strict_types=1);

namespace webignition\BaseBasilTestCase\Attribute;

#[\Attribute(\Attribute::TARGET_METHOD)]
readonly class Data
{
    /**
     * @param array<int, array<int|string, scalar>> $data
     */
    public function __construct(public array $data) {}
}
