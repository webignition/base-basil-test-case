<?php

declare(strict_types=1);

namespace webignition\BaseBasilTestCase;

interface StatementInterface
{
    public function getType(): string;
    public function getContent(): string;
    public function getSourceStatement(): ?StatementInterface;
}
