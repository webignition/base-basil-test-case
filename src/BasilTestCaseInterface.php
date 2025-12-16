<?php

declare(strict_types=1);

namespace webignition\BaseBasilTestCase;

use PHPUnit\Framework\Test;
use webignition\DomElementIdentifier\ElementIdentifierInterface;

interface BasilTestCaseInterface extends Test
{
    public function getStatus(): int;

    public function getExaminedElementIdentifier(): ?ElementIdentifierInterface;

    public function getExpectedElementIdentifier(): ?ElementIdentifierInterface;

    public static function setClientManager(ClientManager $clientManager): void;
}
