<?php

declare(strict_types=1);

namespace webignition\BaseBasilTestCase\Message;

use webignition\BaseBasilTestCase\Enum\StatementStage;
use webignition\BasilModels\Model\Statement\StatementInterface;

readonly class Message implements \Stringable
{
    /**
     * @param null|array<mixed> $context
     */
    public function __construct(
        private ?StatementInterface $statement,
        private StatementStage $statementStage,
        private ?\Throwable $throwable,
        private ?array $context,
        private bool|int|string|null $expectedValue = null,
        private bool|int|string|null $examinedValue = null,
    ) {}

    public function __toString(): string
    {
        return (string) json_encode($this->getData(), JSON_PRETTY_PRINT);
    }

    public function withExpectedValue(bool|int|string $value): self
    {
        return new Message(
            $this->statement,
            $this->statementStage,
            $this->throwable,
            $this->context,
            $value,
            $this->examinedValue,
        );
    }

    public function withExaminedValue(bool|int|string $value): self
    {
        return new Message(
            $this->statement,
            $this->statementStage,
            $this->throwable,
            $this->context,
            $this->expectedValue,
            $value,
        );
    }

    /**
     * @return array<mixed>
     */
    public function getData(): array
    {
        $data = [
            'statement' => $this->statement?->jsonSerialize() ?? null,
            'stage' => $this->statementStage->value,
        ];

        if ($this->throwable instanceof \Throwable) {
            $data['exception'] = [
                'class' => $this->throwable::class,
                'code' => $this->throwable->getCode(),
                'message' => $this->throwable->getMessage(),
            ];
        }

        if (is_array($this->context) && [] !== $this->context) {
            $data['context'] = $this->context;
        }

        if (null !== $this->expectedValue && null !== $this->examinedValue) {
            $data['expected'] = $this->expectedValue;
            $data['examined'] = $this->examinedValue;
        }

        return $data;
    }
}
