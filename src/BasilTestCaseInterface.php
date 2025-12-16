<?php

declare(strict_types=1);

namespace webignition\BaseBasilTestCase;

use PHPUnit\Framework\Test;

interface BasilTestCaseInterface extends Test
{
    public function getStatus(): int;

    public static function setClientManager(ClientManager $clientManager): void;
}
