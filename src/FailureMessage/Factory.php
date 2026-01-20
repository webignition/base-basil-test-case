<?php

declare(strict_types=1);

namespace webignition\BaseBasilTestCase\FailureMessage;

use webignition\BaseBasilTestCase\Enum\StatementStage;
use webignition\BasilModels\Model\StatementFactory;
use webignition\BasilModels\Model\UnknownEncapsulatedStatementException;
use webignition\SymfonyDomCrawlerNavigator\Exception\InvalidLocatorException;

readonly class Factory
{
    public function __construct(
        private StatementFactory $statementFactory,
    ) {}

    public static function createFactory(): Factory
    {
        return new Factory(
            StatementFactory::createFactory(),
        );
    }

    public function create(
        string $statementJson,
        StatementStage $statementStage,
        \Throwable $throwable,
    ): FailureMessage {
        $context = [];
        if ($throwable instanceof InvalidLocatorException) {
            $context = $throwable->getContext();
        }

        $statement = null;

        try {
            $statement = $this->statementFactory->createFromJson($statementJson);
        } catch (UnknownEncapsulatedStatementException $unknownEncapsulatedStatementException) {
            $context = ['statement_json' => $statementJson];
            $throwable = $unknownEncapsulatedStatementException;
        }

        return new FailureMessage($statement, $statementStage, $throwable, $context);
    }
}
