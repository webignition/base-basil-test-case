<?php

declare(strict_types=1);

namespace webignition\BaseBasilTestCase\Tests\Unit\FailureMessage;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use webignition\BaseBasilTestCase\Enum\FailureReason;
use webignition\BaseBasilTestCase\FailureMessage\FailureMessage;
use webignition\BasilModels\Parser\ActionParser;
use webignition\BasilModels\Parser\AssertionParser;

class FailureMessageTest extends TestCase
{
    #[DataProvider('toStringDataProvider')]
    public function testToString(FailureMessage $failureMessage, string $expected): void
    {
        $json = (string) $failureMessage;

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
            'assertion setup failed, no context' => [
                'failureMessage' => new FailureMessage(
                    $assertionParser->parse('$".selector" exists', 0),
                    FailureReason::ASSERTION_SETUP_FAILED,
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
                        "reason": "assertion-setup-failed",
                        "exception": {
                            "class": "RuntimeException",
                            "code": 123,
                            "message": "assertion setup failed, no context message"
                        },
                        "context": []
                    }
                    EOD,
            ],
            'assertion setup failed, has context' => [
                'failureMessage' => new FailureMessage(
                    $assertionParser->parse('$".selector" exists', 0),
                    FailureReason::ASSERTION_SETUP_FAILED,
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
                        "reason": "assertion-setup-failed",
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
                'failureMessage' => new FailureMessage(
                    $actionParser->parse('click $".selector"', 0),
                    FailureReason::ACTION_SETUP_FAILED,
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
                        "reason": "action-setup-failed",
                        "exception": {
                            "class": "RuntimeException",
                            "code": 123,
                            "message": "action setup failed, no context message"
                        },
                        "context": []
                    }
                    EOD,
            ],
            'action failed, no context' => [
                'failureMessage' => new FailureMessage(
                    $actionParser->parse('click $".selector"', 0),
                    FailureReason::ACTION_FAILED,
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
                        "reason": "action-failed",
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
