<?php

declare(strict_types=1);

namespace webignition\BaseBasilTestCase\AssertionMessage;

use webignition\BasilModels\Model\StatementInterface;

readonly class AssertionMessage implements \Stringable
{
    public function __construct(
        private StatementInterface $statement,
        private ?ExpectedExaminedValues $expectedExaminedValues = null,
    ) {}

    public function __toString(): string
    {
        $data = [
            'statement' => $this->statement->jsonSerialize(),
        ];

        if ($this->expectedExaminedValues instanceof ExpectedExaminedValues) {
            $data['expected'] = $this->expectedExaminedValues->expected;
            $data['examined'] = $this->expectedExaminedValues->examined;
        }

        return (string) json_encode($data, JSON_PRETTY_PRINT);
    }
}
