<?php

declare(strict_types=1);

namespace webignition\BaseBasilTestCase\Tests\Unit\AssertionMessage;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use webignition\BaseBasilTestCase\AssertionMessage\AssertionMessage;
use webignition\BaseBasilTestCase\AssertionMessage\ExpectedExaminedValues;
use webignition\BasilModels\Model\Assertion\DerivedValueOperationAssertion;
use webignition\BasilModels\Parser\AssertionParser;

class AssertionMessageTest extends TestCase
{
    #[DataProvider('toStringDataProvider')]
    public function testToString(AssertionMessage $assertionMessage, string $expected): void
    {
        $json = (string) $assertionMessage;

        self::assertSame($expected, $json);

        json_decode($json, true);
        self::assertSame(JSON_ERROR_NONE, json_last_error());
    }

    /**
     * @return array<mixed>
     */
    public static function toStringDataProvider(): array
    {
        $assertionParser = AssertionParser::create();

        return [
            'regular exists assertion, no expected/examined values' => [
                'assertionMessage' => new AssertionMessage(
                    $assertionParser->parse('$".selector" exists', 0),
                ),
                'expected' => <<<'EOD'
                    {
                        "statement": {
                            "statement-type": "assertion",
                            "source": "$\".selector\" exists",
                            "index": 0,
                            "identifier": "$\".selector\"",
                            "operator": "exists"
                        }
                    }
                    EOD,
            ],
            'derived exists assertion, no expected/examined values' => [
                'assertionMessage' => new AssertionMessage(
                    new DerivedValueOperationAssertion(
                        $assertionParser->parse('$".selector".attribute_name exists', 0),
                        '$".selector"',
                        'exists',
                    ),
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
                        }
                    }
                    EOD,
            ],
            'is assertion, no expected/examined values' => [
                'assertionMessage' => new AssertionMessage(
                    $assertionParser->parse('$".selector" is "expected value"', 0),
                    new ExpectedExaminedValues('expected value', 'examined value'),
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
                        "expected": "expected value",
                        "examined": "examined value"
                    }
                    EOD,
            ],
        ];
    }
}
