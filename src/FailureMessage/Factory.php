<?php

declare(strict_types=1);

namespace webignition\BaseBasilTestCase\FailureMessage;

use webignition\BaseBasilTestCase\Enum\StatementStage;
use webignition\BasilModels\Model\StatementInterface;
use webignition\SymfonyDomCrawlerNavigator\Exception\InvalidLocatorException;

readonly class Factory
{
    public function create(
        StatementInterface $statement,
        StatementStage $statementStage,
        \Throwable $throwable,
    ): FailureMessage {
        $context = [];
        if ($throwable instanceof InvalidLocatorException) {
            $context = $throwable->getContext();
        }

        return new FailureMessage($statement, $statementStage, $throwable, $context);
    }
}
