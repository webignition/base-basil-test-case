<?php

declare(strict_types=1);

namespace webignition\BaseBasilTestCase;

class Statement implements StatementInterface
{
    private const TYPE_ACTION = 'action';
    private const TYPE_ASSERTION = 'assertion';

    private $type;
    private $content;
    private $sourceStatement;

    private function __construct(string $type, string $content, ?StatementInterface $sourceStatement = null)
    {
        $this->type = $type;
        $this->content = $content;
        $this->sourceStatement = $sourceStatement;
    }

    public static function createAction(string $content, ?StatementInterface $sourceStatement = null): Statement
    {
        return new Statement(self::TYPE_ACTION, $content, $sourceStatement);
    }

    public static function createAssertion(string $content, ?StatementInterface $sourceStatement = null): Statement
    {
        return new Statement(self::TYPE_ASSERTION, $content, $sourceStatement);
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getSourceStatement(): ?StatementInterface
    {
        return $this->sourceStatement;
    }
}
