<?php

declare(strict_types=1);

namespace webignition\BaseBasilTestCase;

use PHPUnit\Framework\Test;
use webignition\DomElementIdentifier\ElementIdentifierInterface;

interface BasilTestCaseInterface extends Test
{
    public function getStatus(): int;

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

    public static function setClientManager(ClientManager $clientManager): void;

    public static function hasException(): bool;
}
