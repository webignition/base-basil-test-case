<?php

declare(strict_types=1);

namespace webignition\BaseBasilTestCase;

use PHPUnit\Framework\Test;

interface BasilTestCaseInterface extends Test
{
    public static function setBasilTestPath(string $testPath): void;
    public static function getBasilTestPath(): string;

    public function setBasilStepName(string $stepName): void;
    public function getBasilStepName(): string;

    public function getStatus(): int;

    /**
     * @return StatementInterface[]
     */
    public function getHandledStatements(): array;

    /**
     * @return mixed|null
     */
    public function getExaminedValue();
    public function getExpectedValue(): ?string;
}
