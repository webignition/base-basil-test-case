<?php

declare(strict_types=1);

namespace webignition\BaseBasilTestCase;

use PHPUnit\Framework\Test;

interface BasilTestCaseInterface extends Test
{
    public static function setBasilTestPath(string $testPath): void;
    public function setBasilStepName(string $stepName): void;
    public static function getBasilTestPath(): string;
    public function getBasilStepName(): string;
    public function getStatus(): int;
}
