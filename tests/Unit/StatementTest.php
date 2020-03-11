<?php

declare(strict_types=1);

namespace webignition\BaseBasilTestCase\Tests\Unit;

use PHPUnit\Framework\TestCase;
use webignition\BaseBasilTestCase\Statement;
use webignition\BaseBasilTestCase\StatementInterface;

class StatementTest extends TestCase
{
    /**
     * @dataProvider createActionDataProvider
     */
    public function testCreateAction(string $content, ?StatementInterface $sourceStatement)
    {
        $statement = Statement::createAction($content, $sourceStatement);

        $this->assertSame('action', $statement->getType());
        $this->assertSame($content, $statement->getContent());
        $this->assertSame($sourceStatement, $statement->getSourceStatement());
    }

    public function createActionDataProvider(): array
    {
        return [
            'no source statement' => [
                'content' => 'wait 1',
                'sourceStatement' => null,
            ],
            'has source statement' => [
                'content' => '$".selector" exists',
                'sourceStatement' => Statement::createAction('click $".selector"'),
            ],
        ];
    }

    /**
     * @dataProvider createAssertionDataProvider
     */
    public function testCreateAssertion(string $content, ?StatementInterface $sourceStatement)
    {
        $statement = Statement::createAssertion($content, $sourceStatement);

        $this->assertSame('assertion', $statement->getType());
        $this->assertSame($content, $statement->getContent());
        $this->assertSame($sourceStatement, $statement->getSourceStatement());
    }

    public function createAssertionDataProvider(): array
    {
        return [
            'no source statement' => [
                'content' => '$".selector" exists',
                'sourceStatement' => null,
            ],
            'has source statement' => [
                'content' => '$".selector" exists',
                'sourceStatement' => Statement::createAction('$".selector" is "value"'),
            ],
        ];
    }
}
