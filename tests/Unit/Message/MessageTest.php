<?php

declare(strict_types=1);

namespace Unit\Message;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use webignition\BaseBasilTestCase\Enum\StatementStage;
use webignition\BaseBasilTestCase\Message\Message;
use webignition\BasilModels\Model\Assertion\DerivedValueOperationAssertion;
use webignition\BasilModels\Parser\ActionParser;
use webignition\BasilModels\Parser\AssertionParser;

class MessageTest extends TestCase
{
    #[DataProvider('toStringDataProvider')]
    public function testToString(Message $message, string $expected): void
    {
        $json = (string) $message;

        self::assertSame($expected, $json);

        json_decode($json, true);
        self::assertSame(JSON_ERROR_NONE, json_last_error());
    }

    /**
     * @return array<mixed>
     */
    public static function toStringDataProvider(): array
    {
        $actionParser = ActionParser::create();
        $assertionParser = AssertionParser::create();

        return [
            'regular exists assertion, no expected/examined values' => [
                'message' => new Message(
                    $assertionParser->parse('$".selector" exists', 0),
                    StatementStage::EXECUTE,
                    null,
                    null,
                ),
                'expected' => <<<'EOD'
                    {
                        "statement": {
                            "statement-type": "assertion",
                            "source": "$\".selector\" exists",
                            "index": 0,
                            "identifier": "$\".selector\"",
                            "operator": "exists"
                        },
                        "stage": "execute"
                    }
                    EOD,
            ],
            'regular exists assertion, boolean expected/examined values' => [
                'message' => new Message(
                    $assertionParser->parse('$".selector" exists', 0),
                    StatementStage::EXECUTE,
                    null,
                    null,
                )->withExpectedValue(true)
                    ->withExaminedValue(false),
                'expected' => <<<'EOD'
                    {
                        "statement": {
                            "statement-type": "assertion",
                            "source": "$\".selector\" exists",
                            "index": 0,
                            "identifier": "$\".selector\"",
                            "operator": "exists"
                        },
                        "stage": "execute",
                        "expected": true,
                        "examined": false
                    }
                    EOD,
            ],
            'derived exists assertion, no expected/examined values' => [
                'message' => new Message(
                    new DerivedValueOperationAssertion(
                        $assertionParser->parse('$".selector".attribute_name exists', 0),
                        '$".selector"',
                        'exists',
                    ),
                    StatementStage::EXECUTE,
                    null,
                    null,
                ),
                'expected' => <<<'EOD'
                    {
                        "statement": {
                            "container": {
                                "value": "$\".selector\"",
                                "operator": "exists",
                                "type": "derived-value-operation-assertion"
                            },
                            "statement": {
                                "statement-type": "assertion",
                                "source": "$\".selector\".attribute_name exists",
                                "index": 0,
                                "identifier": "$\".selector\".attribute_name",
                                "operator": "exists"
                            }
                        },
                        "stage": "execute"
                    }
                    EOD,
            ],
            'is assertion, has expected/examined values' => [
                'message' => new Message(
                    $assertionParser->parse('$".selector" is "expected value"', 0),
                    StatementStage::EXECUTE,
                    null,
                    null,
                    'expected value',
                    'examined value',
                ),
                'expected' => <<<'EOD'
                    {
                        "statement": {
                            "statement-type": "assertion",
                            "source": "$\".selector\" is \"expected value\"",
                            "index": 0,
                            "identifier": "$\".selector\"",
                            "value": "\"expected value\"",
                            "operator": "is"
                        },
                        "stage": "execute",
                        "expected": "expected value",
                        "examined": "examined value"
                    }
                    EOD,
            ],
            'assertion setup failed, no context' => [
                'message' => new Message(
                    $assertionParser->parse('$".selector" exists', 0),
                    StatementStage::SETUP,
                    new \RuntimeException('assertion setup failed, no context message', 123),
                    []
                ),
                'expected' => <<<'EOD'
                    {
                        "statement": {
                            "statement-type": "assertion",
                            "source": "$\".selector\" exists",
                            "index": 0,
                            "identifier": "$\".selector\"",
                            "operator": "exists"
                        },
                        "stage": "setup",
                        "exception": {
                            "class": "RuntimeException",
                            "code": 123,
                            "message": "assertion setup failed, no context message"
                        }
                    }
                    EOD,
            ],
            'assertion setup failed, has context' => [
                'message' => new Message(
                    $assertionParser->parse('$".selector" exists', 0),
                    StatementStage::SETUP,
                    new \RuntimeException('assertion setup failed, has context message', 456),
                    [
                        'context1' => 'value1',
                        'context2' => 789,
                    ]
                ),
                'expected' => <<<'EOD'
                    {
                        "statement": {
                            "statement-type": "assertion",
                            "source": "$\".selector\" exists",
                            "index": 0,
                            "identifier": "$\".selector\"",
                            "operator": "exists"
                        },
                        "stage": "setup",
                        "exception": {
                            "class": "RuntimeException",
                            "code": 456,
                            "message": "assertion setup failed, has context message"
                        },
                        "context": {
                            "context1": "value1",
                            "context2": 789
                        }
                    }
                    EOD,
            ],
            'action setup failed, no context' => [
                'message' => new Message(
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
                        }
                    }
                    EOD,
            ],
            'action execution failed, no context' => [
                'message' => new Message(
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
                        }
                    }
                    EOD,
            ],
        ];
    }
}
