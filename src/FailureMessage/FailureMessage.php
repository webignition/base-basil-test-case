<?php

declare(strict_types=1);

namespace webignition\BaseBasilTestCase\FailureMessage;

use webignition\BaseBasilTestCase\Enum\FailureReason;
use webignition\BasilModels\Model\StatementInterface;

readonly class FailureMessage implements \Stringable
{
    /**
     * @param array<string, int|string> $context
     */
    public function __construct(
        private StatementInterface $statement,
        private FailureReason $reason,
        private \Throwable $throwable,
        private array $context,
    ) {}

    public function __toString(): string
    {
        return (string) json_encode(
            [
                'statement' => $this->statement->jsonSerialize(),
                'reason' => $this->reason->value,
                'exception' => [
                    'class' => $this->throwable::class,
                    'code' => $this->throwable->getCode(),
                    'message' => $this->throwable->getMessage(),
                ],
                'context' => $this->context,
            ],
            JSON_PRETTY_PRINT
        );
    }
}
