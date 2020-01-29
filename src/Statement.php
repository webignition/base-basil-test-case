<?php

declare(strict_types=1);

namespace webignition\BaseBasilTestCase;

class Statement implements StatementInterface
{
    private const TYPE_ACTION = 'action';
    private const TYPE_ASSERTION = 'assertion';

    private $type;
    private $content;

    private function __construct(string $type, string $content)
    {
        $this->type = $type;
        $this->content = $content;
    }

    public static function createAction(string $content): Statement
    {
        return new Statement(self::TYPE_ACTION, $content);
    }

    public static function createAssertion(string $content): Statement
    {
        return new Statement(self::TYPE_ASSERTION, $content);
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getContent(): string
    {
        return $this->content;
    }
}
