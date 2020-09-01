<?php

declare(strict_types=1);

namespace webignition\BaseBasilTestCase;

use PHPUnit\Framework\Test;
use webignition\BasilModels\DataSet\DataSetInterface;
use webignition\BasilModels\StatementInterface;
use webignition\DomElementIdentifier\ElementIdentifierInterface;

interface BasilTestCaseInterface extends Test
{
    public function setBasilStepName(string $stepName): void;
    public function getBasilStepName(): string;

    public function getStatus(): int;

    /**
     * @return StatementInterface[]
     */
    public function getHandledStatements(): array;

    public function setExaminedValue(?string $examinedValue): void;
    public function setExpectedValue(?string $expectedValue): void;

    public function getExaminedValue(): ?string;
    public function getExpectedValue(): ?string;

    public function setBooleanExaminedValue(bool $examinedValue): void;
    public function setBooleanExpectedValue(bool $expectedValue): void;

    public function getBooleanExaminedValue(): ?bool;
    public function getBooleanExpectedValue(): ?bool;

    public function getExaminedElementIdentifier(): ?ElementIdentifierInterface;
    public function getExpectedElementIdentifier(): ?ElementIdentifierInterface;

    public static function staticSetLastException(\Throwable $exception): void;
    public function setLastException(\Throwable $exception): void;
    public static function staticGetLastException(): ?\Throwable;
    public function getLastException(): ?\Throwable;

    public function setCurrentDataSet(?DataSetInterface $dataSet): void;
    public function getCurrentDataSet(): ?DataSetInterface;

    public static function setClientManager(ClientManager $clientManager): void;
}
