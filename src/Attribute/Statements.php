<?php

declare(strict_types=1);

namespace webignition\BaseBasilTestCase\Attribute;

use webignition\BasilModels\Model\Action\ActionInterface;
use webignition\BasilModels\Model\Assertion\AssertionInterface;

/**
 * @phpstan-import-type SerializedAction from ActionInterface
 * @phpstan-import-type SerializedAssertion from AssertionInterface
 */
#[\Attribute(\Attribute::TARGET_METHOD)]
readonly class Statements
{
    /**
     * @param array<SerializedAction|SerializedAssertion> $statements
     */
    public function __construct(public array $statements) {}
}
