<?php

declare(strict_types=1);

namespace Unit\Message;

use Facebook\WebDriver\Exception\InvalidSelectorException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use SmartAssert\DomIdentifier\ElementIdentifier;
use webignition\BaseBasilTestCase\Enum\StatementStage;
use webignition\BaseBasilTestCase\Message\Factory;
use webignition\BaseBasilTestCase\Message\Message;
use webignition\BasilModels\Model\Assertion\DerivedValueOperationAssertion;
use webignition\BasilModels\Model\InvalidStatementDataException;
use webignition\BasilModels\Parser\AssertionParser;
use webignition\SymfonyDomCrawlerNavigator\Exception\InvalidLocatorException;

class FactoryTest extends TestCase
{
    #[DataProvider('createFailureMessageDataProvider')]
    public function testCreateFailureMessage(
        string $statementJson,
        StatementStage $stage,
        \Throwable $throwable,
        Message $expected,
    ): void {
        $factory = Factory::createFactory();

        self::assertEquals($expected, $factory->createFailureMessage($statementJson, $throwable, $stage));
    }

    /**
     * @return array<mixed>
     */
    public static function createFailureMessageDataProvider(): array
    {
        $assertionParser = AssertionParser::create();

        $assertion = $assertionParser->parse('$".selector" exists', 0);
        $runtimeException = new \RuntimeException('assertion setup failed, no context message', 123);

        return [
            'assertion setup failed' => [
                'statementJson' => (string) json_encode($assertion->jsonSerialize()),
                'stage' => StatementStage::SETUP,
                'throwable' => $runtimeException,
                'expected' => new Message(
                    $assertion,
                    StatementStage::SETUP,
                    $runtimeException,
                    null
                ),
            ],
            'assertion setup failed, invalid locator' => [
                'statementJson' => (string) json_encode($assertion->jsonSerialize()),
                'stage' => StatementStage::SETUP,
                'throwable' => new InvalidLocatorException(
                    new ElementIdentifier('.selector'),
                    new InvalidSelectorException(''),
                ),
                'expected' => new Message(
                    $assertion,
                    StatementStage::SETUP,
                    new InvalidLocatorException(
                        new ElementIdentifier('.selector'),
                        new InvalidSelectorException(''),
                    ),
                    [
                        'locator' => '.selector',
                        'type' => 'css',
                    ]
                ),
            ],
            'assertion execution failed' => [
                'statementJson' => (string) json_encode($assertion->jsonSerialize()),
                'stage' => StatementStage::EXECUTE,
                'throwable' => $runtimeException,
                'expected' => new Message(
                    $assertion,
                    StatementStage::EXECUTE,
                    $runtimeException,
                    null,
                ),
            ],
            'invalid statement json' => [
                'statementJson' => 'invalid statement json',
                'stage' => StatementStage::SETUP,
                'throwable' => new InvalidStatementDataException('invalid statement json'),
                'expected' => new Message(
                    null,
                    StatementStage::SETUP,
                    new InvalidStatementDataException('invalid statement json'),
                    [
                        'statement_json' => 'invalid statement json',
                    ]
                ),
            ],
        ];
    }

    #[DataProvider('createAssertionMessageDataProvider')]
    public function testCreateAssertionMessage(
        string $statementJson,
        bool|int|string $expectedValue,
        bool|int|string $examinedValue,
        Message $expected,
    ): void {
        $factory = Factory::createFactory();

        self::assertEquals($expected, $factory->createAssertionMessage($statementJson, $expectedValue, $examinedValue));
    }

    /**
     * @return array<mixed>
     */
    public static function createAssertionMessageDataProvider(): array
    {
        $assertionParser = AssertionParser::create();

        $existsAssertion = $assertionParser->parse('$".selector" exists', 0);
        $derivedAssertion = new DerivedValueOperationAssertion(
            $assertionParser->parse('$".selector".attribute_name exists', 0),
            '$".selector"',
            'exists',
        );
        $isAssertion = $assertionParser->parse('$".selector" is "expected value"', 0);

        return [
            'regular exists assertion' => [
                'statementJson' => (string) json_encode($existsAssertion->jsonSerialize()),
                'expectedValue' => true,
                'examinedValue' => false,
                'expected' => new Message(
                    $existsAssertion,
                    StatementStage::EXECUTE,
                    null,
                    null,
                )
                    ->withExpectedValue(true)
                    ->withExaminedValue(false),
            ],
            'derived exists assertion' => [
                'statementJson' => (string) json_encode($derivedAssertion->jsonSerialize()),
                'expectedValue' => false,
                'examinedValue' => true,
                'expected' => new Message(
                    $derivedAssertion,
                    StatementStage::EXECUTE,
                    null,
                    null,
                )
                    ->withExpectedValue(false)
                    ->withExaminedValue(true),
            ],
            'is assertion, string expected/examined values' => [
                'statementJson' => (string) json_encode($isAssertion->jsonSerialize()),
                'expectedValue' => 'expected value',
                'examinedValue' => 'examined value',
                'expected' => new Message(
                    $isAssertion,
                    StatementStage::EXECUTE,
                    null,
                    null,
                    'expected value',
                    'examined value',
                ),
            ],
            'is assertion, integer expected/examined values' => [
                'statementJson' => (string) json_encode($isAssertion->jsonSerialize()),
                'expectedValue' => 123,
                'examinedValue' => 456,
                'expected' => new Message(
                    $isAssertion,
                    StatementStage::EXECUTE,
                    null,
                    null,
                    123,
                    456,
                ),
            ],
            'invalid statement json' => [
                'statementJson' => 'invalid statement json',
                'expectedValue' => 'expected value',
                'examinedValue' => 'examined value',
                'expected' => new Message(
                    null,
                    StatementStage::EXECUTE,
                    new InvalidStatementDataException('invalid statement json'),
                    [
                        'statement_json' => 'invalid statement json',
                    ]
                )
                    ->withExpectedValue('expected value')
                    ->withExaminedValue('examined value'),
            ],
        ];
    }
}
