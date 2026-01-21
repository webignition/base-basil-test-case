<?php

declare(strict_types=1);

namespace webignition\BaseBasilTestCase\AssertionMessage;

readonly class ExpectedExaminedValues
{
    public function __construct(
        public int|string $expected,
        public int|string $examined,
    ) {}
}
