<?php

declare(strict_types=1);

namespace webignition\BaseBasilTestCase\Tests\Unit\FailureMessage;

use Facebook\WebDriver\Exception\InvalidSelectorException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use SmartAssert\DomIdentifier\ElementIdentifier;
use webignition\BaseBasilTestCase\Enum\StatementStage;
use webignition\BaseBasilTestCase\FailureMessage\Factory;
use webignition\BaseBasilTestCase\FailureMessage\FailureMessage;
use webignition\BasilModels\Model\InvalidStatementDataException;
use webignition\BasilModels\Parser\AssertionParser;
use webignition\SymfonyDomCrawlerNavigator\Exception\InvalidLocatorException;

class FactoryTest extends TestCase
{
    #[DataProvider('createDataProvider')]
    public function testCreate(
        string $statementJson,
        StatementStage $stage,
        \Throwable $throwable,
        FailureMessage $expected,
    ): void {
        $factory = Factory::createFactory();

        self::assertEquals($expected, $factory->create($statementJson, $stage, $throwable));
    }

    /**
     * @return array<mixed>
     */
    public static function createDataProvider(): array
    {
        $assertionParser = AssertionParser::create();

        $assertion = $assertionParser->parse('$".selector" exists', 0);
        $runtimeException = new \RuntimeException('assertion setup failed, no context message', 123);

        return [
            'assertion setup failed' => [
                'statementJson' => (string) json_encode($assertion->jsonSerialize()),
                'stage' => StatementStage::SETUP,
                'throwable' => $runtimeException,
                'expected' => new FailureMessage(
                    $assertion,
                    StatementStage::SETUP,
                    $runtimeException,
                    []
                ),
            ],
            'assertion setup failed, invalid locator' => [
                'statementJson' => (string) json_encode($assertion->jsonSerialize()),
                'stage' => StatementStage::SETUP,
                'throwable' => new InvalidLocatorException(
                    new ElementIdentifier('.selector'),
                    new InvalidSelectorException(''),
                ),
                'expected' => new FailureMessage(
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
                'expected' => new FailureMessage(
                    $assertion,
                    StatementStage::EXECUTE,
                    $runtimeException,
                    []
                ),
            ],
            'invalid statement json' => [
                'statementJson' => 'invalid statement json',
                'stage' => StatementStage::SETUP,
                'throwable' => new InvalidStatementDataException('invalid statement json'),
                'expected' => new FailureMessage(
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
}
