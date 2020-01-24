<?php

declare(strict_types=1);

namespace webignition\BaseBasilTestCase;

use PHPUnit\Framework\Test;

interface BasilTestCaseInterface extends Test
{
    public function setBasilTestPath(string $testPath): void;
    public function setBasilStepName(string $stepName): void;
    public function getBasilTestPath(): string;
    public function getBasilStepName(): string;
}
