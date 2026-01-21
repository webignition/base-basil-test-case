<?php

declare(strict_types=1);

namespace webignition\BaseBasilTestCase\Message;

use webignition\BaseBasilTestCase\Enum\StatementStage;
use webignition\BasilModels\Model\StatementFactory;
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

    public function createFailureMessage(
        string $statementJson,
        \Throwable $throwable,
        StatementStage $statementStage,
    ): Message {
        $statement = null;
        $context = null;
        if ($throwable instanceof InvalidLocatorException) {
            $context = $throwable->getContext();
        }

        try {
            $statement = $this->statementFactory->createFromJson($statementJson);
        } catch (\Throwable $throwable) {
            $context = ['statement_json' => $statementJson];
        }

        return new Message($statement, $statementStage, $throwable, $context);
    }

    public function createAssertionMessage(
        string $statementJson,
        int|string|null $expected,
        int|string|null $examined,
    ): Message {
        $statement = null;
        $context = null;
        $throwable = null;

        try {
            $statement = $this->statementFactory->createFromJson($statementJson);
        } catch (\Throwable $throwable) {
            $context = ['statement_json' => $statementJson];
        }

        $message = new Message($statement, StatementStage::EXECUTE, $throwable, $context);
        if (null !== $expected && null !== $examined) {
            $message = $message
                ->withExpectedValue($expected)
                ->withExaminedValue($examined)
            ;
        }

        return $message;
    }
}
