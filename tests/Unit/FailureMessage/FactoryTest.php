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
use webignition\BasilModels\Model\StatementInterface;
use webignition\BasilModels\Parser\ActionParser;
use webignition\BasilModels\Parser\AssertionParser;
use webignition\SymfonyDomCrawlerNavigator\Exception\InvalidLocatorException;

class FactoryTest extends TestCase
{
    #[DataProvider('createDataProvider')]
    public function testCreate(
        StatementInterface $statement,
        StatementStage $stage,
        \Throwable $throwable,
        FailureMessage $expected,
    ): void {
        self::assertEquals($expected, new Factory()->create($statement, $stage, $throwable));
    }

    /**
     * @return array<mixed>
     */
    public static function createDataProvider(): array
    {
        $actionParser = ActionParser::create();
        $assertionParser = AssertionParser::create();

        $assertion = $assertionParser->parse('$".selector" exists', 0);
        $runtimeException = new \RuntimeException('assertion setup failed, no context message', 123);

        return [
            'assertion setup failed' => [
                'statement' => $assertion,
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
                'statement' => $assertion,
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
                'statement' => $assertion,
                'stage' => StatementStage::EXECUTE,
                'throwable' => $runtimeException,
                'expected' => new FailureMessage(
                    $assertion,
                    StatementStage::EXECUTE,
                    $runtimeException,
                    []
                ),
            ],
        ];
    }

    /**
     * @return array<mixed>
     */
    public static function toStringDataProvider(): array
    {
        $actionParser = ActionParser::create();
        $assertionParser = AssertionParser::create();

        return [
            'action setup failed, no context' => [
                'failureMessage' => new FailureMessage(
                    $actionParser->parse('click $".selector"', 0),
                    StatementStage::SETUP,
                    new \RuntimeException('action setup failed, no context message', 123),
                    []
                ),
                'expected' => <<<'EOD'
                    {
                        "statement": {
                            "statement-type": "action",
                            "source": "click $\".selector\"",
                            "index": 0,
                            "identifier": "$\".selector\"",
                            "type": "click",
                            "arguments": "$\".selector\""
                        },
                        "stage": "setup",
                        "exception": {
                            "class": "RuntimeException",
                            "code": 123,
                            "message": "action setup failed, no context message"
                        },
                        "context": []
                    }
                    EOD,
            ],
            'action execution failed, no context' => [
                'failureMessage' => new FailureMessage(
                    $actionParser->parse('click $".selector"', 0),
                    StatementStage::EXECUTE,
                    new \RuntimeException('action setup failed, no context message', 456),
                    []
                ),
                'expected' => <<<'EOD'
                    {
                        "statement": {
                            "statement-type": "action",
                            "source": "click $\".selector\"",
                            "index": 0,
                            "identifier": "$\".selector\"",
                            "type": "click",
                            "arguments": "$\".selector\""
                        },
                        "stage": "execute",
                        "exception": {
                            "class": "RuntimeException",
                            "code": 456,
                            "message": "action setup failed, no context message"
                        },
                        "context": []
                    }
                    EOD,
            ],
        ];
    }
}
