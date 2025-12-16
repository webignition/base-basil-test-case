<?php

namespace webignition\BaseBasilTestCase\Tests\Functional;

use webignition\BasePantherTestCase\AbstractBrowserTestCase as BaseTestCase;

abstract class AbstractPantherTestCase extends BaseTestCase
{
    public static function setUpBeforeClass(): void
    {
        self::$webServerDir = __DIR__ . '/../Fixtures/html';

        parent::setUpBeforeClass();
    }
}
